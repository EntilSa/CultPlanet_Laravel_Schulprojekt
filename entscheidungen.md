# CultPlanet – Entscheidungslog

Technische Entscheidungen mit Begründung – direkt verwendbar für die Projektdokumentation.

---

## Entscheidungen

---

### QoL-Review nach Fertigstellung (25.04.2026)

**25.04.2026 – Startseite entfernt, Shop ist jetzt Landing Page**
Nach einem vollständigen Frontend-Review wurde festgestellt, dass die Startseite keinen Mehrwert bietet: Sie zeigte nur einen Titel, einen Untertitel und einen "Zum Shop"-Button. Echte Onlineshops (Amazon, Otto, Zalando) führen Nutzer direkt zu Produkten. Die Startseite war eine unnötige Zwischenstation. Entscheidung: Route `/` leitet per Redirect direkt auf `/shop` um. Der Auktions-Banner wird stattdessen oben in der Shop-Übersicht angezeigt.

**25.04.2026 – Auktions-Banner in Shop-Übersicht integriert statt auf eigener Startseite**
Da die Startseite entfernt wurde, wird der Auktions-Banner direkt in `shop/index.blade.php` oben eingebunden. Das ist näher am Standard moderner Shops: Aktionsware und Sonderangebote erscheinen dort wo der Nutzer eh schon ist – im Shop. Die Logik zum Laden der aktiven/nächsten Auktion wird aus der Home-Route in `ProductController::index()` verschoben.

**25.04.2026 – Produktbild und Produktname klickbar zur Detailseite**
Beim Frontend-Review wurde festgestellt, dass nur der "Ansehen"-Button zur Produktdetailseite führt. Bild und Name sind nicht klickbar. Das widerspricht dem Standard jedes Onlineshops. Nutzer klicken instinktiv auf Bild oder Name. Lösung: beide in einen Link auf `route('shop.show', $product)` einwickeln. Kein neuer Code nötig – nur HTML-Anpassung in der Blade-View.

**25.04.2026 – Hover-Effekte auf Produktkarten für moderneres Erscheinungsbild**
Die Produktkarten wirkten statisch. Ein leichter Hover-Effekt (`hover:shadow-lg hover:-translate-y-1`) gibt dem Shop ein lebendigeres, moderneres Gefühl ohne Overengineering. Standard in zeitgemäßen Onlineshops. Umsetzung: zwei Tailwind-Klassen pro Karte in der Blade-View.

**25.04.2026 – Pagination-Text und Auth-Texte auf Deutsch**
Laravel und Breeze liefern englische Standardtexte ("Showing 1 to 12 of 20 results", "LOG IN", "Forgot your password?"). Da CultPlanet ein deutschsprachiger Shop ist, werden diese Texte eingedeutscht. Außerdem fehlte auf der Login-Seite ein Link zur Registrierung – was ein Usability-Standard ist.

**25.04.2026 – ExampleTest musste nach Redirect-Änderung angepasst werden**
Der Standard-`ExampleTest.php` prüfte `GET /` → HTTP 200. Da `/` jetzt per `Route::redirect()` auf `/shop` weiterleitet, kommt stattdessen HTTP 302. Der Test wurde auf `assertRedirect('/shop')` umgestellt. Alle anderen 131 Tests waren nicht betroffen – die QoL-Änderungen berühren keine Controller-Logik oder Models.

### Neue Features (25.04.2026)

**25.04.2026 – barryvdh/laravel-dompdf für PDF-Rechnungen**
Für das Erstellen von PDF-Rechnungen wurde das Paket `barryvdh/laravel-dompdf` gewählt. Es ist das am weitesten verbreitete PDF-Paket für Laravel, gut dokumentiert und funktioniert ohne zusätzliche System-Abhängigkeiten. Alternative wäre TCPDF gewesen – dompdf ist aber einfacher zu benutzen und gut genug für ein Schulprojekt.

**25.04.2026 – Mailpit als lokaler Fake-Mailserver statt Mailtrap oder Log-Mailer**
Mailpit fängt alle Laravel-E-Mails lokal ab und zeigt sie mit Anhang in einer Web-UI an (localhost:8025). Alternative wäre der `log`-Mailer gewesen (Mail landet in storage/logs/laravel.log), aber damit kann man den PDF-Anhang nicht sichtbar prüfen. Mailpit ist kostenlos, offline nutzbar und zeigt die Mail so wie sie der Kunde sehen würde.

