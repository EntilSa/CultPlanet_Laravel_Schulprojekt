<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// kundenreise-tests: simulieren eine komplette nutzung des shops ohne browser
// von der registrierung bis zur bewertung – alles in einem test-lauf
// laravel schickt http-anfragen intern, kein echter browser nötig
class KundenreiseTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    // hilfsmethode: testprodukt mit konfigurierbaren werten anlegen
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

    // dieser test simuliert den gesamten weg eines neuen kunden:
    // registrieren → warenkorb → checkout → bezahlen → bestätigung
    public function test_kunde_kann_sich_registrieren_und_bestellen(): void
    {
        $produkt     = $this->makeProduct();
        $lagerVorher = $produkt->stock; // lagerbestand vor der bestellung merken

        // schritt 1: neuer kunde registriert sich
        // post an die registrierungs-route mit den formular-daten
        $this->post(route('register'), [
            'name'                  => 'Max Mustermann',
            'email'                 => 'max@example.com',
            'password'              => 'geheim123!',
            'password_confirmation' => 'geheim123!',
        ])->assertRedirect(route('home')); // nach der registrierung zur startseite

        // nutzer muss jetzt in der datenbank existieren mit der 'kunde'-rolle
        $kunde = User::where('email', 'max@example.com')->first();
        $this->assertNotNull($kunde);
        $this->assertTrue($kunde->hasRole('kunde')); // neue nutzer bekommen automatisch die kunde-rolle

        // schritt 2: produkt in den warenkorb legen
        $this->actingAs($kunde)
            ->post(route('cart.add', $produkt), ['quantity' => 2])
            ->assertRedirect();

        // schritt 3: warenkorb-seite öffnen und inhalte prüfen
        $this->actingAs($kunde)
            ->get(route('cart.index'))
            ->assertStatus(200)
            ->assertSee($produkt->name)
            ->assertSee('49,99'); // preis im deutschen format

        // schritt 4: checkout-seite aufrufen
        $this->actingAs($kunde)
            ->get(route('checkout.index'))
            ->assertStatus(200)
            ->assertSee('Kasse');

        // schritt 5: bestellung absenden mit lieferadresse und zahlungsmethode
        $antwort = $this->actingAs($kunde)
            ->post(route('checkout.store'), [
                'vorname'         => 'Max',
                'nachname'        => 'Mustermann',
                'strasse'         => 'Musterstraße 12',
                'plz'             => '12345',
                'ort'             => 'Musterstadt',
                'zahlungsmethode' => 'paypal',
            ]);

        // weiterleitung zur zahlungsseite erwartet (nicht 200 – das wäre kein redirect)
        $antwort->assertRedirect();

        // schritt 6: bestellung muss in der datenbank gelandet sein
        $this->assertDatabaseHas('orders', [
            'user_id'         => $kunde->id,
            'vorname'         => 'Max',
            'nachname'        => 'Mustermann',
            'zahlungsmethode' => 'paypal',
            'status'          => 'offen', // noch nicht bezahlt
        ]);

        $bestellung = Order::where('user_id', $kunde->id)->first();

        // schritt 7: bestellposition (order_item) muss angelegt worden sein
        $this->assertDatabaseHas('order_items', [
            'order_id'   => $bestellung->id,
            'product_id' => $produkt->id,
            'quantity'   => 2,
        ]);

        // schritt 8: lagerbestand muss um 2 reduziert worden sein (10 - 2 = 8)
        // fresh(): produkt neu aus db laden damit wir den aktuellen lagerbestand sehen
        $this->assertEquals($lagerVorher - 2, $produkt->fresh()->stock);

        // schritt 9: attrappen-zahlung abschließen (button klicken)
        $this->actingAs($kunde)
            ->post(route('orders.complete_payment', $bestellung))
            ->assertRedirect(route('orders.success', $bestellung));

        // schritt 10: bestellung muss jetzt als 'bezahlt' markiert sein
        $this->assertEquals('bezahlt', $bestellung->fresh()->status);

        // schritt 11: bestätigungsseite ist erreichbar und zeigt den kundennamen
        $this->actingAs($kunde)
            ->get(route('orders.success', $bestellung))
            ->assertStatus(200)
            ->assertSee('Max');
    }

    // -------------------------------------------------------------------
    // Szenario 2: Warenkorb verwalten (hinzufügen, entfernen, leeren)
    // -------------------------------------------------------------------

    // prüft: produkte hinzufügen, eines entfernen, alles leeren
    public function test_kunde_kann_warenkorb_verwalten(): void
    {
        $kunde = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));

        $produkt1 = $this->makeProduct('Monopoly Classic', 29.99, 5);
        $produkt2 = $this->makeProduct('UNO Kartenspiel', 9.99, 10);

        // beide produkte in den warenkorb legen
        $this->actingAs($kunde)->post(route('cart.add', $produkt1), ['quantity' => 1]);
        $this->actingAs($kunde)->post(route('cart.add', $produkt2), ['quantity' => 3]);

        // warenkorb hat beide produkte – assertSee prüft den html-inhalt der seite
        $this->actingAs($kunde)
            ->get(route('cart.index'))
            ->assertSee('Monopoly Classic')
            ->assertSee('UNO Kartenspiel');

        // produkt1 aus dem warenkorb entfernen (delete-anfrage)
        $this->actingAs($kunde)
            ->delete(route('cart.remove', $produkt1));

        // nur noch produkt2 im warenkorb – assertDontSee prüft das gegenteil
        $this->actingAs($kunde)
            ->get(route('cart.index'))
            ->assertDontSee('Monopoly Classic')
            ->assertSee('UNO Kartenspiel');

        // warenkorb komplett leeren
        $this->actingAs($kunde)->delete(route('cart.clear'));

        // checkout-seite muss jetzt weiterleiten weil warenkorb leer ist
        $this->actingAs($kunde)
            ->get(route('checkout.index'))
            ->assertRedirect(route('cart.index'));
    }

    // -------------------------------------------------------------------
    // Szenario 3: Nicht eingeloggter Nutzer wird zum Login weitergeleitet
    // -------------------------------------------------------------------

    // prüft: gäste können checkout nicht aufrufen – sie werden zum login geschickt
    // das ist laravels 'auth'-middleware die auf der checkout-route sitzt
    public function test_gast_wird_bei_checkout_zum_login_weitergeleitet(): void
    {
        // kein actingAs() → nutzer ist nicht eingeloggt
        $this->get(route('checkout.index'))
            ->assertRedirect(route('login'));

        $this->post(route('checkout.store'), [])
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------
    // Szenario 4: Checkout mit fehlenden Pflichtfeldern schlägt fehl
    // -------------------------------------------------------------------

    // prüft: ein unvollständiges formular wird abgelehnt – alle felder sind pflicht
    // assertSessionHasErrors([...]): alle genannten felder müssen fehler haben
    public function test_bestellung_schlaegt_fehl_bei_unvollstaendigem_formular(): void
    {
        $kunde = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));
        $produkt = $this->makeProduct();

        $this->actingAs($kunde)->post(route('cart.add', $produkt), ['quantity' => 1]);

        // checkout komplett leer absenden – alle pflichtfelder fehlen
        $this->actingAs($kunde)
            ->post(route('checkout.store'), [])
            ->assertSessionHasErrors(['vorname', 'nachname', 'strasse', 'plz', 'ort', 'zahlungsmethode']);

        // keine bestellung angelegt – validierung hat abgebrochen
        $this->assertDatabaseCount('orders', 0);
    }

    // -------------------------------------------------------------------
    // Szenario 5: Produkt mit 0 Lagerbestand kann nicht bestellt werden
    // -------------------------------------------------------------------

    // prüft: ausverkaufte produkte können nicht in den warenkorb gelegt werden
    // assertSessionHas('error'): eine fehlermeldung muss in der session vorhanden sein
    public function test_ausverkauftes_produkt_kann_nicht_in_warenkorb_gelegt_werden(): void
    {
        $kunde = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));
        $produkt = $this->makeProduct('Ausverkaufter Artikel', 19.99, 0); // stock=0

        $this->actingAs($kunde)
            ->post(route('cart.add', $produkt), ['quantity' => 1])
            ->assertSessionHas('error'); // fehlermeldung statt erfolg
    }

    // -------------------------------------------------------------------
    // Szenario 6: Kunde kann Produkt nach dem Kauf bewerten
    // -------------------------------------------------------------------

    // prüft: bewertung wird in der datenbank gespeichert
    public function test_kunde_kann_produkt_nach_kauf_bewerten(): void
    {
        $kunde = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));
        $produkt = $this->makeProduct();

        $this->actingAs($kunde)
            ->post(route('reviews.store', $produkt), [
                'rating' => 5,
                'text'   => 'Super Produkt, mein Kind liebt es!',
            ])
            ->assertRedirect();

        // bewertung muss in der reviews-tabelle existieren
        $this->assertDatabaseHas('reviews', [
            'user_id'    => $kunde->id,
            'product_id' => $produkt->id,
            'rating'     => 5,
        ]);
    }

    // -------------------------------------------------------------------
    // Szenario 7: Kunde kann dasselbe Produkt nicht zweimal bewerten
    // -------------------------------------------------------------------

    // prüft: eine zweite bewertung desselben produkts wird abgelehnt
    // assertDatabaseCount('reviews', 1): genau eine bewertung darf in der tabelle sein
    public function test_kunde_kann_produkt_nicht_zweimal_bewerten(): void
    {
        $kunde = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));
        $produkt = $this->makeProduct();

        // erste bewertung – wird akzeptiert
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

        // nur eine bewertung in der datenbank – kein duplikat
        $this->assertDatabaseCount('reviews', 1);
    }

    // -------------------------------------------------------------------
    // Szenario 8: Zwei Kunden kaufen gleichzeitig – Lagerbestand stimmt
    // -------------------------------------------------------------------

    // prüft: der lagerbestand wird bei mehreren käufen korrekt reduziert
    // simuliert zwei unabhängige kunden die dasselbe produkt kaufen
    public function test_lagerbestand_wird_korrekt_reduziert_bei_mehreren_kaeufen(): void
    {
        $kunde1 = User::factory()->create();
        $kunde2 = User::factory()->create();
        $kunde1->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));
        $kunde2->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));

        $produkt = $this->makeProduct('Beliebtes Spielzeug', 24.99, 5); // 5 stück auf lager

        // kunde1 kauft 3 stück
        $this->actingAs($kunde1)->post(route('cart.add', $produkt), ['quantity' => 3]);
        $this->actingAs($kunde1)->post(route('checkout.store'), [
            'vorname' => 'Kunde', 'nachname' => 'Eins',
            'strasse' => 'Straße 1', 'plz' => '11111', 'ort' => 'Stadt',
            'zahlungsmethode' => 'paypal',
        ]);

        // nach kauf 1: lagerbestand muss 5 - 3 = 2 sein
        $this->assertEquals(2, $produkt->fresh()->stock);

        // kunde2 kauft die restlichen 2 stück
        $this->actingAs($kunde2)->post(route('cart.add', $produkt), ['quantity' => 2]);
        $this->actingAs($kunde2)->post(route('checkout.store'), [
            'vorname' => 'Kunde', 'nachname' => 'Zwei',
            'strasse' => 'Straße 2', 'plz' => '22222', 'ort' => 'Stadt',
            'zahlungsmethode' => 'sofortueberweisung',
        ]);

        // nach kauf 2: lagerbestand muss 2 - 2 = 0 sein (ausverkauft)
        $this->assertEquals(0, $produkt->fresh()->stock);

        // genau 2 bestellungen in der datenbank
        $this->assertDatabaseCount('orders', 2);
    }
}
