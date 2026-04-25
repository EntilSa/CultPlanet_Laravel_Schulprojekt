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
        // dummy-kunden für tests und demo
        $this->call(DummyCustomersSeeder::class);
    }
}
