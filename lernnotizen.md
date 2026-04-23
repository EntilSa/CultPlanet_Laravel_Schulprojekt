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

### Storage und Bildupload – Dateien in Laravel speichern
Laravel speichert hochgeladene Dateien im Ordner `storage/app/public/`. Damit der Browser diese Dateien sehen kann, muss einmalig `php artisan storage:link` ausgeführt werden – das legt einen symbolischen Link von `public/storage` zu `storage/app/public` an. Im Code wird ein Bild so gespeichert: `$request->file('image')->store('products', 'public')`. Im Blade-Template: `asset('storage/' . $product->image)`. Im Projekt: Produktbilder werden in `storage/app/public/products/` gespeichert.

### Eloquent-Beziehungen – Tabellen miteinander verbinden
Eloquent ist das ORM (Object-Relational Mapper) von Laravel – es ermöglicht den Zugriff auf Datenbanktabellen wie auf normale PHP-Objekte. Beziehungen werden im Model definiert: `hasMany` (hat viele) und `belongsTo` (gehört zu). Im Projekt: Ein `Order` hat viele `OrderItem`s (`hasMany`), und ein `OrderItem` gehört zu einer `Order` (`belongsTo`). Damit kann man `$order->items` schreiben statt SQL.

### Session – Daten zwischen Seitenanfragen merken
HTTP ist zustandslos – jede Anfrage vergisst alles vom letzten Mal. Die Session löst das: sie speichert Daten auf dem Server und merkt sich anhand eines Cookies welcher Nutzer welche Daten hat. In Laravel: `session(['key' => $wert])` speichern, `session('key')` lesen, `session()->forget('key')` löschen. Im Projekt: Der Warenkorb liegt komplett in der Session – kein Datenbankeintrag nötig.

### @stack und @push – JavaScript pro Seite einbinden
Mit `@stack('scripts')` im Layout reserviert man einen Platz für seitenspezifisches JavaScript. Einzelne Views können dann mit `@push('scripts')` Code in diesen Platz einfügen. Vorteil: Das `<script>`-Tag landet trotzdem am Ende des Body im Layout, nicht mitten im HTML. Im Projekt: Der Mengenwahl-Button auf der Produktseite nutzt `@push('scripts')` für die `changeQty()`-Funktion.

### location.reload() – Seite per JavaScript neu laden
Mit `location.reload()` kann man die aktuelle Seite per JavaScript neu laden – genau so als ob der Nutzer F5 drückt. Das ist nützlich wenn sich Daten auf dem Server geändert haben und die Seite das anzeigen soll, ohne dass der Nutzer selbst etwas tut. Im Projekt: Wenn der Auktions-Countdown auf 0 läuft, wird die Seite nach 3 Sekunden automatisch neu geladen damit der neue Status "beendet" angezeigt wird.

### mb_substr() – Text kürzen mit Unterstützung für Umlaute
`mb_substr($text, 0, 2)` gibt die ersten 2 Zeichen eines Strings zurück – und funktioniert auch korrekt mit deutschen Umlauten (ä, ö, ü) und anderen Sonderzeichen. Das `mb_` steht für "multibyte" – ohne mb_ kann es bei Umlauten zu falschen Ergebnissen kommen. Im Projekt: Für den anonymisierten Gebotsverlauf werden die ersten 2 Buchstaben des Nutzernamens + "***" angezeigt.

### datetime-local – Datum und Uhrzeit in HTML-Formularen
Das HTML-Inputfeld `type="datetime-local"` zeigt einen Datums- und Uhrzeitpicker direkt im Browser. Der Wert wird als String im Format `2026-04-22T15:30` übertragen. Laravel versteht dieses Format direkt bei der Validierung mit `'date'` und `'after:now'`. Im Projekt: Das Auktions-Planungsformular nutzt `datetime-local` für Start- und Endzeitpunkt der Auktion.

### selectRaw und groupBy – SQL-Aggregation direkt in Eloquent
Manchmal braucht man keine einzelnen Datensätze, sondern Zusammenfassungen: Wie viele Bestellungen gab es pro Tag? Wie viel Umsatz pro Tag? Das macht man mit `selectRaw()` in Laravel – damit kann man SQL direkt schreiben, aber trotzdem das Eloquent-Model nutzen. `groupBy()` gruppiert die Ergebnisse nach einem Feld. Das ist viel effizienter als alle Datensätze zu laden und in PHP zu summieren. Im Projekt: `Order::selectRaw('DATE(created_at) as tag, COUNT(*) as anzahl, SUM(total) as umsatz')->groupBy('tag')` – so bekommt die Verkaufsübersicht direkt die fertigen Tages-Summen aus MySQL.

