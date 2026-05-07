<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Zuerst die Rollen anlegen, sonst schlägt assignRole fehl
        $this->call(RolesSeeder::class);

        // In Tests nur die Rollen seeden. Die Feature-Tests legen ihre
        // Produkte, Bestellungen und Bewertungen bewusst selbst an.
        if (app()->environment('testing')) {
            return;
        }

        // dummy-kunden für tests und demo
        $this->call(DummyCustomersSeeder::class);
        // demo-produkte anlegen – muss vor reviews und bestellungen laufen
        $this->call(ProductSeeder::class);
        // produkt-bewertungen von dummy-kunden
        $this->call(ReviewSeeder::class);
        // demo-bestellungen in verschiedenen szenarien
        $this->call(OrderSeeder::class);
        // bereiche und mitarbeiter für die mitarbeiterverwaltung
        $this->call(DepartmentSeeder::class);
    }
}