**25.04.2026 – Mail nach Checkout direkt in OrderController::store() – kein Job/Queue**
Die Bestellbestätigungs-Mail wird synchron direkt nach dem Speichern der Bestellung gesendet. Eine Queue (asynchrone Verarbeitung) wäre sauberer für Produktion, ist aber für ein Schulprojekt unnötig komplex. Wenn die Mail fehlschlägt, sieht der Nutzer einen Fehler – das ist akzeptabel.

**25.04.2026 – Mail-Logik dupliziert zwischen AuctionController und CloseAuctions-Command (bewusst)**
Beide `schliesseAuktion()`-Methoden (Controller + Command) senden jetzt die Auktion-Gewonnen-Mail. Eine gemeinsame Service-Klasse wäre sauberer, aber für zwei Stellen Over-Engineering. Die Duplikation bleibt überschaubar.

**25.04.2026 – "Meine Bestellungen" nutzt vorhandenes Status-Badge-Partial**
Die Kundenseite `orders/my-orders.blade.php` bindet das Admin-Partial `admin/partials/status-badge.blade.php` ein statt eigene Badge-Logik zu schreiben. Das Partial ist nicht admin-spezifisch – es zeigt nur ein farbiges Badge. Wiederverwendung spart Code und hält die Farben konsistent.

**25.04.2026 – Laravel Pint für einheitliche Code-Formatierung**
Pint ist der offizielle Laravel Code-Formatter (basiert auf PHP-CS-Fixer). Er wurde als Dev-Dependency installiert und einmalig auf das gesamte Projekt angewendet. 32 Dateien wurden formatiert (Leerzeichen, Zeilenumbrüche, import-Reihenfolge). Dadurch ist der Code jetzt im Laravel-Standard-Stil.

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

---

### Phase 3 (22.04.2026)

**22.04.2026 – Admin-Zugriffskontrolle per privater Hilfsmethode statt Middleware**
Die Admin-Routen sind zwar alle hinter `middleware('auth')`, aber die Rollen-Prüfung (admin vs. mitarbeiter) passiert direkt im Controller über private Methoden `nurAdmin()` und `nurAdminOderMitarbeiter()`. Das ist einfacher als eigene Middleware zu schreiben, weil die Logik direkt sichtbar ist und nicht in einer anderen Datei versteckt liegt.

**22.04.2026 – Verkaufsübersicht mit selectRaw und groupBy statt PHP-seitig aggregieren**
Die Tages-Gruppierung der bezahlten Bestellungen wird direkt in MySQL mit `selectRaw('DATE(created_at) as tag, COUNT(*) as anzahl, SUM(total) as umsatz')` + `groupBy('tag')` erledigt. So holt Laravel nur fertig aggregierte Zeilen aus der Datenbank, statt alle Bestellungen zu laden und in PHP zu summieren – deutlich effizienter.

**22.04.2026 – Status-Badge als separates Blade-Partial**
Das farbige Status-Badge (offen/bezahlt/versendet/storniert) wurde als eigenes Partial `admin/partials/status-badge.blade.php` ausgelagert. Es wird sowohl im Dashboard als auch in der Bestellliste eingebunden. So muss der `match()`-Block mit den Farben nur an einer Stelle gepflegt werden.

---

### Spezialisierung (22.04.2026)

**22.04.2026 – Kundennummer nach gleichem Muster wie Artikelnummer (booted-Event)**
Die Kundennummer (`kundennummer = 20000 + id`) folgt exakt dem gleichen Muster wie `artikel_nr`. Das ist konsistent und einfach zu verstehen: beide Nummern werden automatisch per Eloquent-Model-Event gesetzt, nachdem der Datensatz in der DB angelegt wurde und die `id` bekannt ist. Präfix 20000 um Verwechslung mit Artikelnummern (10000+) zu vermeiden.

**22.04.2026 – Auktions-Lagerbestand-Check im Controller statt Datenbankconstraint**
Ob ein Produkt noch eine weitere Auktion aufnehmen kann (`anzahl_geplanter_auktionen < stock`), wird im `AuctionController::store()` geprüft – nicht per DB-Constraint. Ein DB-Constraint könnte das nicht ausdrücken, weil es einen Vergleich zwischen zwei Tabellen erfordert. Die Controller-Prüfung gibt außerdem eine klare Fehlermeldung mit Zahlen ("Maximal X Auktionen möglich, aktuell Y geplant").

