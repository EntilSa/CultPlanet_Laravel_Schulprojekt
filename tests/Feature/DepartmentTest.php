<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// diese testklasse prüft die mitarbeiterverwaltung:
// bereiche anlegen/löschen, mitarbeiter zuweisen/entfernen, warnsystem
class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    // setUp() wird vor JEDEM einzelnen test ausgeführt – wie eine aufräum-routine
    // hier nötig weil andere testklassen ($seed=true) bereiche anlegen die uns stören würden
    protected function setUp(): void
    {
        parent::setUp(); // erst die eltern-methode aufrufen (wichtig!)

        // andere testklassen laufen den seeder der bereiche anlegt – die hier wegräumen
        // sonst würde z.b. 'Lager' doppelt existieren und unique-constraints verletzt werden
        DB::table('department_user')->delete(); // erst die verknüpfungstabelle leeren
        Department::query()->delete();          // dann die bereiche selbst löschen

        // mitarbeiter-rolle anlegen – ohne sie crasht der controller bei User::role('mitarbeiter')
        Role::firstOrCreate(['name' => 'mitarbeiter', 'guard_name' => 'web']);
    }

    // -------------------------------------------------------------------
    // Hilfsmethoden
    // -------------------------------------------------------------------

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));

        return $admin;
    }

    private function makeMitarbeiter(): User
    {
        $ma = User::factory()->create();
        $ma->assignRole(Role::firstOrCreate(['name' => 'mitarbeiter', 'guard_name' => 'web']));

        return $ma;
    }

    private function makeKunde(): User
    {
        $kunde = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));

        return $kunde;
    }

    // -------------------------------------------------------------------
    // Übersichtsseite
    // -------------------------------------------------------------------

    // prüft: bereiche-seite ist nur für admins zugänglich
    public function test_bereiche_seite_ist_nur_fuer_admin_erreichbar(): void
    {
        // gast → login
        $this->get(route('admin.departments.index'))->assertRedirect(route('login'));

        // kunde → 403
        $this->actingAs($this->makeKunde())
            ->get(route('admin.departments.index'))
            ->assertStatus(403);
    }

    // prüft: admin sieht die bereiche-übersicht mit dem richtigen inhalt
    // assertSee('Lager'): der text 'Lager' muss irgendwo im html vorkommen
    public function test_admin_sieht_bereiche_uebersicht(): void
    {
        Department::create(['name' => 'Lager']);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.departments.index'))
            ->assertStatus(200)
            ->assertSee('Lager');
    }

    // -------------------------------------------------------------------
    // Bereich anlegen
    // -------------------------------------------------------------------

    // prüft: admin kann einen neuen bereich anlegen
    // assertDatabaseHas(): der datensatz muss in der departments-tabelle existieren
    public function test_admin_kann_bereich_anlegen(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.departments.store'), ['name' => 'Kasse'])
            ->assertRedirect();

        $this->assertDatabaseHas('departments', ['name' => 'Kasse']);
    }

    // prüft: bereich ohne namen wird abgelehnt (name ist pflichtfeld)
    public function test_bereich_anlegen_schlaegt_fehl_ohne_name(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.departments.store'), ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    // prüft: doppelter bereichsname wird abgelehnt (name muss eindeutig sein)
    // 'Lager' existiert bereits → zweiter versuch muss fehlschlagen
    public function test_bereich_anlegen_schlaegt_fehl_bei_doppeltem_namen(): void
    {
        Department::create(['name' => 'Lager']);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.departments.store'), ['name' => 'Lager'])
            ->assertSessionHasErrors('name');
    }

    // -------------------------------------------------------------------
    // Bereich löschen
    // -------------------------------------------------------------------

    // prüft: admin kann einen bereich löschen
    // assertDatabaseMissing(): das gegenteil von assertDatabaseHas – datensatz darf nicht existieren
    public function test_admin_kann_bereich_loeschen(): void
    {
        $admin   = $this->makeAdmin();
        $bereich = Department::create(['name' => 'Altlager']);

        $this->actingAs($admin)
            ->delete(route('admin.departments.destroy', $bereich))
            ->assertRedirect();

        $this->assertDatabaseMissing('departments', ['name' => 'Altlager']);
    }

    // -------------------------------------------------------------------
    // Mitarbeiter zuweisen / entfernen
    // -------------------------------------------------------------------

    // prüft: mitarbeiter kann einem bereich zugewiesen werden
    // $bereich->users()->where(...)->exists(): prüft ob die many-to-many verknüpfung existiert
    public function test_mitarbeiter_kann_bereich_zugewiesen_werden(): void
    {
        $admin   = $this->makeAdmin();
        $ma      = $this->makeMitarbeiter();
        $bereich = Department::create(['name' => 'Verkauf']);

        $this->actingAs($admin)
            ->post(route('admin.departments.addUser', $bereich), ['user_id' => $ma->id])
            ->assertRedirect();

        // prüfen ob die verknüpfung in der pivot-tabelle 'department_user' existiert
        $this->assertTrue($bereich->users()->where('users.id', $ma->id)->exists());
    }

    // prüft: ein normaler kunde kann NICHT einem bereich zugewiesen werden
    // nur nutzer mit mitarbeiter-rolle dürfen in bereiche
    public function test_kunde_kann_nicht_bereich_zugewiesen_werden(): void
    {
        $admin   = $this->makeAdmin();
        $kunde   = $this->makeKunde();
        $bereich = Department::create(['name' => 'Verkauf']);

        $this->actingAs($admin)
            ->post(route('admin.departments.addUser', $bereich), ['user_id' => $kunde->id])
            ->assertSessionHasErrors('user_id');
    }

    // prüft: mitarbeiter kann aus einem bereich entfernt werden
    // attach(): many-to-many verknüpfung direkt erstellen (ohne controller)
    // assertFalse(): die verknüpfung darf danach nicht mehr existieren
    public function test_mitarbeiter_kann_aus_bereich_entfernt_werden(): void
    {
        $admin   = $this->makeAdmin();
        $ma      = $this->makeMitarbeiter();
        $bereich = Department::create(['name' => 'Lager']);
        $bereich->users()->attach($ma->id); // direkt verknüpfen ohne den controller

        $this->actingAs($admin)
            ->delete(route('admin.departments.removeUser', [$bereich, $ma]))
            ->assertRedirect();

        // verknüpfung muss weg sein
        $this->assertFalse($bereich->users()->where('users.id', $ma->id)->exists());
    }

    // -------------------------------------------------------------------
    // Warnsystem
    // -------------------------------------------------------------------

    // prüft: ein bereich ohne mitarbeiter löst eine sichtbare warnung aus
    // assertSee('Unbesetzt') und assertSee('Achtung'): diese texte müssen im html stehen
    public function test_warnung_wird_angezeigt_fuer_unbesetzte_bereiche(): void
    {
        Department::create(['name' => 'Kasse']); // kein mitarbeiter zugewiesen → unbesetzt

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.departments.index'))
            ->assertSee('Unbesetzt')
            ->assertSee('Achtung');
    }

    // prüft: wenn alle bereiche besetzt sind, gibt es keine warnung
    // assertDontSee(): das gegenteil von assertSee – text darf nicht vorkommen
    public function test_keine_warnung_wenn_alle_bereiche_besetzt(): void
    {
        $ma      = $this->makeMitarbeiter();
        $bereich = Department::create(['name' => 'Lager']);
        $bereich->users()->attach($ma->id); // mitarbeiter zuweisen

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.departments.index'))
            ->assertDontSee('Achtung'); // keine warnung erwartet
    }
}
