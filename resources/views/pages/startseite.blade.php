@extends('layouts.app')

@section('title', 'Willkommen')

{{-- auktions-banner wird im layout über @yield('banner') direkt unter der navigation ausgegeben --}}
@if($auktionBanner)
@section('banner')
<div style="background-color: #1a2e4a;" class="w-full border-t-4 border-orange-500 text-white py-8 px-6 shadow-lg">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center gap-8">

        {{-- produktbild --}}
        @if($auktionBanner->product->image)
            <img src="{{ asset('storage/' . $auktionBanner->product->image) }}"
                 class="w-36 h-36 object-cover rounded-xl shadow-lg shrink-0"
                 alt="{{ $auktionBanner->product->name }}">
        @else
            <div class="w-36 h-36 bg-slate-700 rounded-xl flex items-center justify-center shrink-0">
                <span class="text-5xl">🧸</span>
            </div>
        @endif

        {{-- infos --}}
        <div class="flex-1">
            <span class="text-orange-400 text-xs font-semibold uppercase tracking-widest">
                🔥 {{ $auktionBanner->status === 'aktiv' ? 'Laufende Auktion' : 'Demnächst' }}
            </span>
            <h2 class="text-2xl font-bold mt-1">{{ $auktionBanner->product->name }}</h2>
            <p class="text-slate-300 text-sm mt-2">{{ Str::limit($auktionBanner->product->description, 120) }}</p>
            <p class="text-slate-400 text-sm mt-2">
                @if($auktionBanner->status === 'aktiv')
                    Aktuelles Höchstgebot:
                    <span class="text-white font-semibold">€ {{ number_format($auktionBanner->hoechstGebot(), 2, ',', '.') }}</span>
                    <span class="text-slate-500">({{ $auktionBanner->bids->count() }} Gebote)</span>
                @else
                    Startet am {{ $auktionBanner->start_time->format('d.m.Y') }} um {{ $auktionBanner->start_time->format('H:i') }} Uhr
                @endif
            </p>
        </div>

        {{-- countdown + button --}}
        <div class="text-center shrink-0">
            @if($auktionBanner->status === 'aktiv')
                <p class="text-slate-400 text-xs uppercase tracking-wide mb-1">Auktion endet in</p>
                <p id="banner-countdown" class="text-4xl font-bold text-orange-400 tabular-nums tracking-tight"
                   data-end="{{ $auktionBanner->end_time->toIso8601String() }}">00:00:00</p>
            @else
                <p class="text-slate-400 text-xs uppercase tracking-wide mb-1">Startet in</p>
                <p id="banner-countdown" class="text-4xl font-bold text-orange-400 tabular-nums tracking-tight"
                   data-end="{{ $auktionBanner->start_time->toIso8601String() }}">00:00:00</p>
            @endif
            <a href="{{ route('auction.index') }}"
               class="mt-4 inline-block bg-orange-500 hover:bg-orange-600 text-white font-bold px-8 py-3 rounded-lg transition text-sm">
                {{ $auktionBanner->status === 'aktiv' ? 'Jetzt bieten →' : 'Zur Auktion →' }}
            </a>
        </div>

    </div>
</div>
@endsection
@endif

@section('content')
    <div class="max-w-7xl mx-auto px-6 py-16 text-center">
        <h1 class="text-4xl font-bold text-slate-900 mb-4">Willkommen bei CultPlanet</h1>
        <p class="text-lg text-slate-500 mb-8">Dein Onlineshop für cooles Spielzeug – täglich neue Auktionen.</p>
        <a href="{{ route('shop.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg transition">
            Zum Shop
        </a>
    </div>
@endsection

@push('scripts')
@if($auktionBanner)
<script>
    // countdown für den auktions-banner auf der startseite
    const bannerEl = document.getElementById('banner-countdown');
    if (bannerEl) {
        const zielzeit = new Date(bannerEl.dataset.end).getTime();

        function bannerCountdown() {
            const diff = zielzeit - Date.now();
            if (diff <= 0) {
                bannerEl.textContent = 'Abgelaufen';
                return;
            }
            const h = Math.floor(diff / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);
            bannerEl.textContent = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        }

        bannerCountdown();
        setInterval(bannerCountdown, 1000);
    }
</script>
@endif
@endpush
