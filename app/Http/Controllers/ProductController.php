<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // shop übersicht – alle produkte als liste anzeigen
    public function index()
    {
        $products = Product::withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest()
            ->paginate(12);

        return view('shop.index', compact('products'));
    }

    // einzelne produktseite anzeigen
    public function show(Product $product)
    {
        $product->load(['reviews.user']);
        $product->loadAvg('reviews', 'rating');
        $product->loadCount('reviews');

        return view('shop.show', compact('product'));
    }

    // formular für neues produkt anzeigen (nur admin)
    public function create()
    {
        // nur admins dürfen produkte anlegen
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        return view('products.create');
    }

    // neues produkt speichern (nur admin)
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price'       => ['required', 'numeric', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
            'image'       => ['nullable', 'image', 'max:2048'], // max 2 mb
        ]);

        // bild hochladen falls vorhanden, sonst null
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::create([
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => $request->price,
            'stock'       => $request->stock,
            'image'       => $imagePath,
        ]);

        return redirect()->route('shop.index')->with('success', 'Produkt wurde angelegt.');
    }

    // bearbeitungsformular für bestehendes produkt (nur admin)
    public function edit(Product $product)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        return view('products.edit', compact('product'));
    }

    // bestehendes produkt aktualisieren (nur admin)
    public function update(Request $request, Product $product)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price'       => ['required', 'numeric', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
            'image'       => ['nullable', 'image', 'max:2048'],
        ]);

        $imagePath = $product->image;

        // wenn ein neues bild hochgeladen wird, altes löschen und neues speichern
        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => $request->price,
            'stock'       => $request->stock,
            'image'       => $imagePath,
        ]);

        return redirect()->route('shop.show', $product)->with('success', 'Produkt wurde aktualisiert.');
    }

    // produkt löschen (nur admin)
    public function destroy(Product $product)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        // bild aus dem storage löschen falls vorhanden
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('shop.index')->with('success', 'Produkt wurde gelöscht.');
    }
}
