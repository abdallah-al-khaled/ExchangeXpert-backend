<?php

namespace App\Http\Controllers;

use App\Models\SentimentAnalysis;
use Illuminate\Http\Request;

class SentimentAnalysisController extends Controller
{
    public function index()
    {
        $analyses = SentimentAnalysis::all();
        return response()->json($analyses);
    }
}
