<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// diese testklasse prüft die gesamte auktions-logik:
// auktion anlegen, gebote abgeben, auktionsende mit gewinner und bestellung
class AuctionTest extends TestCase
{
    // RefreshDatabase: die datenbank wird vor jedem einzelnen test komplett zurückgesetzt
    // so beeinflussen sich tests nicht gegenseitig
    use RefreshDatabase;

    // $seed = true: führt den datenbank-seeder aus (rollen anlegen, testdaten)
    // ohne das würden 'admin', 'kunde' etc. nicht existieren
    protected bool $seed = true;

    // -------------------------------------------------------------------
    // Hilfsmethoden – werden in den tests wiederverwendet
    // -------------------------------------------------------------------

    // admin-nutzer anlegen und die admin-rolle zuweisen
    private function makeAdmin(): User
    {
        $admin = User::factory()->create(); // factory erstellt einen fake-nutzer mit zufälligen daten
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

    // testprodukt anlegen – stock kann übergeben werden um lagerbestand zu steuern
    private function makeProduct(int $stock = 3): Product
    {
        return Product::create([
            'name'        => 'Testspielzeug',
            'description' => 'Ein tolles Spielzeug',
            'price'       => 19.99,
            'stock'       => $stock,
        ]);
    }

    // aktive auktion anlegen – start_time liegt in der vergangenheit, end_time in der zukunft
    // das simuliert eine gerade laufende auktion
    private function makeAktiveAuktion(Product $product): Auction
    {
        return Auction::create([
            'product_id'  => $product->id,
            'start_price' => 10.00,
            'start_time'  => now()->subHour(),  // vor einer stunde gestartet
            'end_time'    => now()->addHour(),  // läuft noch eine stunde
            'status'      => 'aktiv',
        ]);
    }

    // -------------------------------------------------------------------
    // Auktion anlegen (Admin)
    // -------------------------------------------------------------------

    // prüft: admin kann erfolgreich eine auktion planen
    // actingAs($admin): simuliert dass dieser nutzer eingeloggt ist
    // assertRedirect(): erwartet eine weiterleitung (erfolgsfall)
    // assertDatabaseHas(): prüft ob der datensatz wirklich in der db gelandet ist
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

        // in der datenbank muss jetzt ein auktions-datensatz existieren
        $this->assertDatabaseHas('auctions', [
            'product_id'  => $product->id,
            'start_price' => 5.00,
            'status'      => 'geplant', // neue auktionen starten immer als 'geplant'
        ]);
    }

