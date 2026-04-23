<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests für die Such- und Filterfunktion im Shop.
 */
class SuchfilterTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    // hilfsmethode: produkt anlegen
    private function produkt(string $name, float $price, int $stock = 10): Product
    {
        return Product::create([
            'name'        => $name,
            'description' => 'Testbeschreibung für ' . $name,
            'price'       => $price,
            'stock'       => $stock,
        ]);
    }

    // -------------------------------------------------------------------
    // Textsuche
    // -------------------------------------------------------------------

    public function test_suche_findet_produkt_nach_name(): void
    {
        $this->produkt('LEGO City Feuerwehr', 49.99);
        $this->produkt('Monopoly Classic', 29.99);

        $this->get(route('shop.index', ['suche' => 'LEGO']))
            ->assertStatus(200)
            ->assertSee('LEGO City Feuerwehr')
            ->assertDontSee('Monopoly Classic');
    }

    public function test_suche_ist_nicht_case_sensitive(): void
    {
        $this->produkt('Ravensburger Puzzle', 14.99);

        // kleinschreibung soll auch treffen
        $this->get(route('shop.index', ['suche' => 'ravensburger']))
            ->assertStatus(200)
            ->assertSee('Ravensburger Puzzle');
    }

    public function test_suche_ohne_ergebnis_zeigt_hinweis(): void
    {
        $this->produkt('UNO Kartenspiel', 9.99);

        $this->get(route('shop.index', ['suche' => 'XYZGibtsNicht']))
            ->assertStatus(200)
            ->assertSee('Keine Produkte gefunden');
    }

    public function test_suche_findet_produkt_mit_teilbegriff(): void
    {
        $this->produkt('Playmobil Piratenschiff', 69.99);

        // nur "piraten" als suchwort reicht
        $this->get(route('shop.index', ['suche' => 'piraten']))
            ->assertStatus(200)
            ->assertSee('Playmobil Piratenschiff');
    }

    // -------------------------------------------------------------------
    // Preisfilter
    // -------------------------------------------------------------------

    public function test_preisfilter_zeigt_nur_produkte_in_range(): void
    {
        $this->produkt('Günstiges Produkt', 5.00);
        $this->produkt('Mittleres Produkt', 30.00);
        $this->produkt('Teures Produkt', 150.00);

        $this->get(route('shop.index', ['preis_min' => 10, 'preis_max' => 50]))
            ->assertStatus(200)
            ->assertSee('Mittleres Produkt')
            ->assertDontSee('Günstiges Produkt')
            ->assertDontSee('Teures Produkt');
    }

    public function test_preisfilter_nur_minimum(): void
    {
        $this->produkt('Billiges Produkt', 9.99);
        $this->produkt('Teures Produkt', 99.99);

        $this->get(route('shop.index', ['preis_min' => 50]))
            ->assertStatus(200)
            ->assertSee('Teures Produkt')
            ->assertDontSee('Billiges Produkt');
    }

    public function test_preisfilter_nur_maximum(): void
    {
        $this->produkt('Billiges Produkt', 9.99);
        $this->produkt('Teures Produkt', 99.99);

        $this->get(route('shop.index', ['preis_max' => 20]))
            ->assertStatus(200)
            ->assertSee('Billiges Produkt')
            ->assertDontSee('Teures Produkt');
    }

    // -------------------------------------------------------------------
    // Verfügbarkeitsfilter
    // -------------------------------------------------------------------

    public function test_nur_verfuegbare_blendet_ausverkaufte_aus(): void
    {
        $this->produkt('Verfügbares Produkt', 19.99, 5);
        $this->produkt('Ausverkauftes Produkt', 19.99, 0);

        $this->get(route('shop.index', ['nur_verfuegbar' => 1]))
            ->assertStatus(200)
            ->assertSee('Verfügbares Produkt')
            ->assertDontSee('Ausverkauftes Produkt');
    }

    public function test_ohne_filter_zeigt_auch_ausverkaufte(): void
    {
        $this->produkt('Verfügbares Produkt', 19.99, 5);
        $this->produkt('Ausverkauftes Produkt', 19.99, 0);

        $this->get(route('shop.index'))
            ->assertStatus(200)
            ->assertSee('Verfügbares Produkt')
            ->assertSee('Ausverkauftes Produkt');
    }

    // -------------------------------------------------------------------
    // Sortierung
    // -------------------------------------------------------------------

    public function test_sortierung_preis_aufsteigend(): void
    {
        $this->produkt('Teures Produkt', 99.99);
        $this->produkt('Billiges Produkt', 5.99);

        $antwort = $this->get(route('shop.index', ['sortierung' => 'preis_asc']));
        $antwort->assertStatus(200);

        // prüfen dass das günstige produkt im html vor dem teuren kommt
        $html = $antwort->getContent();
        $this->assertLessThan(
            strpos($html, 'Teures Produkt'),
            strpos($html, 'Billiges Produkt')
        );
    }

    public function test_sortierung_preis_absteigend(): void
    {
        $this->produkt('Teures Produkt', 99.99);
        $this->produkt('Billiges Produkt', 5.99);

        $antwort = $this->get(route('shop.index', ['sortierung' => 'preis_desc']));
        $antwort->assertStatus(200);

        // teures produkt muss vor billigem kommen
        $html = $antwort->getContent();
        $this->assertLessThan(
            strpos($html, 'Billiges Produkt'),
            strpos($html, 'Teures Produkt')
        );
    }

    // -------------------------------------------------------------------
    // Kombination mehrerer Filter
    // -------------------------------------------------------------------

    public function test_suche_und_preisfilter_kombiniert(): void
    {
        $this->produkt('LEGO Starter', 19.99);
        $this->produkt('LEGO Technic', 89.99);
        $this->produkt('Playmobil Set', 24.99);

        // lego produkte unter 50€
        $this->get(route('shop.index', ['suche' => 'LEGO', 'preis_max' => 50]))
            ->assertStatus(200)
            ->assertSee('LEGO Starter')
            ->assertDontSee('LEGO Technic')
            ->assertDontSee('Playmobil Set');
    }

    // -------------------------------------------------------------------
    // Filterchips und Zurücksetzen
    // -------------------------------------------------------------------

    public function test_aktive_filter_werden_als_chips_angezeigt(): void
    {
        $this->get(route('shop.index', ['suche' => 'Testsuche']))
            ->assertStatus(200)
            ->assertSee('Aktive Filter')
            ->assertSee('Testsuche');
    }

    public function test_ohne_filter_werden_keine_chips_angezeigt(): void
    {
        $this->get(route('shop.index'))
            ->assertStatus(200)
            ->assertDontSee('Aktive Filter');
    }

    public function test_zuruecksetzen_link_erscheint_bei_aktiver_suche(): void
    {
        $this->get(route('shop.index', ['suche' => 'Test']))
            ->assertStatus(200)
            ->assertSee('Zurücksetzen');
    }

    public function test_zuruecksetzen_link_fehlt_ohne_filter(): void
    {
        $this->get(route('shop.index'))
            ->assertStatus(200)
            ->assertDontSee('Zurücksetzen');
    }
}
