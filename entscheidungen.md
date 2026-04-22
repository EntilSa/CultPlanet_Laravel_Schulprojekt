# CultPlanet – Entscheidungslog

Technische Entscheidungen mit Begründung – direkt verwendbar für die Projektdokumentation.

---

## Entscheidungen

*(Werden während des Projekts laufend ergänzt)*

---

### Vorab-Entscheidungen (aus Planungsphase)

**21.04.2026 – Kein Filament als Admin-Panel**
Filament ist ein fertiges Admin-Paket für Laravel. Es wurde bewusst darauf verzichtet, weil es für den Schulrahmen zu komplex ist und den Lerneffekt verringert. Stattdessen werden eigene Blade-Admin-Seiten gebaut, was mehr Verständnis für das Framework schafft.

**21.04.2026 – Keine WebSockets für die Tagesauktion**
WebSockets ermöglichen Echtzeit-Kommunikation zwischen Server und Browser. Für ein Schulprojekt ist der Aufwand zu hoch. Die Auktion funktioniert stattdessen mit einem Vanilla-JS-Countdown und dem Laravel Scheduler – einfacher, wartbarer, ausreichend.

**21.04.2026 – Session-basierter Warenkorb statt Datenbank**
Der Warenkorb wird in der PHP-Session gespeichert, nicht in der Datenbank. Das ist einfacher zu implementieren und für einen Onlineshop dieser Größe völlig ausreichend. Nicht-eingeloggte Nutzer können trotzdem Artikel in den Warenkorb legen.

**21.04.2026 – Spatie Laravel-Permission (oder einfache Enum-Spalte)**
Für 3 Rollen (Admin, Mitarbeiter, Kunde) wird Spatie verwendet, weil es ein weit verbreitetes Paket ist und den Lerneffekt beim Umgang mit Fremdpaketen erhöht. Fallback: einfache role-Spalte in der users-Tabelle, falls Spatie zu komplex wird.

---

### Phase 0 – Umgesetzte Entscheidungen

**21.04.2026 – MySQL statt SQLite**
Laravel 13 richtet standardmäßig SQLite ein, weil das ohne Konfiguration funktioniert. Für dieses Projekt wurde MySQL gewählt, weil es realistischer für einen echten Onlineshop ist und besser zu WAMP passt. SQLite wäre für die Entwicklung technisch ausreichend gewesen, aber MySQL ist die üblichere Wahl in der Praxis.

**21.04.2026 – Tailwind CSS über das offizielle Vite-Plugin**
Tailwind CSS wird über das `@tailwindcss/vite` Plugin eingebunden, nicht über die ältere PostCSS-Methode. Laravel 13 liefert diese Konfiguration bereits mit, sodass kein manuelles Setup nötig war. Das Plugin ist moderner und schneller als der alte Weg.

**21.04.2026 – Blade-Layout mit @extends und @yield**
Alle Seiten teilen sich ein gemeinsames Layout (`layouts/app.blade.php`). Jede Seite erbt dieses Layout mit `@extends` und füllt den Inhaltsbereich mit `@yield`. Die Alternative wäre gewesen, Navigation und Footer auf jeder Seite einzeln zu wiederholen – das wäre fehleranfälliger und schwerer zu warten.

**21.04.2026 – Routen ohne Controller für statische Seiten**
Die drei statischen Seiten (Startseite, Impressum, Datenschutz) geben die View direkt in der Route zurück, ohne einen eigenen Controller. Das reicht für Seiten ohne Logik völlig aus und hält den Code schlank. Für Seiten mit Datenbankzugriffen (ab Phase 2) werden eigene Controller erstellt.

**21.04.2026 – Lila als Hauptfarbe**
Lila/Violett wurde als Hauptfarbe für CultPlanet gewählt, weil es auffällig und für einen Spielzeug-Shop ungewöhnlich ist – damit hebt sich der Shop von typischen Blau/Grün-Designs ab. Die Farbe kann in Phase 2 noch angepasst werden, wenn das vollständige Design besprochen wird.

---

### Phase 1 – Umgesetzte Entscheidungen

**21.04.2026 – Laravel Breeze als Auth-Lösung**
Breeze wurde gewählt weil es eine einfache, offizielle Auth-Lösung von Laravel ist. Es liefert fertige Login-, Registrierungs- und Passwort-Reset-Seiten mit Blade-Templates. Alternativ hätte man Auth komplett selbst bauen können, aber das wäre für ein Schulprojekt unnötig aufwändig.

**21.04.2026 – Tailwind v3/v4 Konflikt durch Breeze – postcss.config.js und tailwind.config.js entfernt**
Breeze hat beim Installieren `postcss.config.js` und `tailwind.config.js` angelegt, die für Tailwind v3 ausgelegt sind. Da das Projekt Tailwind v4 mit dem `@tailwindcss/vite` Plugin nutzt, entstanden Konflikte. Beide Dateien wurden entfernt und `vite.config.js` wurde wiederhergestellt, damit das v4 Plugin korrekt greift.

