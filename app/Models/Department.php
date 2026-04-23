<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name'];

    // ein bereich hat viele mitarbeiter (many-to-many über department_user)
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    // hilfsmethode: ist dieser bereich unbesetzt?
    public function istUnbesetzt(): bool
    {
        return $this->users()->count() === 0;
    }
}
