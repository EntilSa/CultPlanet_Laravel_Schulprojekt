<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    // hilfsmethode: admin-nutzer anlegen und einloggen
    private function loginAsAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));
        return $admin;
    }

    // hilfsmethode: mitarbeiter-nutzer anlegen
    private function loginAsMitarbeiter(): User
    {
        $ma = User::factory()->create();
        $ma->assignRole(Role::firstOrCreate(['name' => 'mitarbeiter', 'guard_name' => 'web']));
        return $ma;
    }

    // hilfsmethode: kunden-nutzer anlegen
    private function loginAsKunde(): User
    {
        $kunde = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));
        return $kunde;
    }

    public function test_dashboard_ist_nur_fuer_admin_erreichbar(): void
    {
        // gast wird zum login weitergeleitet
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        // kunde bekommt 403
        $this->actingAs($this->loginAsKunde())
            ->get(route('admin.dashboard'))
            ->assertStatus(403);
    }

    public function test_admin_sieht_dashboard(): void
    {
        $this->actingAs($this->loginAsAdmin())
            ->get(route('admin.dashboard'))
            ->assertStatus(200)
            ->assertSee('Admin-Dashboard');
    }

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

    public function test_admin_kann_bestellstatus_aendern(): void
    {
        $admin = $this->loginAsAdmin();
        $kunde = $this->loginAsKunde();

        $order = Order::create([
            'user_id'        => $kunde->id,
            'vorname'        => 'Max',
            'nachname'       => 'Muster',
            'strasse'        => 'Hauptstraße 1',
            'plz'            => '12345',
            'ort'            => 'Berlin',
            'zahlungsmethode'=> 'paypal',
            'total'          => 29.99,
            'status'         => 'bezahlt',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), ['status' => 'versendet'])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'versendet']);
    }

    public function test_ungültiger_status_wird_abgelehnt(): void
    {
        $admin = $this->loginAsAdmin();
        $kunde = $this->loginAsKunde();

        $order = Order::create([
            'user_id'        => $kunde->id,
            'vorname'        => 'Max',
            'nachname'       => 'Muster',
            'strasse'        => 'Hauptstraße 1',
            'plz'            => '12345',
            'ort'            => 'Berlin',
            'zahlungsmethode'=> 'paypal',
            'total'          => 10.00,
            'status'         => 'offen',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), ['status' => 'ungültig'])
            ->assertSessionHasErrors('status');
    }

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

    public function test_admin_kann_nutzerrolle_aendern(): void
    {
        $admin  = $this->loginAsAdmin();
        $kunde  = $this->loginAsKunde();
        Role::firstOrCreate(['name' => 'mitarbeiter', 'guard_name' => 'web']);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $kunde), ['role' => 'mitarbeiter'])
            ->assertRedirect();

        $this->assertTrue($kunde->fresh()->hasRole('mitarbeiter'));
    }

    public function test_verkaufsuebersicht_fuer_admin_und_mitarbeiter(): void
    {
        // gast wird weitergeleitet
        $this->get(route('admin.sales'))->assertRedirect(route('login'));

        // kunde bekommt 403
        $this->actingAs($this->loginAsKunde())
            ->get(route('admin.sales'))
            ->assertStatus(403);

        // mitarbeiter darf rein
        $this->actingAs($this->loginAsMitarbeiter())
            ->get(route('admin.sales'))
            ->assertStatus(200);

        // admin darf auch rein
        $this->actingAs($this->loginAsAdmin())
            ->get(route('admin.sales'))
            ->assertStatus(200);
    }
}
