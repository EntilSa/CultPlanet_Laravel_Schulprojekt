@extends('layouts.app')

@section('title', 'Zahlung')

@section('content')
<div class="max-w-lg mx-auto px-6 py-16">

    <div class="text-center mb-8">
        <p class="text-slate-500 text-sm mb-1">Bestellung #{{ $order->id }}</p>
        <p class="text-3xl font-bold text-slate-900">€ {{ number_format($order->total, 2, ',', '.') }}</p>
        <p class="text-slate-400 text-sm mt-1">{{ $order->vorname }} {{ $order->nachname }}</p>
    </div>

    {{-- paypal fake-ui --}}
    @if($order->zahlungsmethode === 'paypal')
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center">

            {{-- paypal-logo als text (kein echtes logo nötig) --}}
            <div class="mb-6">
                <span class="text-3xl font-bold" style="color: #003087;">Pay</span><span class="text-3xl font-bold" style="color: #009cde;">Pal</span>
            </div>

            <p class="text-slate-500 text-sm mb-6">
                Du wirst mit deinem PayPal-Konto bezahlen.<br>
                <span class="text-xs text-orange-500 font-medium">⚠ Schulprojekt – keine echte Transaktion</span>
            </p>

            <form action="{{ route('orders.complete_payment', $order) }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full font-bold py-3 px-6 rounded-full text-white transition"
                        style="background-color: #ffc439; color: #003087;">
                    Mit PayPal bezahlen
                </button>
            </form>

            <p class="text-slate-400 text-xs mt-4">
                Betrag: <strong>€ {{ number_format($order->total, 2, ',', '.') }}</strong>
            </p>
        </div>

    {{-- sofortüberweisung fake-ui --}}
    @else
        <div class="bg-white rounded-2xl shadow-lg p-8">

            <div class="text-center mb-6">
                <p class="text-lg font-bold text-slate-800">Sofortüberweisung</p>
                <p class="text-slate-400 text-xs mt-1">
                    <span class="text-orange-500 font-medium">⚠ Schulprojekt – keine echte Transaktion</span>
                </p>
            </div>

            {{-- fake bankdaten-felder --}}
            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-slate-600 text-sm font-medium mb-1">IBAN</label>
                    <input type="text" value="DE12 3456 7890 1234 5678 90" disabled
                           class="w-full border border-slate-200 rounded-lg p-3 text-sm bg-slate-50 text-slate-400 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-slate-600 text-sm font-medium mb-1">Verwendungszweck</label>
                    <input type="text" value="Bestellung #{{ $order->id }} CultPlanet" disabled
                           class="w-full border border-slate-200 rounded-lg p-3 text-sm bg-slate-50 text-slate-400 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-slate-600 text-sm font-medium mb-1">Betrag</label>
                    <input type="text" value="€ {{ number_format($order->total, 2, ',', '.') }}" disabled
                           class="w-full border border-slate-200 rounded-lg p-3 text-sm bg-slate-50 text-slate-400 cursor-not-allowed">
                </div>
            </div>

            <form action="{{ route('orders.complete_payment', $order) }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                    Überweisung bestätigen
                </button>
            </form>
        </div>
    @endif

    <p class="text-center text-slate-400 text-xs mt-6">
        <a href="{{ route('shop.index') }}" class="hover:text-slate-600 transition">Abbrechen und zurück zum Shop</a>
    </p>

</div>
@endsection
