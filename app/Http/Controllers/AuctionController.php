<?php

namespace App\Http\Controllers;

use App\Mail\AuktionGewonnenMail;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AuctionController extends Controller
{
    // neue auktion für ein produkt anlegen (nur admin)
    public function store(Request $request, Product $product)
    {
        // sicherheitscheck: nur admins dürfen auktionen anlegen
        if (! auth()->user()->hasRole('admin')) {
            abort(403); // 403 = "verboten" – der nutzer ist eingeloggt aber nicht berechtigt
        }

        // eingaben vom formular prüfen – alle drei felder sind pflicht
        $request->validate([
            'start_price' => ['required', 'numeric', 'min:0.01'],
            'start_time'  => ['required', 'date', 'after:now'],     // startzeit muss in der zukunft liegen
            'end_time'    => ['required', 'date', 'after:start_time'], // endzeit muss nach startzeit liegen
        ], [
            // deutsche fehlermeldungen statt englischer standard-texte
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
        // ein produkt mit stock=3 kann maximal 3 gleichzeitige auktionen haben
        $bereitsGeplant = $product->auctions()
            ->whereIn('status', ['geplant', 'aktiv']) // nur laufende/geplante zählen, nicht beendete
            ->count();

        if ($bereitsGeplant >= $product->stock) {
            // mehr auktionen als lagerbestand würde bedeuten: wir versteigern produkte die wir nicht haben
            return back()
                ->withInput()
                ->withErrors([
                    'auction' => 'Nicht genug Lagerbestand für eine weitere Auktion. '.
                                 "Maximal {$product->stock} Auktion(en) möglich, ".
                                 "aktuell bereits {$bereitsGeplant} geplant.",
                ]);
        }

        // auktion in der datenbank speichern – status startet als 'geplant'
        Auction::create([
            'product_id'  => $product->id,
            'start_price' => $request->start_price,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'status'      => 'geplant', // wird automatisch auf 'aktiv' gesetzt wenn start_time erreicht
        ]);

        return redirect()
            ->route('products.edit', $product)
            ->with('success', 'Auktion wurde geplant.');
    }

    // geplante auktion löschen (nur admin, nur wenn noch nicht gestartet)
    public function destroy(Auction $auction)
    {
        // sicherheitscheck: nur admins dürfen löschen
        if (! auth()->user()->hasRole('admin')) {
            abort(403);
        }

        // laufende oder beendete auktionen dürfen nicht gelöscht werden
        // – es könnten bereits gebote vorhanden sein
        if ($auction->status !== 'geplant') {
            return back()->withErrors(['auction' => 'Nur geplante Auktionen können gelöscht werden.']);
        }

        // produkt merken um danach zurückzuleiten
        $product = $auction->product;
        $auction->delete();

        return redirect()
            ->route('products.edit', $product)
            ->with('success', 'Auktion wurde gelöscht.');
    }

    // auktions-übersicht – alle aktiven + geplanten auktionen
    public function index()
    {
        // vor der anzeige: status aller auktionen aktualisieren
        // das sorgt dafür dass geplante auktionen starten und abgelaufene geschlossen werden
        $this->statusAktualisieren();

        // aktive auktionen mit produkt und geboten laden (für anzeige des höchstgebots)
        $aktiv = Auction::with(['product', 'bids'])
            ->where('status', 'aktiv')
            ->orderBy('end_time') // zuerst die, die bald ablaufen
            ->get();

        // geplante auktionen – noch nicht gestartet
        $geplant = Auction::with('product')
            ->where('status', 'geplant')
            ->orderBy('start_time') // nach startzeit sortieren
            ->get();

        return view('auction.index', compact('aktiv', 'geplant'));
    }

    // einzelne auktions-detailseite mit bietformular
    public function show(Auction $auction)
    {
        // auch hier: status aktualisieren bevor wir die seite anzeigen
        $this->statusAktualisieren();
        // refresh: die $auction-variable neu aus der db laden, damit der aktualisierte status stimmt
        $auction->refresh();

        // produkt, alle gebote (absteigend sortiert) und gewinner vorladen
        $auction->load(['product', 'bids' => fn ($q) => $q->with('user')->orderByDesc('amount'), 'winner']);

        return view('auction.show', compact('auction'));
    }

    // gebot abgeben (nur eingeloggte nutzer)
    public function bid(Request $request, Auction $auction)
    {
        // mindest-validierung: der betrag muss eine zahl sein
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ], [
            'amount.required' => 'Bitte gib einen Betrag ein.',
            'amount.numeric'  => 'Der Betrag muss eine Zahl sein – keine Buchstaben erlaubt.',
            'amount.min'      => 'Der Betrag muss mindestens 0,01 € betragen.',
        ]);

        // auktion muss aktiv und noch nicht abgelaufen sein
        // – der status könnte 'aktiv' sein aber die end_time schon überschritten
        if ($auction->status !== 'aktiv' || $auction->abgelaufen()) {
            return back()->withErrors(['amount' => 'Diese Auktion ist bereits beendet oder noch nicht gestartet.']);
        }

        // mindestgebot: aktuelles höchstgebot + 1,00 €
        // verhindert winzige cent-gebote die die auktion nicht voranbringen
        $mindest = $auction->hoechstGebot() + 1.00;
        if ($request->amount < $mindest) {
            // fehlermeldung enthält den genauen mindestbetrag damit der nutzer weiß was er eingeben soll
            return back()->withErrors(['amount' => 'Dein Gebot muss mindestens € '.number_format($mindest, 2, ',', '.').
                ' betragen (aktuelles Höchstgebot + 1,00 €).',
            ]);
        }

        // nutzer darf nicht auf eigenes höchstgebot bieten – das wäre sinnlos
        // ?-> ist der "nullsafe operator": wenn hoechstBieter() null zurückgibt, kein fehler
        if ($auction->hoechstBieter()?->id === auth()->id()) {
            return back()->withErrors(['amount' => 'Du bist bereits der Höchstbietende. Warte auf ein anderes Gebot.']);
        }

        // gebot in der datenbank speichern
        Bid::create([
            'auction_id' => $auction->id,
            'user_id'    => auth()->id(),
            'amount'     => $request->amount,
        ]);

        return back()->with('success', 'Dein Gebot über € '.number_format($request->amount, 2, ',', '.').' wurde abgegeben.');
    }

    // hilfsmethode: geplante auktionen starten, abgelaufene schließen
    // wird beim laden der index- und detail-seite aufgerufen
    private function statusAktualisieren(): void
    {
        // alle geplanten auktionen deren startzeit erreicht ist → status auf 'aktiv' setzen
        // update() ändert direkt in der db ohne jedes objekt einzeln zu laden – effizient
        Auction::where('status', 'geplant')
            ->where('start_time', '<=', now())
            ->update(['status' => 'aktiv']);

        // alle aktiven auktionen die abgelaufen sind – einzeln schließen (wegen gewinner-logik)
        $abgelaufene = Auction::where('status', 'aktiv')
            ->where('end_time', '<', now())
            ->get();

        // für jede abgelaufene auktion die schliess-methode aufrufen
        foreach ($abgelaufene as $auktion) {
            $this->schliesseAuktion($auktion);
        }
    }

    // auktion schließen: gewinner setzen + bestellung automatisch anlegen
    private function schliesseAuktion(Auction $auction): void
    {
        // höchstes gebot dieser auktion ermitteln (sortiert nach betrag absteigend, erstes nehmen)
        $hoechstesBid = $auction->bids()->orderByDesc('amount')->first();

        // auktion als beendet markieren und gewinner eintragen
        // ?-> bedeutet: falls $hoechstesBid null ist (keine gebote), wird null eingetragen
        $auction->update([
            'status'      => 'beendet',
            'winner_id'   => $hoechstesBid?->user_id,
            'winning_bid' => $hoechstesBid?->amount,
        ]);

        // wenn niemand geboten hat, ist hier schluss – keine bestellung nötig
        if (! $hoechstesBid) {
            return;
        }

        $winner = $hoechstesBid->user;

        // name in vor- und nachname aufteilen – explode trennt an leerzeichen, max. 2 teile
        $nameParts = explode(' ', $winner->name, 2);

        // bestellung für den gewinner automatisch anlegen
        $order = Order::create([
            'user_id'         => $winner->id,
            'vorname'         => $nameParts[0],
            'nachname'        => $nameParts[1] ?? '-', // falls kein nachname vorhanden: bindestrich
            // platzhalter adresse – gewinner muss lieferadresse selbst ergänzen
            'strasse'         => 'Bitte Lieferadresse eintragen',
            'plz'             => '00000',
            'ort'             => 'Bitte ergänzen',
            'zahlungsmethode' => 'auktion', // keine echte zahlung – bereits durch das gebot "bezahlt"
            'total'           => $hoechstesBid->amount,
            'status'          => 'bezahlt', // direkt bezahlt – der gewinner hat ja geboten
        ]);

        // die einzelne auktions-position in der bestellung speichern
        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $auction->product_id,
            'name'       => $auction->product->name,
            'price'      => $hoechstesBid->amount,
            'quantity'   => 1, // auktionen sind immer einzelstücke
        ]);

        // lagerbestand um 1 reduzieren – das produkt ist jetzt vergeben
        $auction->product->decrement('stock');

        // gewinner per mail benachrichtigen – items vorher nachladen für die pdf-rechnung im anhang
        $order->load('items');
        Mail::to($winner->email)
            ->send(new AuktionGewonnenMail($order));
    }
}
