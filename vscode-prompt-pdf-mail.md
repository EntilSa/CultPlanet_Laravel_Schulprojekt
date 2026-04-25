# VS Code Prompt – PDF-Rechnungen, E-Mail-Simulation, QoL-Features

Kopiere diesen Text als Prompt in Claude Code (VS Code):

---

Lies zuerst `handover.md` vollständig, dann `entscheidungen.md` und dann `design.md`.

Danach setze die neuen Features aus `handover.md` (Abschnitt "Neue Features – Nächste Session (25.04.2026)") in genau der dort beschriebenen Reihenfolge (Schritte 1–16) um.

Wichtige Regeln:
- Code so einfach wie möglich halten (Umschüler-Niveau)
- Kommentare auf Deutsch
- Kein Over-Engineering, keine neuen Abstraktionen
- Design-Vorgaben aus `design.md` einhalten (Farben, Klassen, Struktur) – außer im PDF-Template (dort nur inline CSS, kein Tailwind!)
- Nach jedem Schritt kurz beschreiben was gemacht wurde
- Am Ende: `php artisan test` ausführen – alle Tests müssen grün sein
- Falls Tests wegen Mail-Versand fehlschlagen: `Mail::fake()` in die betroffenen Tests ergänzen
- Am Ende: `./vendor/bin/pint` ausführen (Code-Formatter)
- `handover.md` am Ende aktualisieren: neue Features als erledigt markieren
- `tracking.md` am Ende aktualisieren

Starte mit Schritt 1: `composer require barryvdh/laravel-dompdf` und `composer require laravel/pint --dev`.
