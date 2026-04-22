@extends('layouts.app')

@section('title', 'Admin-Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">

    <h1 class="text-2xl font-bold text-slate-900 mb-6">Admin-Dashboard</h1>

    {{-- Erfolgs-/Fehlermeldung --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Kennzahlen-Karten --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">

        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-slate-500 text-sm">Produkte</p>
            <p class="text-3xl font-bold text-slate-900 mt-1">{{ $stats['produkte'] }}</p>
            <a href="{{ route('admin.products') }}"
               class="text-blue-600 text-xs font-medium hover:underline mt-2 inline-block">Alle Produkte</a>
        </div>

        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-slate-500 text-sm">Nutzer</p>
            <p class="text-3xl font-bold text-slate-900 mt-1">{{ $stats['nutzer'] }}</p>
            <a href="{{ route('admin.users') }}"
               class="text-blue-600 text-xs font-medium hover:underline mt-2 inline-block">Alle Nutzer</a>
        </div>

        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-slate-500 text-sm">Bestellungen</p>
            <p class="text-3xl font-bold text-slate-900 mt-1">{{ $stats['bestellungen'] }}</p>
            <a href="{{ route('admin.orders') }}"
               class="text-blue-600 text-xs font-medium hover:underline mt-2 inline-block">Alle Bestellungen</a>
        </div>

        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-slate-500 text-sm">Umsatz (bezahlt)</p>
            <p class="text-3xl font-bold text-blue-600 mt-1">€ {{ number_format($stats['umsatz'], 2, ',', '.') }}</p>
            <a href="{{ route('admin.sales') }}"
               class="text-blue-600 text-xs font-medium hover:underline mt-2 inline-block">Verkaufsübersicht</a>
        </div>

    </div>

    {{-- Letzte 5 Bestellungen --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">Letzte Bestellungen</h2>
            <a href="{{ route('admin.orders') }}"
               class="text-blue-600 text-sm hover:underline">Alle anzeigen →</a>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">#</th>
                    <th class="px-6 py-3 text-left">Kunde</th>
                    <th class="px-6 py-3 text-left">Gesamt</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Datum</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($letzteBestellungen as $order)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 text-slate-500">#{{ $order->id }}</td>
                        <td class="px-6 py-3 text-slate-800">{{ $order->user->name }}</td>
                        <td class="px-6 py-3 font-medium text-slate-800">€ {{ number_format($order->total, 2, ',', '.') }}</td>
                        <td class="px-6 py-3">
                            @include('admin.partials.status-badge', ['status' => $order->status])
                        </td>
                        <td class="px-6 py-3 text-slate-500">{{ $order->created_at->format('d.m.Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-6 text-center text-slate-400">Noch keine Bestellungen.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Schnelllinks --}}
    <div class="flex flex-wrap gap-3 mt-6">
        <a href="{{ route('admin.orders') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2.5 rounded-lg transition text-sm">
            Bestellungen verwalten
        </a>
        <a href="{{ route('admin.users') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2.5 rounded-lg transition text-sm">
            Nutzer verwalten
        </a>
        <a href="{{ route('admin.sales') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2.5 rounded-lg transition text-sm">
            Verkaufsübersicht
        </a>
        <a href="{{ route('admin.products') }}"
           class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium px-4 py-2.5 rounded-lg transition text-sm">
            Produkte verwalten
        </a>
    </div>

</div>
@endsection
