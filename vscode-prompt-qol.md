# VS Code Prompt – QoL-Verbesserungen CultPlanet

Kopiere diesen Text als Prompt in Claude Code (VS Code):

---

Lies zuerst `handover.md` vollständig, dann `entscheidungen.md` und dann `design.md`.

Danach setze die QoL-Verbesserungen aus `handover.md` (Abschnitt "QoL-Verbesserungen – Nächste Session") in genau der dort beschriebenen Reihenfolge um.

Wichtige Regeln:
- Code so einfach wie möglich halten (Umschüler-Niveau)
- Kommentare auf Deutsch
- Kein Over-Engineering, keine neuen Abstraktionen
- Design-Vorgaben aus `design.md` einhalten (Farben, Klassen, Struktur)
- Nach jeder Änderung kurz beschreiben was gemacht wurde
- Am Ende: `php artisan test` ausführen und sicherstellen dass alle Tests noch grün sind
- `handover.md` am Ende aktualisieren: QoL-Verbesserungen als erledigt markieren
- `tracking.md` am Ende aktualisieren

Starte mit Schritt 1: Route `/` auf Redirect umstellen in `routes/web.php`.
