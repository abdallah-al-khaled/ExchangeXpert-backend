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

    public function store(Request $request)
    {
        $analysis = SentimentAnalysis::create($request->all());
        return response()->json($analysis, 201);
    }
}
