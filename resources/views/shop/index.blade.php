@extends('layouts.app')

@section('title', 'Shop')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">

    {{-- Kopfzeile mit Produktanzahl --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Alle Produkte</h1>
            <p class="text-slate-500 text-sm mt-1">{{ $products->total() }} Artikel verfügbar</p>
        </div>
        {{-- "Neues Produkt"-Button nur für admins --}}
        @auth
            @if(auth()->user()->hasRole('admin'))
                <a href="{{ route('products.create') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2.5 rounded-lg text-sm transition">
                    + Neues Produkt
                </a>
            @endif
        @endauth
    </div>

    {{-- Produkte vorhanden: Grid anzeigen --}}
    @if($products->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($products as $product)
                <div class="bg-white rounded-xl shadow p-4 flex flex-col hover:shadow-md transition-shadow">

                    {{-- Produktbild --}}
                    <div class="relative w-full h-48 bg-slate-50 rounded-lg mb-4 overflow-hidden flex items-center justify-center">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}"
                                 class="w-full h-full object-cover" alt="{{ $product->name }}">
                        @else
                            {{-- platzhalter wenn kein bild hochgeladen wurde --}}
                            <span class="text-slate-300 text-5xl">🧸</span>
                        @endif

                        {{-- Badge: AUSVERKAUFT, IN AUKTION oder NEU --}}
                        @php $verfuegbar = $product->verfuegbarImShop(); @endphp
                        @if($product->stock === 0)
                            <span class="absolute top-3 right-3 bg-slate-500 text-white text-xs font-semibold px-2 py-1 rounded-md">Ausverkauft</span>
                        @elseif($verfuegbar === 0)
                            <span class="absolute top-3 right-3 bg-orange-500 text-white text-xs font-semibold px-2 py-1 rounded-md">In Auktion</span>
                        @elseif($product->created_at->isCurrentMonth())
                            <span class="absolute top-3 left-3 bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded-md">NEU</span>
                        @endif
                    </div>

                    {{-- Name --}}
                    <h3 class="text-slate-800 font-semibold text-base leading-snug">{{ $product->name }}</h3>

                    {{-- Sterne --}}
                    <div class="flex items-center gap-1 mt-1">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="{{ $i <= round($product->reviews_avg_rating ?? 0) ? 'text-orange-400' : 'text-slate-300' }} text-sm">★</span>
                        @endfor
                        <span class="text-slate-400 text-xs ml-1">({{ $product->reviews_count }})</span>
                    </div>

                    {{-- Preis + Buttons --}}
                    <div class="flex items-center justify-between mt-auto pt-4">
                        <span class="text-blue-600 font-bold text-xl">€ {{ number_format($product->price, 2, ',', '.') }}</span>

                        <div class="flex gap-2">
                            <a href="{{ route('shop.show', $product) }}"
                               class="bg-slate-200 hover:bg-slate-300 text-slate-700 py-2 px-3 rounded-lg text-sm font-medium transition">
                                Ansehen
                            </a>
                            @if($verfuegbar > 0)
                                <form action="{{ route('cart.add', $product) }}" method="POST">
                                    @csrf
                                    <button class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-3 rounded-lg text-sm font-medium transition">
                                        + Warenkorb
                                    </button>
                                </form>
                            @elseif($product->stock === 0)
                                <button disabled class="bg-slate-200 text-slate-400 py-2 px-3 rounded-lg text-sm font-medium cursor-not-allowed">
                                    Ausverkauft
                                </button>
                            @else
                                <button disabled class="bg-orange-100 text-orange-500 py-2 px-3 rounded-lg text-sm font-medium cursor-not-allowed">
                                    In Auktion
                                </button>
                            @endif
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        {{-- Seitennavigation --}}
        <div class="mt-10">
            {{ $products->links() }}
        </div>

    @else
        {{-- keine produkte vorhanden --}}
        <div class="text-center py-24 text-slate-400">
            <p class="text-5xl mb-4">🏪</p>
            <p class="text-lg font-medium">Noch keine Produkte vorhanden.</p>
            <p class="text-sm mt-1">Produkte werden bald hinzugefügt.</p>
        </div>
    @endif

</div>
@endsection
