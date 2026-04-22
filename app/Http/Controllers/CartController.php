<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // warenkorb anzeigen
    public function index()
    {
        // session('cart') ist ein array: [produkt_id => ['name', 'price', 'qty', 'image']]
        $cart = session('cart', []);

        // gesamtpreis berechnen
        $total = array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $cart));

        return view('cart.index', compact('cart', 'total'));
    }

    // produkt in den warenkorb legen
    public function add(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => ['integer', 'min:1'],
        ]);

        $qty = (int) $request->input('quantity', 1);

        // verfügbar = lagerbestand minus reservierte auktions-stücke
        $verfuegbar = $product->verfuegbarImShop();

        if ($verfuegbar < 1) {
            return back()->with('error', 'Dieses Produkt ist nicht mehr verfügbar (alle Stücke sind für Auktionen reserviert).');
        }

        // menge auf verfügbaren bestand begrenzen (nicht gesamten stock)
        if ($qty > $verfuegbar) {
            return back()->with('error', "Nur {$verfuegbar} Stück verfügbar (ein oder mehrere Stücke sind für laufende Auktionen reserviert).");
        }

        // warenkorb aus session holen, produkt hinzufügen oder menge erhöhen
        $cart = session('cart', []);

        if (isset($cart[$product->id])) {
            // produkt ist schon im warenkorb – menge erhöhen, max. verfügbarer bestand
            $neueQty = $cart[$product->id]['qty'] + $qty;
            if ($neueQty > $verfuegbar) {
                return back()->with('error', "Nur {$verfuegbar} Stück verfügbar. Du hast bereits {$cart[$product->id]['qty']} im Warenkorb.");
            }
            $cart[$product->id]['qty'] = $neueQty;
        } else {
            // neues produkt in den warenkorb
            $cart[$product->id] = [
                'name'  => $product->name,
                'price' => $product->price,
                'qty'   => $qty,
                'image' => $product->image,
            ];
        }

        session(['cart' => $cart]);

        return back()->with('success', '"' . $product->name . '" wurde in den Warenkorb gelegt.');
    }

    // einzelnes produkt aus dem warenkorb entfernen
    public function remove(Product $product)
    {
        $cart = session('cart', []);
        unset($cart[$product->id]);
        session(['cart' => $cart]);

        return back()->with('success', 'Produkt wurde entfernt.');
    }

    // ganzen warenkorb leeren
    public function clear()
    {
        session()->forget('cart');

        return back()->with('success', 'Warenkorb wurde geleert.');
    }
}
