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
