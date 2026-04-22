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
            <label class="block text-slate-700 font-medium mb-1 text-sm">Artikelnummer</label>
            <input type="text" value="{{ $product->artikel_nr }}" disabled
                   class="w-full border border-slate-200 rounded-lg p-3 text-sm bg-slate-50 text-slate-400 cursor-not-allowed">
        </div>

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

    {{-- ────────────────────────────────────────────────────────── --}}
    {{-- Auktion planen – eigener Abschnitt unterhalb des Formulars --}}
    {{-- ────────────────────────────────────────────────────────── --}}
    <div class="mt-10 border-t border-slate-200 pt-8">

        <h2 class="text-lg font-bold text-slate-900 mb-1">Auktion planen</h2>
        <p class="text-slate-500 text-sm mb-5">
            Lagerbestand: <strong>{{ $product->stock }}</strong> Stück –
            verfügbar im Shop: <strong>{{ $product->verfuegbarImShop() }}</strong> –
            bereits geplante Auktionen: <strong>{{ $product->auctions()->whereIn('status', ['geplant', 'aktiv'])->count() }}</strong>
        </p>

        {{-- fehlermeldung für auktions-validation --}}
        @if($errors->has('auction'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-5 text-red-600 text-sm">
                {{ $errors->first('auction') }}
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-5 text-green-700 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- formular: neue auktion planen --}}
        <form action="{{ route('auctions.store', $product) }}" method="POST" class="space-y-4">
            @csrf

            @if($errors->hasAny(['start_price', 'start_time', 'end_time']))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-600 text-sm space-y-1">
                    @foreach($errors->only(['start_price', 'start_time', 'end_time']) as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-slate-700 font-medium mb-1 text-sm">Startpreis (€)</label>
                    <input type="number" name="start_price" value="{{ old('start_price') }}"
                           step="0.01" min="0.01" placeholder="z.B. 9.99"
                           class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                </div>
                <div>
                    <label class="block text-slate-700 font-medium mb-1 text-sm">Startdatum & Uhrzeit</label>
                    <input type="datetime-local" name="start_time" value="{{ old('start_time') }}"
                           class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                </div>
                <div>
                    <label class="block text-slate-700 font-medium mb-1 text-sm">Enddatum & Uhrzeit</label>
                    <input type="datetime-local" name="end_time" value="{{ old('end_time') }}"
                           class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                </div>
            </div>

            <button type="submit"
                    class="bg-orange-500 hover:bg-orange-600 text-white font-bold px-6 py-2.5 rounded-lg transition text-sm">
                Auktion planen
            </button>
        </form>

        {{-- liste der geplanten und aktiven auktionen für dieses produkt --}}
        @php
            $auktionen = $product->auctions()->orderBy('start_time')->get();
        @endphp

        @if($auktionen->isNotEmpty())
            <h3 class="font-semibold text-slate-800 mt-8 mb-3">Geplante & laufende Auktionen</h3>
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">Startpreis</th>
                            <th class="px-4 py-3 text-left">Start</th>
                            <th class="px-4 py-3 text-left">Ende</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Aktion</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($auktionen as $auktion)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-800">€ {{ number_format($auktion->start_price, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $auktion->start_time->format('d.m.Y H:i') }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $auktion->end_time->format('d.m.Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    @include('admin.partials.status-badge', ['status' => $auktion->status])
                                </td>
                                <td class="px-4 py-3">
                                    {{-- nur geplante auktionen können gelöscht werden --}}
                                    @if($auktion->status === 'geplant')
                                        <form action="{{ route('auctions.destroy', $auktion) }}" method="POST"
                                              onsubmit="return confirm('Auktion wirklich löschen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-600 hover:text-red-800 text-xs font-medium transition">
                                                Löschen
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-400 text-xs">–</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>

</div>
@endsection
