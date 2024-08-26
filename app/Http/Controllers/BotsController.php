<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use Illuminate\Http\Request;

class BotsController extends Controller
{
    public function index()
    {
        $bots = Bot::all();
        return response()->json($bots);
    }

    public function store(Request $request)
    {
        $bot = Bot::create($request->all());
        return response()->json($bot, 201);
    }

    public function show($id)
    {
        $bot = Bot::findOrFail($id);
        return response()->json($bot);
    }

    public function update(Request $request, $id)
    {
        $bot = Bot::findOrFail($id);
        $bot->update($request->all());
        return response()->json($bot, 200);
    }

    public function destroy($id)
    {
        Bot::destroy($id);
        return response()->json(null, 204);
    }
}
