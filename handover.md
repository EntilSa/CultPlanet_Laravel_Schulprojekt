# CultPlanet – Handover

## Aktueller Stand
**Phase 0, 1, 2, 3, Spezialisierung und Individualprojekt vollständig abgeschlossen. 132 Tests alle grün.**
**QoL-Verbesserungen vollständig umgesetzt (25.04.2026) – alle 7 Schritte erledigt.**

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
- MySQL Passwort: siehe .env
- storage:link vor erstem Bildupload ausführen: `php artisan storage:link`
- Vorlage für alle Views: design.md + mockup-startseite.html + mockup-produktseite.html
