{{-- Fehlerseite 404 – Seite nicht gefunden --}}
@extends('layouts.app')

@section('title', '404 – Seite nicht gefunden')

@section('content')
    <div class="text-center py-16">
        <h1 class="text-6xl font-bold text-slate-800 mb-4">404</h1>
        <p class="text-xl text-gray-600 mb-2">Diese Seite gibt es nicht.</p>
        <p class="text-gray-500 mb-8">Vielleicht wurde sie verschoben oder der Link ist falsch.</p>
        <a href="{{ url('/') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
            Zurück zur Startseite
        </a>
    </div>
@endsection
