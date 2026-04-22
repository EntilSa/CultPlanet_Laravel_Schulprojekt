<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // diese felder dürfen per mass-assignment gefüllt werden (z.b. Product::create([...]))
    protected $fillable = ['name', 'description', 'price', 'image', 'stock', 'artikel_nr'];

    // nach jedem create() wird die artikelnummer automatisch gesetzt: id + 10000
    protected static function booted(): void
    {
        static::created(function ($product) {
            $product->update(['artikel_nr' => 10000 + $product->id]);
        });
    }

    // ein produkt kann viele bewertungen haben
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
