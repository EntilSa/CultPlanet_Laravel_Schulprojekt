# CultPlanet – Lernnotizen

Hier werden neue Konzepte einfach erklärt, so wie ein Umschüler sie verstehen würde.
Jeder Eintrag enthält ein konkretes Beispiel aus dem Projekt.

---

## Konzepte

### Laravel – Das PHP-Framework
Laravel ist ein Werkzeugkasten für PHP-Entwickler. Statt alles selbst zu programmieren (Routen, Datenbankverbindung, Authentifizierung usw.) gibt Laravel fertige Bausteine mit. Man schreibt also weniger Code und kann sich auf das Wesentliche konzentrieren. Vergleich: Laravel ist wie ein Bausatz mit Anleitung – PHP allein wäre wie ein leeres Zimmer mit Rohstoffen. Im Projekt: Das gesamte CultPlanet-Backend läuft auf Laravel 13.

### Blade – Die Template-Sprache von Laravel
Blade ist die Art wie Laravel HTML-Seiten aufbaut. Man schreibt normales HTML, kann aber auch PHP-Logik einbauen mit speziellen Befehlen wie `@if`, `@foreach`, oder `@yield`. Blade-Dateien enden immer auf `.blade.php`. Im Projekt: Alle Seiten wie `startseite.blade.php` oder `impressum.blade.php` sind Blade-Templates.

### @extends und @yield – Layout vererben
Mit `@extends('layouts.app')` sagt eine Seite: "Ich benutze das Haupt-Layout." Mit `@yield('content')` markiert das Layout einen Platzhalter – dort wird dann der Inhalt der jeweiligen Seite eingefügt. Mit `@section('content')` füllt die Seite diesen Platzhalter. Vergleich: Das Layout ist wie ein Bilderrahmen, jede Seite ist ein anderes Bild das reingesteckt wird. Im Projekt: Alle drei statischen Seiten erben `layouts/app.blade.php`.

### Migrationen – Datenbanktabellen per Code anlegen
Migrationen sind PHP-Dateien die beschreiben wie die Datenbank aussehen soll. Statt Tabellen manuell in phpMyAdmin zu klicken, schreibt man Code der das automatisch erledigt. Mit `php artisan migrate` werden alle Migrationen ausgeführt. Vorteil: Wenn jemand anderes das Projekt öffnet, kann er mit einem Befehl die gesamte Datenbankstruktur aufbauen. Im Projekt: Laravel hat beim Setup automatisch Tabellen für users, cache und jobs angelegt.

### Artisan – Das Laravel-Kommandozeilen-Werkzeug
Artisan ist ein Hilfsprogramm das man im Terminal benutzt. Man tippt Befehle wie `php artisan migrate` oder `php artisan serve` und Laravel erledigt komplexe Aufgaben automatisch. Vergleich: Artisan ist wie eine Fernbedienung für Laravel. Im Projekt: Wir haben `php artisan migrate` genutzt um die Datenbanktabellen anzulegen.

### Vite – Der Asset-Builder
Vite ist ein Programm das CSS und JavaScript für den Browser aufbereitet. Es bündelt viele einzelne Dateien zu einer, macht sie kleiner und schneller ladbar. Mit `npm run dev` startet man Vite für die Entwicklung. Im Projekt: Vite verbindet Tailwind CSS mit dem Laravel-Projekt über `@vite(['resources/css/app.css', 'resources/js/app.js'])` im Layout.

### Tailwind CSS – CSS ohne eigene CSS-Datei schreiben
Tailwind ist ein CSS-Framework bei dem man fertige Klassen direkt im HTML verwendet, statt selbst CSS zu schreiben. Zum Beispiel bedeutet `text-purple-700` lila Textfarbe, `flex` macht ein Flex-Layout, `py-4` fügt oben und unten Abstand hinzu. Im Projekt: Die gesamte Navigation, der Footer und die Seiteninhalte sind mit Tailwind-Klassen gestylt.

### Spatie Laravel-Permission – Rollen für Nutzer
Spatie ist ein externes Paket das man zu Laravel dazuinstalliert. Es ermöglicht es Nutzern Rollen zuzuweisen wie "admin", "mitarbeiter" oder "kunde". Rollen werden in einer eigenen Datenbanktabelle gespeichert und über einen Seeder angelegt. Im Code kann man dann prüfen ob ein Nutzer eine bestimmte Rolle hat mit `$user->hasRole('admin')`. Im Projekt: Jeder neue Nutzer bekommt bei der Registrierung automatisch die Rolle "kunde" zugewiesen.

