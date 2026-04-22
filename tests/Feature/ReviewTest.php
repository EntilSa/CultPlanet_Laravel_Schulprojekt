<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function makeProduct(): Product
    {
        return Product::create([
            'name' => 'Test Produkt', 'description' => 'Beschreibung',
            'price' => 9.99, 'stock' => 5,
        ]);
    }

    public function test_bewertung_erfordert_login(): void
    {
        $product = $this->makeProduct();

        $this->post(route('reviews.store', $product), [
            'rating' => 5, 'text' => 'Sehr gutes Produkt!',
        ])->assertRedirect(route('login'));
    }

    public function test_eingeloggter_nutzer_kann_bewertung_abgeben(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $this->actingAs($user)->post(route('reviews.store', $product), [
            'rating' => 4,
            'text'   => 'Wirklich tolles Produkt, sehr empfehlenswert!',
        ]);

        $this->assertDatabaseHas('reviews', [
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'rating'     => 4,
        ]);
    }

    public function test_bewertung_text_muss_mindestens_10_zeichen_haben(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $this->actingAs($user)->post(route('reviews.store', $product), [
            'rating' => 5, 'text' => 'Kurz',
        ])->assertSessionHasErrors('text');
    }

    public function test_nutzer_kann_produkt_nicht_zweimal_bewerten(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        // erste bewertung
        $this->actingAs($user)->post(route('reviews.store', $product), [
            'rating' => 5, 'text' => 'Erste Bewertung, sehr gut!',
        ]);

        // zweite bewertung – sollte scheitern
        $response = $this->actingAs($user)->post(route('reviews.store', $product), [
            'rating' => 3, 'text' => 'Zweite Bewertung versucht...',
        ]);

        $response->assertSessionHas('error');
        $this->assertCount(1, \App\Models\Review::all());
    }

    public function test_bewertung_sterne_muessen_zwischen_1_und_5_liegen(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $this->actingAs($user)->post(route('reviews.store', $product), [
            'rating' => 6, 'text' => 'Ungültige Sterne Anzahl hier',
        ])->assertSessionHasErrors('rating');
    }
}
