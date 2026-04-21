{{-- Startseite von CultPlanet --}}
@extends('layouts.app')

@section('title', 'Willkommen bei CultPlanet')

@section('content')
    <div class="text-center py-16">
        <h1 class="text-4xl font-bold text-purple-700 mb-4">Willkommen bei CultPlanet</h1>
        <p class="text-lg text-gray-600 mb-8">Dein Onlineshop für cooles Spielzeug.</p>
        <a href="#" class="bg-purple-700 text-white px-6 py-3 rounded-lg hover:bg-purple-800">
            Zum Shop
        </a>
    </div>
@endsection
