# CultPlanet – Handover

## Aktueller Stand
**Phase 0, 1, 2, 3, Spezialisierung und Individualprojekt vollständig abgeschlossen. 132 Tests alle grün.**
**QoL-Verbesserungen vollständig umgesetzt (25.04.2026) – alle 7 Schritte erledigt.**
**PDF-Rechnungen, Mailpit, Meine Bestellungen, Lagerbestand-Warnung, Pint vollständig umgesetzt (25.04.2026).**
**Alle Funktionen per Browser getestet und bestätigt (25.04.2026).**

### Wichtige Bugfixes dieser Session (25.04.2026)
- **Timezone-Fix:** `config/app.php` → `'timezone' => 'Europe/Berlin'` (war: `UTC`). Auktionen aktivierten sich nicht weil `now()` UTC zurückgab, Eingaben aber in CEST (UTC+2) gemacht wurden.

### Neue Seeder (25.04.2026)
- `ReviewSeeder` – 35 verschiedene Bewertungstexte, 1–3 Reviews pro Produkt, alle 20 Produkte abgedeckt
- `OrderSeeder` – 15 Bestellungen, alle Status/Zahlungsmethoden/Mengen abgedeckt, 5 Dummy-Kunden
- `DepartmentSeeder` – 4 Bereiche (Lager, Verkauf, Kasse, Versand) + 4 Mitarbeiter-Nutzer; Versand absichtlich leer für Warnsystem-Demo
- Alle drei in `DatabaseSeeder` eingebunden → `php artisan migrate:fresh --seed` läuft alles durch

---

## Neue Features – VOLLSTÄNDIG ERLEDIGT (25.04.2026)

**Ziel:** PDF-Rechnungen, E-Mail-Simulation via Mailpit, Lagerbestand-Warnung, Kundenbestellungsübersicht, Code-Formatierung mit Pint.
**Deadline:** 04. Mai 2026 – alles muss bis dahin fertig und getestet sein.

### Schritt 1 – Pakete installieren

```bash
composer require barryvdh/laravel-dompdf
composer require laravel/pint --dev
```

### Schritt 2 – Mailpit Setup (lokaler Fake-Mailserver)

Mailpit fängt alle Laravel-E-Mails lokal ab und zeigt sie in einer Web-UI an (localhost:8025).
Der Nutzer muss Mailpit einmalig manuell starten: mailpit.exe herunterladen von https://github.com/axllent/mailpit/releases (Windows Binary), dann `mailpit.exe` ausführen.

**`.env` anpassen** – folgende Zeilen ersetzen/ergänzen:
```
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="shop@cultplanet.de"
MAIL_FROM_NAME="CultPlanet"
```

### Schritt 3 – PDF-Template erstellen

**Neue Datei:** `resources/views/pdf/rechnung.blade.php`

Einfaches HTML-Template (dompdf rendert plain HTML – kein Tailwind, kein Vite, nur inline CSS).

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #333; }
        .header { background: #1a2e4a; color: white; padding: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 4px 0 0 0; font-size: 12px; opacity: 0.8; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f1f5f9; text-align: left; padding: 8px 10px; border-bottom: 2px solid #1a2e4a; }
        td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
        .total-row td { font-weight: bold; border-top: 2px solid #1a2e4a; border-bottom: none; }
        .info { margin-bottom: 20px; }
        .info p { margin: 4px 0; }
        .label { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CultPlanet</h1>
        <p>Ihr Spielzeug-Onlineshop</p>
    </div>

    <h2>Rechnung / Auftragsbestätigung</h2>

    <div class="info">
        <p><span class="label">Bestellnummer:</span> #{{ $order->id }}</p>
        <p><span class="label">Datum:</span> {{ $order->created_at->format('d.m.Y H:i') }} Uhr</p>
        <p><span class="label">Kunde:</span> {{ $order->vorname }} {{ $order->nachname }}</p>
        <p><span class="label">Zahlungsmethode:</span> {{ ucfirst($order->zahlungsmethode) }}</p>
        <p><span class="label">Status:</span> {{ ucfirst($order->status) }}</p>
    </div>

    <table>
        <tr>
            <th>Artikel</th>
            <th>Menge</th>
            <th>Einzelpreis</th>
            <th>Gesamt</th>
        </tr>
        @foreach($order->items as $item)
        <tr>
            <td>{{ $item->name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->price, 2, ',', '.') }} €</td>
            <td>{{ number_format($item->price * $item->quantity, 2, ',', '.') }} €</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="3">Gesamtbetrag</td>
            <td>{{ number_format($order->total, 2, ',', '.') }} €</td>
        </tr>
    </table>

    <p style="margin-top: 40px; font-size: 11px; color: #666;">
        Vielen Dank für Ihren Einkauf bei CultPlanet!<br>
        Dies ist eine automatisch generierte Rechnung.
    </p>
</body>
</html>
```

### Schritt 4 – Mail-Klassen erstellen

**Neue Datei:** `app/Mail/BestellbestaetiguungMail.php`

```php
<?php

namespace App\Mail;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BestellbestaetigungMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Deine Bestellung #' . $this->order->id . ' bei CultPlanet',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.bestellbestaetigung',
        );
    }

    public function attachments(): array
    {
        // pdf generieren und als anhang hinzufügen
        $pdf = Pdf::loadView('pdf.rechnung', ['order' => $this->order]);

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => $pdf->output(),
                'Rechnung-' . $this->order->id . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
```

**Neue Datei:** `app/Mail/AuktionGewonnenMail.php`
– gleiche Struktur wie BestellbestaetigungMail, aber anderer Subject-Text und anderes View-Template:

```php
<?php

namespace App\Mail;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuktionGewonnenMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Glückwunsch! Du hast eine Auktion gewonnen – CultPlanet',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.auktion-gewonnen',
        );
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdf.rechnung', ['order' => $this->order]);

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => $pdf->output(),
                'Rechnung-Auktion-' . $this->order->id . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
