<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // rolle "mitarbeiter" holen
        $mitarbeiterRolle = Role::firstOrCreate(['name' => 'mitarbeiter', 'guard_name' => 'web']);

        // 4 dummy-mitarbeiter anlegen
        $mitarbeiterDaten = [
            ['name' => 'Max Hoffmann',   'email' => 'max.hoffmann@cultplanet.de'],
            ['name' => 'Lisa Braun',     'email' => 'lisa.braun@cultplanet.de'],
            ['name' => 'Tom Schulze',    'email' => 'tom.schulze@cultplanet.de'],
            ['name' => 'Sara König',     'email' => 'sara.koenig@cultplanet.de'],
        ];

        $mitarbeiter = [];

        foreach ($mitarbeiterDaten as $data) {
            // nur anlegen wenn noch nicht vorhanden
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => Hash::make('password'),
                ]
            );

            // rolle zuweisen falls noch nicht gesetzt
            if (! $user->hasRole('mitarbeiter')) {
                $user->assignRole($mitarbeiterRolle);
            }

            $mitarbeiter[] = $user;
        }

        // bereiche anlegen
        $bereichsNamen = ['Lager', 'Verkauf', 'Kasse', 'Versand'];

        foreach ($bereichsNamen as $name) {
            // nur anlegen wenn noch nicht vorhanden
            Department::firstOrCreate(['name' => $name]);
        }

        // bereiche mit mitarbeitern belegen (realistisch verteilt)
        $lager   = Department::where('name', 'Lager')->first();
        $verkauf = Department::where('name', 'Verkauf')->first();
        $kasse   = Department::where('name', 'Kasse')->first();
        $versand = Department::where('name', 'Versand')->first();

        // lager: max + tom
        $lager->users()->syncWithoutDetaching([$mitarbeiter[0]->id, $mitarbeiter[2]->id]);

        // verkauf: lisa (allein zuständig)
        $verkauf->users()->syncWithoutDetaching([$mitarbeiter[1]->id]);

        // kasse: sara
        $kasse->users()->syncWithoutDetaching([$mitarbeiter[3]->id]);

        // versand: absichtlich leer lassen → warnsystem soll anspringen

        $this->command->info('DepartmentSeeder fertig – 4 Bereiche und 4 Mitarbeiter angelegt.');
        $this->command->line('  Versand ist absichtlich unbesetzt → Warnsystem aktiv.');
    }
}
