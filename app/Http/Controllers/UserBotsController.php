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
        $validatedData = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'bot_id' => 'required|integer|exists:bots,id',
            'allocated_amount' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive'
        ]);

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
        $userBot = UserBot::findOrFail($id);

        $validatedData = $request->validate([
            'allocated_amount' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive'
        ]);

        $userBot->update($validatedData);

        return response()->json($userBot, 200);
    }

    public function destroy($id)
    {
        $userBot = UserBot::find($id);

        if (!$userBot) {
            return response()->json(['message' => 'User Bot not found'], 404);
        }

        $userBot->delete();

        return response()->json(null, 204);
    }

    public function getUserBotDetails($botId)
    {
        $userId = Auth::id();

        $userBot = UserBot::where('user_id', $userId)
            ->where('bot_id', $botId)
            ->first();

        if (!$userBot) {
            return response()->json(['message' => 'User Bot not found'], 404);
        }

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
        $activeUsers = User::whereHas('userBots', function ($query) use ($id) {
            $query->where('bot_id', $id)
                ->where('status', 'active');
        })->get();

        return response()->json($activeUsers, 200);
    }

    public function updateBalance(Request $request, $botId)
    {
        $userId = Auth::id();

        $userBot = UserBot::where('user_id', $userId)
            ->where('bot_id', $botId)
            ->first();

        if (!$userBot) {
            return response()->json(['message' => 'User Bot not found'], 404);
        }

        $validatedData = $request->validate([
            'allocated_amount' => 'required|numeric|min:0',
        ]);
        $userBot->timestamps = false;

        $userBot->allocated_amount = $validatedData['allocated_amount'];
        $userBot->save();
        $userBot->timestamps = true;
        return response()->json([
            'message' => 'Bot balance updated successfully.',
            'allocated_amount' => $userBot->allocated_amount,
        ], 200);
    }
}
