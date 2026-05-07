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
    // checkout-formular anzeigen (lieferadresse + zahlungswahl)
    public function index()
    {
        // warenkorb aus der session holen
        $cart = session('cart', []);

        // wenn der warenkorb leer ist hat der checkout keinen sinn – zurückleiten
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Dein Warenkorb ist leer.');
        }

        // gesamtpreis für die anzeige berechnen
        $total = array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $cart));

        return view('checkout.index', compact('cart', 'total'));
    }

    // bestellung in der datenbank speichern (formular wird abgesendet)
    public function store(Request $request)
    {
        $cart = session('cart', []);

        // nochmal prüfen ob der warenkorb noch gefüllt ist (könnte in der zwischenzeit geleert worden sein)
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Dein Warenkorb ist leer.');
        }

        // alle felder des checkout-formulars validieren
        $request->validate([
            'vorname'          => ['required', 'string', 'max:100'],
            'nachname'         => ['required', 'string', 'max:100'],
            'strasse'          => ['required', 'string', 'max:255'],
            'plz'              => ['required', 'string', 'max:10'],
            'ort'              => ['required', 'string', 'max:100'],
            // nur diese zwei zahlungsmethoden sind erlaubt (attrappen-buttons)
            'zahlungsmethode'  => ['required', 'in:paypal,sofortueberweisung'],
        ]);

        // gesamtpreis aus dem warenkorb berechnen – nicht aus dem formular übernehmen
        // (formular-werte könnten manipuliert sein)
        $total = array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $cart));

        // bestellung als ganzes in der datenbank anlegen
        $order = Order::create([
            'user_id'          => auth()->id(),
            'vorname'          => $request->vorname,
            'nachname'         => $request->nachname,
            'strasse'          => $request->strasse,
            'plz'              => $request->plz,
            'ort'              => $request->ort,
            'zahlungsmethode'  => $request->zahlungsmethode,
            'total'            => $total,
            'status'           => 'offen', // status startet als 'offen', wird nach zahlung 'bezahlt'
        ]);

        // jede warenkorb-position als einzelne order_item zeile speichern
        foreach ($cart as $productId => $item) {
            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $productId,
                'name'       => $item['name'],  // name wird hier festgehalten – falls produkt später umbenannt wird
                'price'      => $item['price'], // preis ebenfalls einfrieren zum zeitpunkt der bestellung
                'quantity'   => $item['qty'],
            ]);

            // lagerbestand des produkts um die bestellte menge reduzieren
            Product::where('id', $productId)->decrement('stock', $item['qty']);
        }

        // bestellbestätigung per mail mit pdf-rechnung als anhang verschicken
        // items vorher nachladen damit sie in der mail-klasse verfügbar sind
        $order->load('items');
        Mail::to($request->user()->email)
            ->send(new BestellbestaetigungMail($order));

        // warenkorb leeren – die bestellung ist jetzt gespeichert
        session()->forget('cart');

        // zur fake-zahlungsseite weiterleiten (nutzer wählt paypal oder sofortüberweisung)
        return redirect()->route('orders.payment', $order);
    }

    // fake-zahlungsseite anzeigen (attrappen-buttons – keine echte transaktion)
    public function payment(Order $order)
    {
        // sicherheitscheck: nur der eigentümer darf seine zahlungsseite sehen
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // wenn die bestellung schon bezahlt ist, direkt zur bestätigungsseite
        if ($order->status !== 'offen') {
            return redirect()->route('orders.success', $order);
        }

        return view('checkout.payment', compact('order'));
    }

    // zahlung "abschließen" – status auf bezahlt setzen (attrappe)
    public function completePayment(Order $order)
    {
        // sicherheitscheck: nur der eigentümer kann zahlen
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // in einem echten shop würde hier die zahlungs-api antworten
        // bei uns reicht es den status zu ändern
        $order->update(['status' => 'bezahlt']);

        return redirect()->route('orders.success', $order)->with('success', 'Zahlung erfolgreich!');
    }

    // alle bestellungen des eingeloggten kunden anzeigen
    public function myOrders()
    {
        // nur bestellungen des aktuell eingeloggten nutzers laden
        $orders = Order::where('user_id', auth()->id())
            ->with('items')          // positionen gleich mitleiden für die detailansicht
            ->orderByDesc('created_at') // neueste bestellung zuerst
            ->get();

        return view('orders.my-orders', compact('orders'));
    }

    // pdf-rechnung für eine bestellung herunterladen
    public function downloadPdf(Order $order)
    {
        // sicherheitscheck: nutzer darf nur seine eigenen rechnungen herunterladen
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // bestellpositionen laden damit sie im pdf vorhanden sind
        $order->load('items');

        // pdf aus dem blade-template 'pdf.rechnung' generieren (dompdf-paket)
        $pdf = Pdf::loadView('pdf.rechnung', ['order' => $order]);

        // pdf als download senden – dateiname enthält die bestellnummer
        return $pdf->download('Rechnung-'.$order->id.'.pdf');
    }

    // bestellbestätigung anzeigen (danke-seite nach erfolgreicher zahlung)
    public function success(Order $order)
    {
        // sicherheitscheck: nur der eigene nutzer darf seine bestätigung sehen
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items');

        return view('checkout.success', compact('order'));
    }
}
