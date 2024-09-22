<?php

namespace App\Http\Controllers;

use App\Models\UserBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserBotsController extends Controller
{
    public function index()
    {
        $userBots = UserBot::with('user', 'bot')->get();
        return response()->json($userBots);
    }


    public function toggleActivation(Request $request, $botId)
    {
        $userId = Auth::id();

        $userBot = UserBot::where('user_id', $userId)
            ->where('bot_id', $botId)
            ->first();

        if (!$userBot) {
            return response()->json(['message' => 'User Bot not found'], 404);
        }

        $userBot->status = $userBot->status === 'active' ? 'inactive' : 'active';

        $userBot->save();

        return response()->json([
            'user_bot' => ["status" => $userBot->status,]
        ], 200);
    }


    public function store(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'bot_id' => 'required|integer|exists:bots,id',
            'allocated_amount' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive'
        ]);

        // Create the user bot record
        $userBot = UserBot::create($validatedData);

        return response()->json($userBot, 201);
    }

    public function show($id)
    {
        $userBot = UserBot::with('user', 'bot')->find($id);

        if (!$userBot) {
            return response()->json(['message' => 'User Bot not found'], 404);
        }

        return response()->json($userBot, 200);
    }

    public function update(Request $request, $id)
    {
        // Find the user bot record
        $userBot = UserBot::findOrFail($id);

        // Validate the request
        $validatedData = $request->validate([
            'allocated_amount' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive'
        ]);

        // Update the user bot record
        $userBot->update($validatedData);

        return response()->json($userBot, 200);
    }

    public function destroy($id)
    {
        // Find the user bot record
        $userBot = UserBot::find($id);

        if (!$userBot) {
            return response()->json(['message' => 'User Bot not found'], 404);
        }

        // Delete the user bot record
        $userBot->delete();

        return response()->json(null, 204);
    }
}
