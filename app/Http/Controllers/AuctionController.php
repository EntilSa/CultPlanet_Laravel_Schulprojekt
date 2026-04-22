<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    // neue auktion für ein produkt anlegen (nur admin)
    public function store(Request $request, Product $product)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $request->validate([
            'start_price' => ['required', 'numeric', 'min:0.01'],
            'start_time'  => ['required', 'date', 'after:now'],
            'end_time'    => ['required', 'date', 'after:start_time'],
        ], [
            'start_price.required' => 'Bitte einen Startpreis eingeben.',
            'start_price.numeric'  => 'Der Startpreis muss eine Zahl sein.',
            'start_price.min'      => 'Der Startpreis muss mindestens 0,01 € betragen.',
            'start_time.required'  => 'Bitte ein Startdatum eingeben.',
            'start_time.date'      => 'Das Startdatum muss ein gültiges Datum sein.',
            'start_time.after'     => 'Das Startdatum muss in der Zukunft liegen.',
            'end_time.required'    => 'Bitte ein Enddatum eingeben.',
            'end_time.date'        => 'Das Enddatum muss ein gültiges Datum sein.',
            'end_time.after'       => 'Das Enddatum muss nach dem Startdatum liegen.',
        ]);

        // prüfen ob noch genug lagerbestand für eine weitere auktion vorhanden ist
        $bereitsGeplant = $product->auctions()
            ->whereIn('status', ['geplant', 'aktiv'])
            ->count();

        if ($bereitsGeplant >= $product->stock) {
            return back()
                ->withInput()
                ->withErrors([
                    'auction' => "Nicht genug Lagerbestand für eine weitere Auktion. " .
                                 "Maximal {$product->stock} Auktion(en) möglich, " .
                                 "aktuell bereits {$bereitsGeplant} geplant.",
                ]);
        }

        Auction::create([
            'product_id'  => $product->id,
            'start_price' => $request->start_price,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'status'      => 'geplant',
        ]);

        return redirect()
            ->route('products.edit', $product)
            ->with('success', 'Auktion wurde geplant.');
    }

    // geplante auktion löschen (nur admin, nur wenn noch nicht gestartet)
    public function destroy(Auction $auction)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        if ($auction->status !== 'geplant') {
            return back()->withErrors(['auction' => 'Nur geplante Auktionen können gelöscht werden.']);
        }

        $product = $auction->product;
        $auction->delete();

        return redirect()
            ->route('products.edit', $product)
            ->with('success', 'Auktion wurde gelöscht.');
    }

    // auktions-übersicht – alle aktiven + geplanten auktionen
    public function index()
    {
        // beim laden: geplante starten und abgelaufene schließen
        $this->statusAktualisieren();

        $aktiv = Auction::with(['product', 'bids'])
            ->where('status', 'aktiv')
            ->orderBy('end_time')
            ->get();

        $geplant = Auction::with('product')
            ->where('status', 'geplant')
            ->orderBy('start_time')
            ->get();

        return view('auction.index', compact('aktiv', 'geplant'));
    }

    // einzelne auktions-detailseite mit bietformular
    public function show(Auction $auction)
    {
        // beim laden: automatisch aktivieren oder schließen
        $this->statusAktualisieren();
        $auction->refresh();

        $auction->load(['product', 'bids' => fn($q) => $q->with('user')->orderByDesc('amount'), 'winner']);

        return view('auction.show', compact('auction'));
    }

    // gebot abgeben (nur eingeloggte nutzer)
    public function bid(Request $request, Auction $auction)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ], [
            'amount.required' => 'Bitte gib einen Betrag ein.',
            'amount.numeric'  => 'Der Betrag muss eine Zahl sein – keine Buchstaben erlaubt.',
            'amount.min'      => 'Der Betrag muss mindestens 0,01 € betragen.',
        ]);

        // auktion muss aktiv und noch nicht abgelaufen sein
        if ($auction->status !== 'aktiv' || $auction->abgelaufen()) {
            return back()->withErrors(['amount' => 'Diese Auktion ist bereits beendet oder noch nicht gestartet.']);
        }

        // mindestgebot: aktuelles höchstgebot + 1,00 €
        $mindest = $auction->hoechstGebot() + 1.00;
        if ($request->amount < $mindest) {
            return back()->withErrors(['amount' =>
                'Dein Gebot muss mindestens € ' . number_format($mindest, 2, ',', '.') .
                ' betragen (aktuelles Höchstgebot + 1,00 €).'
            ]);
        }

        // nutzer darf nicht auf eigenes höchstgebot bieten
        if ($auction->hoechstBieter()?->id === auth()->id()) {
            return back()->withErrors(['amount' => 'Du bist bereits der Höchstbietende. Warte auf ein anderes Gebot.']);
        }

        Bid::create([
            'auction_id' => $auction->id,
            'user_id'    => auth()->id(),
            'amount'     => $request->amount,
        ]);

        return back()->with('success', 'Dein Gebot über € ' . number_format($request->amount, 2, ',', '.') . ' wurde abgegeben.');
    }

    // hilfsmethode: geplante auktionen starten, abgelaufene schließen
    private function statusAktualisieren(): void
    {
        // geplante auktionen die jetzt starten sollten → aktiv setzen
        Auction::where('status', 'geplant')
            ->where('start_time', '<=', now())
            ->update(['status' => 'aktiv']);

        // aktive auktionen die abgelaufen sind → schließen
        $abgelaufene = Auction::where('status', 'aktiv')
            ->where('end_time', '<', now())
            ->get();

        foreach ($abgelaufene as $auktion) {
            $this->schliesseAuktion($auktion);
        }
    }

    // auktion schließen: gewinner setzen + bestellung anlegen
    private function schliesseAuktion(Auction $auction): void
    {
        $hoechstesBid = $auction->bids()->orderByDesc('amount')->first();

        $auction->update([
            'status'      => 'beendet',
            'winner_id'   => $hoechstesBid?->user_id,
            'winning_bid' => $hoechstesBid?->amount,
        ]);

        // nur wenn jemand geboten hat eine bestellung anlegen
        if (!$hoechstesBid) {
            return;
        }

        $winner = $hoechstesBid->user;
        $nameParts = explode(' ', $winner->name, 2);

        $order = Order::create([
            'user_id'         => $winner->id,
            'vorname'         => $nameParts[0],
            'nachname'        => $nameParts[1] ?? '-',
            // platzhalter – gewinner muss adresse noch ergänzen
            'strasse'         => 'Bitte Lieferadresse eintragen',
            'plz'             => '00000',
            'ort'             => 'Bitte ergänzen',
            'zahlungsmethode' => 'auktion',
            'total'           => $hoechstesBid->amount,
            'status'          => 'bezahlt',
        ]);

        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $auction->product_id,
            'name'       => $auction->product->name,
            'price'      => $hoechstesBid->amount,
            'quantity'   => 1,
        ]);

        // lagerbestand um 1 reduzieren
        $auction->product->decrement('stock');
    }
}