### match() – kurze Alternative zum langen if-else
`match()` ist eine PHP-Funktion die einen Wert mit mehreren Möglichkeiten vergleicht und das passende Ergebnis zurückgibt. Es ist wie ein if-else, aber kürzer und übersichtlicher. Im Projekt: Im Status-Badge-Partial wird `match($status)` genutzt um je nach Bestellstatus eine andere Farbe (Tailwind-Klassen) zurückzugeben – "offen" = gelb, "bezahlt" = blau, "versendet" = grün, "storniert" = rot.

### Blade Partials – wiederverwendbare HTML-Bausteine
Ein Partial ist ein kleines Blade-Template das an mehreren Stellen eingebunden werden kann. Man ruft es mit `@include('pfad.zum.partial', ['variable' => $wert])` auf und kann Daten mitgeben. So schreibt man denselben Code nur einmal. Im Projekt: Das Status-Badge ist ein Partial unter `admin/partials/status-badge.blade.php` und wird sowohl im Dashboard als auch in der Bestellliste eingebunden.

### syncRoles() – Rollen eines Nutzers komplett ersetzen
`syncRoles()` von Spatie setzt die Rollen eines Nutzers neu – erst werden alle alten Rollen entfernt, dann die neue Rolle gesetzt. Das ist einfacher als erst `removeRole()` und dann `assignRole()` aufzurufen. Im Projekt: Wenn ein Admin die Rolle eines Nutzers ändert, wird `$user->syncRoles(['mitarbeiter'])` aufgerufen – so hat der Nutzer danach genau eine Rolle.

### Eloquent Model Events – automatisch etwas tun wenn ein Datensatz erstellt wird
Laravel-Models können auf bestimmte Ereignisse reagieren: erstellt, aktualisiert, gelöscht usw. Das nennt sich Model Event. Man registriert diese Reaktionen in der `booted()`-Methode des Models. `static::created()` wird jedes Mal ausgelöst, nachdem ein neuer Datensatz in die Datenbank geschrieben wurde – dann hat der Datensatz schon eine `id`. Das ist ideal um Felder automatisch zu befüllen die von der `id` abhängen. Im Projekt: Das `Product`-Model nutzt `static::created()` um nach jedem `Product::create()` die `artikel_nr` auf `10000 + $product->id` zu setzen. So muss der Controller sich nicht darum kümmern.
```php
protected static function booted(): void
{
    static::created(function ($product) {
        $product->update(['artikel_nr' => 10000 + $product->id]);
    });
}
```

### Many-to-Many – Beziehung zwischen zwei Tabellen in beide Richtungen
Eine Many-to-Many-Beziehung bedeutet: ein Mitarbeiter kann in mehreren Bereichen sein, und ein Bereich kann mehrere Mitarbeiter haben. In Laravel braucht man dafür eine Zwischentabelle (Pivot-Tabelle), die nur zwei Spalten hat: die IDs beider Tabellen. Die Beziehung wird in beiden Models mit `belongsToMany()` definiert. Laravel kümmert sich dann automatisch um die Pivot-Tabelle – man muss sie nie direkt ansprechen. Im Projekt: `Department::belongsToMany(User)` und `User::belongsToMany(Department)` über die Tabelle `department_user`.

### attach() / detach() / syncWithoutDetaching() – Pivot-Einträge verwalten
Um einen Eintrag in einer Pivot-Tabelle anzulegen nutzt man `attach($id)`, um ihn zu entfernen `detach($id)`. `syncWithoutDetaching([$id])` ist wie attach, aber fügt den Eintrag nur hinzu wenn er noch nicht existiert – verhindert also Duplikate. Im Projekt: `$bereich->users()->syncWithoutDetaching([$user->id])` weist einen Mitarbeiter einem Bereich zu ohne Fehler wenn er schon drin ist.

