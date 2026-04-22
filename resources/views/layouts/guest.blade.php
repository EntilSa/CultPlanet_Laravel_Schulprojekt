<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CultPlanet') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">

    <!-- Inter Schriftart -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50">

    {{-- Dunkelblaue Kopfzeile mit Logo --}}
    <div style="background-color: #1a2e4a;" class="w-full py-4 flex justify-center shadow-md">
        <a href="{{ route('home') }}">
            <img src="{{ asset('images/logo.svg') }}" alt="CultPlanet" class="h-12 w-auto">
        </a>
    </div>

    {{-- Login/Register-Formular zentriert --}}
    <div class="min-h-screen flex flex-col items-center justify-center px-4 -mt-16">
        <div class="w-full max-w-md bg-white rounded-xl shadow-md px-8 py-8">
            {{ $slot }}
        </div>
        <p class="text-slate-400 text-xs mt-6">© {{ date('Y') }} CultPlanet – Schulprojekt FIAE</p>
    </div>

</body>
</html>
