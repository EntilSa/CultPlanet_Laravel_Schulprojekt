<?php

namespace Tests\Feature;

use App\Mail\BestellstatusGeaendertMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// diese testklasse prüft den kompletten admin-bereich:
// zugriffskontrolle, bestellverwaltung, nutzerverwaltung und mail-versand
class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    // -------------------------------------------------------------------
    // Hilfsmethoden – nutzer mit bestimmter rolle anlegen
    // -------------------------------------------------------------------

    // admin-nutzer anlegen und einloggen
    private function loginAsAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));

        return $admin;
    }

    // mitarbeiter-nutzer anlegen
    private function loginAsMitarbeiter(): User
    {
        $ma = User::factory()->create();
        $ma->assignRole(Role::firstOrCreate(['name' => 'mitarbeiter', 'guard_name' => 'web']));

        return $ma;
    }

    // kunden-nutzer anlegen
    private function loginAsKunde(): User
    {
        $kunde = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));

        return $kunde;
    }

    // -------------------------------------------------------------------
    // Zugriffskontrolle
    // -------------------------------------------------------------------

    // prüft: das dashboard ist nur für admins erreichbar
    // gast → weiterleitung zum login / kunde → 403 verboten / admin → 200 ok
    public function test_dashboard_ist_nur_fuer_admin_erreichbar(): void
    {
        // nicht eingeloggt → zum login weiterleiten
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        // eingeloggt als kunde → verboten
        $this->actingAs($this->loginAsKunde())
            ->get(route('admin.dashboard'))
            ->assertStatus(403);
    }

    // prüft: admin sieht das dashboard mit dem text "Admin-Dashboard"
    // assertSee(): prüft ob ein bestimmter text im html der antwort vorkommt
    public function test_admin_sieht_dashboard(): void
    {
        $this->actingAs($this->loginAsAdmin())
            ->get(route('admin.dashboard'))
            ->assertStatus(200)
            ->assertSee('Admin-Dashboard');
    }

    // prüft: das dashboard zeigt kennzahlen an (z.b. produkt-count)
    public function test_dashboard_zeigt_kennzahlen(): void
    {
        Product::create([
            'name' => 'Testprodukt', 'description' => 'Test', 'price' => 9.99, 'stock' => 5,
        ]);

        $this->actingAs($this->loginAsAdmin())
            ->get(route('admin.dashboard'))
            ->assertStatus(200)
            ->assertSee('Produkte');
    }

    // prüft: bestellungsliste mit denselben zugriffsregeln wie dashboard
    public function test_bestellungsliste_nur_fuer_admin(): void
    {
        $this->get(route('admin.orders'))->assertRedirect(route('login'));

        $this->actingAs($this->loginAsKunde())
            ->get(route('admin.orders'))
            ->assertStatus(403);

        $this->actingAs($this->loginAsAdmin())
            ->get(route('admin.orders'))
            ->assertStatus(200);
    }

    // -------------------------------------------------------------------
    // Bestellverwaltung
    // -------------------------------------------------------------------

    // prüft: admin kann den status einer bestellung ändern
    // assertDatabaseHas(): der neue status muss in der db stehen
    public function test_admin_kann_bestellstatus_aendern(): void
    {
        $admin = $this->loginAsAdmin();
        $kunde = $this->loginAsKunde();

        $order = Order::create([
            'user_id'         => $kunde->id,
            'vorname'         => 'Max',
            'nachname'        => 'Muster',
            'strasse'         => 'Hauptstraße 1',
            'plz'             => '12345',
            'ort'             => 'Berlin',
            'zahlungsmethode' => 'paypal',
            'total'           => 29.99,
            'status'          => 'bezahlt',
        ]);

        // status per patch-anfrage ändern (patch = teilaktualisierung eines datensatzes)
        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), ['status' => 'versendet'])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'versendet']);
    }

    // prüft: bei statusänderung wird automatisch eine mail an den kunden geschickt
    // Mail::fake(): verhindert echten mailversand – nur aufzeichnen was gesendet worden wäre
    // Mail::assertSent(): prüft ob die mail-klasse mit den richtigen daten aufgerufen wurde
    public function test_statusaenderung_schickt_mail_an_kunden(): void
    {
        Mail::fake(); // ab jetzt werden keine echten mails verschickt

        $admin = $this->loginAsAdmin();
        $kunde = $this->loginAsKunde();

        $order = Order::create([
            'user_id'         => $kunde->id,
            'vorname'         => 'Max',
            'nachname'        => 'Muster',
            'strasse'         => 'Hauptstraße 1',
            'plz'             => '12345',
            'ort'             => 'Berlin',
            'zahlungsmethode' => 'paypal',
            'total'           => 29.99,
            'status'          => 'offen',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), ['status' => 'versendet'])
            ->assertRedirect();

        // prüfen ob die statusänderungs-mail an den kunden geschickt wurde
        // die closure ($mail) prüft ob die mail an die richtige adresse ging
        Mail::assertSent(BestellstatusGeaendertMail::class, function ($mail) use ($kunde) {
            return $mail->hasTo($kunde->email);
        });
    }

    // prüft: ein unbekannter status-wert wird abgelehnt
    // verhindert manipulation – nur die vier erlaubten werte sind gültig
    public function test_ungültiger_status_wird_abgelehnt(): void
    {
        $admin = $this->loginAsAdmin();
        $kunde = $this->loginAsKunde();

        $order = Order::create([
            'user_id'         => $kunde->id,
            'vorname'         => 'Max',
            'nachname'        => 'Muster',
            'strasse'         => 'Hauptstraße 1',
            'plz'             => '12345',
            'ort'             => 'Berlin',
            'zahlungsmethode' => 'paypal',
            'total'           => 10.00,
            'status'          => 'offen',
        ]);

        // 'ungültig' ist kein erlaubter status-wert (nur: offen/bezahlt/versendet/storniert)
        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), ['status' => 'ungültig'])
            ->assertSessionHasErrors('status');
    }

    // -------------------------------------------------------------------
    // Nutzerverwaltung
    // -------------------------------------------------------------------

    // prüft: nutzerliste ist nur für admins sichtbar
    public function test_nutzerliste_nur_fuer_admin(): void
    {
        $this->get(route('admin.users'))->assertRedirect(route('login'));

        $this->actingAs($this->loginAsKunde())
            ->get(route('admin.users'))
            ->assertStatus(403);

        $this->actingAs($this->loginAsAdmin())
            ->get(route('admin.users'))
            ->assertStatus(200);
    }

    // prüft: admin kann einem nutzer eine neue rolle zuweisen
    // fresh(): datensatz neu aus der db laden damit gecachte werte nicht täuschen
    public function test_admin_kann_nutzerrolle_aendern(): void
    {
        $admin = $this->loginAsAdmin();
        $kunde = $this->loginAsKunde();
        Role::firstOrCreate(['name' => 'mitarbeiter', 'guard_name' => 'web']);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $kunde), ['role' => 'mitarbeiter'])
            ->assertRedirect();

        // fresh() lädt den nutzer neu – hasRole() prüft dann den aktuellen stand in der db
        $this->assertTrue($kunde->fresh()->hasRole('mitarbeiter'));
    }

    // -------------------------------------------------------------------
    // Verkaufsübersicht
    // -------------------------------------------------------------------

    // prüft: verkaufsübersicht ist für admin und mitarbeiter zugänglich, nicht für kunden
    public function test_verkaufsuebersicht_fuer_admin_und_mitarbeiter(): void
    {
        $this->get(route('admin.sales'))->assertRedirect(route('login'));

        $this->actingAs($this->loginAsKunde())
            ->get(route('admin.sales'))
            ->assertStatus(403);

        // mitarbeiter darf rein (verkaufsübersicht ist die einzige seite die mitarbeiter sehen)
        $this->actingAs($this->loginAsMitarbeiter())
            ->get(route('admin.sales'))
            ->assertStatus(200);

        $this->actingAs($this->loginAsAdmin())
            ->get(route('admin.sales'))
            ->assertStatus(200);
    }
}
