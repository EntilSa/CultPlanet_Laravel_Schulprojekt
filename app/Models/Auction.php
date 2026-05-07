<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    // fillable: diese felder dürfen per create() oder update() befüllt werden
    // schützt vor "mass assignment" – ohne fillable könnte jemand beliebige felder aus einem formular setzen
    protected $fillable = [
        'product_id', 'start_price', 'start_time', 'end_time',
        'winner_id', 'winning_bid', 'status',
    ];

    // casts: laravel konvertiert diese felder automatisch beim laden aus der datenbank
    // ohne casts wären start_time und end_time einfache strings – mit casts sind es carbon-objekte
    // carbon ermöglicht dann z.b. $auction->end_time->format('d.m.Y') oder ->isAfter(now())
    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    // beziehung: eine auktion gehört zu genau einem produkt (foreign key: product_id)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // beziehung: der gewinner ist ein nutzer – second parameter nötig weil der fremdschlüssel 'winner_id' heißt
    // ohne den zweiten parameter würde laravel nach 'user_id' suchen
    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    // beziehung: eine auktion hat viele gebote (one-to-many)
    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    // gibt das aktuell höchste gebot zurück – oder den startpreis wenn noch niemand geboten hat
    // max('amount') sucht den höchsten wert in der 'amount'-spalte aller gebote dieser auktion
    // ?? ist der "null coalescing operator": falls max() null zurückgibt (keine gebote), nimm start_price
    public function hoechstGebot(): float
    {
        return $this->bids()->max('amount') ?? $this->start_price;
    }

    // gibt den nutzer zurück der gerade das höchste gebot hält
    // orderByDesc('amount') sortiert absteigend, first() nimmt den ersten (= höchsten)
    // ?-> ist der "nullsafe operator": falls kein gebot existiert, kein fehler sondern null
    public function hoechstBieter()
    {
        $bid = $this->bids()->orderByDesc('amount')->first();

        return $bid?->user;
    }

    // prüft ob die auktion gerade im aktiven zeitraum liegt (start ≤ jetzt ≤ ende)
    // between() ist eine carbon-hilfsmethode für genau diese prüfung
    public function laeuft(): bool
    {
        return now()->between($this->start_time, $this->end_time);
    }

    // prüft ob die auktion abgelaufen ist (end_time liegt in der vergangenheit)
    // isAfter() vergleicht zwei zeitpunkte
    public function abgelaufen(): bool
    {
        return now()->isAfter($this->end_time);
    }
}
