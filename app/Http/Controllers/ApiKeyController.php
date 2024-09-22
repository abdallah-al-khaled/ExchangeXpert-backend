<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use GuzzleHttp\Client;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeAlpacaKey(Request $request)
    {
        // Validate the request
        $request->validate([
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
        ]);

        $userId = Auth::id();

        $apiKey = ApiKey::where('user_id', $userId)->first();

        if ($apiKey) {
            // If the API key record exists, update it
            $apiKey->api_key = Crypt::encryptString($request->api_key);
            $apiKey->api_secret = Crypt::encryptString($request->api_secret);
            $apiKey->save();

            return response()->json(['message' => 'API key updated successfully'], 200);
        } else {
            // If no API key record exists, create a new one
            $newApiKey = new ApiKey();
            $newApiKey->user_id = $userId;
            $newApiKey->api_key = Crypt::encryptString($request->api_key);  // Encrypt and store the API key
            $newApiKey->api_secret = Crypt::encryptString($request->api_secret);  // Encrypt and store the API secret
            $newApiKey->save();

            return response()->json(['message' => 'API key stored successfully'], 201);
        }
    }

    public function getAlpacaAccountDetails()
    {
        $userId = Auth::id();
        $apiKeyRecord = ApiKey::where('user_id', $userId)->first();

        if (!$apiKeyRecord) {
            return response()->json(['error' => 'API keys not found for the user'], 404);
        }

        $apiKey = Crypt::decryptString($apiKeyRecord->api_key);
        $apiSecret = Crypt::decryptString($apiKeyRecord->api_secret);

        // Determine if the user is using live trading or paper trading based on 'is_activated' flag
        $baseUrl = $apiKeyRecord->is_activated ? 'https://api.alpaca.markets/v2/account' : 'https://paper-api.alpaca.markets/v2/account';

        // Make the request to Alpaca API using Guzzle
        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->request('GET', $baseUrl, [
                'headers' => [
                    'APCA-API-KEY-ID' => $apiKey,
                    'APCA-API-SECRET-KEY' => $apiSecret,
                    'accept' => 'application/json',
                ],
            ]);

            // Return the response from Alpaca API as JSON
            return response()->json(json_decode($response->getBody(), true), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve data from Alpaca API', 'details' => $e->getMessage()], 500);
        }
    }

    public function getOpenPositions()
    {
        $userId = Auth::id();

        $apiKeyRecord = ApiKey::where('user_id', $userId)->first();

        // Check if API keys are available
        if (!$apiKeyRecord) {
            return response()->json(['error' => 'API keys not found for the user'], 404);
        }

        $apiKey = Crypt::decryptString($apiKeyRecord->api_key);
        $apiSecret = Crypt::decryptString($apiKeyRecord->api_secret);

        // Determine if the user is activated for live or paper trading
        $baseUrl = $apiKeyRecord->is_activated
            ? 'https://api.alpaca.markets/v2/positions'  // Live trading
            : 'https://paper-api.alpaca.markets/v2/positions';  // Paper trading

        // Make the request to the Alpaca API using Guzzle
        $client = new Client();

        try {
            $response = $client->request('GET', $baseUrl, [
                'headers' => [
                    'APCA-API-KEY-ID' => $apiKey,
                    'APCA-API-SECRET-KEY' => $apiSecret,
                    'accept' => 'application/json',
                ],
            ]);

            // Return the response from Alpaca API as JSON
            return response()->json(json_decode($response->getBody(), true), 200);
        } catch (\Exception $e) {
            // Handle any errors, return a 500 Internal Server Error response
            return response()->json([
                'error' => 'Failed to retrieve open positions from Alpaca API',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPortfolioHistory()
    {
        $userId = Auth::id();
        $apiKeyRecord = ApiKey::where('user_id', $userId)->first();

        if (!$apiKeyRecord) {
            return response()->json(['error' => 'API keys not found for the user'], 404);
        }

        // Decrypt the API key and secret
        $apiKey = Crypt::decryptString($apiKeyRecord->api_key);
        $apiSecret = Crypt::decryptString($apiKeyRecord->api_secret);

        // Determine if this is a paper trading account or a live account
        $baseUrl = $apiKeyRecord->is_active ? 'https://api.alpaca.markets' : 'https://paper-api.alpaca.markets';

        // Make the request to Alpaca API using Guzzle
        $client = new Client();


        try {
            $response = $client->request('GET', "$baseUrl/v2/account/portfolio/history", [
                'headers' => [
                    'APCA-API-KEY-ID' => $apiKey,
                    'APCA-API-SECRET-KEY' => $apiSecret,
                    'accept' => 'application/json',
                ],
                'query' => [
                    'period' => '2D',
                    'timeframe' => '1Min',
                    'intraday_reporting' => 'market_hours',
                    'pnl_reset' => 'per_day'
                ]
            ]);

            // Return the response from Alpaca API as JSON
            return response()->json(json_decode($response->getBody(), true), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve data from Alpaca API', 'details' => $e->getMessage()], 500);
        }
    }

    public function getConfigurations(Request $request)
    {
        $userId = Auth::id();
        $apiKeyRecord = ApiKey::where('user_id', $userId)->first();

        if (!$apiKeyRecord) {
            return response()->json(['error' => 'API keys not found for the user'], 404);
        }

        // Decrypt the API key and secret
        $apiKey = Crypt::decryptString($apiKeyRecord->api_key);
        $apiSecret = Crypt::decryptString($apiKeyRecord->api_secret);


        $client = new Client();
        $response = $client->request('GET', 'https://paper-api.alpaca.markets/v2/account/configurations', [
            'headers' => [
                'APCA-API-KEY-ID' => $apiKey,
                'APCA-API-SECRET-KEY' => $apiSecret,
                'accept' => 'application/json',
            ],
        ]);

        $configurations = json_decode($response->getBody(), true);
        return response()->json($configurations);
    }

    public function updateConfigurations(Request $request)
    {
        $userId = Auth::id();
        $apiKeyRecord = ApiKey::where('user_id', $userId)->first();

        if (!$apiKeyRecord) {
            return response()->json(['error' => 'API keys not found for the user'], 404);
        }

        // Decrypt the API key and secret
        $apiKey = Crypt::decryptString($apiKeyRecord->api_key);
        $apiSecret = Crypt::decryptString($apiKeyRecord->api_secret);

        $client = new Client();
        $response = $client->request('PATCH', 'https://paper-api.alpaca.markets/v2/account/configurations', [
            'headers' => [
                'APCA-API-KEY-ID' => $apiKey,
                'APCA-API-SECRET-KEY' => $apiSecret,
                'accept' => 'application/json',
            ],
            'json' => [
                'dtbp_check' => $request->input('dtbp_check'),
                'fractional_trading' => filter_var($request->input('fractional_trading'), FILTER_VALIDATE_BOOLEAN),
                'max_margin_multiplier' => $request->input('max_margin_multiplier'),
                'no_shorting' => filter_var($request->input('no_shorting'), FILTER_VALIDATE_BOOLEAN),
                'pdt_check' => $request->input('pdt_check'),
                'suspend_trade' => filter_var($request->input('suspend_trade'), FILTER_VALIDATE_BOOLEAN),
                'trade_confirm_email' => $request->input('trade_confirm_email'),
            ],
        ]);
        // filter_var($validatedData['no_shorting'], FILTER_VALIDATE_BOOLEAN);

        $updatedConfigurations = json_decode($response->getBody(), true);
        return response()->json([
            'dtbp_check' => $request->input('dtbp_check'),
            'fractional_trading' => $request->input('fractional_trading'),
            'max_margin_multiplier' => $request->input('max_margin_multiplier'),
            'no_shorting' => $request->input('no_shorting'),
            'pdt_check' => $request->input('pdt_check'),
            'suspend_trade' => $request->input('suspend_trade'),
            'trade_confirm_email' => $request->input('trade_confirm_email'),
        ], 200);
    }

    



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
