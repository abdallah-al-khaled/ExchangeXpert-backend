<?php

namespace App\Http\Controllers;

use App\Models\MlPrediction;
use Illuminate\Http\Request;

class MlPredictionsController extends Controller
{
    public function index()
    {
        $predictions = MlPrediction::all();
        return response()->json($predictions);
    }

}