### Artisan-Command (eigener) – eigene Konsolen-Befehle schreiben
Man kann in Laravel eigene `php artisan`-Befehle schreiben. Dazu legt man eine Klasse in `app/Console/Commands/` an mit zwei Pflichtfeldern: `$signature` (der Befehlsname, z.B. `auctions:close`) und `$description` (kurze Erklärung). Die eigentliche Logik kommt in die `handle()`-Methode. Laravel erkennt den Befehl automatisch und er ist sofort über `php artisan auctions:close` aufrufbar. Im Projekt: Der Command schließt alle abgelaufenen Auktionen und setzt den Gewinner.

### Laravel Scheduler – automatische Aufgaben planen
Der Scheduler ist Laravels eingebaute Lösung für wiederkehrende Aufgaben (wie ein Cron-Job). Man registriert Commands in `routes/console.php` mit `Schedule::command('auctions:close')->everyMinute()`. Auf einem echten Server würde man einmalig einen Cron-Job einrichten: `* * * * * php artisan schedule:run` – das läuft jede Minute und Laravel entscheidet dann selbst welche geplanten Aufgaben dran sind. Lokal kann man `php artisan schedule:run` manuell aufrufen oder den Command direkt. Im Projekt: `auctions:close` wird jede Minute registriert – aktiviert geplante Auktionen und schließt abgelaufene.

---

---

## Terminal-Befehle (PowerShell / Bash)

Alle Befehle die im Projektverlauf in der Kommandozeile eingegeben wurden – so erklärt wie ein Anfänger sie verstehen würde.

---

### Git – Versionskontrolle

#### `git init`
Mit `git init` wird ein neues Git-Repository im aktuellen Ordner angelegt. Git ist ein Programm das alle Änderungen am Code aufzeichnet – so kann man jederzeit sehen was wann geändert wurde und bei Bedarf eine ältere Version wiederherstellen. Man führt diesen Befehl einmalig am Anfang eines Projekts aus.

#### `git status`
`git status` zeigt den aktuellen Zustand des Repositories an: welche Dateien verändert wurden, welche neu sind und welche bereits für den nächsten Commit vorgemerkt sind. Das ist der erste Befehl den man tippt wenn man wissen will was gerade im Projekt los ist. Man kann ihn so oft ausführen wie man möchte – er verändert nichts.

#### `git add <datei>`
Mit `git add` werden Dateien für den nächsten Commit vorgemerkt (man sagt auch "gestaged"). Man gibt entweder einzelne Dateinamen an (`git add web.php`) oder mehrere auf einmal. Erst nach `git add` werden die Änderungen beim nächsten `git commit` gespeichert.

#### `git commit -m "..."`
`git commit` speichert alle vorgemerkten Änderungen dauerhaft im Repository mit einer kurzen Beschreibung was gemacht wurde. Die Option `-m` steht für "message" – dahinter kommt in Anführungszeichen die Commit-Nachricht. Im Projekt: `git commit -m "Phase 2: Warenkorb implementiert"` – so weiß man später noch was in diesem Commit passiert ist.

#### `git log`
`git log` zeigt die gesamte Commit-Historie an – wann was von wem gespeichert wurde. Jeder Eintrag hat einen langen Code (den sogenannten Hash), das Datum, den Autor und die Commit-Nachricht. So kann man den Verlauf eines Projekts von Anfang an nachverfolgen.

#### `git diff`
`git diff` zeigt die genauen Unterschiede zwischen dem aktuellen Stand der Dateien und dem letzten Commit. Hinzugefügte Zeilen werden mit `+` markiert, entfernte mit `-`. Das ist nützlich um vor einem Commit nochmal zu prüfen was man genau verändert hat.

#### `git diff --numstat`
Eine kompaktere Version von `git diff` die nur Zahlen ausgibt: wie viele Zeilen wurden hinzugefügt und wie viele gelöscht, aufgeteilt nach Datei. Im Projekt wurde dieser Befehl nach jeder Session verwendet um zu zählen wie viele Zeilen Code neu geschrieben wurden.

#### `git diff --name-only`
Zeigt nur die Namen der Dateien an die seit dem letzten Commit verändert wurden – ohne den genauen Inhalt der Änderungen. Praktisch wenn man schnell einen Überblick braucht welche Dateien angefasst wurden.

---

### PHP Artisan – Das Laravel-Werkzeug

`php artisan` ist Laravels eingebautes Kommandozeilen-Werkzeug. Man tippt es immer im Projektordner. Es erspart viel Tipparbeit weil es Dateien automatisch anlegt und Aufgaben wie Datenbankmigrationen erledigt.

