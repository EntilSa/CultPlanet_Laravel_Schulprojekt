{{-- Startseite von CultPlanet --}}
{{-- TODO Phase 2: Platzhalter-Inhalt ersetzen durch:
     1. Auktions-Banner in @section('banner') – Vorlage in design.md und mockup-startseite.html
     2. Produktgrid mit echten Produkten aus der Datenbank
     Dafür den ProductController und die shop.index Route anlegen (siehe web.php) --}}
@extends('layouts.app')

@section('title', 'Willkommen')

@section('content')
    <div class="max-w-7xl mx-auto px-6 py-16 text-center">
        <h1 class="text-4xl font-bold text-slate-900 mb-4">Willkommen bei CultPlanet</h1>
        <p class="text-lg text-slate-500 mb-8">Dein Onlineshop für cooles Spielzeug – täglich neue Auktionen.</p>
        <a href="#" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg transition">
            Zum Shop
        </a>
    </div>
@endsection