**21.04.2026 – Spatie Laravel-Permission für Rollenverwaltung**
Statt einer einfachen role-Spalte in der users-Tabelle wird Spatie Laravel-Permission genutzt. Das Paket ist weit verbreitet und ermöglicht flexible Rollen-Zuweisung über eine eigene Tabelle. Die 3 Rollen (admin, mitarbeiter, kunde) werden per Seeder angelegt und neue Nutzer bekommen bei der Registrierung automatisch die Rolle "kunde".

**21.04.2026 – Neue Nutzer bekommen automatisch Rolle "kunde"**
Im RegisteredUserController wird nach dem Erstellen des Users direkt `$user->assignRole('kunde')` aufgerufen. So hat jeder Nutzer sofort eine Rolle und es gibt keinen Nutzer ohne Rolle im System. Admin und Mitarbeiter-Rollen werden manuell über den späteren Adminbereich vergeben.

**21.04.2026 – firstOrCreate statt assignRole direkt für Test-Kompatibilität**
In Tests wird die Datenbank vor jedem Test zurückgesetzt – dadurch fehlen die Rollen. Statt `assignRole('kunde')` direkt aufzurufen, wird die Rolle per `Role::firstOrCreate()` geholt oder angelegt. So funktioniert die Registrierung auch ohne vorher den Seeder laufen zu lassen, was Tests stabiler macht.

---

### Bugfix-Session (22.04.2026)

**22.04.2026 – Test-Erwartungen von route('dashboard') auf route('home') umgestellt**
Breeze legt standardmäßig Tests an die nach dem Login auf `/dashboard` weiterleiten. Da CultPlanet kein Dashboard hat und stattdessen zur Startseite weiterleitet, mussten 3 Test-Dateien angepasst werden: `AuthenticationTest`, `EmailVerificationTest`, `RegistrationTest`. Die Tests prüfen jetzt korrekt `route('home')` statt `route('dashboard')`.

**22.04.2026 – Blade-Kommentar statt HTML-Kommentar für Code-Hinweise die x-Komponenten erwähnen**
In `app.blade.php` stand ein HTML-Kommentar `<!-- ... <x-app-layout> ... -->` der erklärte wie das Layout zwei Blade-Stile unterstützt. Blade versucht jeden `<x-...>`-Tag im Template zu verarbeiten – auch innerhalb von HTML-Kommentaren. Das erzeugte ungültigen PHP-Code und schlug mit "ParseError: unexpected end of file" fehl. Lösung: HTML-Kommentar durch Blade-Kommentar `{{-- ... --}}` ersetzen, der beim Kompilieren vollständig entfernt wird.

---

### Phase 2 (22.04.2026)

**22.04.2026 – Preise und Namen in order_items gespeichert statt nur Fremdschlüssel**
In `order_items` wird nicht nur `product_id` gespeichert, sondern auch `name` und `price` zum Zeitpunkt der Bestellung. Wenn ein Produkt später gelöscht oder der Preis geändert wird, bleibt die Bestellhistorie trotzdem korrekt und nachvollziehbar.

**22.04.2026 – Session-Warenkorb statt Datenbank-Warenkorb**
Der Warenkorb liegt in der PHP-Session als Array `[produkt_id => [name, price, qty, image]]`. Das ist einfacher zu implementieren als eine eigene DB-Tabelle und für dieses Schulprojekt völlig ausreichend. Nicht-eingeloggte Nutzer können trotzdem Artikel hinzufügen.

**22.04.2026 – max-w-7xl pro View statt im Layout-main-Tag**
Das Layout hat keinen fixen max-width auf dem `<main>`-Tag. Jede View setzt ihren eigenen Wrapper (`max-w-7xl`, `max-w-2xl` usw.). Begründung: verschiedene Seiten brauchen verschiedene Breiten – Checkout schmaler, Admin eventuell volle Breite.

**22.04.2026 – Unique-Constraint für Reviews auf Datenbankebene**
Die reviews-Tabelle hat einen `unique(['user_id', 'product_id'])`-Constraint, damit ein Nutzer ein Produkt wirklich nur einmal bewerten kann – auch wenn ein Buggy Request es zweimal versucht. Zusätzlich prüft der Controller die Duplikate vorher ab und gibt eine freundliche Fehlermeldung aus.

**22.04.2026 – Artikelnummer als echte DB-Spalte mit Model-Event statt Controller-Logik**
Die Artikelnummer (10001, 10002, ...) wird als eigene Spalte `artikel_nr` in der Datenbank gespeichert, nicht nur zur Anzeige berechnet. Vorteil: Die Nummer bleibt stabil, auch wenn das Produkt bearbeitet wird, und lässt sich filtern oder sortieren. Die automatische Vergabe passiert im `Product`-Model über ein Eloquent-Model-Event (`booted()` + `static::created()`): nach jedem `Product::create()` wird sofort `artikel_nr = id + 10000` gesetzt. So ist der Controller frei davon und die Logik sitzt an einer einzigen Stelle. Zwei Migrationen waren nötig: erst die Spalte als NOT NULL + unique anlegen, dann in einer zweiten Migration nullable machen – damit das booted()-Event nach dem create() mit update() schreiben kann ohne einen NOT-NULL-Fehler zu werfen.
