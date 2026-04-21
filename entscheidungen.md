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
