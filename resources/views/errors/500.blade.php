{{-- Fehlerseite 500 – Serverfehler --}}
@extends('layouts.app')

@section('title', '500 – Serverfehler')

@section('content')
    <div class="text-center py-16">
        <h1 class="text-6xl font-bold text-slate-800 mb-4">500</h1>
        <p class="text-xl text-gray-600 mb-2">Etwas ist schiefgelaufen.</p>
        <p class="text-gray-500 mb-8">Es gab einen Fehler auf dem Server. Bitte versuche es später nochmal.</p>
        <a href="{{ url('/') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
            Zurück zur Startseite
        </a>
    </div>
@endsection
