<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use Illuminate\Http\Request;

class TradesController extends Controller
{
    public function index()
    {
        $trades = Trade::with('userBot')->get();
        return response()->json($trades);
    }

    public function store(Request $request)
    {
        $trade = Trade::create($request->all());
        return response()->json($trade, 201);
    }

    public function show($id)
    {
        $trade = Trade::with('userBot')->findOrFail($id);
        return response()->json($trade);
    }

    public function update(Request $request, $id)
    {
        $trade = Trade::findOrFail($id);
        $trade->update($request->all());
        return response()->json($trade, 200);
    }

    public function destroy($id)
    {
        Trade::destroy($id);
        return response()->json(null, 204);
    }
}
