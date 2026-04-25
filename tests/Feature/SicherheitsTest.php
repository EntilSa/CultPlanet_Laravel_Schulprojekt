<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Sicherheitstests – prüfen ob Daten von anderen Nutzern geschützt sind
 * und ob niemand Systemgrenzen durch manipulierte Anfragen umgehen kann.
 */
class SicherheitsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    // hilfsmethode: fertigen kunden mit bestellung anlegen
    private function kundeAnlegenMitBestellung(): array
    {
        $kunde = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));

        $produkt = Product::create([
            'name' => 'Testsprodukt',
            'description' => 'Nur für Tests.',
            'price' => 9.99,
            'stock' => 10,
        ]);

        $bestellung = Order::create([
            'user_id' => $kunde->id,
            'vorname' => 'Max',
            'nachname' => 'Mustermann',
            'strasse' => 'Musterstraße 1',
            'plz' => '12345',
            'ort' => 'Musterstadt',
            'zahlungsmethode' => 'paypal',
            'total' => 9.99,
            'status' => 'offen',
        ]);

        return [$kunde, $produkt, $bestellung];
    }

    // -------------------------------------------------------------------
    // Fremde Bestellungen einsehen
    // -------------------------------------------------------------------

    public function test_kunde_kann_nicht_fremde_bestellbestaetigung_sehen(): void
    {
        // kunde A legt eine bestellung an
        [, , $bestellung] = $this->kundeAnlegenMitBestellung();

        // kunde B versucht die bestellbestätigung von kunde A zu öffnen
        $kundeB = User::factory()->create();
        $kundeB->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));

        $this->actingAs($kundeB)
            ->get(route('orders.success', $bestellung))
            ->assertStatus(403);
    }

    public function test_gast_wird_bei_bestellbestaetigung_zum_login_weitergeleitet(): void
    {
        [, , $bestellung] = $this->kundeAnlegenMitBestellung();

        // nicht eingeloggter nutzer versucht die bestellbestätigung zu öffnen
        $this->get(route('orders.success', $bestellung))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------
    // Fremde Zahlungsseite aufrufen / Zahlung abschließen
    // -------------------------------------------------------------------

    public function test_kunde_kann_nicht_fremde_zahlungsseite_sehen(): void
    {
        [, , $bestellung] = $this->kundeAnlegenMitBestellung();

        $kundeB = User::factory()->create();
        $kundeB->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));

        $this->actingAs($kundeB)
            ->get(route('orders.payment', $bestellung))
            ->assertStatus(403);
    }

    public function test_kunde_kann_nicht_fremde_zahlung_abschliessen(): void
    {
        [, , $bestellung] = $this->kundeAnlegenMitBestellung();

        $kundeB = User::factory()->create();
        $kundeB->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));

        // kunde B versucht die zahlung von kunde A zu bestätigen
        $this->actingAs($kundeB)
            ->post(route('orders.complete_payment', $bestellung))
            ->assertStatus(403);

        // bestellung ist immer noch "offen" – nicht bezahlt
        $this->assertEquals('offen', $bestellung->fresh()->status);
    }

    // -------------------------------------------------------------------
    // Admin-Bereich ohne Admin-Rolle
    // -------------------------------------------------------------------

    public function test_kunde_kann_nicht_admin_dashboard_aufrufen(): void
    {
        $kunde = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));

        $this->actingAs($kunde)
            ->get(route('admin.dashboard'))
            ->assertStatus(403);
    }

    public function test_mitarbeiter_kann_nicht_nutzerverwaltung_aufrufen(): void
    {
        $mitarbeiter = User::factory()->create();
        $mitarbeiter->assignRole(Role::firstOrCreate(['name' => 'mitarbeiter', 'guard_name' => 'web']));

        // nutzerverwaltung ist nur für admin
        $this->actingAs($mitarbeiter)
            ->get(route('admin.users'))
            ->assertStatus(403);
    }

    public function test_gast_kann_nicht_admin_bereich_aufrufen(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------
    // Manipulation der Zahlungsmethode
    // -------------------------------------------------------------------

    public function test_ungueltige_zahlungsmethode_wird_abgelehnt(): void
    {
        $kunde = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));

        $produkt = Product::create([
            'name' => 'Testsprodukt',
            'description' => 'Nur für Tests.',
            'price' => 9.99,
            'stock' => 5,
        ]);

        // warenkorb befüllen
        $this->actingAs($kunde)->post(route('cart.add', $produkt), ['quantity' => 1]);

        // checkout mit einer zahlungsmethode die nicht erlaubt ist (z.b. bitcoin)
        $this->actingAs($kunde)
            ->post(route('checkout.store'), [
                'vorname' => 'Max',
                'nachname' => 'Mustermann',
                'strasse' => 'Straße 1',
                'plz' => '12345',
                'ort' => 'Stadt',
                'zahlungsmethode' => 'bitcoin',
            ])
            ->assertSessionHasErrors('zahlungsmethode');

        // keine bestellung angelegt
        $this->assertDatabaseCount('orders', 0);
    }

    // -------------------------------------------------------------------
    // Doppelte E-Mail-Adresse bei Registrierung
    // -------------------------------------------------------------------

    public function test_registrierung_schlaegt_fehl_bei_bereits_verwendeter_email(): void
    {
        // erster nutzer mit dieser email
        User::factory()->create(['email' => 'doppelt@example.com']);

        // zweiter versuch mit derselben email
        $this->post(route('register'), [
            'name' => 'Zweiter Nutzer',
            'email' => 'doppelt@example.com',
            'password' => 'geheim123!',
            'password_confirmation' => 'geheim123!',
        ])->assertSessionHasErrors('email');

        // genau ein nutzer mit dieser email – kein duplikat angelegt
        $this->assertEquals(1, User::where('email', 'doppelt@example.com')->count());
    }
}
