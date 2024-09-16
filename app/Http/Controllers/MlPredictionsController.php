<?php

namespace App\Http\Controllers;

use App\Models\MlPrediction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MlPredictionsController extends Controller
{
    public function index()
    {
        $predictions = MlPrediction::all();
        return response()->json($predictions);
    }

    public function store(Request $request)
    {
        $prediction = MlPrediction::create($request->all());
        return response()->json($prediction, 201);
    }

    public function storePrediction(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'stock_symbol' => 'required|string',
            'predicted_price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle file upload if provided
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/images');
        }

        // Create a new prediction record
        $mlPrediction = MlPrediction::create([
            'stock_symbol' => $validatedData['stock_symbol'],
            'predicted_price' => $validatedData['predicted_price'],
            'image_path' => $imagePath,
        ]);
        return response()->json($mlPrediction, 201);
    }

    public function getPredictions(Request $request)
    {
        // Get predictions filtered by stock_symbol if provided
        if ($request->has('stock_symbol')) {
            $predictions = MlPrediction::where('stock_symbol', $request->input('stock_symbol'))->get();
        } else {
            $predictions = MlPrediction::all();
        }

        return response()->json($predictions, 200);
    }

    public function show($id)
    {
        $prediction = MlPrediction::findOrFail($id);
        return response()->json($prediction);
    }

    public function update(Request $request, $id)
    {
        $prediction = MlPrediction::findOrFail($id);
        $prediction->update($request->all());
        return response()->json($prediction, 200);
    }

    public function destroy($id)
    {
        MlPrediction::destroy($id);
        return response()->json(null, 204);
    }
}
