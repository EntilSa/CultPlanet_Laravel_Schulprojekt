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

        // wenn mehr bestellt wird als auf lager, auf lagerbestand begrenzen
        $qty = min($qty, $product->stock);

        if ($qty < 1) {
            return back()->with('error', 'Dieses Produkt ist nicht mehr auf Lager.');
        }

        // warenkorb aus session holen, produkt hinzufügen oder menge erhöhen
        $cart = session('cart', []);

        if (isset($cart[$product->id])) {
            // produkt ist schon im warenkorb – menge erhöhen, max. lagerbestand
            $cart[$product->id]['qty'] = min($cart[$product->id]['qty'] + $qty, $product->stock);
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
