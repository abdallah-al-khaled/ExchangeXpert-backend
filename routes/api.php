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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    Route::get('user', 'user');
});

Route::prefix('sentiment-analysis')->group(function () {
    Route::post('/', [SentimentAnalysisController::class, 'store']);
    Route::get('/top', [SentimentAnalysisController::class, 'getTopStocksBySentiment']);
    Route::get('/worst', [SentimentAnalysisController::class, 'getWorstStocksBySentiment']);
    Route::get('/{stock_symbol}', [SentimentAnalysisController::class, 'getLatestSentiment']);
    Route::post('/get-latest-sentiments',[SentimentAnalysisController::class, 'getLatestSentimentBatch']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('/store-alpaca-key', [ApiKeyController::class, 'storeAlpacaKey']);
    Route::get('/get-account', [ApiKeyController::class, 'getAlpacaAccountDetails']);
    Route::get('/get-portfolio-history', [ApiKeyController::class, 'getPortfolioHistory']);
    Route::get('/open-positions', [ApiKeyController::class, 'getOpenPositions']);
    Route::get('/alpaca-configurations', [ApiKeyController::class, 'getConfigurations']);
    Route::post('/alpaca-configurations', [ApiKeyController::class, 'updateConfigurations']);
});

Route::post('/ml-prediction', [MlPredictionsController::class, 'storePrediction'])->middleware('microservice.auth');
Route::get('/ml-predictions', [MlPredictionsController::class, 'getPredictions']);

Route::post('/trade-signal/buy/{symbol}/{botId}', [TradesController::class, 'executeBuySignal']);
Route::get('/bots/{id}/latest-trades', [TradesController::class, 'latestTrades'])->middleware('auth:api');
Route::get('/bots/{botId}/user-trades', [TradesController::class, 'getUserTradesForBot'])->middleware('admin');

Route::get('/bots', [BotsController::class, 'index']);
Route::post('/bots', [BotsController::class, 'store']);
Route::get('/bots/{id}', [BotsController::class, 'show']);
Route::put('/bots/{id}', [BotsController::class, 'update']);
Route::delete('/bots/{id}', [BotsController::class, 'destroy']);


// List all user bots with user and bot information
Route::get('/user-bots', [UserBotsController::class, 'index']);

// Toggle activation status (active/inactive) for a specific user bot
Route::put('/user-bots/{botId}/toggle', [UserBotsController::class, 'toggleActivation'])->middleware('auth:api');
Route::post('/user-bots', [UserBotsController::class, 'store']);

Route::get('/user-bots/{botId}', [UserBotsController::class, 'getUserBotDetails'])->middleware('auth:api');

Route::get('/bots/{id}/users', [UserBotsController::class, 'getUsersUsingBot'])->middleware('auth:api');