```

### Schritt 5 – Mail-Templates (Blade) erstellen

**Neue Datei:** `resources/views/mail/bestellbestaetigung.blade.php`

```html
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #333; padding: 20px;">
    <h2 style="color: #1a2e4a;">Vielen Dank für deine Bestellung!</h2>
    <p>Hallo {{ $order->vorname }},</p>
    <p>deine Bestellung <strong>#{{ $order->id }}</strong> ist bei uns eingegangen.</p>
    <p><strong>Gesamtbetrag:</strong> {{ number_format($order->total, 2, ',', '.') }} €</p>
    <p>Im Anhang findest du deine Rechnung als PDF.</p>
    <hr>
    <p style="font-size: 12px; color: #666;">CultPlanet – Dein Spielzeug-Onlineshop</p>
</body>
</html>
```

**Neue Datei:** `resources/views/mail/auktion-gewonnen.blade.php`

```html
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #333; padding: 20px;">
    <h2 style="color: #f97316;">🎉 Glückwunsch – du hast gewonnen!</h2>
    <p>Hallo {{ $order->vorname }},</p>
    <p>du hast die Auktion für <strong>{{ $order->items->first()?->name }}</strong> gewonnen!</p>
    <p><strong>Gewinngebot:</strong> {{ number_format($order->total, 2, ',', '.') }} €</p>
    <p>Deine Bestellnummer lautet: <strong>#{{ $order->id }}</strong></p>
    <p>Bitte ergänze deine Lieferadresse in deinem Konto. Im Anhang findest du deine Rechnung als PDF.</p>
    <hr>
    <p style="font-size: 12px; color: #666;">CultPlanet – Dein Spielzeug-Onlineshop</p>
</body>
</html>
```

### Schritt 6 – Mail senden nach Checkout

**Datei:** `app/Http/Controllers/OrderController.php`

In der `store()`-Methode, **nach** `$order = Order::create([...])` und nachdem die OrderItems gespeichert wurden, folgende Zeilen einfügen:

```php
// bestellbestaetigung per mail schicken (mit pdf-rechnung als anhang)
$order->load('items'); // items nachladen damit sie im pdf vorhanden sind
\Illuminate\Support\Facades\Mail::to($request->user()->email)
    ->send(new \App\Mail\BestellbestaetigungMail($order));
```

Am Anfang der Datei den Import ergänzen:
```php
use App\Mail\BestellbestaetigungMail;
```

### Schritt 7 – Mail senden nach Auktionsende

**Datei:** `app/Http/Controllers/AuctionController.php`

In der `schliesseAuktion()`-Methode, nach `$auction->product->decrement('stock');` (ganz am Ende der Methode), einfügen:

```php
// gewinner per mail benachrichtigen (mit pdf-rechnung)
$order->load('items'); // items nachladen
\Illuminate\Support\Facades\Mail::to($winner->email)
    ->send(new \App\Mail\AuktionGewonnenMail($order));
```

Am Anfang der Datei ergänzen:
```php
use App\Mail\AuktionGewonnenMail;
```

**WICHTIG:** Die gleiche Mail-Logik muss auch in `app/Console/Commands/AuctionsClose.php` ergänzt werden. Dort wird `schliesseAuktion()` ebenfalls aufgerufen – prüfen ob die Methode direkt dort definiert ist oder der Controller genutzt wird. Falls der Command eigene Logik hat, dort gleichermaßen `AuktionGewonnenMail` senden.

### Schritt 8 – Lagerbestand-Warnung im Admin

**Datei:** `resources/views/admin/products/index.blade.php`

In der Produkt-Tabelle, bei der Lagerbestand-Zelle, folgende Bedingung ergänzen:
- Wenn `$product->stock < 5` → rotes Badge `bg-red-100 text-red-700` mit Text "Nur {{ $product->stock }} übrig"
- Wenn `$product->stock == 0` → rotes Badge "Ausverkauft"
- Sonst → normale Zahl in grün `text-green-700`

Beispiel-Blade:
```blade
@if($product->stock == 0)
    <span class="px-2 py-1 rounded text-xs bg-red-100 text-red-700 font-semibold">Ausverkauft</span>
