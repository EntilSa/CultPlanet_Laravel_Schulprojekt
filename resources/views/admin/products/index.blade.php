@extends('layouts.app')

@section('title', 'Produkte verwalten')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Produkte verwalten</h1>
        <a href="{{ route('admin.dashboard') }}"
           class="text-slate-500 hover:text-slate-700 text-sm transition-colors">← Dashboard</a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Aktions-Buttons --}}
    <div class="flex gap-3 mb-6">
        <a href="{{ route('products.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2.5 rounded-lg transition text-sm">
            + Neues Produkt
        </a>

        {{-- attrappe: importieren-button – symbolisiert api-import aus warenwirtschaftssystem --}}
        <button type="button" disabled
                title="API-Import aus Warenwirtschaftssystem – in Entwicklung"
                class="bg-slate-200 text-slate-400 font-medium px-4 py-2.5 rounded-lg text-sm cursor-not-allowed flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Artikel importieren
        </button>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Art.-Nr.</th>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Preis</th>
                    <th class="px-6 py-3 text-left">Lagerbestand</th>
                    <th class="px-6 py-3 text-left">Verfügbar im Shop</th>
                    <th class="px-6 py-3 text-left">Aktionen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($products as $product)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 text-slate-500 font-mono text-xs">{{ $product->artikel_nr }}</td>
                        <td class="px-6 py-3 font-medium text-slate-800">{{ $product->name }}</td>
                        <td class="px-6 py-3 text-slate-700">€ {{ number_format($product->price, 2, ',', '.') }}</td>
                        <td class="px-6 py-3 text-slate-700">{{ $product->stock }}</td>
                        <td class="px-6 py-3">
                            @php $verfuegbar = $product->verfuegbarImShop(); @endphp
                            @if($verfuegbar === 0)
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-700">In Auktion</span>
                            @else
                                <span class="text-slate-700">{{ $verfuegbar }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex gap-2">
                                <a href="{{ route('products.edit', $product) }}"
                                   class="bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-medium px-3 py-1.5 rounded-lg transition">
                                    Bearbeiten
                                </a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST"
                                      onsubmit="return confirm('Produkt wirklich löschen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium px-3 py-1.5 rounded-lg transition">
                                        Löschen
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">Noch keine Produkte angelegt.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>

</div>
@endsection
