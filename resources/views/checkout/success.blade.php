@extends('layouts.app')

@section('title', 'Bestellung erfolgreich')

@section('content')
<div class="max-w-2xl mx-auto px-6 py-16 text-center">

    {{-- bestätigungs-icon --}}
    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <span class="text-green-600 text-4xl">✓</span>
    </div>

    <h1 class="text-3xl font-bold text-slate-900 mb-2">Vielen Dank für deine Bestellung!</h1>
    <p class="text-slate-500 mb-8">Bestellung #{{ $order->id }} wurde erfolgreich aufgenommen.</p>

    {{-- bestelldetails --}}
    <div class="bg-white rounded-xl shadow p-6 text-left mb-8">

        <h2 class="font-semibold text-slate-800 mb-4">Bestellübersicht</h2>

        <div class="space-y-3 mb-4">
            @foreach($order->items as $item)
                <div class="flex justify-between text-sm">
                    <span class="text-slate-600">{{ $item->name }} <span class="text-slate-400">×{{ $item->quantity }}</span></span>
                    <span class="font-medium text-slate-800">€ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</span>
                </div>
            @endforeach
        </div>

        <hr class="border-slate-200 mb-4">

        <div class="flex justify-between items-center mb-4">
            <span class="font-semibold text-slate-800">Gesamt</span>
            <span class="text-xl font-bold text-blue-600">€ {{ number_format($order->total, 2, ',', '.') }}</span>
        </div>

        <div class="text-sm text-slate-500 space-y-1">
            <p><span class="font-medium text-slate-700">Lieferadresse:</span>
                {{ $order->vorname }} {{ $order->nachname }},
                {{ $order->strasse }}, {{ $order->plz }} {{ $order->ort }}
            </p>
            <p><span class="font-medium text-slate-700">Zahlung:</span>
                {{ $order->zahlungsmethode === 'paypal' ? 'PayPal' : 'Sofortüberweisung' }}
            </p>
            <p><span class="font-medium text-slate-700">Status:</span>
                <span class="text-orange-600 font-medium">{{ ucfirst($order->status) }}</span>
            </p>
        </div>

    </div>

    <a href="{{ route('shop.index') }}"
       class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-3 rounded-lg transition">
        Weiter einkaufen
    </a>

</div>
@endsection