#### `php artisan migrate`
Führt alle noch nicht ausgeführten Migrationen aus – also alle Datenbankänderungen die in den Migrations-Dateien beschrieben sind. Jede neue Tabelle oder jede Änderung an einer bestehenden Tabelle wird so in die Datenbank übernommen. Im Projekt: Nach jedem `make:migration` muss `php artisan migrate` ausgeführt werden damit die Tabelle wirklich in MySQL angelegt wird.

#### `php artisan make:migration <name>`
Legt eine neue leere Migrations-Datei in `database/migrations/` an. Der Name beschreibt was die Migration macht, z.B. `create_products_table`. Laravel ergänzt automatisch einen Zeitstempel im Dateinamen damit die Reihenfolge der Migrationen eindeutig ist.

#### `php artisan make:model <Name>`
Erstellt ein neues Eloquent-Model in `app/Models/`. Ein Model ist die Verbindung zwischen einer Datenbanktabelle und dem PHP-Code – über das Model kann man Datensätze lesen, erstellen, ändern und löschen. Im Projekt: `php artisan make:model Product` hat das `Product`-Model angelegt.

#### `php artisan make:controller <Name>`
Legt einen neuen Controller in `app/Http/Controllers/` an. Ein Controller nimmt Anfragen vom Browser entgegen, verarbeitet sie (z.B. Daten aus der Datenbank holen) und gibt eine Antwort zurück (z.B. eine View anzeigen). Im Projekt: `php artisan make:controller ProductController`.

#### `php artisan make:seeder <Name>`
Erstellt eine neue Seeder-Datei in `database/seeders/`. Seeder sind PHP-Dateien die Testdaten in die Datenbank schreiben – z.B. Dummy-Kunden oder Standardrollen. So muss man beim Entwickeln nicht immer alles manuell über den Browser eingeben.

#### `php artisan make:command <Name>`
Erstellt eine neue Command-Datei in `app/Console/Commands/`. Damit kann man eigene `php artisan`-Befehle schreiben. Im Projekt: `php artisan make:command CloseAuctions` hat die Grundstruktur für den `auctions:close`-Befehl angelegt.

#### `php artisan db:seed`
Führt alle Seeder aus und befüllt die Datenbank mit Testdaten. Man kann auch einen einzelnen Seeder angeben mit `--class=DummyCustomersSeeder`. Im Projekt wurde dieser Befehl genutzt um Testrollen und Dummy-Kunden anzulegen.

#### `php artisan storage:link`
Erstellt einen symbolischen Link von `public/storage` auf `storage/app/public`. Dieser Befehl muss einmalig ausgeführt werden damit Bilder die ins Storage hochgeladen werden auch im Browser erreichbar sind. Ohne diesen Link würde man beim Bildupload immer einen 404-Fehler sehen.

#### `php artisan test`
Führt alle PHPUnit-Tests im Projekt aus und zeigt an wie viele bestanden oder fehlgeschlagen sind. Mit `--filter=KlassenName` kann man gezielt nur die Tests einer bestimmten Klasse ausführen, z.B. `php artisan test --filter=AuctionTest`. Im Projekt wurde dieser Befehl nach jeder Änderung ausgeführt um sicherzustellen dass nichts kaputt gegangen ist.

#### `php artisan route:list`
Zeigt eine Tabelle aller registrierten Routen der Anwendung an – mit HTTP-Methode, URL, Route-Name und dem zugehörigen Controller. Das ist sehr nützlich um zu prüfen ob eine Route korrekt registriert ist oder um den genauen Namen einer Route nachzuschlagen.

#### `php artisan tinker`
Tinker ist eine interaktive PHP-Konsole direkt im Laravel-Kontext. Man kann damit schnell Eloquent-Abfragen testen, Datensätze direkt anlegen oder Modelle abfragen ohne eine View oder einen Controller zu bauen. Im Projekt: Tinker wurde genutzt um bei bereits existierenden Nutzern die `kundennummer` nachträglich zu setzen.

#### `php artisan db:show`
Zeigt Informationen zur aktuellen Datenbankverbindung an – welche Datenbank, welcher Host, welche Version. Damit lässt sich schnell prüfen ob die `.env`-Einstellungen korrekt sind und Laravel sich erfolgreich mit MySQL verbinden kann.

