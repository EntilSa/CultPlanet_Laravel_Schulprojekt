<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CultPlanet') }} – @yield('title', 'Spielzeug-Shop')</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">

    <!-- Inter Schriftart von Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS + JS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 flex flex-col min-h-screen">

    <!-- Navigation -->
    @include('layouts.navigation')

    <!-- Auktions-Banner (nur Startseite füllt diesen Bereich) -->
    @yield('banner')

    <!-- Hauptinhalt -->
    {{-- Unterstuetzt zwei Stile: @extends+@section (Phase 2) und x-app-layout+slot (Breeze) --}}
    <main class="flex-1">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer -->
    @include('layouts.footer')

    {{-- optionale scripts von einzelnen views (z.b. countdown, mengenwahl) --}}
    @stack('scripts')

</body>
</html>
