<?php

namespace App\Http\Controllers;

use App\Models\Auction;
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
}
