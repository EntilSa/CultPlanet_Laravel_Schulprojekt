<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
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

        // adressen für verschiedene kunden – realistisch
        $adressen = [
            ['vorname' => 'Anna',   'nachname' => 'Müller',   'strasse' => 'Hauptstraße 12',      'plz' => '45127', 'ort' => 'Essen'],
            ['vorname' => 'Ben',    'nachname' => 'Schmidt',  'strasse' => 'Gartenweg 5',          'plz' => '47051', 'ort' => 'Duisburg'],
            ['vorname' => 'Clara',  'nachname' => 'Weber',    'strasse' => 'Lindenallee 88',       'plz' => '40213', 'ort' => 'Düsseldorf'],
            ['vorname' => 'David',  'nachname' => 'Fischer',  'strasse' => 'Am Markt 3',           'plz' => '44787', 'ort' => 'Bochum'],
            ['vorname' => 'Eva',    'nachname' => 'Becker',   'strasse' => 'Kirchstraße 21',       'plz' => '44135', 'ort' => 'Dortmund'],
        ];

        // 15 bestellungen – verschiedene szenarien
        $szenarien = [
            // --- normale bestellungen ---
            [
                'kunde_index' => 0, // Anna Müller
                'adresse_index' => 0,
                'zahlungsmethode' => 'paypal',
                'status' => 'bezahlt',
                'artikel' => [
                    ['produkt_index' => 0, 'menge' => 1], // 1x erstes produkt
                ],
                'notiz' => 'Normale Einzelbestellung, bezahlt via PayPal',
            ],
            [
                'kunde_index' => 1, // Ben Schmidt
                'adresse_index' => 1,
                'zahlungsmethode' => 'sofortueberweisung',
                'status' => 'versendet',
                'artikel' => [
                    ['produkt_index' => 1, 'menge' => 2], // 2x gleiche artikel
                    ['produkt_index' => 3, 'menge' => 1],
                ],
                'notiz' => 'Mehrere Artikel, versendet',
            ],
            [
                'kunde_index' => 2, // Clara Weber
                'adresse_index' => 2,
                'zahlungsmethode' => 'paypal',
                'status' => 'bezahlt',
                'artikel' => [
                    ['produkt_index' => 2, 'menge' => 3], // 3x gleiche artikel (größere menge)
                ],
                'notiz' => 'Größere Menge, Schenkung vermutlich',
            ],
            [
                'kunde_index' => 3, // David Fischer
                'adresse_index' => 3,
                'zahlungsmethode' => 'sofortueberweisung',
                'status' => 'offen',
                'artikel' => [
                    ['produkt_index' => 4, 'menge' => 1],
                    ['produkt_index' => 5, 'menge' => 1],
                    ['produkt_index' => 6, 'menge' => 2], // drei verschiedene produkte
                ],
                'notiz' => 'Sammelbestellung, noch offen (Zahlung ausständig)',
            ],
            [
                'kunde_index' => 4, // Eva Becker
                'adresse_index' => 4,
                'zahlungsmethode' => 'paypal',
                'status' => 'storniert',
                'artikel' => [
                    ['produkt_index' => 7, 'menge' => 1],
                ],
                'notiz' => 'Storniert – Kunde hat Bestellung zurückgezogen',
            ],
            // --- exotischere szenarien ---
            [
                'kunde_index' => 0, // Anna bestellt nochmal (mehrfachkunde)
                'adresse_index' => 0,
                'zahlungsmethode' => 'paypal',
                'status' => 'versendet',
                'artikel' => [
                    ['produkt_index' => 8, 'menge' => 5], // 5 stück – große menge
                    ['produkt_index' => 9, 'menge' => 5],
                ],
                'notiz' => 'Großbestellung – evtl. Wiederverkäufer oder Geschenke für viele',
            ],
            [
                'kunde_index' => 1, // Ben bestellt teures produkt
                'adresse_index' => 1,
                'zahlungsmethode' => 'sofortueberweisung',
                'status' => 'bezahlt',
                'artikel' => [
                    ['produkt_index' => 10, 'menge' => 1], // einzelnes teures produkt
                ],
                'notiz' => 'Einzelner teurer Artikel',
            ],
            [
                'kunde_index' => 2,
                'adresse_index' => 2,
                'zahlungsmethode' => 'paypal',
                'status' => 'bezahlt',
                'artikel' => [
                    ['produkt_index' => 0, 'menge' => 1],
                    ['produkt_index' => 2, 'menge' => 1],
                    ['produkt_index' => 4, 'menge' => 1],
                    ['produkt_index' => 6, 'menge' => 1],
                    ['produkt_index' => 8, 'menge' => 1], // viele verschiedene produkte (5)
                ],
                'notiz' => 'Viele verschiedene Artikel – Testen des Warenkorbs mit max. Diversität',
            ],
            [
                'kunde_index' => 3,
                'adresse_index' => 3,
                'zahlungsmethode' => 'sofortueberweisung',
                'status' => 'offen',
                'artikel' => [
                    ['produkt_index' => 11, 'menge' => 10], // sehr hohe stückzahl
                ],
                'notiz' => 'Extreme Menge – Stresstest Lagerbestand',
            ],
            [
                'kunde_index' => 4,
                'adresse_index' => 4,
                'zahlungsmethode' => 'paypal',
                'status' => 'bezahlt',
                'artikel' => [
                    ['produkt_index' => 12, 'menge' => 2],
                    ['produkt_index' => 13, 'menge' => 3],
                ],
                'notiz' => 'Normale Mehrfachbestellung, bezahlt',
            ],
            // --- grenzfälle ---
            [
                'kunde_index' => 0,
                'adresse_index' => 0,
                'zahlungsmethode' => 'sofortueberweisung',
                'status' => 'storniert',
                'artikel' => [
                    ['produkt_index' => 14, 'menge' => 1],
                    ['produkt_index' => 15, 'menge' => 2],
                ],
                'notiz' => 'Storniert nach Bestellung – zweite Stornierung für Statistik',
            ],
            [
                'kunde_index' => 1,
                'adresse_index' => 1,
                'zahlungsmethode' => 'paypal',
                'status' => 'versendet',
                'artikel' => [
                    ['produkt_index' => 16, 'menge' => 4],
                ],
                'notiz' => 'Bereits versendet, mittlere Menge',
            ],
            [
                'kunde_index' => 2,
                'adresse_index' => 2,
                'zahlungsmethode' => 'sofortueberweisung',
                'status' => 'bezahlt',
                'artikel' => [
                    ['produkt_index' => 17, 'menge' => 1],
                    ['produkt_index' => 18, 'menge' => 1],
                    ['produkt_index' => 19, 'menge' => 1], // alle letzten produkte
                ],
                'notiz' => 'Bestellung über die letzten Produkte im Sortiment',
            ],
            [
                'kunde_index' => 3,
                'adresse_index' => 3,
                'zahlungsmethode' => 'paypal',
                'status' => 'bezahlt',
                'artikel' => [
                    ['produkt_index' => 0, 'menge' => 2],
                    ['produkt_index' => 5, 'menge' => 1],
                    ['produkt_index' => 10, 'menge' => 1],
                    ['produkt_index' => 15, 'menge' => 3], // mix aus verschiedenen bereichen
                ],
                'notiz' => 'Gemischte Bestellung quer durchs Sortiment',
            ],
            [
                'kunde_index' => 4,
                'adresse_index' => 4,
                'zahlungsmethode' => 'sofortueberweisung',
                'status' => 'versendet',
                'artikel' => [
                    ['produkt_index' => 3, 'menge' => 6],
                    ['produkt_index' => 7, 'menge' => 2],
                ],
                'notiz' => 'Große Mengen, bereits versendet – alles ok',
            ],
        ];

        $erstellt = 0;

        foreach ($szenarien as $szenario) {
            $kundeIndex = $szenario['kunde_index'] % $kunden->count();
            $kunde = $kunden[$kundeIndex];
            $adresse = $adressen[$szenario['adresse_index'] % count($adressen)];

            // gesamtpreis berechnen
            $total = 0;
            $artikelListe = [];

            foreach ($szenario['artikel'] as $artikelDaten) {
                $produktIndex = $artikelDaten['produkt_index'] % $produkte->count();
                $produkt = $produkte[$produktIndex];
                $menge = $artikelDaten['menge'];
                $total += $produkt->price * $menge;

                $artikelListe[] = [
                    'produkt' => $produkt,
                    'menge' => $menge,
                ];
            }

            // bestellung erstellen
            $bestellung = Order::create([
                'user_id'         => $kunde->id,
                'vorname'         => $adresse['vorname'],
                'nachname'        => $adresse['nachname'],
                'strasse'         => $adresse['strasse'],
                'plz'             => $adresse['plz'],
                'ort'             => $adresse['ort'],
                'zahlungsmethode' => $szenario['zahlungsmethode'],
                'total'           => round($total, 2),
                'status'          => $szenario['status'],
            ]);

            // bestellpositionen erstellen
            foreach ($artikelListe as $artikel) {
                OrderItem::create([
                    'order_id'   => $bestellung->id,
                    'product_id' => $artikel['produkt']->id,
                    'name'       => $artikel['produkt']->name,
                    'price'      => $artikel['produkt']->price,
                    'quantity'   => $artikel['menge'],
                ]);
            }

            $erstellt++;
            $this->command->line("  Bestellung #{$bestellung->id}: {$szenario['notiz']}");
        }

        $gesamt = Order::count();
        $this->command->info("OrderSeeder fertig – {$erstellt} Bestellungen erstellt, {$gesamt} gesamt in der DB.");
    }
}
