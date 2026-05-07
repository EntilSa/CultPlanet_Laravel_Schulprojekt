# CultPlanet – Design-Referenz

PFLICHT: Diese Datei vor jedem Frontend-Task vollständig lesen.
Die Mockup-HTML-Dateien (`mockup-startseite.html`, `mockup-produktseite.html`) zeigen das Zielbild.
Code soll so nah wie möglich an den Mockups sein.

---

## Assets

- Logo: `public/images/logo.svg`
- Favicon: `public/images/favicon.svg`
- Logo in Blade einbinden: `<img src="{{ asset('images/logo.svg') }}" alt="CultPlanet" class="h-10 w-auto">`
- Favicon im `<head>`: `<link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">`

---

## Schriftart

Im Haupt-Layout im `<head>` einbinden:

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
```

**WICHTIG – Tailwind v4:** Kein `tailwind.config.js` verwenden (wurde entfernt, da Breeze-Konflikt).
Inter wird stattdessen in `resources/css/app.css` über `@theme` gesetzt – das ist bereits erledigt:

```css
@theme {
    --font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
}
```

---

## Farben

| Verwendung          | Hex     | Tailwind                  |
|---------------------|---------|---------------------------|
| Navigation / Footer | #1a2e4a | `bg-[#1a2e4a]`            |
| Primär-Button       | #2563eb | `bg-blue-600`             |
| Akzent / Auktion    | #f97316 | `bg-orange-500`           |
| Seitenhintergrund   | #f8fafc | `bg-slate-50`             |
| Karten / Boxen      | #ffffff | `bg-white`                |
| Text normal         | #1e293b | `text-slate-800`          |
| Text gedimmt        | #64748b | `text-slate-500`          |
| Trennlinien         | #e2e8f0 | `border-slate-200`        |
| Sterne (Bewertung)  | #fb923c | `text-orange-400`         |

---

## Seitenstruktur (jede Seite)

```html
<body class="bg-slate-50 flex flex-col min-h-screen">
  @include('partials.nav')
  @yield('banner')   {{-- nur Startseite füllt diesen Slot --}}
  <main class="flex-1">
    {{-- Kein max-w-7xl hier – jede View wickelt ihren Inhalt selbst ein.
         Grund: verschiedene Seiten brauchen unterschiedliche Breiten
         (z.B. Produktliste max-w-7xl, Checkout max-w-2xl, Admin volle Breite).
         Jede View startet daher mit: <div class="max-w-7xl mx-auto px-6 py-8"> --}}
    @yield('content')
  </main>
  @include('partials.footer')
</body>
```

---

## Navigation

```html
<nav style="background-color: #1a2e4a;" class="shadow-lg">
  <div class="max-w-7xl mx-auto px-6 flex items-center justify-between h-16">

    {{-- Logo links --}}
    <img src="{{ asset('images/logo.svg') }}" alt="CultPlanet" class="h-10 w-auto">

    {{-- Links rechts --}}
    <div class="flex items-center gap-8">
      <a href="{{ route('shop.index') }}"
         class="text-white hover:text-orange-400 text-sm font-medium transition-colors">Shop</a>
      <a href="{{ route('auction.index') }}"
         class="text-white hover:text-orange-400 text-sm font-medium transition-colors">Auktion</a>
      <a href="{{ route('profile.edit') }}"
         class="text-white hover:text-orange-400 text-sm font-medium transition-colors">Mein Konto</a>

      {{-- Warenkorb mit Badge --}}
      <a href="{{ route('cart.index') }}" class="relative text-white hover:text-orange-400 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        @if(session('cart') && count(session('cart')) > 0)
          <span class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold">
            {{ count(session('cart')) }}
          </span>
        @endif
      </a>
    </div>
  </div>
</nav>
```

---

## Auktions-Banner (Startseite – MUSS hervorstechen)

Volle Breite, orangener Rand oben, dunkler Hintergrund.

```html
<div style="background-color: #1a2e4a;" class="w-full border-t-4 border-orange-500 text-white py-8 px-6 shadow-lg">
  <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center gap-8">

    {{-- Produktbild --}}
    <img src="{{ asset('storage/' . $auction->product->image) }}"
         class="w-36 h-36 object-cover rounded-xl shadow-lg flex-shrink-0" alt="">

    {{-- Infos --}}
    <div class="flex-1">
      <span class="text-orange-400 text-xs font-semibold uppercase tracking-widest">🔥 Tagesauktion</span>
      <h2 class="text-2xl font-bold mt-1">{{ $auction->product->name }}</h2>
      <p class="text-slate-300 text-sm mt-2">{{ Str::limit($auction->product->description, 120) }}</p>
      <p class="text-slate-400 text-sm mt-2">
        Aktuelles Höchstgebot:
        <span class="text-white font-semibold">€ {{ number_format($auction->highest_bid, 2, ',', '.') }}</span>
        <span class="text-slate-500">({{ $auction->bids_count }} Gebote)</span>
      </p>
    </div>

    {{-- Countdown + Button --}}
    <div class="text-center flex-shrink-0">
      <p class="text-slate-400 text-xs uppercase tracking-wide mb-1">Auktion endet in</p>
      <p id="countdown" class="text-4xl font-bold text-orange-400 tabular-nums tracking-tight">00:00:00</p>
      <a href="{{ route('auction.index') }}"
         class="mt-4 inline-block bg-orange-500 hover:bg-orange-600 text-white font-bold px-8 py-3 rounded-lg transition text-sm">
        Jetzt bieten →
      </a>
    </div>

  </div>
</div>
```

---

## Produkt-Grid (Startseite / Shop-Übersicht)

```html
{{-- Kopfzeile mit Produktanzahl + Sortierung --}}
<div class="flex items-center justify-between mb-8">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Alle Produkte</h1>
    <p class="text-slate-500 text-sm mt-1">{{ $products->total() }} Artikel verfügbar</p>
  </div>
  <select class="border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <option>Sortierung: Neueste</option>
    <option>Preis aufsteigend</option>
    <option>Preis absteigend</option>
  </select>
</div>

{{-- Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
  @foreach($products as $product)

    <div class="bg-white rounded-xl shadow p-4 flex flex-col hover:shadow-md transition-shadow">

      {{-- Produktbild --}}
      <div class="relative w-full h-48 bg-slate-50 rounded-lg mb-4 overflow-hidden flex items-center justify-center">
        <img src="{{ asset('storage/' . $product->image) }}"
             class="w-full h-full object-cover" alt="{{ $product->name }}">

        {{-- Badge: NEU oder AUSVERKAUFT --}}
        @if($product->stock === 0)
          <span class="absolute top-3 right-3 bg-slate-500 text-white text-xs font-semibold px-2 py-1 rounded-md">Ausverkauft</span>
        @elseif($product->created_at->isCurrentMonth())
          <span class="absolute top-3 left-3 bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded-md">NEU</span>
        @endif
      </div>

      {{-- Inhalt --}}
      <h3 class="text-slate-800 font-semibold text-base leading-snug">{{ $product->name }}</h3>

      {{-- Sterne --}}
      <div class="flex items-center gap-1 mt-1">
        @for($i = 1; $i <= 5; $i++)
          <span class="{{ $i <= round($product->reviews_avg_rating) ? 'text-orange-400' : 'text-slate-300' }} text-sm">★</span>
        @endfor
        <span class="text-slate-400 text-xs ml-1">({{ $product->reviews_count }})</span>
      </div>

      {{-- Preis + Button --}}
      <div class="flex items-center justify-between mt-auto pt-4">
        <span class="text-blue-600 font-bold text-xl">€ {{ number_format($product->price, 2, ',', '.') }}</span>

        @if($product->stock > 0)
          <form action="{{ route('cart.add', $product) }}" method="POST">
            @csrf
            <button class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition">
              In den Warenkorb
            </button>
          </form>
        @else
          <button disabled class="bg-slate-200 text-slate-500 py-2 px-4 rounded-lg text-sm font-medium cursor-not-allowed">
            Nicht verfügbar
          </button>
        @endif
      </div>

    </div>

  @endforeach
</div>
```

---

## Produktseite (Detailansicht)

```html
{{-- Breadcrumb --}}
<nav class="text-sm text-slate-500 mb-6">
  <a href="{{ route('shop.index') }}" class="hover:text-blue-600 transition-colors">Shop</a>
  <span class="mx-2">›</span>
  <span class="text-slate-800 font-medium">{{ $product->name }}</span>
</nav>

{{-- Produkt: Bild links, Info rechts --}}
<div class="flex flex-col lg:flex-row gap-10">

  {{-- Bild --}}
  <div class="lg:w-1/2">
    <div class="bg-white rounded-2xl shadow p-8 flex items-center justify-center" style="min-height: 420px;">
      <img src="{{ asset('storage/' . $product->image) }}"
           class="max-h-80 object-contain" alt="{{ $product->name }}">
    </div>
  </div>

  {{-- Infos --}}
  <div class="lg:w-1/2 flex flex-col">
    <h1 class="text-3xl font-bold text-slate-900 leading-tight">{{ $product->name }}</h1>

    {{-- Sterne + Anzahl --}}
    <div class="flex items-center gap-2 mt-3">
      @for($i = 1; $i <= 5; $i++)
        <span class="{{ $i <= round($product->reviews_avg_rating) ? 'text-orange-400' : 'text-slate-300' }} text-lg">★</span>
      @endfor
      <span class="text-slate-600 text-sm font-medium">{{ number_format($product->reviews_avg_rating, 1) }} von 5</span>
      <span class="text-slate-400 text-sm">({{ $product->reviews_count }} Bewertungen)</span>
    </div>

    {{-- Preis --}}
    <div class="mt-5">
      <span class="text-4xl font-bold text-blue-600">€ {{ number_format($product->price, 2, ',', '.') }}</span>
    </div>
    <p class="text-slate-500 text-sm mt-1">inkl. MwSt., zzgl. Versandkosten</p>

    {{-- Lagerbestand --}}
    <div class="flex items-center gap-2 mt-4">
      @if($product->stock > 0)
        <span class="w-2.5 h-2.5 bg-green-500 rounded-full"></span>
        <span class="text-green-700 text-sm font-medium">Auf Lager (noch {{ $product->stock }} verfügbar)</span>
      @else
        <span class="w-2.5 h-2.5 bg-red-500 rounded-full"></span>
        <span class="text-red-600 text-sm font-medium">Ausverkauft</span>
      @endif
    </div>

    {{-- In den Warenkorb --}}
    @if($product->stock > 0)
    <form action="{{ route('cart.add', $product) }}" method="POST" class="flex items-center gap-4 mt-6">
      @csrf
      <div class="flex items-center border border-slate-300 rounded-lg overflow-hidden">
        <button type="button" onclick="changeQty(-1)" class="px-4 py-3 text-slate-600 hover:bg-slate-100 transition font-bold text-lg">−</button>
        <input type="number" name="quantity" id="qty" value="1" min="1" max="{{ $product->stock }}"
               class="w-12 py-3 text-center font-semibold text-slate-800 border-x border-slate-300 focus:outline-none">
        <button type="button" onclick="changeQty(1)" class="px-4 py-3 text-slate-600 hover:bg-slate-100 transition font-bold text-lg">+</button>
      </div>
      <button type="submit"
              class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition text-sm">
        In den Warenkorb
      </button>
    </form>
    @endif

    {{-- Trennlinie --}}
    <hr class="my-6 border-slate-200">

    {{-- Beschreibung --}}
    <h3 class="font-semibold text-slate-800 mb-2">Beschreibung</h3>
    <p class="text-slate-600 text-sm leading-relaxed">{{ $product->description }}</p>
  </div>
</div>
```

---

## Bewertungs-Übersicht (Produktseite)

```html
<section class="mt-16">
  <h2 class="text-2xl font-bold text-slate-900 mb-6">Kundenbewertungen</h2>

  {{-- Übersicht-Box --}}
  <div class="bg-white rounded-xl shadow p-6 mb-6 flex flex-col sm:flex-row items-center gap-8">
    <div class="text-center">
      <p class="text-6xl font-bold text-slate-900">{{ number_format($product->reviews_avg_rating, 1) }}</p>
      <div class="text-orange-400 text-xl mt-1">★★★★★</div>
      <p class="text-slate-500 text-sm mt-1">{{ $product->reviews_count }} Bewertungen</p>
    </div>
    <div class="flex-1 w-full space-y-2">
      @foreach([5,4,3,2,1] as $star)
        @php $pct = $product->reviews_count > 0 ? round(($product->reviews->where('rating',$star)->count() / $product->reviews_count) * 100) : 0; @endphp
        <div class="flex items-center gap-3 text-sm">
          <span class="text-slate-500 w-8">{{ $star }} ★</span>
          <div class="flex-1 bg-slate-100 rounded-full h-2.5">
            <div class="bg-orange-400 h-2.5 rounded-full" style="width: {{ $pct }}%"></div>
          </div>
          <span class="text-slate-500 w-10 text-right">{{ $pct }}%</span>
        </div>
      @endforeach
    </div>
  </div>

  {{-- Einzelne Reviews --}}
  <div class="space-y-4">
    @foreach($product->reviews as $review)
    <div class="bg-white rounded-xl shadow p-6">
      <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center font-bold text-blue-700 text-sm">
            {{ strtoupper(substr($review->user->name, 0, 2)) }}
          </div>
          <span class="font-semibold text-slate-800">{{ $review->user->name }}</span>
        </div>
        <span class="text-slate-400 text-xs">{{ $review->created_at->format('d.m.Y') }}</span>
      </div>
      <div class="mb-2">
        @for($i = 1; $i <= 5; $i++)
          <span class="{{ $i <= $review->rating ? 'text-orange-400' : 'text-slate-300' }} text-sm">★</span>
        @endfor
      </div>
      <p class="text-slate-600 text-sm leading-relaxed">{{ $review->text }}</p>
    </div>
    @endforeach

    {{-- Bewertung schreiben --}}
    @auth
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-semibold text-slate-800 mb-4">Eigene Bewertung schreiben</h3>
        <form action="{{ route('reviews.store', $product) }}" method="POST" class="space-y-4">
          @csrf
          <div>
            <label class="block text-slate-700 font-medium mb-1">Bewertung</label>
            <select name="rating" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="5">★★★★★ Ausgezeichnet</option>
              <option value="4">★★★★ Gut</option>
              <option value="3">★★★ Okay</option>
              <option value="2">★★ Schlecht</option>
              <option value="1">★ Sehr schlecht</option>
            </select>
          </div>
          <div>
            <label class="block text-slate-700 font-medium mb-1">Kommentar</label>
            <textarea name="text" rows="3"
                      class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Deine Erfahrung mit dem Produkt..."></textarea>
          </div>
          <button class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-lg text-sm transition">
            Bewertung abschicken
          </button>
        </form>
      </div>
    @else
      <div class="bg-white rounded-xl shadow p-6 border-2 border-dashed border-slate-200">
        <p class="text-slate-500 text-sm mb-3">Du musst eingeloggt sein um eine Bewertung zu schreiben.</p>
        <a href="{{ route('login') }}"
           class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-lg text-sm transition">
          Jetzt einloggen
        </a>
      </div>
    @endauth
  </div>
</section>
```

---

## Footer

```html
<footer style="background-color: #1a2e4a;" class="text-white text-sm text-center py-8 mt-auto">
  <p class="font-semibold text-base mb-1">CultPlanet</p>
  <p class="text-slate-400">Dein Spielzeug-Shop mit täglichen Auktionen</p>
  <div class="flex justify-center gap-8 mt-4">
    <a href="{{ route('impressum') }}" class="text-slate-400 hover:text-orange-400 transition-colors">Impressum</a>
    <a href="{{ route('datenschutz') }}" class="text-slate-400 hover:text-orange-400 transition-colors">Datenschutz</a>
  </div>
  <p class="text-slate-600 text-xs mt-4">© {{ date('Y') }} CultPlanet – Schulprojekt FIAE</p>
</footer>
```

---

## Buttons

| Typ             | Klassen                                                                              |
|-----------------|--------------------------------------------------------------------------------------|
| Primär (Blau)   | `bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2.5 rounded-lg transition` |
| Akzent (Orange) | `bg-orange-500 hover:bg-orange-600 text-white font-bold px-8 py-3 rounded-lg transition` |
| Neutral (Grau)  | `bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium px-4 py-2.5 rounded-lg transition` |
| Gefahr (Rot)    | `bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2.5 rounded-lg transition` |
| Deaktiviert     | `bg-slate-200 text-slate-500 px-4 py-2.5 rounded-lg cursor-not-allowed` + `disabled` |

---

## Formulare

```html
<label class="block text-slate-700 font-medium mb-1">Feldname</label>
<input type="text" class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
<p class="text-red-500 text-sm mt-1">{{ $errors->first('feldname') }}</p>
```

---

## Allgemeine Regeln

- Kein eigenes CSS – nur Tailwind-Utilities
- Inhalt immer in `max-w-7xl mx-auto px-6` einwickeln
- Abrundungen: `rounded-lg` (klein) oder `rounded-xl` (Karten/Boxen)
- Schatten: `shadow` (Standard) oder `shadow-md` (hervorgehoben)
- Hover-Übergänge: immer `transition` oder `transition-colors` dazuschreiben
- Sternebewertung: gefüllt = `text-orange-400`, leer = `text-slate-300`
- Abstände zwischen Abschnitten: `mt-16`
- Karten: immer `bg-white rounded-xl shadow p-4 flex flex-col`
