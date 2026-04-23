# CultPlanet – Spielzeug-Onlineshop

## Projektkontext
Schulprojekt im Rahmen der FIAE-Umschulung, Lernfeld 12a.
Alleinentwicklung. Entwickler ist Umschüler im Lernprozess.

## Kommunikation
- Claude antwortet immer auf Deutsch
- CultPlanet ist der Name des Shops

## Wichtige Regeln
- Code immer so einfach wie möglich halten – kein Over-Engineering
- Kommentare im Code so schreiben, wie ein Umschüler sie schreiben würde (erklärend, simpel, umgangssprachlich auf Deutsch – z.B. "// hier prüfen ob der nutzer eingeloggt ist, sonst weiterleiten")
- Code so simpel wie möglich halten – keine cleveren Einzeiler, keine abstrakten Muster, direkte Lösungen
- Kein Filament, keine WebSockets, kein Docker, kein CI/CD
- Spatie Laravel-Permission ist geplant – bei Problemen auf einfache Rollen-Spalte (enum) umsteigen
- Niemals Fortgeschrittenen-Code ohne explizite Aufforderung
- Deutsch als Kommentarsprache im Code

## Design-Vorgaben (PFLICHT – vor jedem Frontend-Task lesen)
WICHTIG: Vor dem Erstellen oder Bearbeiten von Blade-Templates IMMER zuerst `design.md` lesen.
- Primärfarbe: #1a2e4a (Dunkelblau für Nav/Header/Footer)
- Buttons: #2563eb (Blau, Tailwind `bg-blue-600`)
- Akzent: #f97316 (Orange, Tailwind `bg-orange-500`) – besonders für Auktions-Banner
- Hintergrund: #f8fafc (`bg-slate-50`)
- Kein eigenes CSS – nur Tailwind-Utilities
- Auktions-Banner: IMMER prominent, volle Breite, orangener Rand oben, gut sichtbar
- Logo: `public/images/logo.png` (blauer Planet, weißer Text "Cult Planet")
- Alle Details in `design.md`

## Tech-Stack
- Laravel 13
- Blade (Frontend-Templates)
- Tailwind CSS + Vite
- MySQL (lokale Entwicklung via WAMP)
- Laravel Breeze (Auth)
- Spatie Laravel-Permission (Rollen)
- PHPUnit (Tests – Pflicht)
- Vanilla JS (nur wo nötig, z.B. Auktion-Countdown)

## Datenbankverbindung (lokal)
- Host: localhost
- Port: 3306 (WAMP Standard)
- Datenbank: cultplanet (muss in MySQL angelegt werden)
- User/Passwort: in .env eintragen (Standard WAMP: root / kein Passwort)

## Phasen

### Phase 0 – Foundation & Setup (AKTUELL)
- Laravel 13 Projekt aufsetzen, Git-Repo initialisieren, .env konfigurieren
- Vite + Tailwind CSS + Blade-Layouts einrichten (Layout, Navigation, Footer)
- Statische Seiten: Startseite, Impressum, Datenschutz
- Fehlerseiten: 404 und 500
- ER-Diagramm erstellen (Datenbankstruktur planen)

### Phase 1 – Authentifizierung & Rollen
- Laravel Breeze installieren (Registrierung, Login, Passwort-Reset)
- 3 Rollen über ein gemeinsames Auth-Backend: Admin, Mitarbeiter, Kunde
- Einfaches Benutzerprofil (Name, E-Mail)
- PHPUnit Tests für Auth

### Phase 2 – Shop-Funktionen
- Produkte CRUD: Name, Beschreibung, Preis, Bild, Lagerbestand
- Bildupload für Produkte
- Produktliste & Detailseite für Kunden
- Kurzrezension: Kunden können Produkte bewerten (1–5 Sterne + Text)
- Session-basierter Warenkorb
- Bestellprozess: Warenkorb → Checkout → Bestellung speichern
- Zahlung: nur Attrappen-Buttons (PayPal, Sofortüberweisung) – keine echte Transaktion
- PHPUnit Tests für Shop-Logik

