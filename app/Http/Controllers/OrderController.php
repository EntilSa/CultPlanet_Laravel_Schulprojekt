<?php

namespace App\Http\Controllers;

use App\Mail\BestellbestaetigungMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    // checkout-formular anzeigen
    public function index()
    {
        $cart = session('cart', []);

        // wenn warenkorb leer ist, zurück zum warenkorb
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Dein Warenkorb ist leer.');
        }

        $total = array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $cart));

        return view('checkout.index', compact('cart', 'total'));
    }

    // bestellung speichern
    public function store(Request $request)
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Dein Warenkorb ist leer.');
        }

        $request->validate([
            'vorname' => ['required', 'string', 'max:100'],
            'nachname' => ['required', 'string', 'max:100'],
            'strasse' => ['required', 'string', 'max:255'],
            'plz' => ['required', 'string', 'max:10'],
            'ort' => ['required', 'string', 'max:100'],
            'zahlungsmethode' => ['required', 'in:paypal,sofortueberweisung'],
        ]);

        $total = array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $cart));

        // bestellung anlegen
        $order = Order::create([
            'user_id' => auth()->id(),
            'vorname' => $request->vorname,
            'nachname' => $request->nachname,
            'strasse' => $request->strasse,
            'plz' => $request->plz,
            'ort' => $request->ort,
            'zahlungsmethode' => $request->zahlungsmethode,
            'total' => $total,
            'status' => 'offen',
        ]);

        // jede position der bestellung speichern und lagerbestand reduzieren
        foreach ($cart as $productId => $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['qty'],
            ]);

            // lagerbestand um die bestellte menge reduzieren
            Product::where('id', $productId)->decrement('stock', $item['qty']);
        }

        // bestellbestaetigung per mail schicken (mit pdf-rechnung als anhang)
        $order->load('items'); // items nachladen damit sie im pdf vorhanden sind
        Mail::to($request->user()->email)
            ->send(new BestellbestaetigungMail($order));

        // warenkorb leeren – bestellung ist gespeichert
        session()->forget('cart');

        // zur fake-zahlungsseite weiterleiten (schritt 10: attrappen-zahlung)
        return redirect()->route('orders.payment', $order);
    }

    // fake-zahlungsseite anzeigen (attrappen-button für paypal / sofortüberweisung)
    public function payment(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // nur bestellungen mit status 'offen' kommen auf diese seite
        if ($order->status !== 'offen') {
            return redirect()->route('orders.success', $order);
        }

        return view('checkout.payment', compact('order'));
    }

    // zahlung "abschließen" – status auf bezahlt setzen und zur bestätigung
    public function completePayment(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->update(['status' => 'bezahlt']);

        return redirect()->route('orders.success', $order)->with('success', 'Zahlung erfolgreich!');
    }

    // alle bestellungen des eingeloggten kunden anzeigen
    public function myOrders()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with('items')
            ->orderByDesc('created_at')
            ->get();

        return view('orders.my-orders', compact('orders'));
    }

    // pdf für eine bestellung herunterladen (nur eigene bestellungen!)
    public function downloadPdf(Order $order)
    {
        // sicherstellen dass der kunde nur seine eigenen bestellungen herunterladen kann
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items');
        $pdf = Pdf::loadView('pdf.rechnung', ['order' => $order]);

        return $pdf->download('Rechnung-'.$order->id.'.pdf');
    }

    // bestellbestätigung anzeigen
    public function success(Order $order)
    {
        // sicherheitscheck: nur der eigene nutzer darf seine bestellung sehen
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items');

        return view('checkout.success', compact('order'));
    }
}
