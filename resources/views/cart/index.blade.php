@extends('layouts.app')

@section('title', 'Warenkorb')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">

    <h1 class="text-2xl font-bold text-slate-900 mb-6">Warenkorb</h1>

    {{-- erfolgs- oder fehlermeldung anzeigen --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-600 rounded-lg p-4 mb-6 text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if(count($cart) > 0)

        {{-- warenkorbpositionen --}}
        <div class="bg-white rounded-xl shadow divide-y divide-slate-100">
            @foreach($cart as $productId => $item)
                <div class="flex items-center gap-4 p-4">

                    {{-- produktbild --}}
                    <div class="w-20 h-20 bg-slate-50 rounded-lg overflow-hidden flex-shrink-0 flex items-center justify-center">
                        @if($item['image'])
                            <img src="{{ asset('storage/' . $item['image']) }}"
                                 class="w-full h-full object-cover" alt="{{ $item['name'] }}">
                        @else
                            <span class="text-slate-300 text-3xl">🧸</span>
                        @endif
                    </div>

                    {{-- name + preis --}}
                    <div class="flex-1">
                        <p class="font-semibold text-slate-800">{{ $item['name'] }}</p>
                        <p class="text-slate-500 text-sm">€ {{ number_format($item['price'], 2, ',', '.') }} pro Stück</p>
                    </div>

                    {{-- menge --}}
                    <div class="text-slate-700 font-medium text-sm">
                        {{ $item['qty'] }}x
                    </div>

                    {{-- zeilenpreis --}}
                    <div class="text-blue-600 font-bold w-24 text-right">
                        € {{ number_format($item['price'] * $item['qty'], 2, ',', '.') }}
                    </div>

                    {{-- entfernen-button --}}
                    <form action="{{ route('cart.remove', $productId) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="text-slate-400 hover:text-red-500 transition text-sm">
                            ✕
                        </button>
                    </form>

                </div>
            @endforeach
        </div>

        {{-- gesamtpreis + buttons --}}
        <div class="mt-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">

            {{-- warenkorb leeren --}}
            <form action="{{ route('cart.clear') }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium px-4 py-2.5 rounded-lg text-sm transition">
                    Warenkorb leeren
                </button>
            </form>

            {{-- summe + zur kasse --}}
            <div class="text-right">
                <p class="text-slate-500 text-sm mb-1">Gesamtsumme</p>
                <p class="text-3xl font-bold text-blue-600">€ {{ number_format($total, 2, ',', '.') }}</p>
                <a href="#"
                   class="mt-3 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-3 rounded-lg transition text-sm">
                    Zur Kasse →
                </a>
                <p class="text-slate-400 text-xs mt-2">Checkout folgt in Kürze</p>
            </div>

        </div>

    @else
        {{-- leerer warenkorb --}}
        <div class="text-center py-24 text-slate-400">
            <p class="text-5xl mb-4">🛒</p>
            <p class="text-lg font-medium">Dein Warenkorb ist leer.</p>
            <a href="{{ route('shop.index') }}"
               class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg transition text-sm">
                Zum Shop
            </a>
        </div>
    @endif

</div>
@endsection
