<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    protected $fillable = ['auction_id', 'user_id', 'amount'];

    // ein gebot gehört zu einer auktion
    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }

    // ein gebot gehört zu einem nutzer
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
