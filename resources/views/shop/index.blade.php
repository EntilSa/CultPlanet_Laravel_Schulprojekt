@extends('layouts.app')

@section('title', 'Shop')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">

    {{-- auktions-banner: nur anzeigen wenn eine auktion läuft oder geplant ist --}}
    @if($auktionBanner && $auktionBanner->product)
        <div style="background-color: #1a2e4a;" class="w-full border-t-4 border-orange-500 text-white py-8 px-6 rounded-xl shadow-lg mb-8">
            <div class="flex flex-col md:flex-row items-center gap-8">

                {{-- produktbild --}}
                @if($auktionBanner->product->image)
                    <img src="{{ asset('storage/' . $auktionBanner->product->image) }}"
                         class="w-32 h-32 object-cover rounded-xl shadow-lg shrink-0" alt="">
                @endif

                {{-- infos --}}
                <div class="flex-1">
                    <span class="text-orange-400 text-xs font-semibold uppercase tracking-widest">
                        🔥 {{ $auktionBanner->status === 'aktiv' ? 'Tagesauktion – läuft jetzt' : 'Demnächst – Tagesauktion' }}
                    </span>
                    <h2 class="text-2xl font-bold mt-1">{{ $auktionBanner->product->name }}</h2>
                    <p class="text-slate-300 text-sm mt-2">{{ Str::limit($auktionBanner->product->description, 120) }}</p>
                    @if($auktionBanner->status === 'aktiv')
                        <p class="text-slate-400 text-sm mt-2">
                            Aktuelles Höchstgebot:
                            <span class="text-white font-semibold">
                                € {{ number_format($auktionBanner->bids()->max('amount') ?? $auktionBanner->start_price, 2, ',', '.') }}
                            </span>
                            <span class="text-slate-500">({{ $auktionBanner->bids_count }} Gebote)</span>
                        </p>
                    @else
                        <p class="text-slate-400 text-sm mt-2">
                            Startet am {{ $auktionBanner->start_time->format('d.m.Y') }} um {{ $auktionBanner->start_time->format('H:i') }} Uhr
                        </p>
                    @endif
                </div>

                {{-- countdown + button --}}
                <div class="text-center shrink-0">
                    @if($auktionBanner->status === 'aktiv')
                        <p class="text-slate-400 text-xs uppercase tracking-wide mb-1">Auktion endet in</p>
                        <p id="banner-countdown" class="text-4xl font-bold text-orange-400 tabular-nums tracking-tight">–</p>
                    @endif
                    <a href="{{ route('auction.index') }}"
                       class="mt-4 inline-block bg-orange-500 hover:bg-orange-600 text-white font-bold px-8 py-3 rounded-lg transition text-sm">
                        Jetzt bieten →
                    </a>
                </div>

            </div>
        </div>
    @endif

    {{-- kopfzeile mit produktanzahl --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Alle Produkte</h1>
            <p class="text-slate-500 text-sm mt-1">{{ $products->total() }} Artikel gefunden</p>
        </div>
        @auth
            @if(auth()->user()->hasRole('admin'))
                <a href="{{ route('products.create') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2.5 rounded-lg text-sm transition">
                    + Neues Produkt
                </a>
            @endif
        @endauth
    </div>

    {{-- such- und filterleiste --}}
    <form method="GET" action="{{ route('shop.index') }}" class="bg-white rounded-xl shadow p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-end">

            {{-- suchfeld --}}
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-slate-600 mb-1">Suche</label>
                <input type="text" name="suche" value="{{ request('suche') }}"
                       placeholder="z.B. LEGO, Monopoly..."
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- preisbereich --}}
            <div class="flex gap-2 items-end">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Preis von €</label>
                    <input type="number" name="preis_min" value="{{ request('preis_min') }}"
                           min="0" step="0.01" placeholder="0"
                           class="w-24 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">bis €</label>
                    <input type="number" name="preis_max" value="{{ request('preis_max') }}"
                           min="0" step="0.01" placeholder="999"
                           class="w-24 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- sortierung --}}
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Sortierung</label>
                <select name="sortierung"
                        class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="neueste"    {{ request('sortierung') === 'neueste'    ? 'selected' : '' }}>Neueste zuerst</option>
                    <option value="preis_asc"  {{ request('sortierung') === 'preis_asc'  ? 'selected' : '' }}>Preis: aufsteigend</option>
                    <option value="preis_desc" {{ request('sortierung') === 'preis_desc' ? 'selected' : '' }}>Preis: absteigend</option>
                    <option value="bewertung"  {{ request('sortierung') === 'bewertung'  ? 'selected' : '' }}>Beste Bewertung</option>
                </select>
            </div>

            {{-- nur verfügbare --}}
            <div class="flex items-center gap-2 pb-2">
                <input type="checkbox" name="nur_verfuegbar" id="nur_verfuegbar" value="1"
                       {{ request('nur_verfuegbar') ? 'checked' : '' }}
                       class="w-4 h-4 text-blue-600 rounded border-slate-300">
                <label for="nur_verfuegbar" class="text-sm text-slate-700 whitespace-nowrap">Nur verfügbare</label>
            </div>

            {{-- buttons --}}
            <div class="flex gap-2 pb-0.5">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg text-sm transition">
                    Suchen
                </button>
                @if(request()->anyFilled(['suche', 'preis_min', 'preis_max', 'sortierung', 'nur_verfuegbar']))
                    <a href="{{ route('shop.index') }}"
                       class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium px-4 py-2 rounded-lg text-sm transition">
                        Zurücksetzen
                    </a>
                @endif
            </div>
        </div>

        {{-- aktive filter als chips anzeigen --}}
        @php
            $aktiveFilter = [];
            if(request('suche'))          $aktiveFilter[] = 'Suche: "' . request('suche') . '"';
            if(request('preis_min'))      $aktiveFilter[] = 'Ab € ' . number_format(request('preis_min'), 2, ',', '.');
            if(request('preis_max'))      $aktiveFilter[] = 'Bis € ' . number_format(request('preis_max'), 2, ',', '.');
            if(request('nur_verfuegbar')) $aktiveFilter[] = 'Nur verfügbare';
        @endphp
        @if(count($aktiveFilter) > 0)
            <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-slate-100">
                <span class="text-xs text-slate-500 self-center">Aktive Filter:</span>
                @foreach($aktiveFilter as $filter)
                    <span class="bg-blue-50 text-blue-700 text-xs font-medium px-2.5 py-1 rounded-full">
                        {{ $filter }}
                    </span>
                @endforeach
            </div>
        @endif
    </form>

    {{-- produkte vorhanden: grid anzeigen --}}
    @if($products->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($products as $product)
                {{-- hover: schatten größer + karte leicht anheben --}}
                <div class="bg-white rounded-xl shadow p-4 flex flex-col hover:shadow-lg hover:-translate-y-1 transition-all duration-200">

                    {{-- produktbild – klickbar zur detailseite --}}
                    <a href="{{ route('shop.show', $product) }}" class="relative w-full h-48 bg-slate-50 rounded-lg mb-4 overflow-hidden flex items-center justify-center">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}"
                                 class="w-full h-full object-cover" alt="{{ $product->name }}">
                        @else
                            <span class="text-slate-300 text-5xl">🧸</span>
                        @endif

                        {{-- badge: ausverkauft, in auktion oder neu --}}
                        @php $verfuegbar = $product->verfuegbarImShop(); @endphp
                        @if($product->stock === 0)
                            <span class="absolute top-3 right-3 bg-slate-500 text-white text-xs font-semibold px-2 py-1 rounded-md">Ausverkauft</span>
                        @elseif($verfuegbar === 0)
                            <span class="absolute top-3 right-3 bg-orange-500 text-white text-xs font-semibold px-2 py-1 rounded-md">In Auktion</span>
                        @elseif($product->created_at->isCurrentMonth())
                            <span class="absolute top-3 left-3 bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded-md">NEU</span>
                        @endif
                    </a>

                    {{-- produktname – klickbar zur detailseite --}}
                    <a href="{{ route('shop.show', $product) }}" class="hover:text-blue-600 transition-colors">
                        <h3 class="text-slate-800 font-semibold text-base leading-snug">{{ $product->name }}</h3>
                    </a>

                    {{-- sterne --}}
                    <div class="flex items-center gap-1 mt-1">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="{{ $i <= round($product->reviews_avg_rating ?? 0) ? 'text-orange-400' : 'text-slate-300' }} text-sm">★</span>
                        @endfor
                        <span class="text-slate-400 text-xs ml-1">({{ $product->reviews_count }})</span>
                    </div>

                    {{-- preis + buttons --}}
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

        {{-- seitennavigation auf deutsch --}}
        <div class="mt-10">
            @if($products->total() > $products->perPage())
                <p class="text-center text-slate-500 text-sm mb-3">
                    Zeige {{ $products->firstItem() }} bis {{ $products->lastItem() }} von {{ $products->total() }} Artikeln
                </p>
            @endif
            {{ $products->links() }}
        </div>

    @else
        {{-- keine treffer --}}
        <div class="text-center py-24 text-slate-400">
            <p class="text-5xl mb-4">🔍</p>
            <p class="text-lg font-medium">Keine Produkte gefunden.</p>
            <p class="text-sm mt-1">
                Versuche andere Suchbegriffe oder
                <a href="{{ route('shop.index') }}" class="text-blue-600 hover:underline">alle Produkte anzeigen</a>.
            </p>
        </div>
    @endif

</div>

{{-- countdown-script für den auktions-banner --}}
@if(isset($auktionBanner) && $auktionBanner && $auktionBanner->status === 'aktiv')
@push('scripts')
<script>
    const bannerEndZeit = new Date("{{ $auktionBanner->end_time->toIso8601String() }}").getTime();

    function bannerCountdown() {
        const jetzt = Date.now();
        const diff  = bannerEndZeit - jetzt;
        const el    = document.getElementById('banner-countdown');
        if (!el) return;

        if (diff <= 0) {
            el.textContent = 'Beendet';
            return;
        }

        const h = Math.floor(diff / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);

        el.textContent =
            String(h).padStart(2, '0') + ':' +
            String(m).padStart(2, '0') + ':' +
            String(s).padStart(2, '0');
    }

    bannerCountdown();
    setInterval(bannerCountdown, 1000);
</script>
@endpush
@endif

@endsection