**22.04.2026 – Auktionsende und Statuswechsel beim Seitenaufruf statt nur per Scheduler**
Die Methode `statusAktualisieren()` wird in `index()` und `show()` aufgerufen – beim Laden der Seite werden geplante Auktionen automatisch aktiviert und abgelaufene automatisch geschlossen. So funktioniert das System auch ohne laufenden Cron-Job. Der Artisan-Command (Schritt 10) wird zusätzlich gebaut um das Konzept zu zeigen, ist aber nicht zwingend nötig für den Betrieb.

**22.04.2026 – Gebotsverlauf anonymisiert (erste 2 Buchstaben + Sternchen)**
Im Gebotsverlauf wird der Name des Bieters auf die ersten 2 Buchstaben + "***" gekürzt (z.B. "An***"). So sehen andere Bieter wer bietet, können aber die Person nicht vollständig identifizieren. Einfache Umsetzung mit `mb_substr()`.

**22.04.2026 – Countdown mit automatischem Seiten-Reload bei Ablauf**
Wenn der JS-Countdown auf 0 läuft, reloadet die Seite nach 3 Sekunden automatisch. Beim Reload wird `statusAktualisieren()` aufgerufen und die Auktion als "beendet" angezeigt. Kein WebSocket oder AJAX nötig – simpel und ausreichend.

**22.04.2026 – Auktions-Planungsformular direkt im Produkt-Edit statt separater Seite**
Das Formular zum Planen einer Auktion wurde als zusätzlicher Abschnitt in `products/edit.blade.php` eingebaut, nicht als eigene Seite. So hat der Admin alles auf einen Blick: Produktdaten bearbeiten und Auktionen planen in einer Ansicht. Weniger Klicks, weniger Routen.

---

### Individualprojekt (24.04.2026)

**24.04.2026 – Mitarbeiterverwaltung als eigene Seite statt in bestehende Admin-Views integriert**
Die Bereiche-Verwaltung hat eine eigene View `admin/departments/index.blade.php` statt z.B. in die Nutzer-Verwaltung integriert zu werden. So ist alles was mit Bereichen zu tun hat an einer Stelle – Bereiche anlegen, Mitarbeiter zuweisen, Warnanzeige. Ein eigener Controller `DepartmentController` hält die Logik sauber getrennt vom `AdminController`.

**24.04.2026 – Many-to-Many ohne eigenes Pivot-Model (nur Pivot-Tabelle)**
Die Beziehung Mitarbeiter ↔ Bereiche läuft über eine einfache Pivot-Tabelle `department_user`. Ein eigenes Eloquent-Model für die Pivot-Tabelle (`DepartmentUser`) wäre nur nötig wenn man auf der Pivot-Tabelle eigene Spalten (z.B. `since`) bräuchte. Da hier nur die Zuordnung zählt, reicht `belongsToMany()` direkt – einfacher und ausreichend.

**24.04.2026 – Warnsystem als orange Hervorhebung statt E-Mail oder Push**
Der Hinweis auf unbesetzte Bereiche erscheint als auffällige orangene Infobox oben auf der Bereiche-Seite sowie als "Unbesetzt"-Badge auf jeder Bereichskarte. Eine aktivere Benachrichtigung (E-Mail, Dashboard-Badge) wäre für ein Schulprojekt Over-Engineering. Die Warnung ist gut sichtbar wenn ein Admin die Seite aufruft.

---

**24.04.2026 – Auktions-Banner zeigt aktive oder nächste geplante Auktion**
Der Banner auf der Startseite zeigt zuerst die aktive Auktion, falls keine läuft die nächste geplante. Die Logik sitzt direkt in der Home-Route-Closure in `web.php` – kein eigener Controller nötig, da es nur zwei Abfragen sind. Falls gar keine Auktion vorhanden ist, wird der Banner-Bereich komplett ausgeblendet (`@if($auktionBanner)`).

**24.04.2026 – Artisan-Command enthält doppelte Logik (bewusste Entscheidung)**
Die `schliesseAuktion()`-Methode existiert sowohl im `AuctionController` als auch im `CloseAuctions`-Command. Eine gemeinsame Service-Klasse wäre sauberer, aber für ein Schulprojekt mit zwei Stellen ist das Over-Engineering. Die Doppelung ist überschaubar und der Code bleibt dadurch in jeder Datei direkt lesbar ohne zwischen Dateien zu springen.

