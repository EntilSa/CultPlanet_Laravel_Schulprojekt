<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    protected $fillable = [
        'product_id', 'start_price', 'start_time', 'end_time',
        'winner_id', 'winning_bid', 'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    // ein auktions-artikel gehört zu einem produkt
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // der gewinner ist ein user
    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    // eine auktion hat viele gebote
    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    // hilfsmethode: aktuelles höchstgebot oder startpreis wenn noch kein gebot
    public function hoechstGebot(): float
    {
        return $this->bids()->max('amount') ?? $this->start_price;
    }

    // hilfsmethode: wer bietet gerade am höchsten
    public function hoechstBieter()
    {
        $bid = $this->bids()->orderByDesc('amount')->first();
        return $bid?->user;
    }

    // prüfen ob die auktion gerade läuft (zeitraum aktiv)
    public function laeuft(): bool
    {
        return now()->between($this->start_time, $this->end_time);
    }

    // prüfen ob die auktion abgelaufen ist
    public function abgelaufen(): bool
    {
        return now()->isAfter($this->end_time);
    }
}
