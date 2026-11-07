<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CoinGeckoController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\TvController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Ces routes sont chargées via le groupe "api" avec le middleware "api".
| Elles supportent JSON, rate limiting, etc. (docs Laravel) :contentReference[oaicite:1]{index=1}
|
*/

// Route publique pour infos utilisateur (auth sanctum)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// Route publique (bandeau ticker)
Route::get('/crypto-prices', [CoinGeckoController::class, 'ticker']);

// Actus (proxy + cache serveur — évite rate-limit CryptoCompare côté navigateur)
Route::get('/news', [NewsController::class, 'index']);

// TV live YouTube — résout le vrai videoId (live_stream?channel= est trop fragile)
Route::get('/tv/live', [TvController::class, 'live']);

// CoinGecko REST public endpoints
Route::get('/markets',   [CoinGeckoController::class, 'markets']);
Route::get('/price',     [CoinGeckoController::class, 'price']);
Route::get('/ohlc/{id}', [CoinGeckoController::class, 'ohlc']);
Route::get('/market_chart',  [CoinGeckoController::class, 'marketChart']); // ✅ ajout

// Route de test pour la connexion Flutter <-> Laravel
Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Connexion Laravel <-> Flutter OK ✅',
        'time' => now(),
    ]);
});

// Routes d'authentification
Route::get('/auth/test', [AuthController::class, 'test']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