### Seeder – Testdaten und Grunddaten in die Datenbank eintragen
Ein Seeder ist eine PHP-Datei die beim Ausführen Daten in die Datenbank einträgt. Man startet ihn mit `php artisan db:seed --class=RolesSeeder`. Das ist praktisch um zum Beispiel die 3 Rollen automatisch anzulegen, ohne sie manuell in phpMyAdmin einzutragen. Im Projekt: Der `RolesSeeder` legt die Rollen admin, mitarbeiter und kunde an.

### Laravel Breeze – Fertige Anmeldung in Minuten
Breeze ist ein offizielles Laravel-Paket das automatisch alles für Login und Registrierung anlegt. Mit einem einzigen Befehl (`php artisan breeze:install blade`) bekommt man fertige Seiten für Registrierung, Login, Passwort vergessen und Profil. Man spart sich Stunden Arbeit und kann sich auf die eigentliche Shop-Logik konzentrieren. Im Projekt: Breeze liefert die Dateien in `resources/views/auth/` und die Controller in `app/Http/Controllers/Auth/`.

### Routen – Welche URL zeigt welche Seite
Eine Route verbindet eine URL mit einer Aktion. In Laravel steht das in `routes/web.php`. Wenn jemand `/impressum` aufruft, schaut Laravel in dieser Datei nach was es zurückgeben soll. Im Projekt: `Route::get('/impressum', ...)` zeigt die Impressum-Seite wenn jemand die URL aufruft.

### .env – Die Konfigurationsdatei
Die `.env`-Datei enthält Einstellungen die sich je nach Umgebung ändern – zum Beispiel Datenbankpasswort oder App-Name. Diese Datei wird nicht in Git eingecheckt, damit Passwörter nicht öffentlich werden. Laravel liest diese Datei beim Start automatisch. Im Projekt: Hier haben wir `DB_DATABASE=cultplanet` und das MySQL-Passwort eingetragen.

---

## Vokabular (Wörterbuch)

Alle Begriffe die im Projekt vorkommen, kurz und einfach erklärt.

| Begriff | Erklärung |
|---------|-----------|
| Framework | Fertiger Baukasten mit Regeln und Werkzeugen – man baut darauf auf statt von Null anzufangen |
| Laravel | PHP-Framework für Webseiten und Webanwendungen |
| Blade | Template-Sprache von Laravel für HTML-Seiten |
| Migration | PHP-Datei die beschreibt wie eine Datenbanktabelle aussehen soll |
| Artisan | Laravel-Kommandozeilen-Werkzeug (`php artisan ...`) |
| Vite | Programm das CSS und JS für den Browser aufbereitet und bündelt |
| Tailwind CSS | CSS-Framework mit fertigen Klassen direkt im HTML |
| Route | Verbindung zwischen einer URL und einer Aktion/Seite |
| .env | Konfigurationsdatei mit Passwörtern und Einstellungen (nicht in Git) |
| View | Eine Blade-Datei die HTML für den Browser erzeugt |
| FK | Foreign Key – ein Verweis auf eine andere Tabelle in der Datenbank |
| PK | Primary Key – die eindeutige ID einer Tabellenzeile |
| Composer | Paketverwaltung für PHP – installiert Laravel und andere Pakete |
| npm | Paketverwaltung für JavaScript – installiert Vite, Tailwind usw. |
| Breeze | Offizielles Laravel-Paket das fertige Login/Registrierungs-Seiten liefert |
| Auth | Kurzform für Authentifizierung – wer darf sich einloggen und mit welchen Rechten |
| Scaffolding | Automatisch generierter Grundcode als Startpunkt (z.B. durch Breeze) |
| PostCSS | Werkzeug das CSS transformiert – wird von Tailwind v3 genutzt, nicht von v4 |
| Spatie | Externes Laravel-Paket für Rollenverwaltung |
| Rolle | Eine Bezeichnung für die Rechte eines Nutzers (z.B. admin, kunde) |
| Seeder | PHP-Datei die Grunddaten in die Datenbank einträgt |
| assignRole | Spatie-Funktion um einem Nutzer eine Rolle zuzuweisen |
