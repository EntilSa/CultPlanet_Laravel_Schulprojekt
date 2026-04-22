@extends('layouts.app')

@section('title', 'Kasse')

@section('content')
<div class="max-w-5xl mx-auto px-6 py-8">

    <h1 class="text-2xl font-bold text-slate-900 mb-8">Kasse</h1>

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- linke seite: lieferadresse + zahlungsmethode --}}
        <div class="flex-1">
            <form action="{{ route('checkout.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- fehlermeldungen --}}
                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <ul class="text-red-600 text-sm space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- lieferadresse --}}
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="font-semibold text-slate-800 mb-4">Lieferadresse</h2>

                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label class="block text-slate-700 font-medium mb-1 text-sm">Vorname</label>
                            <input type="text" name="vorname" value="{{ old('vorname') }}"
                                   class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        </div>
                        <div class="flex-1">
                            <label class="block text-slate-700 font-medium mb-1 text-sm">Nachname</label>
                            <input type="text" name="nachname" value="{{ old('nachname') }}"
                                   class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-slate-700 font-medium mb-1 text-sm">Straße + Hausnummer</label>
                        <input type="text" name="strasse" value="{{ old('strasse') }}"
                               class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    </div>

                    <div class="flex gap-4">
                        <div class="w-32">
                            <label class="block text-slate-700 font-medium mb-1 text-sm">PLZ</label>
                            <input type="text" name="plz" value="{{ old('plz') }}"
                                   class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        </div>
                        <div class="flex-1">
                            <label class="block text-slate-700 font-medium mb-1 text-sm">Ort</label>
                            <input type="text" name="ort" value="{{ old('ort') }}"
                                   class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        </div>
                    </div>
                </div>

                {{-- zahlungsmethode --}}
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="font-semibold text-slate-800 mb-4">Zahlungsmethode</h2>

                    <div class="space-y-3">
                        {{-- paypal-option --}}
                        <label class="flex items-center gap-3 border border-slate-200 rounded-lg p-4 cursor-pointer hover:border-blue-400 transition has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="zahlungsmethode" value="paypal"
                                   {{ old('zahlungsmethode') === 'paypal' ? 'checked' : '' }}
                                   class="accent-blue-600">
                            <div>
                                <p class="font-medium text-slate-800 text-sm">PayPal</p>
                                <p class="text-slate-400 text-xs">Attrappen-Zahlung – keine echte Transaktion</p>
                            </div>
                        </label>

                        {{-- sofortüberweisung-option --}}
                        <label class="flex items-center gap-3 border border-slate-200 rounded-lg p-4 cursor-pointer hover:border-blue-400 transition has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="zahlungsmethode" value="sofortueberweisung"
                                   {{ old('zahlungsmethode') === 'sofortueberweisung' ? 'checked' : '' }}
                                   class="accent-blue-600">
                            <div>
                                <p class="font-medium text-slate-800 text-sm">Sofortüberweisung</p>
                                <p class="text-slate-400 text-xs">Attrappen-Zahlung – keine echte Transaktion</p>
                            </div>
                        </label>
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                    Jetzt bestellen →
                </button>

            </form>
        </div>

        {{-- rechte seite: bestellübersicht --}}
        <div class="lg:w-80">
            <div class="bg-white rounded-xl shadow p-6 sticky top-6">
                <h2 class="font-semibold text-slate-800 mb-4">Deine Bestellung</h2>

                <div class="space-y-3 mb-4">
                    @foreach($cart as $item)
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">{{ $item['name'] }} <span class="text-slate-400">×{{ $item['qty'] }}</span></span>
                            <span class="font-medium text-slate-800">€ {{ number_format($item['price'] * $item['qty'], 2, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                <hr class="border-slate-200 mb-4">

                <div class="flex justify-between items-center">
                    <span class="font-semibold text-slate-800">Gesamt</span>
                    <span class="text-xl font-bold text-blue-600">€ {{ number_format($total, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
