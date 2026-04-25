<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $produkte = [
            [
                'name' => 'LEGO City Polizeistation',
                'description' => 'Die große LEGO City Polizeistation mit 743 Teilen. Enthält 6 Minifiguren, Polizeiauto, Motorrad und Hubschrauber. Für Kinder ab 6 Jahren – fördert kreatives Bauen und Rollenspiele.',
                'price' => 89.99,
                'stock' => 15,
                'seed' => 'lego-police',
            ],
            [
                'name' => 'Playmobil Piratenschiff',
                'description' => 'Das klassische Playmobil Piratenschiff mit aufklappbarem Rumpf, Kanonen und 4 Piraten-Figuren. Mit Totenkopf-Flagge und viel Zubehör. Für Kinder ab 4 Jahren.',
                'price' => 69.99,
                'stock' => 8,
                'seed' => 'playmobil-ship',
            ],
            [
                'name' => 'Barbie Traumvilla',
                'description' => 'Die Barbie Traumvilla mit 3 Etagen, Pool, Aufzug und über 75 Zubehörteilen. Komplett eingerichtet mit Küche, Bad und Schlafzimmer. Für Kinder ab 3 Jahren.',
                'price' => 149.99,
                'stock' => 5,
                'seed' => 'barbie-villa',
            ],
            [
                'name' => 'Hot Wheels Track Builder Set',
                'description' => 'Das ultimative Hot Wheels Track Builder Set mit über 3 Metern Strecke, Loopings und Sprungschanzen. Inklusive 2 Fahrzeugen und erweiterbar mit weiteren Track Builder Sets.',
                'price' => 39.99,
                'stock' => 20,
                'seed' => 'hotwheels-track',
            ],
            [
                'name' => 'Monopoly Classic',
                'description' => 'Das weltberühmte Brettspiel Monopoly in der klassischen deutschen Version. Kaufe Straßen, baue Häuser und Hotels und bringe deine Mitspieler in den Ruin. Für 2–8 Spieler ab 8 Jahren.',
                'price' => 29.99,
                'stock' => 25,
                'seed' => 'monopoly-board',
            ],
            [
                'name' => 'Nerf Elite 2.0 Echo CS-10',
                'description' => 'Der Nerf Elite 2.0 Echo CS-10 Blaster mit 10-Dart-Magazin und motorisiertem Schussmechanismus. Schießt bis zu 27 Meter weit. Für Kinder ab 8 Jahren – inklusive 24 Elite-Darts.',
                'price' => 44.99,
                'stock' => 12,
                'seed' => 'nerf-blaster',
            ],
            [
                'name' => 'Ravensburger Puzzle 1000 Teile',
                'description' => 'Das hochwertige Ravensburger Puzzle mit 1000 Teilen zeigt ein wunderschönes Panorama. Aus extra dickem, matten Puzzlekarton – kein Blendeffekt beim Puzzeln. Für Erwachsene und Jugendliche ab 14 Jahren.',
                'price' => 14.99,
                'stock' => 30,
                'seed' => 'puzzle-landscape',
            ],
            [
                'name' => 'Tamagotchi Original',
                'description' => 'Das kultiges digitale Haustier aus den 90ern ist zurück! Pflege, füttere und spiele mit deinem virtuellen Freund. Mit 3 Knöpfen und verschiedenen Mini-Games. Für Kinder und Nostalgiker ab 8 Jahren.',
                'price' => 19.99,
                'stock' => 18,
                'seed' => 'tamagotchi-pet',
            ],
            [
                'name' => "Rubik's Cube 3x3",
                'description' => "Der originale Rubik's Cube – das meistverkaufte Puzzle der Welt. Über 43 Trillionen mögliche Kombinationen, aber nur eine Lösung. Mit glatten Drehmechanismus und lebhaften Farben. Ab 8 Jahren.",
                'price' => 12.99,
                'stock' => 40,
                'seed' => 'rubiks-cube',
            ],
            [
                'name' => 'Jenga Classic',
                'description' => 'Das spannende Gleichgewichtsspiel Jenga mit 54 Holzklötzen. Ziehe vorsichtig Klötze heraus und stapele sie oben drauf – wer den Turm zum Einsturz bringt verliert. Für 1 oder mehr Spieler ab 6 Jahren.',
                'price' => 19.99,
                'stock' => 22,
                'seed' => 'jenga-tower',
            ],
            [
                'name' => 'UNO Kartenspiel',
                'description' => 'Das berühmte Kartenspiel UNO für die ganze Familie. Mit 112 Karten inklusive Aktionskarten wie "Ziehe 4" und "Richtungswechsel". Für 2–10 Spieler ab 7 Jahren – Spielspaß garantiert!',
                'price' => 9.99,
                'stock' => 50,
                'seed' => 'uno-cards',
            ],
            [
                'name' => 'Minecraft Creeper Plüsch',
                'description' => 'Der offizielle Minecraft Creeper als 30 cm großer Plüsch. Aus weichem, strapazierfähigem Material mit detailgetreuer Pixel-Optik. Der perfekte Begleiter für alle Minecraft-Fans ab 6 Jahren.',
                'price' => 24.99,
                'stock' => 14,
                'seed' => 'minecraft-creeper',
            ],
            [
                'name' => 'Star Wars Lichtschwert',
                'description' => 'Das elektronische Star Wars Lichtschwert mit Licht- und Soundeffekten. Der Blade leuchtet in Rot oder Blau und macht authentische Kampfgeräusche. Für alle Jedi und Sith ab 4 Jahren – Länge: 84 cm.',
                'price' => 34.99,
                'stock' => 10,
                'seed' => 'lightsaber-star',
            ],
            [
                'name' => 'Harry Potter Zauberstab',
                'description' => 'Der detailgetreue Nachbau von Harry Potters Zauberstab aus dem Ollivander-Laden. Aus hochwertigem Kunstharz, 38 cm lang mit individuellen Oberflächendetails. Das perfekte Geschenk für Potter-Fans.',
                'price' => 29.99,
                'stock' => 9,
                'seed' => 'harry-wand',
            ],
            [
                'name' => 'Pokémon Booster Pack Karmesin',
                'description' => 'Das offizielle Pokémon Sammelkartenspiel Booster Pack aus der Serie Karmesin & Purpur mit 10 zufälligen Karten. Jedes Pack enthält mindestens 1 seltene Karte – vielleicht sogar ein Holo oder ex-Karte!',
                'price' => 5.99,
                'stock' => 100,
                'seed' => 'pokemon-cards',
            ],
            [
                'name' => 'Steiff Teddybär 35 cm',
                'description' => 'Der klassische Steiff Teddybär in goldbraun, 35 cm groß. Aus hochwertigem Plüsch mit dem berühmten Knopf im Ohr. Ideal als Kuscheltier und Sammlerstück – für Kinder ab 0 Jahren geeignet.',
                'price' => 59.99,
                'stock' => 7,
                'seed' => 'teddy-bear',
            ],
            [
                'name' => 'Carrera GO!!! Autorennbahn',
                'description' => 'Die Carrera GO!!! Elektrische Autorennbahn mit 5,3 Metern Fahrbahn, 2 Fahrzeugen und 2 Handreglern. Kurven, Gerade und Überführung inklusive – erweiterbar mit allen Carrera GO!!! Sets.',
                'price' => 79.99,
                'stock' => 6,
                'seed' => 'carrera-racing',
            ],
            [
                'name' => 'Fisher-Price Lerntablett',
                'description' => 'Das bunte Fisher-Price Lerntablett mit 5 verschiedenen Lernmodi. Buchstaben, Zahlen, Musik und mehr – alles auf einem robusten Kunststoff-Tablet. Für Kleinkinder ab 18 Monaten mit englischer und deutscher Sprache.',
                'price' => 27.99,
                'stock' => 11,
                'seed' => 'fisher-price-tablet',
            ],
            [
                'name' => 'Play-Doh Knetmasse 24er Set',
                'description' => 'Das große Play-Doh Set mit 24 verschiedenen Farben à 28g. Die sichere, nicht-toxische Knetmasse ist einfach zu formen und trocknet nicht aus wenn sie richtig gelagert wird. Für Kinder ab 2 Jahren.',
                'price' => 22.99,
                'stock' => 35,
                'seed' => 'playdoh-colors',
            ],
            [
                'name' => 'Schleich Dinosaurier Set',
                'description' => 'Das Schleich Dinosaurier Set mit 5 detailgetreuen Figuren: T-Rex, Triceratops, Brachiosaurus, Velociraptor und Spinosaurus. Handgemalt aus hochwertigem Kunststoff. Für Dino-Fans ab 4 Jahren.',
                'price' => 49.99,
                'stock' => 13,
                'seed' => 'dinosaur-figures',
            ],
        ];

        foreach ($produkte as $daten) {
            // bild von picsum.photos herunterladen (kostenlose platzhalterbilder)
            $seed = $daten['seed'];
            $bildPfad = "products/{$seed}.jpg";

            // nur herunterladen wenn das bild noch nicht existiert
            if (! Storage::disk('public')->exists($bildPfad)) {
                try {
                    // ssl-prüfung deaktiviert weil wamp unter windows kein lokales ssl-zertifikat hat
                    $response = Http::withOptions(['verify' => false])->timeout(10)->get("https://picsum.photos/seed/{$seed}/600/600");
                    if ($response->successful()) {
                        Storage::disk('public')->put($bildPfad, $response->body());
                    }
                } catch (\Exception $e) {
                    // wenn download fehlschlägt einfach ohne bild anlegen
                    $bildPfad = null;
                }
            }

            Product::create([
                'name' => $daten['name'],
                'description' => $daten['description'],
                'price' => $daten['price'],
                'stock' => $daten['stock'],
                'image' => $bildPfad,
            ]);

            $this->command->info("✓ {$daten['name']}");
        }

        $this->command->info("\n20 Produkte erfolgreich angelegt!");
    }
}
