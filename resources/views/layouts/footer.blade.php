{{-- CultPlanet Footer – dunkelblau, wie Navigation --}}
<footer style="background-color: #1a2e4a;" class="text-white text-sm text-center py-8 mt-auto">
    <p class="font-semibold text-base mb-1">CultPlanet</p>
    <p class="text-slate-400">Dein Spielzeug-Shop mit täglichen Auktionen</p>
    <div class="flex justify-center gap-8 mt-4">
        <a href="{{ route('impressum') }}"
           class="text-slate-400 hover:text-orange-400 transition-colors">Impressum</a>
        <a href="{{ route('datenschutz') }}"
           class="text-slate-400 hover:text-orange-400 transition-colors">Datenschutz</a>
    </div>
    <p class="text-slate-600 text-xs mt-4">© {{ date('Y') }} CultPlanet – Schulprojekt FIAE</p>
</footer>
