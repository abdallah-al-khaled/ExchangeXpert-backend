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
}
