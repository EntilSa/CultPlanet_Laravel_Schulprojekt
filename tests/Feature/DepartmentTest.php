<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

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

    public function test_bereiche_seite_ist_nur_fuer_admin_erreichbar(): void
    {
        // gast → login
        $this->get(route('admin.departments.index'))->assertRedirect(route('login'));

        // kunde → 403
        $this->actingAs($this->makeKunde())
            ->get(route('admin.departments.index'))
            ->assertStatus(403);
    }

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

    public function test_admin_kann_bereich_anlegen(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.departments.store'), ['name' => 'Kasse'])
            ->assertRedirect();

        $this->assertDatabaseHas('departments', ['name' => 'Kasse']);
    }

    public function test_bereich_anlegen_schlaegt_fehl_ohne_name(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.departments.store'), ['name' => ''])
            ->assertSessionHasErrors('name');
    }

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

    public function test_admin_kann_bereich_loeschen(): void
    {
        $admin = $this->makeAdmin();
        $bereich = Department::create(['name' => 'Altlager']);

        $this->actingAs($admin)
            ->delete(route('admin.departments.destroy', $bereich))
            ->assertRedirect();

        $this->assertDatabaseMissing('departments', ['name' => 'Altlager']);
    }

    // -------------------------------------------------------------------
    // Mitarbeiter zuweisen / entfernen
    // -------------------------------------------------------------------

    public function test_mitarbeiter_kann_bereich_zugewiesen_werden(): void
    {
        $admin = $this->makeAdmin();
        $ma = $this->makeMitarbeiter();
        $bereich = Department::create(['name' => 'Verkauf']);

        $this->actingAs($admin)
            ->post(route('admin.departments.addUser', $bereich), ['user_id' => $ma->id])
            ->assertRedirect();

        $this->assertTrue($bereich->users()->where('users.id', $ma->id)->exists());
    }

    public function test_kunde_kann_nicht_bereich_zugewiesen_werden(): void
    {
        $admin = $this->makeAdmin();
        $kunde = $this->makeKunde();
        $bereich = Department::create(['name' => 'Verkauf']);

        $this->actingAs($admin)
            ->post(route('admin.departments.addUser', $bereich), ['user_id' => $kunde->id])
            ->assertSessionHasErrors('user_id');
    }

    public function test_mitarbeiter_kann_aus_bereich_entfernt_werden(): void
    {
        $admin = $this->makeAdmin();
        $ma = $this->makeMitarbeiter();
        $bereich = Department::create(['name' => 'Lager']);
        $bereich->users()->attach($ma->id);

        $this->actingAs($admin)
            ->delete(route('admin.departments.removeUser', [$bereich, $ma]))
            ->assertRedirect();

        $this->assertFalse($bereich->users()->where('users.id', $ma->id)->exists());
    }

    // -------------------------------------------------------------------
    // Warnsystem
    // -------------------------------------------------------------------

    public function test_warnung_wird_angezeigt_fuer_unbesetzte_bereiche(): void
    {
        Department::create(['name' => 'Kasse']); // kein mitarbeiter → unbesetzt

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.departments.index'))
            ->assertSee('Unbesetzt')
            ->assertSee('Achtung');
    }

    public function test_keine_warnung_wenn_alle_bereiche_besetzt(): void
    {
        $ma = $this->makeMitarbeiter();
        $bereich = Department::create(['name' => 'Lager']);
        $bereich->users()->attach($ma->id);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.departments.index'))
            ->assertDontSee('Achtung');
    }
}
