<?php

namespace App\Http\Controllers;

use App\Models\UserBot;
use Illuminate\Http\Request;

class UserBotsController extends Controller
{
    public function index()
    {
        $userBots = UserBot::with('user', 'bot')->get();
        return response()->json($userBots);
    }


    public function toggleActivation($id)
    {
        $userBot = UserBot::find($id);

        if (!$userBot) {
            return response()->json(['message' => 'User Bot not found'], 404);
        }

        $userBot->status = $userBot->status === 'active' ? 'inactive' : 'active';

        $userBot->save();

        return response()->json([
            'message' => 'User Bot status updated successfully',
            'user_bot' => $userBot,
        ], 200);
    }

    public function store(Request $request)
    {
        $userBot = UserBot::create($request->all());
        return response()->json($userBot, 201);
    }

    public function show($id)
    {
        $userBot = UserBot::with('user', 'bot')->findOrFail($id);
        return response()->json($userBot);
    }

    public function update(Request $request, $id)
    {
        $userBot = UserBot::findOrFail($id);
        $userBot->update($request->all());
        return response()->json($userBot, 200);
    }

    public function destroy($id)
    {
        UserBot::destroy($id);
        return response()->json(null, 204);
    }
}