#### `php artisan auctions:close`
Das ist unser selbst geschriebener Artisan-Command. Er prüft alle Auktionen in der Datenbank: geplante Auktionen die jetzt starten sollen werden auf "aktiv" gesetzt, und aktive Auktionen deren Endzeit abgelaufen ist werden geschlossen – mit Gewinner-Ermittlung und automatischer Bestellanlage. Im Produktivbetrieb würde dieser Befehl automatisch jede Minute durch den Laravel Scheduler ausgeführt.

---

### Composer – PHP-Paketverwaltung

Composer ist das Programm mit dem man PHP-Pakete (fertige Bibliotheken) installiert. Man führt es immer im Projektordner aus. Die installierten Pakete und ihre Versionen werden in `composer.json` gespeichert.

#### `composer require laravel/breeze`
Installiert das Breeze-Paket von Laravel. Breeze liefert fertige Login-, Registrierungs- und Passwort-Reset-Seiten mit Blade-Templates. Nach der Installation muss noch `php artisan breeze:install` ausgeführt werden damit die Dateien ins Projekt kopiert werden.

#### `composer require spatie/laravel-permission`
Installiert das Spatie Permission-Paket das Rollenverwaltung für Laravel ermöglicht. Mit diesem Paket kann man Nutzern Rollen wie "admin", "mitarbeiter" oder "kunde" zuweisen und im Code ganz einfach prüfen ob ein Nutzer eine bestimmte Rolle hat. Nach der Installation müssen noch die Migrationen ausgeführt und der Provider veröffentlicht werden.

---

### npm – JavaScript-Paketverwaltung

npm (Node Package Manager) ist das Gegenstück zu Composer – aber für JavaScript-Pakete. In diesem Projekt wird es hauptsächlich für Tailwind CSS und Vite verwendet. Man führt npm-Befehle ebenfalls immer im Projektordner aus.

#### `npm install`
Liest die `package.json`-Datei und installiert alle darin aufgelisteten JavaScript-Pakete in den `node_modules`-Ordner. Dieser Befehl muss einmalig nach dem Klonen oder Einrichten eines Projekts ausgeführt werden. Die `node_modules`-Ordner werden nicht in Git gespeichert weil sie sehr groß sind und jeder sie selbst installieren kann.

#### `npm run dev`
Startet Vite im Entwicklungsmodus. Vite überwacht alle CSS- und JavaScript-Dateien und aktualisiert den Browser automatisch wenn sich etwas ändert (Hot Reload). Im Projekt: Wenn man an Tailwind-Klassen arbeitet muss `npm run dev` im Hintergrund laufen damit die Änderungen sofort sichtbar sind.

