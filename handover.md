# CultPlanet – Handover

## Aktueller Stand
**Phase 0 und Phase 1 sind vollständig abgeschlossen.**

### Phase 0 – Erledigt:
- Laravel 13 installiert, MySQL konfiguriert, Git-Repository initialisiert
- Vite + Tailwind CSS v4 eingerichtet
- Blade-Layout erstellt (Navigation, Footer, Lila als Hauptfarbe)
- Statische Seiten: Startseite, Impressum, Datenschutz
- Fehlerseiten: 404 und 500
- ER-Diagramm erstellt (er-diagramm.drawio)

### Phase 1 – Erledigt:
- Laravel Breeze installiert (Login, Registrierung, Passwort-Reset, Profil)
- Tailwind v3/v4 Konflikt durch Breeze behoben (postcss.config.js + tailwind.config.js entfernt)
- Spatie Laravel-Permission installiert, 3 Rollen angelegt (admin, mitarbeiter, kunde)
- Neue Nutzer bekommen automatisch die Rolle "kunde" bei der Registrierung
- 28 PHPUnit Tests – alle grün (inkl. eigene Rollen-Tests)

## Letzte bearbeitete Datei
`tests/Feature/RoleTest.php`

## Was als nächstes ansteht
**Vor Phase 2:** Layout-Besprechung (Farben, Design) – wurde bewusst verschoben.
**Phase 2:** Produkte CRUD, Bildupload, Produktliste, Bewertungen, Warenkorb, Bestellprozess, Zahlung (Attrappe), PHPUnit Tests.

## Offene Punkte
- Layout-Besprechung steht noch aus (vor Phase 2 einplanen)
- MySQL Passwort ist in .env gesetzt (Henry+007)
