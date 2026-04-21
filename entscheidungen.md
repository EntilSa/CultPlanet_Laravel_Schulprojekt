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
