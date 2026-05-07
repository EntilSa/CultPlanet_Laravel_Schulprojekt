<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// diese testklasse prüft den session-basierten warenkorb:
// produkte hinzufügen, mengen erhöhen, lagerbestand-begrenzung, entfernen, leeren
class CartTest extends TestCase
{
    use RefreshDatabase;

    // $seed = true: seeder laufen vor den tests (rollen anlegen)
    protected bool $seed = true;

    // hilfsmethode: testprodukt mit konfigurierbarem lagerbestand anlegen
    private function makeProduct(int $stock = 5): Product
    {
        return Product::create([
            'name' => 'Test Produkt', 'description' => 'Beschreibung',
            'price' => 9.99, 'stock' => $stock,
        ]);
    }

    // prüft: die warenkorb-seite ist öffentlich erreichbar (kein login nötig)
    // assertStatus(200): server antwortet mit "ok"
    public function test_warenkorb_seite_ist_erreichbar(): void
    {
        $this->get(route('cart.index'))->assertStatus(200);
    }

    // prüft: produkt wird korrekt in den warenkorb gelegt
    // session('cart'): warenkorb ist ein php-array in der session des browsers
    // [$product->id]['qty']: zugriff auf die menge des produkts im warenkorb-array
    public function test_produkt_wird_in_warenkorb_gelegt(): void
    {
        $product = $this->makeProduct();

        $this->post(route('cart.add', $product))->assertRedirect();

        // prüfen ob das produkt mit menge 1 in der session-variable 'cart' ist
        $this->assertEquals(1, session('cart')[$product->id]['qty']);
    }

    // prüft: wird dasselbe produkt nochmal hinzugefügt, erhöht sich nur die menge
    // statt zwei einträge gibt es einen eintrag mit qty=3
    public function test_menge_wird_erhoeht_bei_erneutem_hinzufuegen(): void
    {
        $product = $this->makeProduct();

        $this->post(route('cart.add', $product), ['quantity' => 2]); // erst 2 stück
        $this->post(route('cart.add', $product), ['quantity' => 1]); // dann 1 stück dazu

        // gesamtmenge muss 3 sein – nicht 2 einträge
        $this->assertEquals(3, session('cart')[$product->id]['qty']);
    }

    // prüft: man kann nicht mehr bestellen als auf lager ist
    // stock=3, bestellung von 99 → fehlermeldung, kein eintrag im warenkorb
    public function test_menge_wird_auf_lagerbestand_begrenzt(): void
    {
        $product = $this->makeProduct(stock: 3); // nur 3 stück verfügbar

        // 99 stück bestellen – viel mehr als vorrätig
        $response = $this->post(route('cart.add', $product), ['quantity' => 99]);
        $response->assertRedirect();
        // assertSessionHas('error'): eine fehlermeldung muss in der session sein
        $response->assertSessionHas('error');
    }

    // prüft: einzelnes produkt kann aus dem warenkorb entfernt werden
    // assertArrayNotHasKey(): der schlüssel (produkt-id) darf nicht mehr im array sein
    public function test_produkt_kann_aus_warenkorb_entfernt_werden(): void
    {
        $product = $this->makeProduct();
        $this->post(route('cart.add', $product)); // zuerst hinzufügen

        // dann entfernen (delete-anfrage)
        $this->delete(route('cart.remove', $product));

        // produkt-id darf nicht mehr als schlüssel im cart-array sein
        $this->assertArrayNotHasKey($product->id, session('cart', []));
    }

    // prüft: der gesamte warenkorb kann geleert werden
    // assertEmpty(): das array muss komplett leer sein
    public function test_warenkorb_kann_geleert_werden(): void
    {
        $product = $this->makeProduct();
        $this->post(route('cart.add', $product)); // erst befüllen

        $this->delete(route('cart.clear')); // dann komplett leeren

        // warenkorb-array muss leer sein
        $this->assertEmpty(session('cart', []));
    }
}
