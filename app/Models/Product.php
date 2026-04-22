<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // diese felder dürfen per mass-assignment gefüllt werden (z.b. Product::create([...]))
    protected $fillable = ['name', 'description', 'price', 'image', 'stock'];

    // ein produkt kann viele bewertungen haben
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
