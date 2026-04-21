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

### Spezialisierung – Tagesauktion
- Täglich ein Artikel zur Versteigerung
- Eingeloggte Nutzer können Gebote abgeben
- Countdown per Vanilla JS (kein WebSocket, keine Echtzeit)
- Höchstes Gebot gewinnt – Auktionsende via Laravel Scheduler

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

## Entscheidungslog (PFLICHT)
Bei jeder technischen Entscheidung im Code kurz in entscheidungen.md festhalten:
- Was wurde entschieden?
- Warum diese Lösung und nicht eine andere?
- Format: Datum + Thema + Begründung (2-3 Sätze reichen)

## Lernnotizen (PFLICHT)
Nach jedem neuen Konzept einen Eintrag in lernnotizen.md schreiben:
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
- Phase 0 – noch nicht gestartet

## Datenbankstruktur (geplant)
Tabellen die wir brauchen werden:
- users (Breeze Standard + role)
- products (id, name, description, price, image, stock, timestamps)
- orders (id, user_id, status, total, timestamps)
- order_items (id, order_id, product_id, quantity, price)
- reviews (id, user_id, product_id, rating, text, timestamps)
- auctions (id, product_id, start_price, end_time, winner_id, timestamps)
- bids (id, auction_id, user_id, amount, timestamps)
- departments (id, name, timestamps)
- department_user (department_id, user_id) -- Pivot-Tabelle Many-to-Many
