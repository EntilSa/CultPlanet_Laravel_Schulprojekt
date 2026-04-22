<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'vorname', 'nachname', 'strasse', 'plz', 'ort',
        'zahlungsmethode', 'total', 'status',
    ];

    // eine bestellung gehört zu einem nutzer
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // eine bestellung hat viele positionen
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
