<?php

namespace App\Http\Controllers;

use App\Jobs\ExecuteBuySignalJob;
use App\Models\ApiKey;
use App\Models\Trade;
use App\Models\UserBot;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
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

        if ($activeUserBots->isEmpty()) {
            Log::warning("No active bots found for bot ID: {$botId}");
            return response()->json(['message' => 'No active bots found for this bot.'], 404);
        }

        foreach ($activeUserBots as $userBot) {
            ExecuteBuySignalJob::dispatch($userBot, $stockSymbol);  // Dispatch a job for each user bot
        }

        return response()->json(['message' => 'Buy signal executed for active users of bot ' . $botId], 200);
    }
}
