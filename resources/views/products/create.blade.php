@extends('layouts.app')

@section('title', 'Neues Produkt')

@section('content')
<div class="max-w-2xl mx-auto px-6 py-8">

    <h1 class="text-2xl font-bold text-slate-900 mb-6">Neues Produkt anlegen</h1>

    {{-- fehlermeldungen anzeigen falls validierung fehlschlägt --}}
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <ul class="text-red-600 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- enctype multipart/form-data ist nötig für datei-uploads --}}
    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <div>
            <label class="block text-slate-700 font-medium mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name') }}"
                   class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
        </div>

        <div>
            <label class="block text-slate-700 font-medium mb-1">Beschreibung</label>
            <textarea name="description" rows="4"
                      class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">{{ old('description') }}</textarea>
        </div>

        <div class="flex gap-4">
            <div class="flex-1">
                <label class="block text-slate-700 font-medium mb-1">Preis (€)</label>
                <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0"
                       class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            </div>
            <div class="flex-1">
                <label class="block text-slate-700 font-medium mb-1">Lagerbestand</label>
                <input type="number" name="stock" value="{{ old('stock', 0) }}" min="0"
                       class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            </div>
        </div>

        <div>
            <label class="block text-slate-700 font-medium mb-1">Bild (optional, max. 2 MB)</label>
            <input type="file" name="image" accept="image/*"
                   class="w-full border border-slate-300 rounded-lg p-3 text-sm bg-white">
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg transition">
                Produkt anlegen
            </button>
            <a href="{{ route('shop.index') }}"
               class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium px-6 py-2.5 rounded-lg transition">
                Abbrechen
            </a>
        </div>
    </form>

</div>
@endsection
