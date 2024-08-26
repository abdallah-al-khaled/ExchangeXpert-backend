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

    public function show($id)
    {
        $analysis = SentimentAnalysis::findOrFail($id);
        return response()->json($analysis);
    }

    public function update(Request $request, $id)
    {
        $analysis = SentimentAnalysis::findOrFail($id);
        $analysis->update($request->all());
        return response()->json($analysis, 200);
    }

    public function destroy($id)
    {
        SentimentAnalysis::destroy($id);
        return response()->json(null, 204);
    }
}
