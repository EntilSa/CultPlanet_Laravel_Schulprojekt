<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Dieser Test simuliert eine vollständige Kundenreise durch den Shop –
 * von der Registrierung bis zur bezahlten Bestellung und Produktbewertung.
 * Kein Browser nötig – Laravel schickt die HTTP-Anfragen automatisch.
 */
class KundenreiseTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    // hilfsmethode: produkt mit lagerbestand anlegen
    private function makeProduct(string $name = 'LEGO Testset', float $price = 49.99, int $stock = 10): Product
    {
        return Product::create([
            'name'        => $name,
            'description' => 'Ein tolles Spielzeug für die ganze Familie.',
            'price'       => $price,
            'stock'       => $stock,
        ]);
    }

    // -------------------------------------------------------------------
    // Szenario 1: Komplette Bestellung von Registrierung bis Bezahlung
    // -------------------------------------------------------------------

    public function test_kunde_kann_sich_registrieren_und_bestellen(): void
    {
        $produkt = $this->makeProduct();
        $lagerVorher = $produkt->stock;

        // schritt 1: neuer kunde registriert sich
        $this->post(route('register'), [
            'name'                  => 'Max Mustermann',
            'email'                 => 'max@example.com',
            'password'              => 'geheim123!',
            'password_confirmation' => 'geheim123!',
        ])->assertRedirect(route('home'));

        $kunde = User::where('email', 'max@example.com')->first();
        $this->assertNotNull($kunde);
        $this->assertTrue($kunde->hasRole('kunde'));

        // schritt 2: produkt in den warenkorb legen
        $this->actingAs($kunde)
            ->post(route('cart.add', $produkt), ['quantity' => 2])
            ->assertRedirect();

        // schritt 3: warenkorb prüfen
        $this->actingAs($kunde)
            ->get(route('cart.index'))
            ->assertStatus(200)
            ->assertSee($produkt->name)
            ->assertSee('49,99');

        // schritt 4: checkout-seite aufrufen
        $this->actingAs($kunde)
            ->get(route('checkout.index'))
            ->assertStatus(200)
            ->assertSee('Kasse');

        // schritt 5: bestellung absenden (lieferadresse + zahlungsmethode)
        $antwort = $this->actingAs($kunde)
            ->post(route('checkout.store'), [
                'vorname'         => 'Max',
                'nachname'        => 'Mustermann',
                'strasse'         => 'Musterstraße 12',
                'plz'             => '12345',
                'ort'             => 'Musterstadt',
                'zahlungsmethode' => 'paypal',
            ]);

        // weiterleitung zur zahlungsseite erwartet
        $antwort->assertRedirect();

        // schritt 6: bestellung wurde in der datenbank angelegt
        $this->assertDatabaseHas('orders', [
            'user_id'         => $kunde->id,
            'vorname'         => 'Max',
            'nachname'        => 'Mustermann',
            'zahlungsmethode' => 'paypal',
            'status'          => 'offen',
        ]);

        $bestellung = Order::where('user_id', $kunde->id)->first();

        // schritt 7: bestellposition wurde gespeichert
        $this->assertDatabaseHas('order_items', [
            'order_id'   => $bestellung->id,
            'product_id' => $produkt->id,
            'quantity'   => 2,
        ]);

        // schritt 8: lagerbestand wurde reduziert (10 - 2 = 8)
        $this->assertEquals($lagerVorher - 2, $produkt->fresh()->stock);

        // schritt 9: zahlung abschließen (attrappen-button)
        $this->actingAs($kunde)
            ->post(route('orders.complete_payment', $bestellung))
            ->assertRedirect(route('orders.success', $bestellung));

        // schritt 10: bestellung ist jetzt bezahlt
        $this->assertEquals('bezahlt', $bestellung->fresh()->status);

        // schritt 11: bestellbestätigung ist erreichbar
        $this->actingAs($kunde)
            ->get(route('orders.success', $bestellung))
            ->assertStatus(200)
            ->assertSee('Max');
    }

    // -------------------------------------------------------------------
    // Szenario 2: Warenkorb verwalten (hinzufügen, entfernen, leeren)
    // -------------------------------------------------------------------

    public function test_kunde_kann_warenkorb_verwalten(): void
    {
        $kunde    = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));

        $produkt1 = $this->makeProduct('Monopoly Classic', 29.99, 5);
        $produkt2 = $this->makeProduct('UNO Kartenspiel', 9.99, 10);

        // beide produkte in den warenkorb legen
        $this->actingAs($kunde)->post(route('cart.add', $produkt1), ['quantity' => 1]);
        $this->actingAs($kunde)->post(route('cart.add', $produkt2), ['quantity' => 3]);

        // warenkorb hat beide produkte
        $this->actingAs($kunde)
            ->get(route('cart.index'))
            ->assertSee('Monopoly Classic')
            ->assertSee('UNO Kartenspiel');

        // produkt1 entfernen
        $this->actingAs($kunde)
            ->delete(route('cart.remove', $produkt1));

        // nur noch produkt2 im warenkorb
        $this->actingAs($kunde)
            ->get(route('cart.index'))
            ->assertDontSee('Monopoly Classic')
            ->assertSee('UNO Kartenspiel');

        // warenkorb komplett leeren
        $this->actingAs($kunde)->delete(route('cart.clear'));

        // warenkorb ist leer – checkout nicht möglich
        $this->actingAs($kunde)
            ->get(route('checkout.index'))
            ->assertRedirect(route('cart.index'));
    }

    // -------------------------------------------------------------------
    // Szenario 3: Nicht eingeloggter Nutzer wird zum Login weitergeleitet
    // -------------------------------------------------------------------

    public function test_gast_wird_bei_checkout_zum_login_weitergeleitet(): void
    {
        $this->get(route('checkout.index'))
            ->assertRedirect(route('login'));

        $this->post(route('checkout.store'), [])
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------
    // Szenario 4: Checkout mit fehlenden Pflichtfeldern schlägt fehl
    // -------------------------------------------------------------------

    public function test_bestellung_schlaegt_fehl_bei_unvollstaendigem_formular(): void
    {
        $kunde   = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));
        $produkt = $this->makeProduct();

        $this->actingAs($kunde)->post(route('cart.add', $produkt), ['quantity' => 1]);

        // checkout ohne felder absenden
        $this->actingAs($kunde)
            ->post(route('checkout.store'), [])
            ->assertSessionHasErrors(['vorname', 'nachname', 'strasse', 'plz', 'ort', 'zahlungsmethode']);

        // keine bestellung angelegt
        $this->assertDatabaseCount('orders', 0);
    }

    // -------------------------------------------------------------------
    // Szenario 5: Produkt mit 0 Lagerbestand kann nicht bestellt werden
    // -------------------------------------------------------------------

    public function test_ausverkauftes_produkt_kann_nicht_in_warenkorb_gelegt_werden(): void
    {
        $kunde   = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));
        $produkt = $this->makeProduct('Ausverkaufter Artikel', 19.99, 0);

        $this->actingAs($kunde)
            ->post(route('cart.add', $produkt), ['quantity' => 1])
            ->assertSessionHas('error');
    }

    // -------------------------------------------------------------------
    // Szenario 6: Kunde kann Produkt nach dem Kauf bewerten
    // -------------------------------------------------------------------

    public function test_kunde_kann_produkt_nach_kauf_bewerten(): void
    {
        $kunde   = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));
        $produkt = $this->makeProduct();

        // bewertung abgeben
        $this->actingAs($kunde)
            ->post(route('reviews.store', $produkt), [
                'rating' => 5,
                'text'   => 'Super Produkt, mein Kind liebt es!',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'user_id'    => $kunde->id,
            'product_id' => $produkt->id,
            'rating'     => 5,
        ]);
    }

    // -------------------------------------------------------------------
    // Szenario 7: Kunde kann dasselbe Produkt nicht zweimal bewerten
    // -------------------------------------------------------------------

    public function test_kunde_kann_produkt_nicht_zweimal_bewerten(): void
    {
        $kunde   = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));
        $produkt = $this->makeProduct();

        // erste bewertung
        $this->actingAs($kunde)->post(route('reviews.store', $produkt), [
            'rating' => 4,
            'text'   => 'Sehr gut, würde ich empfehlen!',
        ]);

        // zweite bewertung – muss fehlschlagen
        $this->actingAs($kunde)
            ->post(route('reviews.store', $produkt), [
                'rating' => 2,
                'text'   => 'Doch nicht so gut.',
            ])
            ->assertSessionHas('error');

        // nur eine bewertung in der datenbank
        $this->assertDatabaseCount('reviews', 1);
    }

    // -------------------------------------------------------------------
    // Szenario 8: Zwei Kunden kaufen gleichzeitig – Lagerbestand stimmt
    // -------------------------------------------------------------------

    public function test_lagerbestand_wird_korrekt_reduziert_bei_mehreren_kaeufen(): void
    {
        $kunde1  = User::factory()->create();
        $kunde2  = User::factory()->create();
        $kunde1->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));
        $kunde2->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));

        $produkt = $this->makeProduct('Beliebtes Spielzeug', 24.99, 5);

        // kunde1 kauft 3 stück
        $this->actingAs($kunde1)->post(route('cart.add', $produkt), ['quantity' => 3]);
        $this->actingAs($kunde1)->post(route('checkout.store'), [
            'vorname' => 'Kunde', 'nachname' => 'Eins',
            'strasse' => 'Straße 1', 'plz' => '11111', 'ort' => 'Stadt',
            'zahlungsmethode' => 'paypal',
        ]);

        // lagerbestand: 5 - 3 = 2
        $this->assertEquals(2, $produkt->fresh()->stock);

        // kunde2 kauft 2 stück
        $this->actingAs($kunde2)->post(route('cart.add', $produkt), ['quantity' => 2]);
        $this->actingAs($kunde2)->post(route('checkout.store'), [
            'vorname' => 'Kunde', 'nachname' => 'Zwei',
            'strasse' => 'Straße 2', 'plz' => '22222', 'ort' => 'Stadt',
            'zahlungsmethode' => 'sofortueberweisung',
        ]);

        // lagerbestand: 2 - 2 = 0
        $this->assertEquals(0, $produkt->fresh()->stock);

        // 2 bestellungen in der datenbank
        $this->assertDatabaseCount('orders', 2);
    }
}
