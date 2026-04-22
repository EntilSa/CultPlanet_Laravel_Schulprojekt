@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">

    {{-- admin-links: bearbeiten (nur für admins sichtbar) --}}
    @auth
        @if(auth()->user()->hasRole('admin'))
            <div class="flex gap-3 mb-6">
                <a href="{{ route('products.edit', $product) }}"
                   class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium px-4 py-2 rounded-lg text-sm transition">
                    Produkt bearbeiten
                </a>
            </div>
        @endif
    @endauth

    {{-- Breadcrumb: Shop > Produktname --}}
    <nav class="text-sm text-slate-500 mb-6">
        <a href="{{ route('shop.index') }}" class="hover:text-blue-600 transition-colors">Shop</a>
        <span class="mx-2">›</span>
        <span class="text-slate-800 font-medium">{{ $product->name }}</span>
    </nav>

    {{-- Produkt: Bild links, Info rechts --}}
    <div class="flex flex-col lg:flex-row gap-10">

        {{-- Bild --}}
        <div class="lg:w-1/2">
            <div class="bg-white rounded-2xl shadow p-8 flex items-center justify-center" style="min-height: 420px;">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}"
                         class="max-h-80 object-contain" alt="{{ $product->name }}">
                @else
                    <span class="text-slate-200 text-9xl">🧸</span>
                @endif
            </div>
        </div>

        {{-- Infos --}}
        <div class="lg:w-1/2 flex flex-col">
            <h1 class="text-3xl font-bold text-slate-900 leading-tight">{{ $product->name }}</h1>

            {{-- Sterne + Anzahl --}}
            <div class="flex items-center gap-2 mt-3">
                @for($i = 1; $i <= 5; $i++)
                    <span class="{{ $i <= round($product->reviews_avg_rating ?? 0) ? 'text-orange-400' : 'text-slate-300' }} text-lg">★</span>
                @endfor
                <span class="text-slate-600 text-sm font-medium">{{ number_format($product->reviews_avg_rating ?? 0, 1) }} von 5</span>
                <span class="text-slate-400 text-sm">({{ $product->reviews_count }} Bewertungen)</span>
            </div>

            {{-- Preis --}}
            <div class="mt-5">
                <span class="text-4xl font-bold text-blue-600">€ {{ number_format($product->price, 2, ',', '.') }}</span>
            </div>
            <p class="text-slate-500 text-sm mt-1">inkl. MwSt., zzgl. Versandkosten</p>

            {{-- Lagerbestand --}}
            <div class="flex items-center gap-2 mt-4">
                @if($product->stock > 0)
                    <span class="w-2.5 h-2.5 bg-green-500 rounded-full"></span>
                    <span class="text-green-700 text-sm font-medium">Auf Lager (noch {{ $product->stock }} verfügbar)</span>
                @else
                    <span class="w-2.5 h-2.5 bg-red-500 rounded-full"></span>
                    <span class="text-red-600 text-sm font-medium">Ausverkauft</span>
                @endif
            </div>

            {{-- In den Warenkorb --}}
            @if($product->stock > 0)
                <form action="{{ route('cart.add', $product) }}" method="POST" class="flex items-center gap-4 mt-6">
                    @csrf
                    <div class="flex items-center border border-slate-300 rounded-lg overflow-hidden">
                        <button type="button" onclick="changeQty(-1)"
                                class="px-4 py-3 text-slate-600 hover:bg-slate-100 transition font-bold text-lg">−</button>
                        <input type="number" name="quantity" id="qty" value="1" min="1" max="{{ $product->stock }}"
                               class="w-12 py-3 text-center font-semibold text-slate-800 border-x border-slate-300 focus:outline-none">
                        <button type="button" onclick="changeQty(1)"
                                class="px-4 py-3 text-slate-600 hover:bg-slate-100 transition font-bold text-lg">+</button>
                    </div>
                    <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition text-sm">
                        In den Warenkorb
                    </button>
                </form>
            @endif

            {{-- Trennlinie --}}
            <hr class="my-6 border-slate-200">

            {{-- Beschreibung --}}
            <h3 class="font-semibold text-slate-800 mb-2">Beschreibung</h3>
            <p class="text-slate-600 text-sm leading-relaxed">{{ $product->description }}</p>
        </div>
    </div>

    {{-- Bewertungen – Abschnitt kommt wenn ReviewController fertig ist --}}
    <section class="mt-16">
        <h2 class="text-2xl font-bold text-slate-900 mb-6">Kundenbewertungen</h2>

        @if($product->reviews_count > 0)
            {{-- Übersicht-Box --}}
            <div class="bg-white rounded-xl shadow p-6 mb-6 flex flex-col sm:flex-row items-center gap-8">
                <div class="text-center">
                    <p class="text-6xl font-bold text-slate-900">{{ number_format($product->reviews_avg_rating, 1) }}</p>
                    <div class="text-orange-400 text-xl mt-1">★★★★★</div>
                    <p class="text-slate-500 text-sm mt-1">{{ $product->reviews_count }} Bewertungen</p>
                </div>
                <div class="flex-1 w-full space-y-2">
                    @foreach([5,4,3,2,1] as $star)
                        @php $pct = $product->reviews_count > 0 ? round(($product->reviews->where('rating', $star)->count() / $product->reviews_count) * 100) : 0; @endphp
                        <div class="flex items-center gap-3 text-sm">
                            <span class="text-slate-500 w-8">{{ $star }} ★</span>
                            <div class="flex-1 bg-slate-100 rounded-full h-2.5">
                                <div class="bg-orange-400 h-2.5 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="text-slate-500 w-10 text-right">{{ $pct }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Einzelne Bewertungen --}}
            <div class="space-y-4">
                @foreach($product->reviews as $review)
                    <div class="bg-white rounded-xl shadow p-6">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center font-bold text-blue-700 text-sm">
                                    {{ strtoupper(substr($review->user->name, 0, 2)) }}
                                </div>
                                <span class="font-semibold text-slate-800">{{ $review->user->name }}</span>
                            </div>
                            <span class="text-slate-400 text-xs">{{ $review->created_at->format('d.m.Y') }}</span>
                        </div>
                        <div class="mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="{{ $i <= $review->rating ? 'text-orange-400' : 'text-slate-300' }} text-sm">★</span>
                            @endfor
                        </div>
                        <p class="text-slate-600 text-sm leading-relaxed">{{ $review->text }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-slate-400 text-sm">Noch keine Bewertungen vorhanden.</p>
        @endif

        {{-- Bewertung schreiben – Formular kommt wenn ReviewController fertig ist --}}
        <div class="mt-6 bg-white rounded-xl shadow p-6 border-2 border-dashed border-slate-200">
            <p class="text-slate-400 text-sm">Bewertungen abgeben wird in Kürze freigeschaltet.</p>
        </div>
    </section>

</div>
@push('scripts')
<script>
// menge im eingabefeld erhöhen oder verringern, nicht unter 1 und nicht über max
function changeQty(delta) {
    const input = document.getElementById('qty');
    const newVal = parseInt(input.value) + delta;
    if (newVal >= 1 && newVal <= parseInt(input.max)) {
        input.value = newVal;
    }
}
</script>
@endpush

@endsection
