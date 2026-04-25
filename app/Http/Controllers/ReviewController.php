<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // bewertung speichern
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'text' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        // prüfen ob der nutzer dieses produkt schon bewertet hat
        $schonBewertet = Review::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->exists();

        if ($schonBewertet) {
            return back()->with('error', 'Du hast dieses Produkt bereits bewertet.');
        }

        Review::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'rating' => $request->rating,
            'text' => $request->text,
        ]);

        return back()->with('success', 'Deine Bewertung wurde gespeichert!');
    }
}
