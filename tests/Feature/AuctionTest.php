<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuctionTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    // admin anlegen
    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));
        return $admin;
    }

    // kunden-nutzer anlegen
    private function makeKunde(): User
    {
        $kunde = User::factory()->create();
        $kunde->assignRole(Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']));
        return $kunde;
    }

    // produkt anlegen
    private function makeProduct(int $stock = 3): Product
    {
        return Product::create([
            'name'        => 'Testspielzeug',
            'description' => 'Ein tolles Spielzeug',
            'price'       => 19.99,
            'stock'       => $stock,
        ]);
    }

    // aktive auktion anlegen (läuft gerade)
    private function makeAktiveAuktion(Product $product): Auction
    {
        return Auction::create([
            'product_id'  => $product->id,
            'start_price' => 10.00,
            'start_time'  => now()->subHour(),
            'end_time'    => now()->addHour(),
            'status'      => 'aktiv',
        ]);
    }

    // -------------------------------------------------------------------
    // Auktion anlegen (Admin)
    // -------------------------------------------------------------------

    public function test_admin_kann_auktion_planen(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct();

        $this->actingAs($admin)
            ->post(route('auctions.store', $product), [
                'start_price' => '5.00',
                'start_time'  => now()->addHour()->format('Y-m-d\TH:i'),
                'end_time'    => now()->addHours(2)->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('products.edit', $product));

        $this->assertDatabaseHas('auctions', [
            'product_id'  => $product->id,
            'start_price' => 5.00,
            'status'      => 'geplant',
        ]);
    }

    public function test_auktion_anlegen_schlaegt_fehl_bei_fehlendem_startpreis(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct();

        $this->actingAs($admin)
            ->post(route('auctions.store', $product), [
                'start_price' => '',
                'start_time'  => now()->addHour()->format('Y-m-d\TH:i'),
                'end_time'    => now()->addHours(2)->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('start_price');
    }

    public function test_auktion_anlegen_schlaegt_fehl_bei_startzeit_in_vergangenheit(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct();

        $this->actingAs($admin)
            ->post(route('auctions.store', $product), [
                'start_price' => '5.00',
                'start_time'  => now()->subHour()->format('Y-m-d\TH:i'),
                'end_time'    => now()->addHour()->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('start_time');
    }

    public function test_auktion_anlegen_schlaegt_fehl_wenn_lagerbestand_erschoepft(): void
    {
        $admin   = $this->makeAdmin();
        // produkt mit stock 1 – schon eine auktion geplant → keine weitere möglich
        $product = $this->makeProduct(stock: 1);

        Auction::create([
            'product_id'  => $product->id,
            'start_price' => 5.00,
            'start_time'  => now()->addHour(),
            'end_time'    => now()->addHours(2),
            'status'      => 'geplant',
        ]);

        $this->actingAs($admin)
            ->post(route('auctions.store', $product), [
                'start_price' => '8.00',
                'start_time'  => now()->addHours(3)->format('Y-m-d\TH:i'),
                'end_time'    => now()->addHours(4)->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('auction');
    }

    public function test_kunde_kann_keine_auktion_anlegen(): void
    {
        $kunde   = $this->makeKunde();
        $product = $this->makeProduct();

        $this->actingAs($kunde)
            ->post(route('auctions.store', $product), [
                'start_price' => '5.00',
                'start_time'  => now()->addHour()->format('Y-m-d\TH:i'),
                'end_time'    => now()->addHours(2)->format('Y-m-d\TH:i'),
            ])
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------
    // Bieten
    // -------------------------------------------------------------------

    public function test_eingeloggter_nutzer_kann_gebot_abgeben(): void
    {
        $kunde   = $this->makeKunde();
        $product = $this->makeProduct();
        $auction = $this->makeAktiveAuktion($product);

        $this->actingAs($kunde)
            ->post(route('auction.bid', $auction), ['amount' => '15.00'])
            ->assertRedirect();

        $this->assertDatabaseHas('bids', [
            'auction_id' => $auction->id,
            'user_id'    => $kunde->id,
            'amount'     => 15.00,
        ]);
    }

    public function test_gebot_schlaegt_fehl_wenn_zu_niedrig(): void
    {
        $kunde   = $this->makeKunde();
        $product = $this->makeProduct();
        $auction = $this->makeAktiveAuktion($product);

        // zuerst ein gebot von 20 € abgeben
        Bid::create(['auction_id' => $auction->id, 'user_id' => $this->makeKunde()->id, 'amount' => 20.00]);

        // neues gebot muss mindestens 21 € sein – 15 € ist zu wenig
        $this->actingAs($kunde)
            ->post(route('auction.bid', $auction), ['amount' => '15.00'])
            ->assertSessionHasErrors('amount');
    }

    public function test_gebot_schlaegt_fehl_bei_buchstaben(): void
    {
        $kunde   = $this->makeKunde();
        $product = $this->makeProduct();
        $auction = $this->makeAktiveAuktion($product);

        $this->actingAs($kunde)
            ->post(route('auction.bid', $auction), ['amount' => 'abc'])
            ->assertSessionHasErrors('amount');
    }

    public function test_nicht_eingeloggter_nutzer_wird_zum_login_weitergeleitet(): void
    {
        $product = $this->makeProduct();
        $auction = $this->makeAktiveAuktion($product);

        $this->post(route('auction.bid', $auction), ['amount' => '15.00'])
            ->assertRedirect(route('login'));
    }

    public function test_gebot_auf_beendete_auktion_nicht_moeglich(): void
    {
        $kunde   = $this->makeKunde();
        $product = $this->makeProduct();

        $beendeteAuktion = Auction::create([
            'product_id'  => $product->id,
            'start_price' => 10.00,
            'start_time'  => now()->subHours(3),
            'end_time'    => now()->subHour(),
            'status'      => 'beendet',
        ]);

        $this->actingAs($kunde)
            ->post(route('auction.bid', $beendeteAuktion), ['amount' => '15.00'])
            ->assertSessionHasErrors('amount');
    }

    public function test_hoechstbietender_kann_nicht_nochmal_bieten(): void
    {
        $kunde   = $this->makeKunde();
        $product = $this->makeProduct();
        $auction = $this->makeAktiveAuktion($product);

        // nutzer bietet zuerst 20 €
        Bid::create(['auction_id' => $auction->id, 'user_id' => $kunde->id, 'amount' => 20.00]);

        // gleicher nutzer versucht nochmal zu bieten
        $this->actingAs($kunde)
            ->post(route('auction.bid', $auction), ['amount' => '25.00'])
            ->assertSessionHasErrors('amount');
    }

    // -------------------------------------------------------------------
    // Auktionsende
    // -------------------------------------------------------------------

    public function test_artisan_command_setzt_gewinner_und_legt_bestellung_an(): void
    {
        $kunde   = $this->makeKunde();
        $product = $this->makeProduct();

        // auktion die bereits abgelaufen ist
        $auction = Auction::create([
            'product_id'  => $product->id,
            'start_price' => 10.00,
            'start_time'  => now()->subHours(2),
            'end_time'    => now()->subMinutes(5),
            'status'      => 'aktiv',
        ]);

        Bid::create(['auction_id' => $auction->id, 'user_id' => $kunde->id, 'amount' => 25.00]);

        // command ausführen
        $this->artisan('auctions:close')->assertExitCode(0);

        // auktion muss beendet sein mit dem richtigen gewinner
        $auction->refresh();
        $this->assertEquals('beendet', $auction->status);
        $this->assertEquals($kunde->id, $auction->winner_id);
        $this->assertEquals(25.00, $auction->winning_bid);

        // bestellung muss angelegt worden sein
        $this->assertDatabaseHas('orders', [
            'user_id'         => $kunde->id,
            'zahlungsmethode' => 'auktion',
            'status'          => 'bezahlt',
            'total'           => 25.00,
        ]);
    }

    public function test_auktion_ohne_gebot_bekommt_keinen_gewinner(): void
    {
        $product = $this->makeProduct();

        $auction = Auction::create([
            'product_id'  => $product->id,
            'start_price' => 10.00,
            'start_time'  => now()->subHours(2),
            'end_time'    => now()->subMinutes(5),
            'status'      => 'aktiv',
        ]);

        $this->artisan('auctions:close')->assertExitCode(0);

        $auction->refresh();
        $this->assertEquals('beendet', $auction->status);
        $this->assertNull($auction->winner_id);
        // keine bestellung angelegt
        $this->assertDatabaseCount('orders', 0);
    }

    // -------------------------------------------------------------------
    // Lagerbestand-Sperre im Shop
    // -------------------------------------------------------------------

    public function test_produkt_mit_aktiver_auktion_kann_nicht_in_warenkorb_gelegt_werden(): void
    {
        $kunde   = $this->makeKunde();
        // produkt mit genau 1 stück im lager
        $product = $this->makeProduct(stock: 1);
        // diese 1 auktion reserviert den gesamten lagerbestand
        $this->makeAktiveAuktion($product);

        $this->actingAs($kunde)
            ->post(route('cart.add', $product), ['quantity' => 1])
            ->assertSessionHas('error');
    }
}
