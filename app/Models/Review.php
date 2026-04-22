<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['user_id', 'product_id', 'rating', 'text'];

    // eine bewertung gehört zu einem nutzer
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // eine bewertung gehört zu einem produkt
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
