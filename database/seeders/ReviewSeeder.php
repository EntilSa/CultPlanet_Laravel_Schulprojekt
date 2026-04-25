<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // dummy-kunden holen
        $kunden = User::whereIn('email', [
            'anna.mueller@example.com',
            'ben.schmidt@example.com',
            'clara.weber@example.com',
            'david.fischer@example.com',
            'eva.becker@example.com',
        ])->get()->values();

        // alle produkte holen
        $produkte = Product::orderBy('id')->get();

        if ($kunden->isEmpty() || $produkte->isEmpty()) {
            $this->command->warn('Keine Kunden oder Produkte gefunden – Seeder übersprungen.');
            return;
        }

        // viele verschiedene bewertungstexte – realistisch und abwechslungsreich
        $bewertungen = [
            ['rating' => 5, 'text' => 'Absolut top! Mein Kind liebt das Spielzeug, Qualität ist super. Kommt gut verpackt an.'],
            ['rating' => 5, 'text' => 'Sehr schnelle Lieferung, Produkt entspricht genau der Beschreibung. Gerne wieder!'],
            ['rating' => 4, 'text' => 'Gutes Produkt, macht Spaß. Ein Stern Abzug weil die Verpackung etwas beschädigt ankam.'],
            ['rating' => 4, 'text' => 'Tolle Qualität für den Preis. Würde ich weiterempfehlen.'],
            ['rating' => 3, 'text' => 'Okay für den Preis, aber ich hatte etwas mehr erwartet. Tut was es soll.'],
            ['rating' => 5, 'text' => 'Perfektes Geschenk! War rechtzeitig zum Geburtstag da und das Kind war begeistert.'],
            ['rating' => 2, 'text' => 'Leider etwas enttäuschend. Das Material wirkt billiger als auf den Fotos.'],
            ['rating' => 4, 'text' => 'Schönes Produkt, gerne gekauft. Lieferung war schnell.'],
            ['rating' => 5, 'text' => 'Mega! Qualität stimmt, Preis-Leistung passt. Kann ich nur empfehlen.'],
            ['rating' => 3, 'text' => 'Solides Produkt, nichts Besonderes aber erfüllt seinen Zweck.'],
            ['rating' => 4, 'text' => 'Gut verarbeitet, hält was die Beschreibung verspricht. Bin zufrieden.'],
            ['rating' => 5, 'text' => 'Superschnelle Lieferung und Top-Qualität. Bin sehr zufrieden!'],
            ['rating' => 1, 'text' => 'Produkt kam defekt an. Schade, hätte mich auf die Bestellung gefreut.'],
            ['rating' => 4, 'text' => 'Macht genau was es soll. Mein Kind spielt täglich damit.'],
            ['rating' => 5, 'text' => 'Wunderbar! Besser als erwartet, sofort wieder bestellen.'],
            ['rating' => 3, 'text' => 'Ganz okay. Für Kinder die nicht so wählerisch sind sicher gut.'],
            ['rating' => 5, 'text' => 'Schnelle Lieferung, tolle Verpackung, begeistertes Kind – was will man mehr?'],
            ['rating' => 4, 'text' => 'Qualitativ gut, Preis stimmt. Kommt im Haushalt gut an.'],
            ['rating' => 2, 'text' => 'Nach 2 Wochen schon kaputt. Für den Preis hätte ich mehr erwartet.'],
            ['rating' => 5, 'text' => 'Einfach super. Haben schon 3x hier bestellt und waren immer zufrieden.'],
            ['rating' => 4, 'text' => 'Sehr schönes Produkt, macht optisch was her. Hält bisher gut.'],
            ['rating' => 3, 'text' => 'Mittelmäßig. Nicht schlecht, aber auch nichts Besonderes.'],
            ['rating' => 5, 'text' => 'Mein Favorit! Nutze es täglich und bin restlos begeistert.'],
            ['rating' => 4, 'text' => 'Ordentliche Verarbeitung, faire Lieferzeit. Empfehlung!'],
            ['rating' => 1, 'text' => 'Komplett falsche Größe geliefert. Sehr enttäuscht vom Service.'],
            ['rating' => 5, 'text' => 'Genau wie beschrieben, sehr zufrieden. Danke CultPlanet!'],
            ['rating' => 3, 'text' => 'Passt. Nicht mehr, nicht weniger. Macht seinen Job.'],
            ['rating' => 4, 'text' => 'Gutes Preis-Leistungs-Verhältnis. Würde wieder kaufen.'],
            ['rating' => 5, 'text' => 'Klare Kaufempfehlung! Top Qualität und super schnelle Lieferung.'],
            ['rating' => 2, 'text' => 'Farbe weicht stark vom Foto ab. Bin nicht ganz zufrieden.'],
            ['rating' => 5, 'text' => 'Für meinen Sohn zum Geburtstag – er ist total aus dem Häuschen. Danke!'],
            ['rating' => 4, 'text' => 'Alles gut, nur die Anleitung könnte besser sein. Produkt selbst ist super.'],
            ['rating' => 3, 'text' => 'Solide, aber nichts womit man angeben kann. Erfüllt den Zweck.'],
            ['rating' => 5, 'text' => 'Bin wirklich begeistert! Übertrifft meine Erwartungen bei weitem.'],
            ['rating' => 4, 'text' => 'Gute Qualität, sieht genau so aus wie auf den Fotos. Zufrieden!'],
        ];

        $erstellt = 0;
        $bewertungsIndex = 0;

        // durch alle produkte gehen und bewertungen verteilen
        foreach ($produkte as $produktIndex => $produkt) {
            // jedes produkt bekommt 1 bis 3 bewertungen
            $anzahl = ($produktIndex % 3) + 1;

            for ($i = 0; $i < $anzahl; $i++) {
                // reihum durch die kunden
                $kundeIndex = ($produktIndex * 3 + $i) % $kunden->count();
                $kunde = $kunden[$kundeIndex];

                // nicht doppelt bewerten (unique constraint)
                $bereitsBewertet = Review::where('user_id', $kunde->id)
                    ->where('product_id', $produkt->id)
                    ->exists();

                if ($bereitsBewertet) {
                    continue;
                }

                $bewertung = $bewertungen[$bewertungsIndex % count($bewertungen)];
                $bewertungsIndex++;

                Review::create([
                    'user_id'    => $kunde->id,
                    'product_id' => $produkt->id,
                    'rating'     => $bewertung['rating'],
                    'text'       => $bewertung['text'],
                ]);
                $erstellt++;
            }
        }

        $gesamt = Review::count();
        $this->command->info("ReviewSeeder fertig – {$erstellt} neue Bewertungen erstellt, {$gesamt} gesamt in der DB.");
    }
}
