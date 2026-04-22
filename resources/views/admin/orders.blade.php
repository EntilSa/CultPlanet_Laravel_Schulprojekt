@extends('layouts.app')

@section('title', 'Bestellungen verwalten')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Bestellungen</h1>
        <a href="{{ route('admin.dashboard') }}"
           class="text-slate-500 hover:text-slate-700 text-sm transition-colors">← Dashboard</a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">#</th>
                    <th class="px-6 py-3 text-left">Kunde</th>
                    <th class="px-6 py-3 text-left">Adresse</th>
                    <th class="px-6 py-3 text-left">Gesamt</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Datum</th>
                    <th class="px-6 py-3 text-left">Aktion</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($orders as $order)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 text-slate-500 font-mono">#{{ $order->id }}</td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-slate-800">{{ $order->vorname }} {{ $order->nachname }}</p>
                            <p class="text-slate-400 text-xs">{{ $order->user->email }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            {{ $order->strasse }}, {{ $order->plz }} {{ $order->ort }}
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-800">
                            € {{ number_format($order->total, 2, ',', '.') }}
                        </td>
                        <td class="px-6 py-4">
                            @include('admin.partials.status-badge', ['status' => $order->status])
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            {{ $order->created_at->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            {{-- status-formular zum direkt ändern --}}
                            <form action="{{ route('admin.orders.update', $order) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="status"
                                        class="border border-slate-300 rounded-lg px-2 py-1.5 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="offen"     {{ $order->status === 'offen'     ? 'selected' : '' }}>Offen</option>
                                    <option value="bezahlt"   {{ $order->status === 'bezahlt'   ? 'selected' : '' }}>Bezahlt</option>
                                    <option value="versendet" {{ $order->status === 'versendet' ? 'selected' : '' }}>Versendet</option>
                                    <option value="storniert" {{ $order->status === 'storniert' ? 'selected' : '' }}>Storniert</option>
                                </select>
                                <button type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition">
                                    Speichern
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-400">Noch keine Bestellungen vorhanden.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginierung --}}
    <div class="mt-4">
        {{ $orders->links() }}
    </div>

</div>
@endsection
