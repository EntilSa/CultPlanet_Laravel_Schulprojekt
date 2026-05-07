{{-- Impressum-Seite --}}
@extends('layouts.app')

@section('title', 'Impressum – CultPlanet')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Impressum</h1>

    <div class="bg-white rounded-lg shadow p-6 space-y-4 text-gray-700">
        <div>
            <h2 class="font-semibold text-lg">Angaben gemäß § 5 TMG</h2>
            <p>Max Mustermann<br>
            Musterstraße 1<br>
            12345 Musterstadt</p>
        </div>

        <div>
            <h2 class="font-semibold text-lg">Kontakt</h2>
            <p>E-Mail: info@cultplanet.de</p>
        </div>

        <div>
            <h2 class="font-semibold text-lg">Hinweis</h2>
            <p>Dies ist ein Schulprojekt im Rahmen der FIAE-Umschulung. Kein echtes Unternehmen.</p>
        </div>
    </div>
@endsection
