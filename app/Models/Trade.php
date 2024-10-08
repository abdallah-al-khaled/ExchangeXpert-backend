<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_bot_id', 'stock_symbol', 'action', 'quantity', 'price', 
        'buy_at', 'sold_at'
    ];

    public function userBot()
    {
        return $this->belongsTo(UserBot::class);
    }

    // To check if the trade is still open (i.e., not sold)
    public function isOpen()
    {
        return is_null($this->sold_at);
    }
}
