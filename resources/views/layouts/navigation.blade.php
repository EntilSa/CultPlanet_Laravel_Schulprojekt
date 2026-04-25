{{-- CultPlanet Navigation – dunkelblaue Navbar mit Logo, Links und Warenkorb-Icon --}}
{{--
    PHASE 2 TODO für Claude Code:
    Folgende Platzhalter (#) durch echte Routen ersetzen sobald sie existieren:
    - Logo-Link + "Shop"-Link: route('shop.index')   → nach ProductController + Routen anlegen
    - "Auktion"-Link:          route('auction.index') → nach AuctionController + Routen anlegen
    - Warenkorb-Link:          route('cart.index')    → nach CartController + Routen anlegen
    Breeze-Routen (login, register, logout, profile.edit) funktionieren bereits.
--}}
<nav style="background-color: #1a2e4a;" class="shadow-lg">
    <div class="max-w-7xl mx-auto px-6 flex items-center justify-between h-16">

        {{-- Logo links – führt zur Shop-Übersicht --}}
        <a href="{{ route('shop.index') }}">
            <img src="{{ asset('images/logo.svg') }}" alt="CultPlanet" class="h-10 w-auto">
        </a>

        {{-- Navigationslinks rechts --}}
        <div class="flex items-center gap-8">

            {{-- Shop-Link --}}
            <a href="{{ route('shop.index') }}"
               class="text-white hover:text-orange-400 text-sm font-medium transition-colors">
                Shop
            </a>

            <a href="{{ route('auction.index') }}"
               class="text-white hover:text-orange-400 text-sm font-medium transition-colors">
                Auktion
            </a>

            @auth
                {{-- Admin-Link – nur für admins sichtbar --}}
                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}"
                       class="text-white hover:text-orange-400 text-sm font-medium transition-colors">
                        Admin
                    </a>
                @endif

                {{-- Verkauf-Link – für admins und mitarbeiter --}}
                @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('mitarbeiter'))
                    <a href="{{ route('admin.sales') }}"
                       class="text-white hover:text-orange-400 text-sm font-medium transition-colors">
                        Verkauf
                    </a>
                @endif

                {{-- Bereiche-Link – nur für admins --}}
                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.departments.index') }}"
                       class="text-white hover:text-orange-400 text-sm font-medium transition-colors">
                        Bereiche
                    </a>
                @endif

                {{-- Meine Bestellungen – nur für eingeloggte Kunden --}}
                <a href="{{ route('orders.my') }}"
                   class="text-white hover:text-orange-400 text-sm font-medium transition-colors">
                    Bestellungen
                </a>

                {{-- Mein Konto – funktioniert bereits (Breeze) --}}
                <a href="{{ route('profile.edit') }}"
                   class="text-white hover:text-orange-400 text-sm font-medium transition-colors">
                    Mein Konto
                </a>

                {{-- Logout – funktioniert bereits (Breeze) --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="text-white hover:text-orange-400 text-sm font-medium transition-colors">
                        Logout
                    </button>
                </form>

                {{-- Warenkorb-Icon --}}
                <a href="{{ route('cart.index') }}"
                   class="relative text-white hover:text-orange-400 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    {{-- Badge: Anzahl Artikel im Warenkorb --}}
                    @if(session('cart') && count(session('cart')) > 0)
                        <span class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs
                                     rounded-full h-5 w-5 flex items-center justify-center font-bold">
                            {{ count(session('cart')) }}
                        </span>
                    @endif
                </a>

            @else
                {{-- Login + Registrieren – funktionieren bereits (Breeze) --}}
                <a href="{{ route('login') }}"
                   class="text-white hover:text-orange-400 text-sm font-medium transition-colors">
                    Login
                </a>
                <a href="{{ route('register') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium
                          px-4 py-2 rounded-lg transition">
                    Registrieren
                </a>
            @endauth

        </div>
    </div>
</nav>