@elseif($product->stock < 5)
    <span class="px-2 py-1 rounded text-xs bg-red-100 text-red-700 font-semibold">Nur {{ $product->stock }} übrig</span>
@else
    <span class="text-green-700 font-semibold">{{ $product->stock }}</span>
@endif
```

### Schritt 9 – "Meine Bestellungen" Seite für Kunden

Diese Seite existiert noch nicht – sie muss komplett neu erstellt werden.

**Route hinzufügen in `routes/web.php`** (innerhalb des `auth`-Middleware-Blocks, bei den anderen Order-Routen):
```php
Route::get('/meine-bestellungen', [OrderController::class, 'myOrders'])->name('orders.my');
Route::get('/meine-bestellungen/{order}/pdf', [OrderController::class, 'downloadPdf'])->name('orders.pdf');
```

**Methoden in `app/Http/Controllers/OrderController.php` ergänzen:**

```php
// alle bestellungen des eingeloggten kunden anzeigen
public function myOrders()
{
    $orders = Order::where('user_id', auth()->id())
        ->with('items')
        ->orderByDesc('created_at')
        ->get();

    return view('orders.my-orders', compact('orders'));
}

// pdf für eine bestellung herunterladen (nur eigene bestellungen!)
public function downloadPdf(Order $order)
{
    // sicherstellen dass der kunde nur seine eigenen bestellungen sehen kann
    if ($order->user_id !== auth()->id()) {
        abort(403);
    }

    $order->load('items');
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.rechnung', ['order' => $order]);

    return $pdf->download('Rechnung-' . $order->id . '.pdf');
}
```

Am Dateianfang ergänzen:
```php
use Barryvdh\DomPDF\Facade\Pdf;
```

**Neue View:** `resources/views/orders/my-orders.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Meine Bestellungen</h1>

    @if($orders->isEmpty())
        <p class="text-gray-500">Du hast noch keine Bestellungen.</p>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="font-semibold text-gray-800">Bestellung #{{ $order->id }}</p>
                        <p class="text-sm text-gray-500">{{ $order->created_at->format('d.m.Y H:i') }} Uhr</p>
                    </div>
                    <div class="text-right">
                        {{-- status badge aus dem partial --}}
                        @include('partials.status-badge', ['status' => $order->status])
                        <p class="text-lg font-bold text-gray-800 mt-1">{{ number_format($order->total, 2, ',', '.') }} €</p>
                    </div>
                </div>

                {{-- artikel liste --}}
                <ul class="text-sm text-gray-600 mb-4">
                    @foreach($order->items as $item)
                        <li>{{ $item->quantity }}x {{ $item->name }} – {{ number_format($item->price, 2, ',', '.') }} € / Stk.</li>
                    @endforeach
                </ul>

                {{-- pdf download button --}}
                <a href="{{ route('orders.pdf', $order) }}"
                   class="inline-block px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                    Rechnung als PDF
                </a>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
