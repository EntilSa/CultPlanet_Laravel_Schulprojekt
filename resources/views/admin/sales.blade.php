@extends('layouts.app')

@section('title', 'Verkaufsübersicht')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Verkaufsübersicht</h1>
            <p class="text-slate-500 text-sm mt-1">Bezahlte Bestellungen der letzten 30 Tage</p>
        </div>
        @if(auth()->user()->hasRole('admin'))
            <a href="{{ route('admin.dashboard') }}"
               class="text-slate-500 hover:text-slate-700 text-sm transition-colors">← Dashboard</a>
        @endif
    </div>

    @if($verkaufe->isEmpty())
        <div class="bg-white rounded-xl shadow p-8 text-center text-slate-400">
            Noch keine bezahlten Bestellungen in den letzten 30 Tagen.
        </div>
    @else

        {{-- Gesamtsumme oben --}}
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-slate-500 text-sm">Bestellungen (30 Tage)</p>
                <p class="text-3xl font-bold text-slate-900 mt-1">{{ $verkaufe->sum('anzahl') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-slate-500 text-sm">Umsatz (30 Tage)</p>
                <p class="text-3xl font-bold text-blue-600 mt-1">
                    € {{ number_format($verkaufe->sum('umsatz'), 2, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Tabelle pro Tag --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left">Datum</th>
                        <th class="px-6 py-3 text-right">Bestellungen</th>
                        <th class="px-6 py-3 text-right">Umsatz</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($verkaufe as $tag)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 text-slate-800 font-medium">
                                {{ \Carbon\Carbon::parse($tag->tag)->format('d.m.Y') }}
                            </td>
                            <td class="px-6 py-3 text-right text-slate-600">{{ $tag->anzahl }}</td>
                            <td class="px-6 py-3 text-right font-semibold text-slate-800">
                                € {{ number_format($tag->umsatz, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                    <tr>
                        <td class="px-6 py-3 font-semibold text-slate-700">Gesamt</td>
                        <td class="px-6 py-3 text-right font-semibold text-slate-700">{{ $verkaufe->sum('anzahl') }}</td>
                        <td class="px-6 py-3 text-right font-bold text-blue-600">
                            € {{ number_format($verkaufe->sum('umsatz'), 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

    @endif

</div>
@endsection
