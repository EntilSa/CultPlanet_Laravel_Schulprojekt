@extends('layouts.app')

@section('title', 'Meine Bestellungen')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Meine Bestellungen</h1>
        <a href="{{ route('shop.index') }}"
           class="text-slate-500 hover:text-slate-700 text-sm transition-colors">← Zurück zum Shop</a>
    </div>

    @if($orders->isEmpty())
        <div class="text-center py-16 text-slate-400">
            <p class="text-4xl mb-4">📦</p>
            <p class="text-lg font-medium">Du hast noch keine Bestellungen.</p>
            <a href="{{ route('shop.index') }}"
               class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-lg text-sm transition">
                Jetzt einkaufen
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
            <div class="bg-white rounded-xl shadow p-5">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="font-semibold text-slate-800">Bestellung #{{ $order->id }}</p>
                        <p class="text-sm text-slate-500">{{ $order->created_at->format('d.m.Y H:i') }} Uhr</p>
                    </div>
                    <div class="text-right">
                        {{-- status badge aus dem admin-partial --}}
                        @include('admin.partials.status-badge', ['status' => $order->status])
                        <p class="text-lg font-bold text-slate-800 mt-1">{{ number_format($order->total, 2, ',', '.') }} €</p>
                    </div>
                </div>

                {{-- artikel liste --}}
                <ul class="text-sm text-slate-600 mb-4 space-y-1">
                    @foreach($order->items as $item)
                        <li>{{ $item->quantity }}× {{ $item->name }} – {{ number_format($item->price, 2, ',', '.') }} € / Stk.</li>
                    @endforeach
                </ul>

                {{-- pdf download button --}}
                <a href="{{ route('orders.pdf', $order) }}"
                   class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Rechnung als PDF
                </a>
            </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
