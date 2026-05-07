{{-- Datenschutz-Seite --}}
@extends('layouts.app')

@section('title', 'Datenschutz – CultPlanet')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Datenschutzerklärung</h1>

    <div class="bg-white rounded-lg shadow p-6 space-y-4 text-gray-700">
        <div>
            <h2 class="font-semibold text-lg">Allgemeines</h2>
            <p>Der Schutz deiner persönlichen Daten ist uns wichtig. Wir verarbeiten deine Daten nur im Rahmen der gesetzlichen Vorschriften (DSGVO).</p>
        </div>

        <div>
            <h2 class="font-semibold text-lg">Erhobene Daten</h2>
            <p>Bei der Registrierung speichern wir deinen Namen und deine E-Mail-Adresse. Diese Daten werden ausschließlich für den Betrieb des Shops verwendet.</p>
        </div>

        <div>
            <h2 class="font-semibold text-lg">Cookies</h2>
            <p>Diese Seite verwendet Session-Cookies, die für den Betrieb des Shops notwendig sind (z.B. Warenkorb, Login-Status).</p>
        </div>

        <div>
            <h2 class="font-semibold text-lg">Hinweis</h2>
            <p>Dies ist ein Schulprojekt. Die Datenschutzerklärung ist vereinfacht und nicht rechtsverbindlich.</p>
        </div>
    </div>
@endsection
