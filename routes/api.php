<?php

use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BotsController;
use App\Http\Controllers\MlPredictionsController;
use App\Http\Controllers\SentimentAnalysisController;
use App\Http\Controllers\TradesController;
use App\Http\Controllers\UserBotsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Register API routes for the application. These routes are loaded by the
| RouteServiceProvider and are assigned to the "api" middleware group.
|
*/

// User Route (Authenticated)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication Routes
Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});

// Sentiment Analysis Routes
Route::prefix('sentiment-analysis')->group(function () {
    Route::post('/', [SentimentAnalysisController::class, 'store']);
    Route::get('/top', [SentimentAnalysisController::class, 'getTopStocksBySentiment']);
    Route::get('/worst', [SentimentAnalysisController::class, 'getWorstStocksBySentiment']);
    Route::get('/{stock_symbol}', [SentimentAnalysisController::class, 'getLatestSentiment']);
});

// Alpaca API Key and Account Management Routes (Authenticated)
Route::middleware('auth:api')->group(function () {
    Route::post('/store-alpaca-key', [ApiKeyController::class, 'storeAlpacaKey']);
    Route::get('/get-account', [ApiKeyController::class, 'getAlpacaAccountDetails']);
    Route::get('/get-portfolio-history', [ApiKeyController::class, 'getPortfolioHistory']);
    Route::get('/open-positions', [ApiKeyController::class, 'getOpenPositions']);
    Route::get('/alpaca-configurations', [ApiKeyController::class, 'getConfigurations']);
    Route::post('/alpaca-configurations', [ApiKeyController::class, 'updateConfigurations']);
});

// Machine Learning Predictions Routes
Route::post('/ml-prediction', [MlPredictionsController::class, 'storePrediction'])->middleware('microservice.auth');
Route::get('/ml-predictions', [MlPredictionsController::class, 'getPredictions']);

Route::post('/trade-signal/buy/{symbol}/{botId}', [TradesController::class, 'executeBuySignal']);
Route::get('/bots/{id}/latest-trades', [TradesController::class, 'latestTrades'])->middleware('auth:api');

// {
//     "dtbp_check": "entry",
//     "fractional_trading": true,
//     "max_margin_multiplier": "4",
//     "no_shorting": false,
//     "pdt_check": "entry",
//     "ptp_no_exception_entry": false,
//     "suspend_trade": false,
//     "trade_confirm_email": "all"
// }

Route::get('/bots', [BotsController::class, 'index']);
Route::post('/bots', [BotsController::class, 'store']);
Route::get('/bots/{id}', [BotsController::class, 'show']);
Route::put('/bots/{id}', [BotsController::class, 'update']);
Route::delete('/bots/{id}', [BotsController::class, 'destroy']);


// List all user bots with user and bot information
Route::get('/user-bots', [UserBotsController::class, 'index']);

// Toggle activation status (active/inactive) for a specific user bot
Route::put('/user-bots/{botId}/toggle', [UserBotsController::class, 'toggleActivation'])->middleware('auth:api');
// Store a new user bot
Route::post('/user-bots', [UserBotsController::class, 'store']);

// Show a specific user bot by ID with user and bot relationships
Route::get('/user-bots/{botId}', [UserBotsController::class, 'getUserBotDetails'])->middleware('auth:api');

// Update a specific user bot by ID
Route::put('/user-bots/{id}', [UserBotsController::class, 'update']);

// Delete a specific user bot by ID
Route::delete('/user-bots/{id}', [UserBotsController::class, 'destroy']);