### Phase 3 – Adminbereich
- Eigene Blade-Admin-Seiten (kein Filament, kein externes Admin-Paket)
- Produkte, Bestellungen und Nutzer verwalten
- Rollen zuweisen
- Verkaufsübersicht: Mitarbeiter → verkaufte Produkte pro Tag
- PHPUnit Tests für Admin-Logik

### Spezialisierung – Tagesauktion (VOLLSTÄNDIG ABGESTIMMT – handover.md lesen!)
- Mehrere Auktionen parallel oder sequenziell möglich (flexible start_time/end_time)
- Ein Produkt max. eine gleichzeitig laufende Auktion; mehrere sequenzielle erlaubt
- Lagerbestand-Logik: verfügbar_im_shop = stock - anzahl_aktiver_oder_geplanter_auktionen
- Mindestgebot: aktuelles Höchstgebot + min. 1,00 €
- Vollständige Validierung aller Eingaben (Gebot + Auktion anlegen)
- Auktionsende: end_time-Prüfung beim Seitenaufruf + Artisan-Command auctions:close
- Gewinner bekommt automatisch eine Bestellung in der DB
- Kundennummer (kundennummer) für alle User: 20000 + id, per booted()-Event
- 5 Dummy-Kunden per Seeder
- Admin: Produkt-Übersichtsseite + "Artikel importieren"-Attrappe
- Countdown per Vanilla JS, kein WebSocket
- Alle Details + Umsetzungsreihenfolge in handover.md

### Individualprojekt – Mitarbeiterverwaltung
- Bereiche anlegen: z.B. Lager, Verkauf, Kasse
- Mitarbeiter ↔ Bereiche (Many-to-Many via Eloquent)
- Warnsystem im Admin: Bereich ohne Mitarbeiter → sichtbarer Hinweis
- Übersicht welche Bereiche besetzt / unbesetzt sind

## Tests
PHPUnit ist Pflicht. Tests werden laufend in jeder Phase geschrieben, nicht erst am Ende.
- Unit-Tests für Modelle und Logik
- Feature-Tests für Routen und Controller

## Dokumentation
Wird laufend während des Projekts geschrieben und angepasst.
- Ca. 3 Seiten (±1)
- ER-Diagramm (kein UML)
- Ablaufdiagramm Tagesauktion
- Entscheidungsfindung, Fazit, Umsetzungsauszug

## Entscheidungslog (PFLICHT – SOFORT, NICHT AM ENDE)
WICHTIG: Jede technische Entscheidung wird DIREKT nach dem Treffen eingetragen – nicht erst am Ende der Session.
Wenn Code geschrieben wird der eine Wahl trifft (z.B. Session statt DB für Warenkorb, Spatie statt Enum, etc.) → SOFORT in entscheidungen.md eintragen, bevor weitergemacht wird.
- Was wurde entschieden?
- Warum diese Lösung und nicht eine andere?
- Format: Datum + Thema + Begründung (2-3 Sätze reichen)

## Lernnotizen (PFLICHT – SOFORT, NICHT AM ENDE)
WICHTIG: Nach jedem neuen Konzept wird DIREKT ein Eintrag in lernnotizen.md geschrieben – nicht erst am Ende der Session.
Wenn ein neues Laravel/PHP/Konzept zum ersten Mal verwendet wird → SOFORT eintragen, bevor weitergemacht wird.
- Einfache, anfängerfreundliche Erklärung (max. 5-6 Sätze)
- Konkretes Beispiel aus dem Projekt
- Neuen Begriff ins Vokabular am Ende der Datei eintragen
- Kein Fachjargon ohne Erklärung

