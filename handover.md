# CultPlanet – Handover

## Aktueller Stand
**Phase 0, 1, 2 und 3 abgeschlossen. 60 Tests alle grün. Spezialisierung (Tagesauktion) kann gestartet werden.**

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
`tests/Feature/AdminTest.php` (22.04.2026)

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

## Wichtig für den Start der Spezialisierung – Tagesauktion

- Täglich ein Artikel zur Versteigerung (auction-Tabelle + bid-Tabelle anlegen)
- Gebote: eingeloggte Nutzer, Mindestgebot = aktuelles Höchstgebot + 1
- Countdown per Vanilla JS (kein WebSocket) – Auktionsende via Laravel Scheduler
- Auktions-Banner auf Startseite (design.md enthält fertige Vorlage)
- Route: `auction.index` – Platzhalter schon in navigation.blade.php

## Wichtig für den Start von Phase 2 (ABGESCHLOSSEN)

Das Layout ist fertig aufgebaut und entspricht den Mockups.
In navigation.blade.php sind drei Stellen mit "TODO Phase 2" markiert:
- Logo + Shop-Link → `route('shop.index')` ersetzen (nach ProductController)
- Auktion-Link     → `route('auction.index')` ersetzen (nach AuctionController)
- Warenkorb-Link   → `route('cart.index')` ersetzen (nach CartController)
In web.php sind diese Routen als auskommentierte TODOs vorbereitet.

## Phase 2 – Reihenfolge
1. `php artisan storage:link` ausführen (einmalig, für Bildupload)
2. Migration: products-Tabelle anlegen + `php artisan migrate`
3. Product-Model + ProductController (CRUD)
4. Routen in web.php aktivieren (shop.index, shop.show)
5. Navigation TODO-Kommentare ersetzen (# → echte Routen)
6. Produktliste (shop/index.blade.php) + Produktseite (shop/show.blade.php)
7. Bildupload für Produkte
8. Session-Warenkorb (CartController + cart.index, cart.add)
9. Checkout + Bestellung speichern (OrderController)
10. Attrappen-Zahlung (PayPal/Sofortüberweisung – nur Buttons)
11. Reviews (Sternebewertung 1–5 + Text)
12. PHPUnit Tests für alle neuen Features

## Wichtige Hinweise
- Tailwind v4: kein tailwind.config.js – Inter-Font über @theme in app.css
- MySQL Passwort: Henry+007 (in .env)
- storage:link vor erstem Bildupload ausführen: `php artisan storage:link`
- Vorlage für alle Views: design.md + mockup-startseite.html + mockup-produktseite.html
