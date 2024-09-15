<?php

use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SentimentAnalysisController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
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
});

Route::post('/sentiment-analysis', [SentimentAnalysisController::class, 'store']);
Route::get('/sentiment-analysis/{stock_symbol}', [SentimentAnalysisController::class, 'getLatestSentiment']);
Route::post('/store-alpaca-key', [ApiKeyController::class, 'storeAlpacaKey'])->middleware('auth:api');
Route::post('/get-account', [ApiKeyController::class, 'getAlpacaAccountDetails'])->middleware('auth:api');

Route::get('/top-sentiment-stocks', [SentimentAnalysisController::class, 'getTopStocksBySentiment']);
Route::get('/worst-sentiment-stocks', [SentimentAnalysisController::class, 'getWorstStocksBySentiment']);

Route::get('/open-positions', [ApiKeyController::class, 'getOpenPositions'])->middleware('auth:api');

