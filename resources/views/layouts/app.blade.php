<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Seitentitel – wird von der jeweiligen Seite gesetzt, Standard ist "CultPlanet" --}}
    <title>@yield('title', 'CultPlanet')</title>
    {{-- Tailwind CSS und JS werden von Vite eingebunden --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen">

    {{-- Navigation oben --}}
    <nav class="bg-white shadow-md">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            {{-- Logo / Shop-Name links --}}
            <a href="{{ url('/') }}" class="text-xl font-bold text-purple-700">CultPlanet</a>

            {{-- Navigation-Links rechts --}}
            <div class="flex gap-6 text-sm font-medium">
                <a href="{{ url('/') }}" class="hover:text-purple-600">Startseite</a>
                <a href="{{ url('/impressum') }}" class="hover:text-purple-600">Impressum</a>
                <a href="{{ url('/datenschutz') }}" class="hover:text-purple-600">Datenschutz</a>
            </div>
        </div>
    </nav>

    {{-- Hauptinhalt – jede Seite füllt diesen Bereich --}}
    <main class="flex-grow max-w-6xl mx-auto px-4 py-8 w-full">
        @yield('content')
    </main>

    {{-- Footer unten --}}
    <footer class="bg-white border-t border-gray-200 text-center text-sm text-gray-500 py-4">
        &copy; {{ date('Y') }} CultPlanet – Spielzeug-Onlineshop | Schulprojekt FIAE
    </footer>

</body>
</html>
