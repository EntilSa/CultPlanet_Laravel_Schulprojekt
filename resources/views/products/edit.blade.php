@extends('layouts.app')

@section('title', 'Produkt bearbeiten')

@section('content')
<div class="max-w-2xl mx-auto px-6 py-8">

    <h1 class="text-2xl font-bold text-slate-900 mb-6">Produkt bearbeiten</h1>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <ul class="text-red-600 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PATCH')

        <div>
            <label class="block text-slate-700 font-medium mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}"
                   class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
        </div>

        <div>
            <label class="block text-slate-700 font-medium mb-1">Beschreibung</label>
            <textarea name="description" rows="4"
                      class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="flex gap-4">
            <div class="flex-1">
                <label class="block text-slate-700 font-medium mb-1">Preis (€)</label>
                <input type="number" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0"
                       class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            </div>
            <div class="flex-1">
                <label class="block text-slate-700 font-medium mb-1">Lagerbestand</label>
                <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" min="0"
                       class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            </div>
        </div>

        <div>
            <label class="block text-slate-700 font-medium mb-1">Neues Bild (optional, ersetzt das alte)</label>
            {{-- aktuelles bild anzeigen falls vorhanden --}}
            @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" class="h-24 rounded-lg mb-2 object-cover" alt="">
            @endif
            <input type="file" name="image" accept="image/*"
                   class="w-full border border-slate-300 rounded-lg p-3 text-sm bg-white">
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg transition">
                Speichern
            </button>
            <a href="{{ route('shop.show', $product) }}"
               class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium px-6 py-2.5 rounded-lg transition">
                Abbrechen
            </a>
            {{-- produkt löschen --}}
            <form action="{{ route('products.destroy', $product) }}" method="POST" class="ml-auto"
                  onsubmit="return confirm('Produkt wirklich löschen?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white font-medium px-6 py-2.5 rounded-lg transition">
                    Löschen
                </button>
            </form>
        </div>
    </form>

</div>
@endsection
