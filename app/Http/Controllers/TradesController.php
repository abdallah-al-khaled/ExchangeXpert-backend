<?php

namespace App\Http\Controllers;

use App\Jobs\ExecuteBuySignalJob;
use App\Models\ApiKey;
use App\Models\Trade;
use App\Models\UserBot;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

    // Retrieve all unsold stocks for a user-bot relation
    public function unsoldStocks($userBotId)
    {
        $unsoldStocks = Trade::where('user_bot_id', $userBotId)
            ->whereNull('sold_at')
            ->get();
        return response()->json($unsoldStocks);
    }

    public function executeBuySignal($stockSymbol, $botId)
    {
        $activeUserBots = UserBot::with('apiKey')
            ->where('status', 'active')
            ->where('bot_id', $botId)
            ->get();
            // echo $activeUserBots;
        if ($activeUserBots->isEmpty()) {
            Log::warning("No active bots found for bot ID: {$botId}");
            return response()->json(['message' => 'No active bots found for this bot.'], 404);
        }

        foreach ($activeUserBots as $userBot) {
            ExecuteBuySignalJob::dispatch($userBot, $stockSymbol);  // Dispatch a job for each user bot
        }

        return response()->json(['message' => 'Buy signal executed for active users of bot ' . $botId], 200);
    }

    public function latestTrades($botId)
    {
        // Get the authenticated user's ID
        $userId = 10;

        // Find the user_bot record for the given user and bot
        $userBot = UserBot::where('user_id', $userId)
            ->where('bot_id', $botId)
            ->first();

        // Check if the user_bot exists
        if (!$userBot) {
            return response()->json(['message' => 'User Bot not found'], 404);
        }

        // Get the latest trades for the user_bot
        $latestTrades = Trade::where('user_bot_id', $userBot->id)
        ->where('action', '=', 'buy')
            ->orderBy('created_at', 'asc')
            ->take(5)  // Fetch the latest 5 trades, adjust the number as needed
            ->get();

        // Check if there are any trades
        if ($latestTrades->isEmpty()) {
            return response()->json(['message' => 'No trades found for this bot'], 404);
        }

        // Return the latest trades
        return response()->json($latestTrades, 200);
    }
}
