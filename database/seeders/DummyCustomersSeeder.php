<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DummyCustomersSeeder extends Seeder
{
    public function run(): void
    {
        // rolle "kunde" holen oder anlegen
        $kundeRolle = Role::firstOrCreate(['name' => 'kunde', 'guard_name' => 'web']);

        $kunden = [
            ['name' => 'Anna Müller',   'email' => 'anna.mueller@example.com'],
            ['name' => 'Ben Schmidt',   'email' => 'ben.schmidt@example.com'],
            ['name' => 'Clara Weber',   'email' => 'clara.weber@example.com'],
            ['name' => 'David Fischer', 'email' => 'david.fischer@example.com'],
            ['name' => 'Eva Becker',    'email' => 'eva.becker@example.com'],
        ];

        foreach ($kunden as $data) {
            // nur anlegen wenn die e-mail noch nicht existiert
            if (!User::where('email', $data['email'])->exists()) {
                $user = User::create([
                    'name'     => $data['name'],
                    'email'    => $data['email'],
                    'password' => Hash::make('password'),
                ]);
                $user->assignRole($kundeRolle);
            }
        }
    }
}
