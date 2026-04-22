@extends('layouts.app')

@section('title', 'Auktionen')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">

    <h1 class="text-2xl font-bold text-slate-900 mb-2">Auktionen</h1>
    <p class="text-slate-500 text-sm mb-8">Biete auf exklusive Artikel – das höchste Gebot gewinnt.</p>

    {{-- aktive auktionen als grid --}}
    @if($aktiv->isNotEmpty())
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Jetzt laufend</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            @foreach($aktiv as $auktion)
                <div class="bg-white rounded-xl shadow hover:shadow-md transition-shadow overflow-hidden flex flex-col">

                    {{-- produktbild --}}
                    <div class="relative h-48 bg-slate-50 flex items-center justify-center overflow-hidden">
                        @if($auktion->product->image)
                            <img src="{{ asset('storage/' . $auktion->product->image) }}"
                                 class="w-full h-full object-cover" alt="{{ $auktion->product->name }}">
                        @else
                            <span class="text-slate-200 text-6xl">🧸</span>
                        @endif
                        <span class="absolute top-3 left-3 bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded-md">
                            Live
                        </span>
                    </div>

                    <div class="p-4 flex flex-col flex-1">
                        <h3 class="font-semibold text-slate-800 leading-snug">{{ $auktion->product->name }}</h3>

                        <div class="mt-3 flex items-center justify-between text-sm">
                            <div>
                                <p class="text-slate-400 text-xs">Aktuelles Höchstgebot</p>
                                <p class="text-blue-600 font-bold text-lg">€ {{ number_format($auktion->hoechstGebot(), 2, ',', '.') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-slate-400 text-xs">Gebote</p>
                                <p class="font-semibold text-slate-700">{{ $auktion->bids->count() }}</p>
                            </div>
                        </div>

                        {{-- mini-countdown --}}
                        <div class="mt-3 bg-slate-50 rounded-lg px-3 py-2 text-center">
                            <p class="text-slate-400 text-xs mb-0.5">Endet in</p>
                            <p class="font-bold text-orange-500 tabular-nums text-sm"
                               data-countdown="{{ $auktion->end_time->toIso8601String() }}">
                                –
                            </p>
                        </div>

                        <a href="{{ route('auction.show', $auktion) }}"
                           class="mt-4 bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 rounded-lg text-sm text-center transition">
                            Jetzt bieten →
                        </a>
                    </div>

                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-xl shadow p-10 text-center text-slate-400 mb-12">
            <p class="text-4xl mb-3">🔨</p>
            <p class="font-medium">Gerade keine aktiven Auktionen.</p>
        </div>
    @endif

    {{-- geplante auktionen --}}
    @if($geplant->isNotEmpty())
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Demnächst</h2>
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left">Produkt</th>
                        <th class="px-6 py-3 text-left">Startpreis</th>
                        <th class="px-6 py-3 text-left">Start</th>
                        <th class="px-6 py-3 text-left">Ende</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($geplant as $auktion)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-medium text-slate-800">{{ $auktion->product->name }}</td>
                            <td class="px-6 py-3 text-slate-600">€ {{ number_format($auktion->start_price, 2, ',', '.') }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $auktion->start_time->format('d.m.Y H:i') }} Uhr</td>
                            <td class="px-6 py-3 text-slate-600">{{ $auktion->end_time->format('d.m.Y H:i') }} Uhr</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    // mini-countdowns auf der übersichtsseite aktualisieren
    function aktualisiereCountdowns() {
        document.querySelectorAll('[data-countdown]').forEach(el => {
            const end = new Date(el.dataset.countdown).getTime();
            const diff = end - Date.now();
            if (diff <= 0) {
                el.textContent = 'Abgelaufen';
                return;
            }
            const h = Math.floor(diff / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);
            el.textContent = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        });
    }
    aktualisiereCountdowns();
    setInterval(aktualisiereCountdowns, 1000);
</script>
@endpush
