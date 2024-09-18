<?php

namespace App\Http\Controllers;

use App\Models\SentimentAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;  // Import the Validator facade

class SentimentAnalysisController extends Controller
{
    public function index()
    {
        $analyses = SentimentAnalysis::all();
        return response()->json($analyses);
    }

    public function store(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'stock_symbol' => 'required|string|max:10',
            'sentiment_score' => 'required|numeric|between:-100,100',
            'analysis_date' => 'required|date',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create sentiment analysis record
        $analysis = SentimentAnalysis::create([
            'stock_symbol' => $request->stock_symbol,
            'sentiment_score' => $request->sentiment_score,
            'analysis_date' => $request->analysis_date,
        ]);

        // Return the created record as a JSON response
        return response()->json($analysis, 201);
    }

    public function getLatestSentiment($stock_symbol)
    {
        // Fetch the latest sentiment entry for the provided stock symbol
        $latestSentiment = SentimentAnalysis::where('stock_symbol', $stock_symbol)
            ->orderBy('created_at', 'desc')
            ->first();

        // Check if a sentiment record was found
        if ($latestSentiment) {
            return response()->json($latestSentiment, 200);
        } else {
            return response()->json(['message' => 'No sentiment analysis found for the stock'], 404);
        }
    }

    public function getLatestSentimentBatch(Request $request)
    {
        // Get the stock symbols from the request (expecting an array of stock symbols)
        $stockSymbols = $request->input('stock_symbols');

        // Check if stock symbols were provided
        if (!$stockSymbols || !is_array($stockSymbols)) {
            return response()->json(['message' => 'Invalid or missing stock symbols'], 400);
        }

        // Fetch the latest sentiment for each stock symbol
        $latestSentiments = SentimentAnalysis::whereIn('stock_symbol', $stockSymbols)
            ->select('stock_symbol', DB::raw('MAX(created_at) as latest_created_at')) // Find the latest created_at for each stock
            ->groupBy('stock_symbol')
            ->get();

        // If no sentiments were found, return a 404 response
        if ($latestSentiments->isEmpty()) {
            return response()->json(['message' => 'No sentiment analysis found for the provided stock symbols'], 404);
        }

        return response()->json($latestSentiments, 200);
    }


    public function getTopStocksBySentiment()
    {
        $fiveDaysAgo = now()->subDays(5);

        // Subquery to get the latest sentiment score for each stock symbol
        $topStocks = SentimentAnalysis::select('stock_symbol', DB::raw('MAX(sentiment_score) as sentiment_score'))
            ->where('created_at', '>=', $fiveDaysAgo)
            ->groupBy('stock_symbol')  // Ensure we only get the latest entry per stock symbol
            ->orderBy('sentiment_score', 'desc')  // Order by highest sentiment score
            ->take(5)  // Limit to top 5 results
            ->get();

        if ($topStocks->isNotEmpty()) {
            return response()->json($topStocks, 200);
        } else {
            return response()->json(['message' => 'No sentiment analysis found in the last 5 days'], 404);
        }
    }

    public function getWorstStocksBySentiment()
    {
        $fiveDaysAgo = now()->subDays(5);

        // Query to get the worst 5 stocks by sentiment score, grouped by stock_symbol
        $worstStocks = SentimentAnalysis::where('created_at', '>=', $fiveDaysAgo)
            ->select('stock_symbol', DB::raw('MIN(sentiment_score) as sentiment_score'))  // Get the lowest sentiment score for each stock
            ->groupBy('stock_symbol')            // Group by stock_symbol to ensure unique symbols
            ->orderBy('sentiment_score', 'asc')  // Order by ascending sentiment score
            ->take(5)                            // Limit to 5 results
            ->get();

        // Check if any results were found
        if ($worstStocks->isNotEmpty()) {
            return response()->json($worstStocks, 200);
        } else {
            return response()->json(['message' => 'No sentiment analysis found in the last 5 days'], 404);
        }
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
