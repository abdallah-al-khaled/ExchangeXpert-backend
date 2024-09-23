<?php

namespace App\Http\Controllers;

use App\Models\User;
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

    public function getUserBotDetails($botId)
    {
        // Get the authenticated user's ID
        $userId = Auth::id();

        // Find the user_bot record for the given user and bot
        $userBot = UserBot::where('user_id', $userId)
            ->where('bot_id', $botId)
            ->first();

        // Check if the user_bot exists
        if (!$userBot) {
            return response()->json(['message' => 'User Bot not found'], 404);
        }

        // Return the user_bot details
        return response()->json([
            'user_bot_id' => $userBot->id,
            'status' => $userBot->status,
            'allocated_amount' => $userBot->allocated_amount,
            'created_at' => $userBot->created_at,
            'updated_at' => $userBot->updated_at
        ], 200);
    }
    public function getUsersUsingBot($id)
    {
        // Find all users associated with the bot where the status is 'active'
        $activeUsers = User::whereHas('userBots', function ($query) use ($id) {
            $query->where('bot_id', $id)
                ->where('status', 'active');
        })->get();

        // Return the users as a JSON response
        return response()->json($activeUsers, 200);
    }
}
