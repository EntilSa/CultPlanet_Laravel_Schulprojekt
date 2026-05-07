<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // warenkorb anzeigen
    public function index()
    {
        // warenkorb aus der session holen – falls leer, leeres array als standard
        // die session ist ein temporärer speicher im browser, der zwischen anfragen bestehen bleibt
        // struktur: [produkt_id => ['name' => ..., 'price' => ..., 'qty' => ..., 'image' => ...]]
        $cart = session('cart', []);

        // gesamtpreis berechnen: für jede position preis × menge, dann alle summieren
        // array_map() wendet eine funktion auf jedes element an
        // array_sum() addiert alle ergebnisse zusammen
        $total = array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $cart));

        return view('cart.index', compact('cart', 'total'));
    }

    // produkt in den warenkorb legen
    public function add(Request $request, Product $product)
    {
        // menge validieren – muss eine ganze zahl und mindestens 1 sein
        $request->validate([
            'quantity' => ['integer', 'min:1'],
        ]);

        // gewünschte menge aus dem formular holen, standard ist 1
        $qty = (int) $request->input('quantity', 1);

        // verfügbar = lagerbestand minus stücke die für aktive/geplante auktionen reserviert sind
        // ein produkt mit stock=5 und 2 aktiven auktionen hat nur 3 stück im shop verfügbar
        $verfuegbar = $product->verfuegbarImShop();

        if ($verfuegbar < 1) {
            return back()->with('error', 'Dieses Produkt ist nicht mehr verfügbar (alle Stücke sind für Auktionen reserviert).');
        }

        // bestellte menge darf den verfügbaren bestand nicht überschreiten
        if ($qty > $verfuegbar) {
            return back()->with('error', "Nur {$verfuegbar} Stück verfügbar (ein oder mehrere Stücke sind für laufende Auktionen reserviert).");
        }

        // aktuellen warenkorb aus der session laden
        $cart = session('cart', []);

        if (isset($cart[$product->id])) {
            // produkt ist bereits im warenkorb – menge erhöhen statt neu hinzufügen
            $neueQty = $cart[$product->id]['qty'] + $qty;

            // prüfen ob die neue gesamtmenge den verfügbaren bestand überschreitet
            if ($neueQty > $verfuegbar) {
                return back()->with('error', "Nur {$verfuegbar} Stück verfügbar. Du hast bereits {$cart[$product->id]['qty']} im Warenkorb.");
            }
            $cart[$product->id]['qty'] = $neueQty;
        } else {
            // neues produkt zum warenkorb hinzufügen – als array mit allen nötigen infos
            // wir speichern name/preis/bild direkt, damit sich änderungen am produkt nicht rückwirkend auswirken
            $cart[$product->id] = [
                'name'  => $product->name,
                'price' => $product->price,
                'qty'   => $qty,
                'image' => $product->image,
            ];
        }

        // aktualisierten warenkorb zurück in die session schreiben
        session(['cart' => $cart]);

        return back()->with('success', '"'.$product->name.'" wurde in den Warenkorb gelegt.');
    }

    // einzelnes produkt aus dem warenkorb entfernen
    public function remove(Product $product)
    {
        $cart = session('cart', []);
        // unset() entfernt den eintrag mit dem schlüssel $product->id aus dem array
        unset($cart[$product->id]);
        // array ohne den entfernen artikel zurück in die session schreiben
        session(['cart' => $cart]);

        return back()->with('success', 'Produkt wurde entfernt.');
    }

    // ganzen warenkorb leeren
    public function clear()
    {
        // forget() löscht den 'cart'-schlüssel komplett aus der session
        session()->forget('cart');

        return back()->with('success', 'Warenkorb wurde geleert.');
    }
}
