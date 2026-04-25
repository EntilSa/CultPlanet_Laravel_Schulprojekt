<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function makeProduct(int $stock = 5): Product
    {
        return Product::create([
            'name' => 'Test Produkt', 'description' => 'Beschreibung',
            'price' => 9.99, 'stock' => $stock,
        ]);
    }

    private function checkoutDaten(): array
    {
        return [
            'vorname' => 'Max',
            'nachname' => 'Mustermann',
            'strasse' => 'Musterstraße 1',
            'plz' => '12345',
            'ort' => 'Musterstadt',
            'zahlungsmethode' => 'paypal',
        ];
    }

    public function test_checkout_erfordert_login(): void
    {
        $this->get(route('checkout.index'))->assertRedirect(route('login'));
    }

    public function test_checkout_leitet_weiter_bei_leerem_warenkorb(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('checkout.index'))
            ->assertRedirect(route('cart.index'));
    }

    public function test_bestellung_wird_gespeichert(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        // warenkorb füllen
        $this->actingAs($user)->post(route('cart.add', $product));

        // bestellung aufgeben
        $this->actingAs($user)->post(route('checkout.store'), $this->checkoutDaten());

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'vorname' => 'Max',
            'status' => 'offen',
        ]);
        $this->assertDatabaseHas('order_items', ['name' => 'Test Produkt']);
    }

    public function test_lagerbestand_wird_nach_bestellung_reduziert(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(stock: 5);

        $this->actingAs($user)->post(route('cart.add', $product), ['quantity' => 2]);
        $this->actingAs($user)->post(route('checkout.store'), $this->checkoutDaten());

        $this->assertEquals(3, $product->fresh()->stock);
    }

    public function test_warenkorb_wird_nach_bestellung_geleert(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $this->actingAs($user)->post(route('cart.add', $product));
        $this->actingAs($user)->post(route('checkout.store'), $this->checkoutDaten());

        $this->assertEmpty(session('cart', []));
    }

    public function test_zahlung_setzt_status_auf_bezahlt(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $this->actingAs($user)->post(route('cart.add', $product));
        $this->actingAs($user)->post(route('checkout.store'), $this->checkoutDaten());

        $order = $user->fresh()->orders()->first();
        $this->actingAs($user)->post(route('orders.complete_payment', $order));

        $this->assertEquals('bezahlt', $order->fresh()->status);
    }
}
