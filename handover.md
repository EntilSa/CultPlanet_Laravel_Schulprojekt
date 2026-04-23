# CultPlanet – Handover

## Aktueller Stand
**Phase 0, 1, 2, 3, Spezialisierung und Individualprojekt vollständig abgeschlossen. 116 Tests alle grün.**

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
`tests/Feature/SicherheitsTest.php` + `GrenzwertTest.php` (24.04.2026) – 23 neue Tests nach E-Commerce-Branchenstandard: Zugriffschutz auf fremde Bestellungen, Mengen-Grenzwerte im Warenkorb, Bewertungs-Grenzwerte, ungültige Zahlungsmethoden, Doppel-Registrierung

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
