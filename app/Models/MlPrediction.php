<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MlPrediction extends Model
{
    use HasFactory;

    protected $table = 'ml_predictions';
    protected $fillable = ['stock_symbol', 'predicted_price', 'prediction_date','image_path'];
}
