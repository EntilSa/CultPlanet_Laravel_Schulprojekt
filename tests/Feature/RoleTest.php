<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    // prüfen ob alle 3 Rollen nach der Registrierung in der DB vorhanden sind
    public function test_die_drei_rollen_existieren_in_der_datenbank(): void
    {
        // Rollen anlegen wie es der Seeder macht
        Role::firstOrCreate(['name' => 'admin',       'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'mitarbeiter', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'kunde',       'guard_name' => 'web']);

        $this->assertDatabaseHas('roles', ['name' => 'admin']);
        $this->assertDatabaseHas('roles', ['name' => 'mitarbeiter']);
        $this->assertDatabaseHas('roles', ['name' => 'kunde']);
    }

    // prüfen ob ein neuer Nutzer nach der Registrierung die Rolle "kunde" hat
    public function test_neuer_nutzer_bekommt_rolle_kunde(): void
    {
        $this->post('/register', [
            'name'                  => 'Test Kunde',
            'email'                 => 'kunde@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        // den gerade registrierten Nutzer aus der DB holen
        $nutzer = \App\Models\User::where('email', 'kunde@example.com')->first();

        // prüfen ob er die Rolle "kunde" hat
        $this->assertTrue($nutzer->hasRole('kunde'));
    }

    // prüfen ob ein neuer Nutzer NICHT die Rolle "admin" hat
    public function test_neuer_nutzer_hat_keine_admin_rolle(): void
    {
        $this->post('/register', [
            'name'                  => 'Test Nutzer',
            'email'                 => 'nutzer@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $nutzer = \App\Models\User::where('email', 'nutzer@example.com')->first();

        $this->assertFalse($nutzer->hasRole('admin'));
    }
}
