@extends('layouts.app')

@section('title', 'Mitarbeiterverwaltung')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Mitarbeiterverwaltung – Bereiche</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-blue-600 text-sm hover:underline">← Dashboard</a>
    </div>

    {{-- erfolgs- / fehlermeldung --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-red-700 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- warnung: unbesetzte bereiche --}}
    @php $unbesetzte = $bereiche->filter(fn($b) => $b->users->isEmpty()); @endphp
    @if($unbesetzte->isNotEmpty())
        <div class="bg-orange-50 border-l-4 border-orange-500 rounded-lg p-4 mb-6">
            <p class="text-orange-800 font-semibold text-sm">⚠ Achtung – {{ $unbesetzte->count() }} Bereich(e) ohne Mitarbeiter:</p>
            <ul class="mt-1 text-orange-700 text-sm list-disc list-inside">
                @foreach($unbesetzte as $b)
                    <li>{{ $b->name }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- linke spalte: bereiche übersicht --}}
        <div class="lg:col-span-2 space-y-4">

            @forelse($bereiche as $bereich)
                <div class="bg-white rounded-xl shadow p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg font-semibold text-slate-900">{{ $bereich->name }}</h2>
                            @if($bereich->users->isEmpty())
                                {{-- warnung-badge für unbesetzte bereiche --}}
                                <span class="bg-orange-100 text-orange-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                                    Unbesetzt
                                </span>
                            @else
                                <span class="bg-green-100 text-green-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                                    {{ $bereich->users->count() }} Mitarbeiter
                                </span>
                            @endif
                        </div>

                        {{-- bereich löschen --}}
                        <form action="{{ route('admin.departments.destroy', $bereich) }}" method="POST"
                              onsubmit="return confirm('Bereich \"{{ $bereich->name }}\" wirklich löschen?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-red-500 hover:text-red-700 text-xs font-medium transition">
                                Löschen
                            </button>
                        </form>
                    </div>

                    {{-- mitarbeiterliste --}}
                    @if($bereich->users->isNotEmpty())
                        <ul class="space-y-2 mb-4">
                            @foreach($bereich->users as $user)
                                <li class="flex items-center justify-between bg-slate-50 rounded-lg px-4 py-2">
                                    <div>
                                        <span class="text-slate-800 text-sm font-medium">{{ $user->name }}</span>
                                        <span class="text-slate-400 text-xs ml-2">{{ $user->email }}</span>
                                    </div>
                                    {{-- mitarbeiter aus bereich entfernen --}}
                                    <form action="{{ route('admin.departments.removeUser', [$bereich, $user]) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-slate-400 hover:text-red-500 text-xs transition">
                                            Entfernen
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-slate-400 text-sm italic mb-4">Noch kein Mitarbeiter zugewiesen.</p>
                    @endif

                    {{-- mitarbeiter zuweisen --}}
                    @if($mitarbeiter->isNotEmpty())
                        <form action="{{ route('admin.departments.addUser', $bereich) }}" method="POST"
                              class="flex gap-2">
                            @csrf
                            <select name="user_id"
                                    class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Mitarbeiter auswählen...</option>
                                @foreach($mitarbeiter as $ma)
                                    <option value="{{ $ma->id }}">{{ $ma->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                                Zuweisen
                            </button>
                        </form>
                    @else
                        <p class="text-slate-400 text-xs italic">Keine Mitarbeiter vorhanden. Weise einem Nutzer zuerst die Rolle "mitarbeiter" zu.</p>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-xl shadow p-8 text-center text-slate-400">
                    Noch keine Bereiche angelegt.
                </div>
            @endforelse
        </div>

        {{-- rechte spalte: neuen bereich anlegen --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow p-5 sticky top-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Neuen Bereich anlegen</h2>
                <form action="{{ route('admin.departments.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-sm text-slate-600 mb-1">Bereichsname</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               placeholder="z.B. Lager, Verkauf, Kasse..."
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition text-sm">
                        Bereich anlegen
                    </button>
                </form>

                {{-- übersicht besetzt/unbesetzt --}}
                <div class="mt-6 border-t border-slate-100 pt-5">
                    <h3 class="text-sm font-semibold text-slate-700 mb-3">Übersicht</h3>
                    <div class="space-y-1">
                        @foreach($bereiche as $b)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-700">{{ $b->name }}</span>
                                @if($b->users->isEmpty())
                                    <span class="text-orange-500 font-medium text-xs">⚠ Unbesetzt</span>
                                @else
                                    <span class="text-green-600 font-medium text-xs">✓ Besetzt</span>
                                @endif
                            </div>
                        @endforeach
                        @if($bereiche->isEmpty())
                            <p class="text-slate-400 text-xs italic">Keine Bereiche vorhanden.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
