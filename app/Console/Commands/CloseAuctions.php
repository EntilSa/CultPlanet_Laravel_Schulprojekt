<?php

namespace App\Console\Commands;

use App\Mail\AuktionGewonnenMail;
use App\Models\Auction;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CloseAuctions extends Command
{
    // so wird der command aufgerufen: php artisan auctions:close
    protected $signature = 'auctions:close';

    protected $description = 'Geplante Auktionen aktivieren und abgelaufene Auktionen schließen';

    public function handle(): void
    {
        // schritt 1: geplante auktionen aktivieren die jetzt starten sollten
        $zuAktivieren = Auction::where('status', 'geplant')
            ->where('start_time', '<=', now())
            ->get();

        foreach ($zuAktivieren as $auktion) {
            $auktion->update(['status' => 'aktiv']);
            $this->info("Auktion #{$auktion->id} aktiviert: {$auktion->product->name}");
        }

        // schritt 2: aktive auktionen schließen die abgelaufen sind
        $zuSchliessen = Auction::where('status', 'aktiv')
            ->where('end_time', '<', now())
            ->with(['bids', 'product'])
            ->get();

        foreach ($zuSchliessen as $auktion) {
            $this->schliesseAuktion($auktion);
            $this->info("Auktion #{$auktion->id} geschlossen: {$auktion->product->name}");
        }

        $this->info("Fertig. {$zuAktivieren->count()} aktiviert, {$zuSchliessen->count()} geschlossen.");
    }

    // gleiche logik wie im AuctionController – gewinner setzen + bestellung anlegen
    private function schliesseAuktion(Auction $auction): void
    {
        $hoechstesBid = $auction->bids()->orderByDesc('amount')->first();

        $auction->update([
            'status' => 'beendet',
            'winner_id' => $hoechstesBid?->user_id,
            'winning_bid' => $hoechstesBid?->amount,
        ]);

        // ohne gebot keine bestellung
        if (! $hoechstesBid) {
            return;
        }

        $winner = $hoechstesBid->user;
        $nameParts = explode(' ', $winner->name, 2);

        $order = Order::create([
            'user_id' => $winner->id,
            'vorname' => $nameParts[0],
            'nachname' => $nameParts[1] ?? '-',
            'strasse' => 'Bitte Lieferadresse eintragen',
            'plz' => '00000',
            'ort' => 'Bitte ergänzen',
            'zahlungsmethode' => 'auktion',
            'total' => $hoechstesBid->amount,
            'status' => 'bezahlt',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $auction->product_id,
            'name' => $auction->product->name,
            'price' => $hoechstesBid->amount,
            'quantity' => 1,
        ]);

        // lagerbestand um 1 reduzieren
        $auction->product->decrement('stock');

        // gewinner per mail benachrichtigen (mit pdf-rechnung)
        $order->load('items'); // items nachladen
        Mail::to($winner->email)
            ->send(new AuktionGewonnenMail($order));
    }
}
