<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
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

    public function test_warenkorb_seite_ist_erreichbar(): void
    {
        $this->get(route('cart.index'))->assertStatus(200);
    }

    public function test_produkt_wird_in_warenkorb_gelegt(): void
    {
        $product = $this->makeProduct();

        $this->post(route('cart.add', $product))->assertRedirect();

        $this->assertEquals(1, session('cart')[$product->id]['qty']);
    }

    public function test_menge_wird_erhoeht_bei_erneutem_hinzufuegen(): void
    {
        $product = $this->makeProduct();

        $this->post(route('cart.add', $product), ['quantity' => 2]);
        $this->post(route('cart.add', $product), ['quantity' => 1]);

        $this->assertEquals(3, session('cart')[$product->id]['qty']);
    }

    public function test_menge_wird_auf_lagerbestand_begrenzt(): void
    {
        $product = $this->makeProduct(stock: 3);

        // mehr als verfügbar → fehlermeldung, kein eintrag im warenkorb
        $response = $this->post(route('cart.add', $product), ['quantity' => 99]);
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_produkt_kann_aus_warenkorb_entfernt_werden(): void
    {
        $product = $this->makeProduct();
        $this->post(route('cart.add', $product));

        $this->delete(route('cart.remove', $product));

        $this->assertArrayNotHasKey($product->id, session('cart', []));
    }

    public function test_warenkorb_kann_geleert_werden(): void
    {
        $product = $this->makeProduct();
        $this->post(route('cart.add', $product));

        $this->delete(route('cart.clear'));

        $this->assertEmpty(session('cart', []));
    }
}
