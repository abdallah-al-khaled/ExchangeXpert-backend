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
}