## Git Commits
- Git wird lokal verwendet (kein GitHub nötig)
- Jeder Commit bekommt eine kurze, aussagekräftige Nachricht auf Deutsch
- Format: "Phase X: Was wurde gemacht"
- Beispiel: "Phase 0: Blade Layout mit Navigation und Footer erstellt"

## Session-Abschluss (PFLICHT)
Am Ende jeder Arbeits-Session folgende Schritte ausführen:

1. Zeilen gezählt mit:
   - Neu geschriebene/veränderte Zeilen: `git diff --numstat | awk '{sum += $1} END {print sum}'`
   - Anzahl veränderter Dateien: `git diff --name-only | wc -l`
2. tracking.md aktualisieren:
   - Neue Zeile in der Sessions-Tabelle eintragen (Session-Nr, Datum, Uhrzeit, Zeilen, Dateien, Phase, kurze Notiz)
   - Gesamt-Tabelle aktualisieren (Summen aufrechnen)
3. handover.md schreiben mit:
   - Aktuelle Phase und was fertig ist
   - Letzte bearbeitete Datei
   - Was als nächstes ansteht
   - Offene Punkte oder Probleme

## Aktueller Stand
- Phase 0 – abgeschlossen
- Phase 1 – abgeschlossen
- Phase 2 – abgeschlossen (inkl. Artikelnummer)
- Phase 3 – abgeschlossen
- Spezialisierung – abgeschlossen (alle 12 Schritte erledigt)
- Individualprojekt – abgeschlossen

### Was bisher fertig ist
- Laravel 13, MySQL, Git, Vite + Tailwind v4
- Blade-Layout (app.blade.php, navigation.blade.php, footer.blade.php, guest.blade.php) – CultPlanet-Design aktiv
- app.blade.php unterstützt beide Blade-Stile: $slot (Breeze) + @yield (Phase-2-Views)
- Statische Seiten: Startseite, Impressum, Datenschutz, 404, 500
- web.php: alle Routen aktiv (shop, cart, checkout, orders, reviews, products CRUD, auctions, departments)
- Alle Auth-Controller: Redirect nach Login/Register/Verify → route('home') statt dashboard
- Laravel Breeze (Login, Registrierung, Passwort-Reset, Profil)
- Spatie Laravel-Permission: 3 Rollen (admin, mitarbeiter, kunde)
- Neue Nutzer bekommen automatisch Rolle "kunde"
- Produkte CRUD (Admin), Bildupload, Session-Warenkorb, Checkout, Attrappen-Zahlung, Reviews
- Artikelnummer (artikel_nr): echte DB-Spalte, automatisch gesetzt per Eloquent-Model-Event
- Admin-Bereich: Dashboard, Bestellungsverwaltung, Nutzerverwaltung, Verkaufsübersicht
- Spezialisierung Tagesauktion: Auktionen, Gebote, Countdown, Lagerbestand-Logik, Artisan-Command, Auktions-Banner
- Individualprojekt Mitarbeiterverwaltung: Bereiche CRUD, Mitarbeiter zuweisen/entfernen, Warnsystem
- 85 PHPUnit Tests – alle grün
- Logo (logo.svg), Favicon (favicon.svg) in public/images/
- Design-Referenz (design.md) + Mockups erstellt

### Technische Besonderheiten
- Tailwind v4: kein tailwind.config.js – Inter-Font wird über @theme in app.css gesetzt
- MySQL Passwort in .env gesetzt (siehe .env)
- storage:link bereits ausgeführt (Bildupload aktiv)

## Datenbankstruktur (geplant)
Tabellen die wir brauchen werden:
- users (Breeze Standard + role)
- products (id, name, description, price, image, stock, timestamps)
- orders (id, user_id, status, total, timestamps)
- order_items (id, order_id, product_id, quantity, price)
- reviews (id, user_id, product_id, rating, text, timestamps)
- auctions (id, product_id, start_price, end_time, winner_id, timestamps)
- bids (id, auction_id, user_id, amount, timestamps)
- departments (id, 