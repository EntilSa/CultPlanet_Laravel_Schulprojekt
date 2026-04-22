@extends('layouts.app')

@section('title', 'Nutzer verwalten')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Nutzer</h1>
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
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">E-Mail</th>
                    <th class="px-6 py-3 text-left">Aktuelle Rolle</th>
                    <th class="px-6 py-3 text-left">Registriert</th>
                    <th class="px-6 py-3 text-left">Rolle ändern</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 text-slate-500">{{ $user->id }}</td>
                        <td class="px-6 py-4 font-medium text-slate-800">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            {{-- zeige alle rollen des nutzers als badges --}}
                            @foreach($user->roles as $role)
                                @php
                                    $farbe = match($role->name) {
                                        'admin'       => 'bg-red-100 text-red-800',
                                        'mitarbeiter' => 'bg-blue-100 text-blue-800',
                                        default       => 'bg-slate-100 text-slate-600',
                                    };
                                @endphp
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $farbe }}">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ $user->created_at->format('d.m.Y') }}</td>
                        <td class="px-6 py-4">
                            {{-- rolle direkt über dropdown ändern --}}
                            <form action="{{ route('admin.users.role', $user) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="role"
                                        class="border border-slate-300 rounded-lg px-2 py-1.5 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}"
                                            {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
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
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">Keine Nutzer vorhanden.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginierung --}}
    <div class="mt-4">
        {{ $users->links() }}
    </div>

</div>
@endsection
