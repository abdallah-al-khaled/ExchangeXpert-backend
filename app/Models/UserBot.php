<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBot extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'bot_id', 'allocated_amount', 'status'];
    protected $table = 'user_bots';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bot()
    {
        return $this->belongsTo(Bot::class);
    }
    
    public function apiKey()
    {
        return $this->hasOne(ApiKey::class, 'user_id', 'user_id');
    }
    public function trades()
    {
        return $this->hasMany(Trade::class);
    }
}
