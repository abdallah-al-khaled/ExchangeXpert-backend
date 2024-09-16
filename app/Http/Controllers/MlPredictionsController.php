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
            'prediction_date' => 'required|date',
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
            'prediction_date' => $validatedData['prediction_date'],
            'image_path' => $imagePath,
        ]);

        return response()->json($mlPrediction, 201);
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
