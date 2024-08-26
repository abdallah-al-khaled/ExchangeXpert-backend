<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SentimentAnalysis extends Model
{
    use HasFactory;

    protected $fillable = ['stock_symbol', 'sentiment_score', 'analysis_date'];
}
