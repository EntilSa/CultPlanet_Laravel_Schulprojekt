<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_shop_liste_ist_oeffentlich_erreichbar(): void
    {
        $response = $this->get(route('shop.index'));
        $response->assertStatus(200);
    }

    public function test_shop_zeigt_produkte_an(): void
    {
        Product::create([
            'name' => 'Test Produkt', 'description' => 'Beschreibung',
            'price' => 9.99, 'stock' => 5,
        ]);

        $response = $this->get(route('shop.index'));
        $response->assertSee('Test Produkt');
    }

    public function test_produktseite_ist_oeffentlich_erreichbar(): void
    {
        $product = Product::create([
            'name' => 'Detail Produkt', 'description' => 'Beschreibung',
            'price' => 14.99, 'stock' => 3,
        ]);

        $response = $this->get(route('shop.show', $product));
        $response->assertStatus(200);
        $response->assertSee('Detail Produkt');
    }

    public function test_produkt_anlegen_nur_fuer_admin(): void
    {
        // gast wird zum login weitergeleitet
        $this->get(route('products.create'))->assertRedirect(route('login'));

        // normale kunden bekommen 403
        $kunde = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));
        $this->actingAs($kunde)->get(route('products.create'))->assertStatus(403);
    }

    public function test_admin_kann_produkt_anlegen(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));

        $response = $this->actingAs($admin)->post(route('products.store'), [
            'name'        => 'Neues Produkt',
            'description' => 'Eine Beschreibung',
            'price'       => 19.99,
            'stock'       => 10,
        ]);

        $response->assertRedirect(route('shop.index'));
        $this->assertDatabaseHas('products', ['name' => 'Neues Produkt']);
    }

    public function test_neues_produkt_bekommt_automatisch_artikelnummer(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));

        $this->actingAs($admin)->post(route('products.store'), [
            'name'        => 'Artikel Test',
            'description' => 'Beschreibung',
            'price'       => 9.99,
            'stock'       => 5,
        ]);

        $product = \App\Models\Product::where('name', 'Artikel Test')->first();

        // artikelnummer muss gesetzt sein und größer als 10000
        $this->assertNotNull($product->artikel_nr);
        $this->assertGreaterThan(10000, $product->artikel_nr);
        $this->assertEquals(10000 + $product->id, $product->artikel_nr);
    }
}
