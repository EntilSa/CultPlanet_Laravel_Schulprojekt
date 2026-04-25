<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'kundennummer'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    // HasRoles kommt von Spatie – damit kann man dem User eine Rolle zuweisen
    use HasFactory, HasRoles, Notifiable;

    // nach jedem create() wird die kundennummer automatisch gesetzt: id + 20000
    protected static function booted(): void
    {
        static::created(function ($user) {
            $user->update(['kundennummer' => 20000 + $user->id]);
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ein nutzer kann viele bestellungen haben
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // ein nutzer kann viele gebote abgeben
    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    // ein mitarbeiter kann in mehreren bereichen sein (many-to-many)
    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }
}
