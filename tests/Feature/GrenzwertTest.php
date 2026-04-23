<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Grenzwerttests – prüfen was passiert wenn jemand extreme oder ungültige
 * Werte eingibt. Wichtig um sicherzustellen dass das System stabil bleibt.
 */
class GrenzwertTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    // hilfsmethode: kunde + produkt anlegen
    private function kundeUndProdukt(int $stock = 5): array
    {
        $kunde = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));

        $produkt = Product::create([
            'name'        => 'Grenzwert-Testprodukt',
            'description' => 'Wird nur in Tests verwendet.',
            'price'       => 19.99,
            'stock'       => $stock,
        ]);

        return [$kunde, $produkt];
    }

    // -------------------------------------------------------------------
    // Warenkorb – Mengengrenzen
    // -------------------------------------------------------------------

    public function test_menge_null_wird_abgelehnt(): void
    {
        [$kunde, $produkt] = $this->kundeUndProdukt();

        // quantity=0 ist nicht erlaubt (min:1 validierung)
        $this->actingAs($kunde)
            ->post(route('cart.add', $produkt), ['quantity' => 0])
            ->assertSessionHasErrors('quantity');

        // warenkorb ist leer geblieben
        $this->actingAs($kunde)
            ->get(route('cart.index'))
            ->assertDontSee('Grenzwert-Testprodukt');
    }

    public function test_negative_menge_wird_abgelehnt(): void
    {
        [$kunde, $produkt] = $this->kundeUndProdukt();

        // quantity=-5 ist nicht erlaubt
        $this->actingAs($kunde)
            ->post(route('cart.add', $produkt), ['quantity' => -5])
            ->assertSessionHasErrors('quantity');
    }

    public function test_menge_groesser_als_lagerbestand_wird_abgelehnt(): void
    {
        // lagerbestand ist 3, wir versuchen 9999 zu bestellen
        [$kunde, $produkt] = $this->kundeUndProdukt(3);

        $this->actingAs($kunde)
            ->post(route('cart.add', $produkt), ['quantity' => 9999])
            ->assertSessionHas('error');

        // lagerbestand ist unverändert
        $this->assertEquals(3, $produkt->fresh()->stock);
    }

    public function test_menge_exakt_gleich_lagerbestand_ist_erlaubt(): void
    {
        // genau den gesamten lagerbestand bestellen – muss klappen
        [$kunde, $produkt] = $this->kundeUndProdukt(3);

        $this->actingAs($kunde)
            ->post(route('cart.add', $produkt), ['quantity' => 3])
            ->assertSessionHas('success');
    }

    public function test_menge_eins_ueber_lagerbestand_wird_abgelehnt(): void
    {
        // lagerbestand ist 3, wir versuchen 4 zu bestellen
        [$kunde, $produkt] = $this->kundeUndProdukt(3);

        $this->actingAs($kunde)
            ->post(route('cart.add', $produkt), ['quantity' => 4])
            ->assertSessionHas('error');
    }

    // -------------------------------------------------------------------
    // Checkout – Feldlängengrenzen
    // -------------------------------------------------------------------

    public function test_vorname_mit_mehr_als_100_zeichen_wird_abgelehnt(): void
    {
        [$kunde, $produkt] = $this->kundeUndProdukt();
        $this->actingAs($kunde)->post(route('cart.add', $produkt), ['quantity' => 1]);

        // 101 zeichen langer vorname
        $this->actingAs($kunde)
            ->post(route('checkout.store'), [
                'vorname'         => str_repeat('A', 101),
                'nachname'        => 'Mustermann',
                'strasse'         => 'Musterstraße 1',
                'plz'             => '12345',
                'ort'             => 'Stadt',
                'zahlungsmethode' => 'paypal',
            ])
            ->assertSessionHasErrors('vorname');
    }

    public function test_plz_mit_mehr_als_10_zeichen_wird_abgelehnt(): void
    {
        [$kunde, $produkt] = $this->kundeUndProdukt();
        $this->actingAs($kunde)->post(route('cart.add', $produkt), ['quantity' => 1]);

        $this->actingAs($kunde)
            ->post(route('checkout.store'), [
                'vorname'         => 'Max',
                'nachname'        => 'Mustermann',
                'strasse'         => 'Musterstraße 1',
                'plz'             => '123456789012', // 12 zeichen, max ist 10
                'ort'             => 'Stadt',
                'zahlungsmethode' => 'paypal',
            ])
            ->assertSessionHasErrors('plz');
    }

    // -------------------------------------------------------------------
    // Bewertung – Grenzwerte
    // -------------------------------------------------------------------

    public function test_bewertung_mit_0_sternen_wird_abgelehnt(): void
    {
        [$kunde, $produkt] = $this->kundeUndProdukt();

        $this->actingAs($kunde)
            ->post(route('reviews.store', $produkt), [
                'rating' => 0,
                'text'   => 'Geht leider gar nicht, bin sehr enttäuscht.',
            ])
            ->assertSessionHasErrors('rating');
    }

    public function test_bewertung_mit_6_sternen_wird_abgelehnt(): void
    {
        [$kunde, $produkt] = $this->kundeUndProdukt();

        $this->actingAs($kunde)
            ->post(route('reviews.store', $produkt), [
                'rating' => 6,
                'text'   => 'Absolut fantastisch, mehr als 5 Sterne verdient!',
            ])
            ->assertSessionHasErrors('rating');
    }

    public function test_bewertung_mit_exakt_1_stern_ist_erlaubt(): void
    {
        [$kunde, $produkt] = $this->kundeUndProdukt();

        $this->actingAs($kunde)
            ->post(route('reviews.store', $produkt), [
                'rating' => 1,
                'text'   => 'Leider sehr enttäuschend, würde es nicht empfehlen.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'user_id'    => $kunde->id,
            'product_id' => $produkt->id,
            'rating'     => 1,
        ]);
    }

    public function test_bewertung_mit_exakt_5_sternen_ist_erlaubt(): void
    {
        [$kunde, $produkt] = $this->kundeUndProdukt();

        $this->actingAs($kunde)
            ->post(route('reviews.store', $produkt), [
                'rating' => 5,
                'text'   => 'Absolut perfekt, kann ich nur wärmstens empfehlen!',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'user_id'    => $kunde->id,
            'product_id' => $produkt->id,
            'rating'     => 5,
        ]);
    }

    public function test_bewertungstext_mit_unter_10_zeichen_wird_abgelehnt(): void
    {
        [$kunde, $produkt] = $this->kundeUndProdukt();

        $this->actingAs($kunde)
            ->post(route('reviews.store', $produkt), [
                'rating' => 3,
                'text'   => 'Kurz', // nur 4 zeichen, min:10
            ])
            ->assertSessionHasErrors('text');
    }

    // -------------------------------------------------------------------
    // Leere / fehlende Werte
    // -------------------------------------------------------------------

    public function test_bewertung_ohne_rating_wird_abgelehnt(): void
    {
        [$kunde, $produkt] = $this->kundeUndProdukt();

        $this->actingAs($kunde)
            ->post(route('reviews.store', $produkt), [
                'text' => 'Kein Rating angegeben, sollte abgelehnt werden.',
            ])
            ->assertSessionHasErrors('rating');
    }

    public function test_bewertung_ohne_text_wird_abgelehnt(): void
    {
        [$kunde, $produkt] = $this->kundeUndProdukt();

        $this->actingAs($kunde)
            ->post(route('reviews.store', $produkt), [
                'rating' => 4,
            ])
            ->assertSessionHasErrors('text');
    }
}