    // prüft: fehlender startpreis löst einen validierungsfehler aus
    // assertSessionHasErrors('start_price'): laravel hat den fehler zur session hinzugefügt
    public function test_auktion_anlegen_schlaegt_fehl_bei_fehlendem_startpreis(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct();

        $this->actingAs($admin)
            ->post(route('auctions.store', $product), [
                'start_price' => '', // absichtlich leer
                'start_time'  => now()->addHour()->format('Y-m-d\TH:i'),
                'end_time'    => now()->addHours(2)->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('start_price');
    }

    // prüft: startzeit in der vergangenheit wird abgelehnt
    // verhindert dass jemand eine auktion anlegt die schon "laufen" würde
    public function test_auktion_anlegen_schlaegt_fehl_bei_startzeit_in_vergangenheit(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct();

        $this->actingAs($admin)
            ->post(route('auctions.store', $product), [
                'start_price' => '5.00',
                'start_time'  => now()->subHour()->format('Y-m-d\TH:i'), // eine stunde in der vergangenheit
                'end_time'    => now()->addHour()->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('start_time');
    }

    // prüft: keine weitere auktion wenn lagerbestand schon voll reserviert ist
    // produkt hat stock=1, bereits eine auktion geplant → zweite auktion muss fehlschlagen
    public function test_auktion_anlegen_schlaegt_fehl_wenn_lagerbestand_erschoepft(): void
    {
        $admin   = $this->makeAdmin();
        $product = $this->makeProduct(stock: 1); // nur 1 stück auf lager

        // diese auktion reserviert das einzige stück
        Auction::create([
            'product_id'  => $product->id,
            'start_price' => 5.00,
            'start_time'  => now()->addHour(),
            'end_time'    => now()->addHours(2),
            'status'      => 'geplant',
        ]);

        // zweite auktion für dasselbe produkt – muss fehlschlagen
        $this->actingAs($admin)
            ->post(route('auctions.store', $product), [
                'start_price' => '8.00',
                'start_time'  => now()->addHours(3)->format('Y-m-d\TH:i'),
                'end_time'    => now()->addHours(4)->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('auction');
    }

    // prüft: ein normaler kunde darf keine auktion anlegen
    // assertStatus(403): server antwortet mit "verboten"
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

    // prüft: eingeloggter nutzer kann erfolgreich ein gebot abgeben
    // das gebot muss in der bids-tabelle der db landen
    public function test_eingeloggter_nutzer_kann_gebot_abgeben(): void
    {
        $kunde   = $this->makeKunde();
        $product = $this->makeProduct();
        $auction = $this->makeAktiveAuktion($product);

        $this->actingAs($kunde)
            ->post(route('auction.bid', $auction), ['amount' => '15.00'])
            ->assertRedirect(); // weiterleitung = erfolg

        // gebot in der datenbank prüfen
        $this->assertDatabaseHas('bids', [
            'auction_id' => $auction->id,
            'user_id'    => $kunde->id,
            'amount'     => 15.00,
        ]);
    }

    // prüft: gebot das unter dem mindestgebot liegt wird abgelehnt
    // mindestgebot = aktuelles höchstgebot + 1,00 € (hier: 20 + 1 = 21 €)
    public function test_gebot_schlaegt_fehl_wenn_zu_niedrig(): void
    {
        $kunde   = $this->makeKunde();
        $product = $this->makeProduct();
        $auction = $this->makeAktiveAuktion($product);

        // erst ein gebot von 20 € setzen (von einem anderen nutzer)
        Bid::create(['auction_id' => $auction->id, 'user_id' => $this->makeKunde()->id, 'amount' => 20.00]);

        // neues gebot mit 15 € – zu wenig (mindest wäre 21 €)
        $this->actingAs($kunde)
            ->post(route('auction.bid', $auction), ['amount' => '15.00'])
            ->assertSessionHasErrors('amount');
    }

    // prüft: buchstaben statt zahlen werden abgelehnt (eingabe-validierung)
    public function test_gebot_schlaegt_fehl_bei_buchstaben(): void
    {
        $kunde   = $this->makeKunde();
        $product = $this->makeProduct();
        $auction = $this->makeAktiveAuktion($product);

        $this->actingAs($kunde)
            ->post(route('auction.bid', $auction), ['amount' => 'abc'])
            ->assertSessionHasErrors('amount');
    }

    // prüft: nicht eingeloggter nutzer wird zum login weitergeleitet statt 403
    // das ist laravels auth-middleware: kein login → weiterleitung, nicht verbot
    public function test_nicht_eingeloggter_nutzer_wird_zum_login_weitergeleitet(): void
    {
        $product = $this->makeProduct();
        $auction = $this->makeAktiveAuktion($product);

        // kein actingAs() → nutzer ist nicht eingeloggt
        $this->post(route('auction.bid', $auction), ['amount' => '15.00'])
            ->assertRedirect(route('login'));
    }

    // prüft: auf beendete auktionen kann nicht mehr geboten werden
    public function test_gebot_auf_beendete_auktion_nicht_moeglich(): void
    {
        $kunde   = $this->makeKunde();
        $product = $this->makeProduct();

        // auktion die bereits beendet ist (end_time in der vergangenheit)
        $beendeteAuktion = Auction::create([
            'product_id'  => $product->id,
            'start_price' => 10.00,
            'start_time'  => now()->subHours(3),
            'end_time'    => now()->subHour(), // vor einer stunde abgelaufen
            'status'      => 'beendet',
        ]);

        $this->actingAs($kunde)
            ->post(route('auction.bid', $beendeteAuktion), ['amount' => '15.00'])
            ->assertSessionHasErrors('amount');
    }

    // prüft: wer bereits höchstbietender ist, kann nicht nochmal bieten
    // verhindert dass jemand seinen eigenen preis immer weiter hochbietet
    public function test_hoechstbietender_kann_nicht_nochmal_bieten(): void
    {
        $kunde   = $this->makeKunde();
        $product = $this->makeProduct();
        $auction = $this->makeAktiveAuktion($product);

        // nutzer bietet 20 € – er ist jetzt höchstbietender
        Bid::create(['auction_id' => $auction->id, 'user_id' => $kunde->id, 'amount' => 20.00]);

        // derselbe nutzer versucht nochmal zu bieten – muss fehlschlagen
        $this->actingAs($kunde)
            ->post(route('auction.bid', $auction), ['amount' => '25.00'])
            ->assertSessionHasErrors('amount');
    }

    // -------------------------------------------------------------------
    // Auktionsende
    // -------------------------------------------------------------------

    // prüft: der artisan-command 'auctions:close' setzt gewinner und legt eine bestellung an
    // this->artisan('auctions:close'): führt den command aus als wäre man im terminal
    // assertExitCode(0): exitcode 0 bedeutet "erfolgreich" (wie in der unix-welt üblich)
    public function test_artisan_command_setzt_gewinner_und_legt_bestellung_an(): void
    {
        $kunde   = $this->makeKunde();
        $product = $this->makeProduct();

        // auktion die bereits abgelaufen ist
        $auction = Auction::create([
            'product_id'  => $product->id,
            'start_price' => 10.00,
            'start_time'  => now()->subHours(2),
            'end_time'    => now()->subMinutes(5), // vor 5 minuten abgelaufen
            'status'      => 'aktiv',
        ]);

        // gebot des gewinners
        Bid::create(['auction_id' => $auction->id, 'user_id' => $kunde->id, 'amount' => 25.00]);

        // artisan-command ausführen
        $this->artisan('auctions:close')->assertExitCode(0);

        // auktions-datensatz neu laden und prüfen
        $auction->refresh(); // refresh: aktuellen stand aus der db laden
        $this->assertEquals('beendet', $auction->status);
        $this->assertEquals($kunde->id, $auction->winner_id);
        $this->assertEquals(25.00, $auction->winning_bid);

        // die automatisch angelegte bestellung prüfen
        $this->assertDatabaseHas('orders', [
            'user_id'         => $kunde->id,
            'zahlungsmethode' => 'auktion',
            'status'          => 'bezahlt', // auktionsgewinn gilt als direkt bezahlt
            'total'           => 25.00,
        ]);
    }

    // prüft: auktion ohne einziges gebot endet ohne gewinner – keine bestellung
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

        // kein bid angelegt – niemand hat geboten

        $this->artisan('auctions:close')->assertExitCode(0);

        $auction->refresh();
        $this->assertEquals('beendet', $auction->status);
        $this->assertNull($auction->winner_id); // kein gewinner
        // assertDatabaseCount: prüft ob genau 0 bestellungen in der tabelle sind
        $this->assertDatabaseCount('orders', 0);
    }

    // -------------------------------------------------------------------
    // Lagerbestand-Sperre im Shop
    // -------------------------------------------------------------------

    // prüft: ein produkt das komplett für auktionen reserviert ist, kann nicht in den warenkorb
    // stock=1, eine aktive auktion → verfügbar im shop = 0
    public function test_produkt_mit_aktiver_auktion_kann_nicht_in_warenkorb_gelegt_werden(): void
    {
        $kunde   = $this->makeKunde();
        $product = $this->makeProduct(stock: 1); // nur 1 stück auf lager
        $this->makeAktiveAuktion($product);      // dieses stück ist für die auktion reserviert

        $this->actingAs($kunde)
            ->post(route('cart.add', $product), ['quantity' => 1])
            ->assertSessionHas('error'); // fehlermeldung erwartet
    }
}
