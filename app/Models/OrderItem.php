<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'name', 'price', 'quantity'];

    // eine position gehört zu einer bestellung
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // eine position verweist auf ein produkt
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