```

**Navigation ergänzen:** In `resources/views/layouts/navigation.blade.php` im eingeloggten Bereich (wo auch "Profil" steht) einen Link "Meine Bestellungen" hinzufügen:
```blade
<a href="{{ route('orders.my') }}" class="...">Meine Bestellungen</a>
```

### Schritt 10 – Pint ausführen (Code-Formatter)

```bash
./vendor/bin/pint
```

Pint formatiert automatisch alle PHP-Dateien nach Laravel-Standard. Danach kurz prüfen ob alles noch funktioniert.

### Schritt 11 – Tests prüfen und ggf. anpassen

```bash
php artisan test
```

Falls Tests wegen der neuen Mail-Aufrufe fehlschlagen: In den betroffenen Feature-Tests `Mail::fake()` ergänzen, damit keine echten Mails versucht werden.

Beispiel am Anfang eines Tests:
```php
\Illuminate\Support\Facades\Mail::fake();
```

Neue Tests sind optional – wenn Zeit bleibt, einen einfachen Test schreiben der prüft ob nach dem Checkout eine Mail-Klasse dispatched wird.

### Schritt 12 – handover.md + tracking.md aktualisieren

Am Ende alle erledigten Schritte als ✓ markieren, tracking.md mit neuer Session-Zeile ergänzen.

### Reihenfolge der Umsetzung
1. ✓ composer require barryvdh/laravel-dompdf + laravel/pint
2. ✓ .env anpassen (Mailpit-Einstellungen: SMTP port 1025, shop@cultplanet.de)
3. ✓ resources/views/pdf/rechnung.blade.php erstellen
4. ✓ app/Mail/BestellbestaetigungMail.php erstellen
5. ✓ app/Mail/AuktionGewonnenMail.php erstellen
6. ✓ resources/views/mail/bestellbestaetigung.blade.php erstellen
7. ✓ resources/views/mail/auktion-gewonnen.blade.php erstellen
8. ✓ OrderController: Mail nach Checkout senden + myOrders() + downloadPdf()
9. ✓ AuctionController: Mail nach Auktionsende senden + CloseAuctions-Command ebenfalls
10. ✓ admin/products/index.blade.php: Lagerbestand-Warnung (rot < 5, grün ≥ 5)
11. ✓ Route + Methoden für "Meine Bestellungen" in web.php + OrderController
12. ✓ resources/views/orders/my-orders.blade.php erstellen
13. ✓ navigation.blade.php: Link "Bestellungen" ergänzen
14. ✓ php artisan test – 132 Tests grün (phpunit.xml hat MAIL_MAILER=array, kein Mail::fake() nötig)
15. ✓ ./vendor/bin/pint – 32 Dateien formatiert
16. ✓ handover.md + tracking.md aktualisiert

---

## QoL-Verbesserungen – Nächste Session (25.04.2026)

Nach einem vollständigen Frontend-Review wurden folgende Verbesserungen identifiziert und beschlossen.
Alle Änderungen sind klein (kein neuer Controller, keine neue Migration, keine neuen Routen außer einem Redirect).

### Änderung 1 – Startseite entfernt, Shop ist jetzt Landing Page
**Datei:** `routes/web.php`
- Die Route `/` gibt nicht mehr `pages.startseite` zurück
- Stattdessen: `Route::redirect('/', '/shop')->name('home');`
- Begründung: Echte Shops führen direkt zu Produkten. Die Startseite war eine leere Zwischenstation ohne Mehrwert.

### Änderung 2 – Auktions-Banner in Shop-Übersicht integriert
**Dateien:** `app/Http/Controllers/ProductController.php`, `resources/views/shop/index.blade.php`
- Der Auktions-Banner (bisher nur auf der Startseite) wird jetzt oben in der Shop-Übersicht angezeigt, direkt über dem Produktgrid
- `ProductController::index()` muss `$auktionBanner` laden (gleiche Logik wie bisher in der Home-Route in web.php):
  ```php
  $auktionBanner = \App\Models\Auction::with('product')
      ->where('status', 'aktiv')
      ->where('end_time', '>', now())
      ->withCount('bids')
      ->first();
  if (!$auktionBanner) {
      $auktionBanner = \App\Models\Auction::with('product')
          ->where('status', 'geplant')
          ->where('start_time', '>', now())
          ->orderBy('start_time')
          ->first();
  }
  ```
- In `shop/index.blade.php` ganz oben (vor dem Produktgrid, nach dem Seitentitel) den Auktions-Banner aus `design.md` einfügen – nur wenn `$auktionBanner` vorhanden (`@if($auktionBanner)`)
- Banner-Template steht fertig in `design.md` unter "Auktions-Banner"
- Countdown-Script (Vanilla JS) muss mitgezogen werden – steht in `auction/show.blade.php` als Vorlage

### Änderung 3 – Produktbild und Produktname klickbar (→ Detailseite)
**Datei:** `resources/views/shop/index.blade.php`
- Das Produktbild (`<img>`) und der Produktname (`<h3>` oder `<p>`) in jeder Produktkarte müssen in einen `<a href="{{ route('shop.show', $product) }}">` Link eingewickelt werden
- Der "Ansehen"-Button bleibt zusätzlich bestehen
- Standard in jedem Onlineshop – Nutzer erwarten dass Bild und Name klickbar sind

### Änderung 4 – Hover-Effekt auf Produktkarten
**Datei:** `resources/views/shop/index.blade.php`
- Jede Produktkarte bekommt einen subtilen Hover-Effekt: `hover:shadow-lg hover:-translate-y-1 transition-all duration-200`
- Das `transition-shadow` das bereits da ist durch `transition-all duration-200` ersetzen
- Gibt dem Shop ein moderneres, lebendigeres Gefühl

### Änderung 5 – Pagination-Text auf Deutsch
**Datei:** `resources/views/shop/index.blade.php`
- Der Text "Showing X to Y of Z results" (Laravel Standard-Pagination) auf Deutsch umstellen
- Entweder: `AppServiceProvider` mit `Paginator::defaultView()` und eigenem Blade anpassen
- Oder einfacher: unterhalb des Grids statt `{{ $products->links() }}` einen eigenen Pagination-Block mit deutschem Text bauen:
  ```php
  // Zeige X bis Y von Z Ergebnissen
  Zeige {{ $products->firstItem() }} bis {{ $products->lastItem() }} von {{ $products->total() }} Artikeln
  ```
- Die Pagination-Links (`$products->links()`) bleiben, nur der Text darüber/darunter wird deutsch

### Änderung 6 – Login- und Auth-Seiten auf Deutsch
**Dateien:** `resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php`, `resources/views/auth/forgot-password.blade.php`
- "LOG IN" → "Anmelden"
- "Forgot your password?" → "Passwort vergessen?"
- "Remember me" → "Angemeldet bleiben"
- "Email" bleibt (international verständlich)
- "Password" → "Passwort"
- Auf der Login-Seite fehlt ein Link "Noch kein Konto? Jetzt registrieren" → hinzufügen

### Reihenfolge der Umsetzung
1. web.php: Route `/` auf Redirect umstellen
2. ProductController::index(): $auktionBanner laden + in View übergeben
3. shop/index.blade.php: Auktions-Banner oben einfügen
4. shop/index.blade.php: Bild + Name klickbar machen
5. shop/index.blade.php: Hover-Effekte auf Karten
6. shop/index.blade.php: Pagination-Text eingedeutscht
7. Auth-Views: Texte eingedeutscht + Register-Link auf Login-Seite

### Offener Bugfix nach Review (25.04.2026)

**Pagination – doppelter Text in `resources/views/shop/index.blade.php`**
Claude Code hat den deutschen Text "Zeige X bis Y von Z Artikeln" korrekt hinzugefügt, aber den englischen Laravel-Standard-Text "Showing 1 to 12 of 20 results" nicht entfernt. Beide werden gerade gleichzeitig angezeigt. Der englische Text muss entfernt werden – nur der deutsche bleibt.

### Was NICHT geändert wird
- `startseite.blade.php` bleibt bestehen (wird nur nicht mehr direkt aufgerufen – schadet nicht)
- Keine neuen Tests nötig (nur Frontend-Änderungen, keine Logik-Änderungen)
- Keine neuen Routen außer dem Redirect
- Keine Datenbankänderungen

### Phase 0 – Erledigt
- Laravel 13 installiert, MySQL konfiguriert, Git initialisiert
- Vite + Tailwind v4 eingerichtet (kein tailwind.config.js – Inter via @theme in app.css)
- Blade-Layout erstellt (app.blade.php, navigation.blade.php, footer.blade.php)
- CultPlanet-Design aktiv: Dunkelblau (#1a2e4a), Inter-Schriftart, Favicon + Logo (SVG)
- Statische Seiten: Startseite, Impressum, Datenschutz
- Fehlerseiten: 404 und 500
- ER-Diagramm erstellt (er-diagramm.drawio)

### Phase 1 – Erledigt
- Laravel Breeze installiert (Login, Registrierung, Passwort-Reset, Profil)
- Tailwind v3/v4 Konflikt gelöst (postcss.config.js + tailwind.config.js entfernt)
- Spatie Laravel-Permission: 3 Rollen (admin, mitarbeiter, kunde)
- Neue Nutzer bekommen automatisch die Rolle "kunde" bei Registrierung
- 28 PHPUnit Tests – alle grün (inkl. eigene Rollen-Tests)

### Design & Layout – Erledigt
- Logo: public/images/logo.svg (Planet mit Ring, blau/schwarz)
- Favicon: public/images/favicon.svg (nur Planet, kein Text)
- Mockups: mockup-startseite.html + mockup-produktseite.html (abgenommen)
- design.md: vollständige Design-Referenz mit fertigem Blade-Code
- app.blade.php: CultPlanet-Design, unterstützt beide Blade-Stile ($slot für Breeze + @yield für Phase 2)
- navigation.blade.php, footer.blade.php, guest.blade.php: CultPlanet-Design aktiv
- web.php: Startseite → pages.startseite, benannte Routen für impressum + datenschutz
- startseite.blade.php: neues Design, TODO-Hinweis für Phase 2
- Alle 6 Auth-Controller: Redirect von route('dashboard') → route('home')
- Phase-2-Routen in web.php als auskommentierte TODOs vorbereitet

### Phase 2 – Erledigt (22.04.2026)
- storage:link ausgeführt
- Produkte: Migration, Model, ProductController (CRUD), Admin-geschützte Verwaltungsrouten
- Bildupload: Storage::store(), altes Bild wird beim Update gelöscht
- Session-Warenkorb: CartController (add, remove, clear, index), Badge in Navigation
- Checkout: OrderController, Lieferadresse + Zahlungswahl, Bestellung + OrderItems speichern
- Attrappen-Zahlung: Fake-PayPal und Sofortüberweisung Seite, Status offen → bezahlt
- Reviews: ReviewController, Duplikat-Schutz (unique constraint), Sterne 1–5
- Lagerbestand wird bei Bestellung automatisch reduziert
- Artikelnummer: echte DB-Spalte `artikel_nr`, wird automatisch per Eloquent-Model-Event gesetzt (id + 10000)
- Artikelnummer (artikel_nr): echte DB-Spalte, automatisch gesetzt per Eloquent-Model-Event
- 51 PHPUnit Tests – alle grün

### Phase 3 – Erledigt (22.04.2026)
- AdminController: dashboard(), orders(), orderUpdate(), users(), userRoleUpdate(), sales()
- 6 Admin-Routen unter `/admin/` mit Prefix `admin.`
- 4 Views: admin/dashboard, admin/orders, admin/users, admin/sales
- Status-Badge als wiederverwendbares Partial (offen/bezahlt/versendet/storniert)
- Navigation: Admin-Link (nur admin), Verkauf-Link (admin + mitarbeiter)
- Produkte in Admin-Panel: create/edit/destroy war bereits fertig (aus Phase 2)
- 9 neue PHPUnit-Tests – 60 Tests insgesamt, alle grün

## Letzte bearbeitete Datei
`resources/views/auth/login.blade.php` + `register.blade.php` + `forgot-password.blade.php` + `shop/index.blade.php` + `ExampleTest.php` (25.04.2026) – QoL-Verbesserungen: Shop-Redirect, Auktions-Banner, klickbare Karten, Hover-Effekte, Pagination auf Deutsch, Auth-Views auf Deutsch, Register-Link auf Login-Seite.

## QoL-Verbesserungen – VOLLSTÄNDIG ERLEDIGT (25.04.2026)

| Schritt | Beschreibung | Status |
|---------|-------------|--------|
| 1 | Route `/` → Redirect auf `/shop` | ✓ |
| 2 | `ProductController::index()` lädt `$auktionBanner` | ✓ |
| 3 | Auktions-Banner oben in `shop/index.blade.php` | ✓ |
| 4 | Produktbild + Name klickbar zur Detailseite | ✓ |
| 5 | Hover-Effekte auf Produktkarten | ✓ |
| 6 | Pagination-Text auf Deutsch | ✓ |
| 7 | Auth-Views auf Deutsch + Register-Link | ✓ |

Außerdem: `ExampleTest.php` auf `assertRedirect('/shop')` umgestellt (war `assertStatus(200)` für GET `/`).

## Browsertest-Ergebnisse (25.04.2026)

| Feature | Ergebnis |
|---------|---------|
| PDF-Rechnung per Mail nach Checkout | ✓ Mail in Mailpit, PDF-Anhang 1.1 MB |
| "Meine Bestellungen" unter /meine-bestellungen | ✓ Nav-Link vorhanden, alle Bestellungen angezeigt |
| Lagerbestand-Warnung im Admin | ✓ Rote Zahlen bei stock < 5 |
| Auktion anlegen (Admin) | ✓ Formular funktioniert |
| Auktion aktiviert sich automatisch beim Seitenaufruf | ✓ nach Timezone-Fix |
| Zu niedriges Gebot | ✓ Fehlermeldung mit korrektem Mindestbetrag |
| Gültiges Gebot | ✓ Höchstgebot aktualisiert, Verlauf angezeigt |
| Als Höchstbietender nochmal bieten | ✓ korrekt blockiert |
| Auktionsende – Gewinner angezeigt | ✓ "Gewinner: Benjamin Bannach" |
| Mitarbeiterverwaltung mit Testdaten | ✓ Warnsystem aktiv (Versand unbesetzt) |
| 132 PHPUnit Tests | ✓ alle grün |

## Was als nächstes ansteht

### Offen (nach Priorität)
1. **Projektdokumentation schreiben** (höchste Priorität – Schulabgabe)
   - Ca. 3 Seiten (±1): ER-Diagramm, Ablaufdiagramm Tagesauktion, Entscheidungsfindung, Fazit, Umsetzungsauszug
   - Material liegt bereits vor: `entscheidungen.md`, `lernnotizen.md`, `handover.md` als Quellen nutzen
2. **Abschlusspräsentation vorbereiten**
   - 132 Tests als Beleg für Qualitätssicherung zeigen
   - Phasen-Aufbau erklären, Entscheidungen begründen

### Bereits fertig (nicht mehr anfassen nötig)
- Alle 6 Phasen abgeschlossen (Phase 0–3, Spezialisierung, Individualprojekt)
- 20 Produkte mit Bildern im Shop
- Suche + Filter im Shop (Textsuche, Preisbereich, Verfügbarkeit, Sortierung)
- 132 PHPUnit Tests – alle grün
- Testdateien: AdminTest, AuctionTest, CartTest, DepartmentTest, GrenzwertTest, KundenreiseTest, OrderTest, ReviewTest, RoleTest, ShopTest, SicherheitsTest, SuchfilterTest

## Individualprojekt – Mitarbeiterverwaltung – ABGESCHLOSSEN (24.04.2026)
- Migration `departments` (id, name unique, timestamps)
- Migration `department_user` (Pivot-Tabelle, cascadeOnDelete)
- `Department`-Model: `belongsToMany(User)`, `istUnbesetzt()`-Hilfsmethode
- `User`-Model: `belongsToMany(Department)` ergänzt
- `DepartmentController`: index(), store(), destroy(), addUser(), removeUser()
- 5 Routen unter `/admin/bereiche/` mit Prefix `admin.departments.`
- View `admin/departments/index.blade.php`: Bereiche-Karten, Mitarbeiterliste, Zuweisungs-Dropdown, Warnsystem
- Warnsystem: orangene Warnung wenn min. 1 Bereich unbesetzt; Badge "Unbesetzt"/"Besetzt" pro Bereich
- Link im Admin-Dashboard + "Bereiche"-Link in Navigation (nur admin)
- 11 PHPUnit-Tests – alle grün

## Bugfixes dieser Session (22.04.2026)
- `tests/Feature/Auth/AuthenticationTest.php`: `route('dashboard')` → `route('home')` gefixt
- `tests/Feature/Auth/EmailVerificationTest.php`: `route('dashboard')` → `route('home')` gefixt
- `tests/Feature/Auth/RegistrationTest.php`: `route('dashboard')` → `route('home')` gefixt
- `resources/views/layouts/app.blade.php`: HTML-Kommentar mit `<x-app-layout>` durch Blade-Kommentar `{{-- --}}` ersetzt (Blade versuchte die Komponente zu kompilieren → ParseError)

## Geprüfte Konsistenz (alles grün)
- Alle route()-Aufrufe in Views zeigen auf existierende Routen ✓
- Fehlende Phase-2-Routen (shop.index, cart.index, auction.index) sind mit # gesichert ✓
- Alle @extends/@include referenzieren existierende Dateien ✓
- app.blade.php unterstützt $slot (Breeze) und @yield (Phase 2) ✓
- logo.svg und favicon.svg existieren in public/images/ ✓
- Kein route('dashboard') mehr in Auth-Controllern ✓
- Tailwind v4 @theme korrekt in app.css ✓
- @tailwindcss/vite korrekt in vite.config.js ✓

## Spezialisierung – Vollständige Arbeitsanweisung (Schritte 1–7, 9, 11 erledigt – offen: 8, 10, 12)

### A) Vorarbeiten – ERLEDIGT (22.04.2026)

**1. Kundennummer ✓**
- Migration `kundennummer` (nullable, unique) in users-Tabelle
- User-Model: `booted()` + `created()` → `kundennummer = 20000 + id`
- Bestehende User per Tinker nachgezogen

**2. Dummy-Kunden ✓**
- `DummyCustomersSeeder`: 5 Kunden (Anna Müller, Ben Schmidt, Clara Weber, David Fischer, Eva Becker)
- Passwort: "password", Rolle: "kunde", Kundennummer: automatisch

**3. Admin-Produkt-Übersicht ✓**
- Route: `admin.products` (GET `/admin/produkte`) → `AdminController::products()`
- View: `admin/products/index.blade.php` – Tabelle mit Lagerbestand + verfügbar im Shop
- "Artikel importieren"-Attrappe (disabled Button mit Tooltip)

**4. Auktion planen (Admin) ✓**
- `AuctionController::store()` – vollständige Validierung + Lagerbestand-Check
- `AuctionController::destroy()` – nur geplante Auktionen löschbar
- Auktions-Planungsformular in `products/edit.blade.php` (eigener Abschnitt)
- Tabelle der geplanten/laufenden Auktionen direkt im Edit-Formular
- Status-Badge erweitert um: geplant, aktiv, beendet

---

### B) Spezialisierung – Tagesauktion

**Datenbankstruktur**

`auctions`-Tabelle:
- `id`, `product_id` (FK), `start_price` (decimal 8,2), `start_time` (datetime), `end_time` (datetime), `winner_id` (FK users, nullable), `winning_bid` (decimal 8,2, nullable), `status` (enum: geplant, aktiv, beendet), `timestamps`

`bids`-Tabelle:
- `id`, `auction_id` (FK), `user_id` (FK), `amount` (decimal 8,2), `timestamps`

**Auktion anlegen (Admin)**
- Im Produkt-Edit-Formular (`products/edit.blade.php`): neuer Abschnitt "Auktion planen"
- Felder: Startpreis, Startdatum + Uhrzeit, Enddatum + Uhrzeit
- Validation (vollständig – KEIN Feld darf fehlen oder falsch sein):
  - `start_price`: required, numeric, min:0.01
  - `start_time`: required, date, after:now
  - `end_time`: required, date, after:start_time
  - Lagerbestand-Check: Anzahl geplanter/aktiver Auktionen für dieses Produkt darf nicht >= stock sein → Fehlermeldung: "Nicht genug Lagerbestand für eine weitere Auktion (max. X Auktionen möglich)"
  - Ein Produkt kann nur EINE gleichzeitig laufende Auktion haben; mehrere sequenzielle sind erlaubt

**Lagerbestand-Logik**
- `verfügbar_im_shop(product)` = `stock` - `anzahl_aktiver_oder_geplanter_auktionen_dieses_produkts`
- Im Shop (CartController `add()`): `quantity` darf `verfügbar_im_shop` nicht überschreiten → Fehlermeldung: "Nur X Stück verfügbar (1 Stück ist für eine laufende Auktion reserviert)"
- In `shop/index.blade.php` + `shop/show.blade.php`: wenn `verfügbar_im_shop = 0` → Button gesperrt, Badge "In Auktion"

**Bieten (eingeloggte Nutzer)**
- Validation Gebot (vollständig):
  - `amount`: required, numeric – Fehlermeldung bei Buchstaben/leer: "Bitte gib einen gültigen Betrag ein"
  - `amount` muss >= `höchstes_gebot + 1.00` sein – Fehlermeldung: "Dein Gebot muss mindestens €X,XX betragen (aktuelles Höchstgebot + 1,00 €)"
  - Auktion muss `status = aktiv` sein und `end_time > now()` – Fehlermeldung: "Diese Auktion ist bereits beendet"
  - Eingeloggt-Check via `middleware('auth')`
  - Nutzer darf nicht auf eigenes Höchstgebot bieten (wenn er bereits Höchstbietender ist) – Fehlermeldung: "Du bist bereits der Höchstbietende"

**Auktionsende**
- Beim Aufruf der Auktionsseite: wenn `end_time < now()` und `status != beendet` → Auktion automatisch schließen (winner_id = höchster Bieter, winning_bid setzen, status = "beendet", Bestellung für Gewinner anlegen)
- Artisan-Command `php artisan auctions:close` – schließt alle abgelaufenen Auktionen (gleiche Logik)
- Im Laravel Scheduler registrieren (zeigt Konzept, läuft lokal manuell)
- Bestellung für Gewinner: wie normaler Checkout, aber `zahlungsmethode = 'auktion'`, `status = 'bezahlt'`, Lieferadresse leer (Gewinner muss noch eintragen – oder Dummy-Adresse)

**Frontend – Auktionsübersicht (`auction.index`)**
- Grid-Ansatz wie Shop: alle aktiven Auktionen als Karten (Produktbild, Name, aktuelles Höchstgebot, Countdown, Button "Jetzt bieten")
- Darunter: "Demnächst" – geplante aber noch nicht gestartete Auktionen (ohne Biet-Button)
- Auktions-Banner auf Startseite: design.md enthält fertige Vorlage – zeigt die nächste/aktive Auktion
- Auktion-Link in Navigation: `route('auction.index')` – Platzhalter `#` ist bereits in `navigation.blade.php`

