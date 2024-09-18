<?php

use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MlPredictionsController;
use App\Http\Controllers\SentimentAnalysisController;
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
    Route::get('/{stock_symbol}', [SentimentAnalysisController::class, 'getLatestSentiment']);
    Route::get('/top', [SentimentAnalysisController::class, 'getTopStocksBySentiment']);
    Route::get('/worst', [SentimentAnalysisController::class, 'getWorstStocksBySentiment']);
});

// Alpaca API Key and Account Management Routes (Authenticated)
Route::middleware('auth:api')->group(function () {
    Route::post('/store-alpaca-key', [ApiKeyController::class, 'storeAlpacaKey']);
    Route::get('/get-account', [ApiKeyController::class, 'getAlpacaAccountDetails']);
    Route::get('/get-portfolio-history', [ApiKeyController::class, 'getPortfolioHistory']);
    Route::get('/open-positions', [ApiKeyController::class, 'getOpenPositions']);
});

// Machine Learning Predictions Routes
Route::post('/ml-prediction', [MlPredictionsController::class, 'storePrediction'])->middleware('microservice.auth');
Route::get('/ml-predictions', [MlPredictionsController::class, 'getPredictions']);