#### `npm run build`
Erstellt eine optimierte, komprimierte Version aller CSS- und JavaScript-Dateien für den Produktivbetrieb. Die Dateien werden in `public/build/` gespeichert und sind kleiner und schneller als im Entwicklungsmodus. Im Entwicklungsalltag reicht `npm run dev` – `npm run build` würde man erst beim Deployment auf einen echten Server brauchen.

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
| RefreshDatabase | PHPUnit-Trait der die Datenbank vor jedem Test zurücksetzt |
| firstOrCreate | Laravel-Funktion: hol den Eintrag, oder leg ihn an falls er nicht existiert |
| assertAuthenticated | PHPUnit-Prüfung: ist der Nutzer nach der Aktion eingeloggt? |
| hasRole | Spatie-Funktion: hat dieser Nutzer die angegebene Rolle? |
| ParseError | PHP-Fehler: der Code hat einen Syntaxfehler – z.B. eine nicht geschlossene if-Anweisung |
| Blade-Kommentar | `{{-- ... --}}` wird beim Kompilieren komplett entfernt; HTML-Kommentare `<!-- -->` werden von Blade verarbeitet |
| storage:link | Einmaliger Artisan-Befehl der `public/storage` mit `storage/app/public` verknüpft – nötig für Bildupload |
| hasMany | Eloquent-Beziehung: ein Datensatz hat viele andere (z.B. Order hat viele OrderItems) |
| belongsTo | Eloquent-Beziehung: ein Datensatz gehört zu einem anderen (z.B. OrderItem gehört zu Order) |
| Session | Server-seitiger Zwischenspeicher der Daten zwischen Seitenanfragen aufbewahrt |
| decrement | Laravel-Funktion: Zahl in der Datenbank direkt um einen Wert verringern (z.B. Lagerbestand -2) |
| unique constraint | Datenbankregel die verhindert dass ein Wert doppelt vorkommt (z.B. ein Nutzer bewertet ein Produkt nur einmal) |
| @stack / @push | Blade-Mechanismus um seitenspezifisches JavaScript gesammelt am Ende des Layouts einzubinden |
| Model Event | Automatische Reaktion auf Datenbankaktionen wie erstellen, aktualisieren oder löschen |
| booted() | Methode im Eloquent-Model wo man Model Events registriert |
| created() | Model Event das nach dem Speichern eines neuen Datensatzes ausgelöst wird – `id` ist dann bereits gesetzt |
| artikel_nr | Eigene Spalte für eine sichtbare Artikelnummer (10001, 10002, ...) – wird automatisch per Model Event gesetzt |
| selectRaw | Eloquent-Methode um rohes SQL für Aggregationen zu schreiben (z.B. COUNT, SUM) |
| groupBy | SQL/Eloquent: Ergebnisse nach einem Feld zusammenfassen (z.B. pro Tag) |
| match() | PHP-Kurzform für if-else: vergleicht einen Wert und gibt das passende Ergebnis zurück |
| Partial | Kleines wiederverwendbares Blade-Template, eingebunden per @include() |
| syncRoles | Spatie-Funktion: alle alten Rollen entfernen und neue Rolle(n) setzen |
| datetime-local | HTML-Input-Typ für Datum + Uhrzeit kombiniert – wird von Laravel-Validierung direkt verstanden |
| kundennummer | Automatisch vergebene Kundennummer (20000 + id) – per booted()-Event wie artikel_nr |
| Artisan-Command | Eigener Konsolen-Befehl in Laravel – wird in `app/Console/Commands/` angelegt, Aufruf: `php artisan name` |
| Scheduler | Laravels eingebautes System für geplante Aufgaben – konfiguriert in `routes/console.php` |
| everyMinute() | Scheduler-Methode: diese Aufgabe soll jede Minute ausgeführt werden |
| $signature | Pflichtfeld in einem Artisan-Command – legt den Befehlsnamen fest (z.B. `auctions:close`) |
| handle() | Methode in einem Artisan-Command die beim Ausführen aufgerufen wird – hier kommt die Logik rein |
| Many-to-Many | Datenbankbeziehung: beide Seiten können viele der anderen haben – braucht eine Pivot-Tabelle |
| Pivot-Tabelle | Zwischentabelle bei Many-to-Many – enthält nur die IDs der beiden verknüpften Tabellen |
| belongsToMany | Eloquent-Beziehung für Many-to-Many – in beiden Models definiert |
| attach() | Einen Eintrag in der Pivot-Tabelle anlegen (Mitarbeiter einem Bereich zuweisen) |
| detach() | Einen Eintrag aus der Pivot-Tabelle entfernen (Mitarbeiter aus Bereich entfernen) |
| syncWithoutDetaching | Wie attach(), aber verhindert Duplikate wenn der Eintrag schon existiert |
| git init | Neues Git-Repository im aktuellen Ordner anlegen – einmalig am Projektstart |
| git status | Aktuellen Stand des Repos anzeigen – was ist geändert, was ist vorgemerkt |
| git add | Dateien für den nächsten Commit vormerken ("stagen") |
| git commit | Vorgemerkte Änderungen dauerhaft speichern mit einer Beschreibung |
| git log | Commit-Verlauf anzeigen – wann was gespeichert wurde |
| git diff | Genaue Unterschiede zum letzten Commit anzeigen |
| php artisan migrate | Noch nicht ausgeführte Migrationen in der Datenbank ausführen |
| php artisan make:... | Laravel-Befehl zum automatischen Erstellen von Dateien (Model, Controller, Migration usw.) |
| php artisan db:seed | Seeder ausführen und Testdaten in die Datenbank eintragen |
| php artisan storage:link | Einmaliger Befehl um Bildupload über den Browser erreichbar zu machen |
| php artisan test | Alle PHPUnit-Tests ausführen |
| php artisan route:list | Alle registrierten Routen der Anwendung anzeigen |
| php artisan tinker | Interaktive PHP-Konsole im Laravel-Kontext – zum schnellen Testen |
| composer require | Ein PHP-Paket installieren und in composer.json eintragen |
| npm install | Alle JavaScript-Pakete aus package.json installieren |
| npm run dev | Vite im Entwicklungsmodus starten (automatische Browser-Aktualisierung) |
| npm run build | Optimierte CSS/JS-Dateien für den Produktivbetrieb erstellen |