**Auktions-Detailseite (`auction.show`)**
- Produktbild, Name, Beschreibung
- Aktuelles Höchstgebot + Name des Höchstbietenden
- Countdown (Vanilla JS, kein WebSocket)
- Biet-Formular mit Betrag-Feld + vollständiger Validierung + Fehlermeldungen
- Gebotsverlauf: Tabelle mit allen Geboten (Nutzer anonymisiert z.B. "Ma***", Betrag, Zeitpunkt)

**PHPUnit Tests**
- Auktion anlegen (Admin): Validation-Fehler, Lagerbestand-Check, Erfolg
- Bieten: zu niedriges Gebot, Buchstaben, nicht eingeloggt, auf beendete Auktion, bereits Höchstbietender
- Auktionsende: winner wird gesetzt, Bestellung wird angelegt
- Lagerbestand-Sperre im Shop bei aktiver Auktion

---

### Umsetzungsreihenfolge

1. ✓ Migration `kundennummer` zu users + User-Model-Event + Seeder Dummy-Kunden
2. ✓ Migration `auctions` + `bids` + Models (Auction, Bid) + Beziehungen
3. ✓ Admin: Produkt-Übersichtsseite (`admin.products`) + Import-Attrappe
4. ✓ Admin: Auktion-Planungsformular im Produkt-Edit (Validation + Lagerbestand-Check)
5. ✓ Lagerbestand-Logik in CartController + Shop-Views (Badge "In Auktion", gesperrter Button)
6. ✓ AuctionController: index(), show(), bid(), schliesseAuktion() (privat), statusAktualisieren() (privat)
7. ✓ Auktions-Views: auction/index.blade.php (Grid + Demnächst + Mini-Countdown), auction/show.blade.php (Countdown, Bietformular, Gebotsverlauf anonymisiert)
8. ✓ Auktions-Banner auf Startseite (aktive oder nächste geplante Auktion, Countdown, Button)
9. ✓ Navigation: `auction.index` Route aktiviert
10. ✓ Artisan-Command `auctions:close` + Scheduler-Registrierung in `routes/console.php` (everyMinute)
11. ✓ Gewinner-Bestellung automatisch anlegen bei Auktionsende (in schliesseAuktion() umgesetzt, zahlungsmethode='auktion', status='bezahlt')
12. ✓ PHPUnit Tests – 14 Tests: Auktion anlegen, Validation, Lagerbestand-Check, Bieten, Auktionsende, Shop-Sperre

### Spezialisierung – VOLLSTÄNDIG ABGESCHLOSSEN (24.04.2026)

## Wichtig für den Start von Phase 2 (ABGESCHLOSSEN)

Das Layout ist fertig aufgebaut und entspricht den Mockups.
In navigation.blade.php sind drei Stellen mit "TODO Phase 2" markiert:
- Logo + Shop-Link → `route('shop.index')` ersetzen (nach ProductController)
- Auktion-Link     → `route('auction.index')` ersetzen (nach AuctionController)
- Warenkorb-Link   → `route('cart.index')` ersetzen (nach CartController)
In web.php sind diese Routen als auskommentierte TODOs vorbereitet.

## Phase 2 – Reihenfolge
1. `php artisan storage:link` ausführen (einmalig, für Bildupload)
2. Migration: products-Tabelle anlegen + `php artisan mig