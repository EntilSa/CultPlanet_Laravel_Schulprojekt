<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Die 3 Rollen anlegen die wir im Shop brauchen
        // firstOrCreate bedeutet: nur anlegen wenn noch nicht vorhanden
        Role::firstOrCreate(['name' => 'admin',       'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'mitarbeiter', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'kunde',       'guard_name' => 'web']);
    }
}
