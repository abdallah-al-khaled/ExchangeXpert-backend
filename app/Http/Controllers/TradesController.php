<?php

namespace App\Http\Controllers;

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

    public function executeBuySignal($stockSymbol)
    {
        // Fetch all users with active bots
        $activeUserBots = UserBot::with('apiKey')->where('status', 'active')->get();
        echo $activeUserBots;
        // Loop through each active user bot
        foreach ($activeUserBots as $userBot) {
            // Get the user's API keys
            $apiKeyRecord = $userBot->apiKey;

            if (!$apiKeyRecord) {
                Log::warning("User {$userBot->user_id} does not have Alpaca API keys.");
                continue;
            }

            $apiKey = Crypt::decryptString($apiKeyRecord->api_key);
            $apiSecret = Crypt::decryptString($apiKeyRecord->api_secret);

            // Check if the account is activated (live) or not (paper)
            $alpacaBaseUrl = $apiKeyRecord->is_activated
                ? 'https://api.alpaca.markets'  // Live Trading Account
                : 'https://paper-api.alpaca.markets';  // Paper Trading Account

            $alpacaUrl = "{$alpacaBaseUrl}/v2/stocks/bars/latest";

            // Alpaca API Client
            $client = new Client();
            try {
                // Make API call to fetch the latest stock price
                $response = $client->request('GET', 'https://data.alpaca.markets/v2/stocks/bars/latest', [
                    'headers' => [
                        'APCA-API-KEY-ID' => $apiKey,
                        'APCA-API-SECRET-KEY' => $apiSecret,
                        'accept' => 'application/json',
                    ],
                    'query' => [
                        'symbols' => $stockSymbol,
                        'feed' => 'iex',  // Using the IEX feed
                    ],
                ]);

                $responseData = json_decode($response->getBody(), true);

                if (!isset($responseData['bars'][$stockSymbol])) {
                    Log::error("Failed to fetch stock price for {$stockSymbol}");
                    continue;
                }
                $stockPrice = $responseData['bars'][$stockSymbol]['c'];
            } catch (\Exception $e) {
                Log::error("Failed to fetch stock price for {$stockSymbol}. Error: " . $e->getMessage());
                continue;
            }

            // Calculate how many shares the user can buy based on their allocated amount
            $allocatedAmount = $userBot->allocated_amount;
            $quantity = floor($allocatedAmount / $stockPrice);

            if ($quantity <= 0) {
                Log::warning("User {$userBot->user_id} has insufficient funds to buy {$stockSymbol}");
                continue;
            }

            // Place a buy order through the Alpaca API (live or paper based on is_activated)
            $alpacaOrderUrl = "{$alpacaBaseUrl}/v2/orders";
            $orderResponse = Http::withHeaders([
                'APCA-API-KEY-ID' => $apiKey,
                'APCA-API-SECRET-KEY' => $apiSecret,
                'accept' => 'application/json',
            ])->post($alpacaOrderUrl, [
                'symbol' => $stockSymbol,
                'qty' => $quantity,
                'side' => 'buy',
                'type' => 'market',
                'time_in_force' => 'gtc', // Good Till Cancelled
            ]);

            if ($orderResponse->failed()) {
                Log::error("Failed to place buy order for user {$userBot->user_id} for stock {$stockSymbol}");
                continue;
            }

            // Save the trade record in the database
            Trade::create([
                'user_bot_id' => $userBot->id,
                'stock_symbol' => $stockSymbol,
                'action' => 'buy',
                'quantity' => $quantity,
                'price' => $stockPrice,
                'buy_at' => now(),
            ]);

            Log::info("Successfully placed buy order for user {$userBot->user_id} for stock {$stockSymbol}");
        }

        return response()->json(['message' => 'Buy signal executed for active users.'], 200);
    }
}
