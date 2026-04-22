@extends('layouts.app')

@section('title', 'Auktion: ' . $auction->product->name)

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">

    {{-- Breadcrumb --}}
    <nav class="text-sm text-slate-500 mb-6">
        <a href="{{ route('auction.index') }}" class="hover:text-blue-600 transition-colors">Auktionen</a>
        <span class="mx-2">›</span>
        <span class="text-slate-800 font-medium">{{ $auction->product->name }}</span>
    </nav>

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Linke Spalte: Produktbild --}}
        <div class="lg:w-2/5">
            <div class="bg-white rounded-2xl shadow p-6 flex items-center justify-center" style="min-height: 320px;">
                @if($auction->product->image)
                    <img src="{{ asset('storage/' . $auction->product->image) }}"
                         class="max-h-72 object-contain" alt="{{ $auction->product->name }}">
                @else
                    <span class="text-slate-200 text-9xl">🧸</span>
                @endif
            </div>
        </div>

        {{-- Rechte Spalte: Info + Gebot --}}
        <div class="lg:w-3/5 flex flex-col">

            <h1 class="text-2xl font-bold text-slate-900 leading-tight">{{ $auction->product->name }}</h1>
            <p class="text-slate-500 text-sm mt-2">Art.-Nr. {{ $auction->product->artikel_nr }}</p>

            {{-- Status --}}
            <div class="mt-4">
                @include('admin.partials.status-badge', ['status' => $auction->status])
            </div>

            {{-- Aktuelles Höchstgebot --}}
            <div class="mt-5 bg-slate-50 rounded-xl p-4">
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Aktuelles Höchstgebot</p>
                <p class="text-4xl font-bold text-blue-600">€ {{ number_format($auction->hoechstGebot(), 2, ',', '.') }}</p>
                <p class="text-slate-400 text-sm mt-1">{{ $auction->bids->count() }} Gebot(e)</p>
            </div>

            {{-- Countdown --}}
            @if($auction->status === 'aktiv')
                <div class="mt-4 text-center bg-[#1a2e4a] rounded-xl py-4 px-6">
                    <p class="text-slate-400 text-xs uppercase tracking-wide mb-1">Auktion endet in</p>
                    <p id="countdown" class="text-4xl font-bold text-orange-400 tabular-nums tracking-tight">–</p>
                    <p class="text-slate-500 text-xs mt-1">{{ $auction->end_time->format('d.m.Y H:i') }} Uhr</p>
                </div>
            @elseif($auction->status === 'beendet')
                <div class="mt-4 bg-green-50 border border-green-200 rounded-xl p-4 text-center">
                    @if($auction->winner)
                        <p class="text-green-700 font-semibold">Auktion beendet – Gewinner: {{ $auction->winner->name }}</p>
                        <p class="text-green-600 text-sm mt-1">Höchstgebot: € {{ number_format($auction->winning_bid, 2, ',', '.') }}</p>
                    @else
                        <p class="text-slate-500 font-medium">Auktion beendet – kein Gebot abgegeben.</p>
                    @endif
                </div>
            @else
                <div class="mt-4 bg-slate-50 border border-slate-200 rounded-xl p-4 text-center">
                    <p class="text-slate-500 text-sm">Startet am {{ $auction->start_time->format('d.m.Y') }} um {{ $auction->start_time->format('H:i') }} Uhr</p>
                </div>
            @endif

            {{-- Bietformular --}}
            @if($auction->status === 'aktiv')
                <div class="mt-5">
                    @auth
                        {{-- fehlermeldung --}}
                        @if($errors->has('amount'))
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-3 text-red-600 text-sm">
                                {{ $errors->first('amount') }}
                            </div>
                        @endif
                        @if(session('success'))
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-3 text-green-700 text-sm">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form action="{{ route('auction.bid', $auction) }}" method="POST" class="flex gap-3">
                            @csrf
                            <div class="flex-1">
                                <input type="number" name="amount" step="0.01" min="{{ $auction->hoechstGebot() + 1 }}"
                                       placeholder="Min. € {{ number_format($auction->hoechstGebot() + 1, 2, ',', '.') }}"
                                       value="{{ old('amount') }}"
                                       class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-white">
                            </div>
                            <button type="submit"
                                    class="bg-orange-500 hover:bg-orange-600 text-white font-bold px-6 py-3 rounded-lg transition text-sm whitespace-nowrap">
                                Gebot abgeben
                            </button>
                        </form>
                        <p class="text-slate-400 text-xs mt-2">Mindestgebot: € {{ number_format($auction->hoechstGebot() + 1, 2, ',', '.') }} (aktuelles Höchstgebot + 1,00 €)</p>
                    @else
                        <div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl p-5 text-center">
                            <p class="text-slate-500 text-sm mb-3">Du musst eingeloggt sein um zu bieten.</p>
                            <a href="{{ route('login') }}"
                               class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-lg text-sm transition">
                                Jetzt einloggen
                            </a>
                        </div>
                    @endauth
                </div>
            @endif

        </div>
    </div>

    {{-- Gebotsverlauf --}}
    @if($auction->bids->isNotEmpty())
        <div class="mt-12">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Gebotsverlauf</h2>
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3 text-left">Bieter</th>
                            <th class="px-6 py-3 text-left">Gebot</th>
                            <th class="px-6 py-3 text-left">Zeitpunkt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($auction->bids as $bid)
                            <tr class="{{ $loop->first ? 'bg-orange-50' : 'hover:bg-slate-50' }}">
                                <td class="px-6 py-3 text-slate-700">
                                    {{-- name anonymisieren: erste 2 buchstaben + *** --}}
                                    {{ mb_substr($bid->user->name, 0, 2) }}***
                                    @if($loop->first)
                                        <span class="ml-1 text-xs text-orange-600 font-semibold">Höchstgebot</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 font-semibold {{ $loop->first ? 'text-orange-600' : 'text-slate-800' }}">
                                    € {{ number_format($bid->amount, 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-3 text-slate-500">{{ $bid->created_at->format('d.m.Y H:i:s') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
@if($auction->status === 'aktiv')
<script>
    // countdown bis auktionsende
    const endZeit = new Date("{{ $auction->end_time->toIso8601String() }}").getTime();

    function countdown() {
        const diff = endZeit - Date.now();
        const el = document.getElementById('countdown');
        if (!el) return;

        if (diff <= 0) {
            el.textContent = 'Abgelaufen';
            // seite nach 3 sekunden neu laden damit status aktualisiert wird
            setTimeout(() => location.reload(), 3000);
            return;
        }

        const h = Math.floor(diff / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        el.textContent = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
    }

    countdown();
    setInterval(countdown, 1000);
</script>
@endif
@endpush