**24.04.2026 – ExampleTest braucht RefreshDatabase nach Startseiten-Änderung**
Der Standard-ExampleTest hatte kein `RefreshDatabase` – das war kein Problem solange die Startseite keine DB-Abfrage machte. Nach dem Hinzufügen der Auktions-Abfrage in der Home-Route schlug der Test fehl ("no such table: auctions"). Lösung: `RefreshDatabase` + `$seed = true` in ExampleTest ergänzt. Konsequenz: alle Feature-Tests brauchen RefreshDatabase wenn die App DB-Abfragen macht.

---

---

### Suche & Filter im Shop (24.04.2026)

**24.04.2026 – Suche und Filter als GET-Formular statt JavaScript/AJAX**
Die Such- und Filterleiste im Shop verwendet ein einfaches HTML-Formular mit `method="GET"`. Beim Absenden werden die Parameter an die URL angehängt (z.B. `/shop?suche=lego&preis_max=50`). Die Alternative wäre AJAX gewesen – die Seite aktualisiert sich ohne Reload. GET hat aber entscheidende Vorteile: der Link ist teilbar, der Zurück-Button funktioniert, und kein JavaScript ist nötig. Für ein Schulprojekt eindeutig die einfachere und sicherere Wahl.

**24.04.2026 – Filterchips zeigen aktive Filter visuell an**
Nach dem Vorbild von Amazon und Otto werden aktive Filter als kleine farbige "Chips" (blaue Badges) unterhalb der Filterleiste angezeigt. Das empfiehlt das Baymard Institute als Best Practice, weil Nutzer sonst vergessen was sie gefiltert haben. Der "Zurücksetzen"-Button erscheint nur wenn mindestens ein Filter aktiv ist – nicht immer, um den Screen nicht zu überladen.

**24.04.2026 – Sortierung per match()-Ausdruck statt if/else-Kette**
Die vier Sortieroptionen (neueste, preis_asc, preis_desc, bewertung) werden mit einem PHP-`match()`-Ausdruck auf die entsprechende Eloquent-Methode gemappt. Das ist kompakter als vier if/else-Blöcke und trotzdem direkt lesbar. Default-Fall ist `latest()` – das war schon vorher die Standardsortierung.

---

### Tests nach Branchenstandard (24.04.2026)

**24.04.2026 – Testfälle an E-Commerce-Branchenstandards ausgerichtet**
Nach einer Internetrecherche (Quellen: Katalon, BrowserStack, TestGrid) wurden die fehlenden Standardtestfälle für Onlineshops identifiziert und in zwei neue Testdateien umgesetzt. Die wichtigsten Lücken waren Sicherheitstests (fremde Bestellungen einsehen) und Grenzwerttests (negative Mengen, überlange Felder). Beides ist in echten Shops erfahrungsgemäß Quelle von Bugs oder Sicherheitslücken.

**24.04.2026 – Sicherheitstests prüfen 403-Status statt Redirect**
Wenn ein Nutzer auf eine fremde Ressource zugreift, gibt `abort(403)` einen HTTP-403-Fehlercode zurück – keinen Redirect. Der Test prüft deshalb `->assertStatus(403)` und nicht `->assertRedirect()`. Gäste hingegen werden von Laravels Auth-Middleware mit einem Redirect zu `route('login')` abgefangen, bevor der Controller-Code überhaupt ausgeführt wird.

**24.04.2026 – Zwei separate Testdateien statt eine große**
Sicherheitstests (`SicherheitsTest.php`) und Grenzwerttests (`GrenzwertTest.php`) wurden bewusst getrennt, weil sie verschiedene Konzepte testen: Zugriffsschutz vs. Eingabe-Validierung. So ist die Teststruktur übersichtlich und man findet beim Fehlschlag eines Tests sofort die richtige Kategorie.

---

**22.04.2026 – Artikelnummer als echte DB-Spalte mit Model-Event statt Controller-Logik**
Die Artikelnummer (10001, 10002, ...) wird als eigene Spalte `artikel_nr` in der Datenbank gespeichert, nicht nur zur Anzeige berechnet. Vorteil: Die Nummer bleibt stabil, auch wenn das Produkt bearbeitet wird, und lässt sich filtern oder sortieren. Die automatische Vergabe passiert im `Product`-Model über ein Eloquent-Model-Event (`booted()` + `static::created()`): nach jedem `Product::create()` wird sofort `artikel_nr = id + 10000` gesetzt. So ist der Controller frei davon und die Logik sitzt an einer einzigen Stelle. Zwei Migrationen waren nötig: erst die Spalte als NOT NULL + unique anlegen, dann in einer zweiten Migration nullable machen – damit das booted()-Event nach dem create() mit update() schreiben kann ohne einen NOT-NULL-Fehler zu werfen.
