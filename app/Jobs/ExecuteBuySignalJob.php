<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\UserBot;
use App\Models\ApiKey;
use App\Models\Trade;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExecuteBuySignalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userBot;
    public $stockSymbol;
    /**
     * Create a new job instance.
     */
    public function __construct(UserBot $userBot, $stockSymbol)
    {
        $this->userBot = $userBot;
        $this->stockSymbol = $stockSymbol;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $apiKeyRecord = $this->userBot->apiKey;

        if (!$apiKeyRecord) {
            Log::warning("User {$this->userBot->user_id} does not have Alpaca API keys.");
            return;
        }

        $apiKey = Crypt::decryptString($apiKeyRecord->api_key);
        $apiSecret = Crypt::decryptString($apiKeyRecord->api_secret);

        // Determine if the account is activated (live) or not (paper)
        $alpacaBaseUrl = $apiKeyRecord->is_activated
            ? 'https://api.alpaca.markets'  // Live Trading Account
            : 'https://paper-api.alpaca.markets';  // Paper Trading Account

        $alpacaUrl = "{$alpacaBaseUrl}/v2/stocks/bars/latest";

        // Fetch the latest stock price using the Alpaca API
        try {
            $client = new Client();
            $response = $client->request('GET', 'https://data.alpaca.markets/v2/stocks/bars/latest', [
                'headers' => [
                    'APCA-API-KEY-ID' => $apiKey,
                    'APCA-API-SECRET-KEY' => $apiSecret,
                    'accept' => 'application/json',
                ],
                'query' => [
                    'symbols' => $this->stockSymbol,
                    'feed' => 'iex',  // Using the IEX feed
                ],
            ]);

            $responseData = json_decode($response->getBody(), true);
            $stockPrice = $responseData['bars'][$this->stockSymbol]['c'] ?? null;

            if (!$stockPrice) {
                Log::error("Failed to fetch stock price for {$this->stockSymbol}");
                return;
            }

            // Calculate how many shares the user can buy based on their allocated amount
            $allocatedAmount = $this->userBot->allocated_amount;
            $quantity = floor($allocatedAmount / $stockPrice);

            if ($quantity <= 0) {
                Log::warning("User {$this->userBot->user_id} has insufficient funds to buy {$this->stockSymbol}");
                return;
            }

            // Place a buy order through the Alpaca API
            $alpacaOrderUrl = "{$alpacaBaseUrl}/v2/orders";
            $orderResponse = Http::withHeaders([
                'APCA-API-KEY-ID' => $apiKey,
                'APCA-API-SECRET-KEY' => $apiSecret,
                'accept' => 'application/json',
            ])->post($alpacaOrderUrl, [
                'symbol' => $this->stockSymbol,
                'qty' => $quantity,
                'side' => 'buy',
                'type' => 'market',
                'time_in_force' => 'gtc', // Good Till Cancelled
            ]);

            if ($orderResponse->failed()) {
                Log::error("Failed to place buy order for user {$this->userBot->user_id} for stock {$this->stockSymbol}");
                return;
            }

            // Save the trade record
            Trade::create([
                'user_bot_id' => $this->userBot->id,
                'stock_symbol' => $this->stockSymbol,
                'action' => 'buy',
                'quantity' => $quantity,
                'price' => $stockPrice,
                'buy_at' => now(),
            ]);

            Log::info("Successfully placed buy order for user {$this->userBot->user_id} for stock {$this->stockSymbol}");
        } catch (\Exception $e) {
            Log::error("Failed to execute buy signal for user {$this->userBot->user_id} for stock {$this->stockSymbol}: " . $e->getMessage());
        }
    }
}
