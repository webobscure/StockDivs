<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DividendController;
use App\Http\Controllers\Api\PortfolioController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\WatchlistController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/stocks/search', [StockController::class, 'search']);
    Route::get('/stocks/{ticker}', [StockController::class, 'show']);
    Route::get('/stocks/{ticker}/quote', [StockController::class, 'quote']);
    Route::get('/stocks/{ticker}/dividends', [StockController::class, 'dividends']);

    Route::get('/portfolio', [PortfolioController::class, 'index']);
    Route::get('/portfolio/summary', [PortfolioController::class, 'summary']);
    Route::get('/portfolio/transactions', [PortfolioController::class, 'transactions']);
    Route::post('/portfolio/transactions', [PortfolioController::class, 'storeTransaction']);
    Route::put('/portfolio/transactions/{transaction}', [PortfolioController::class, 'updateTransaction']);
    Route::delete('/portfolio/transactions/{transaction}', [PortfolioController::class, 'deleteTransaction']);
    Route::get('/portfolio/{ticker}', [PortfolioController::class, 'showTicker']);

    Route::get('/watchlist', [WatchlistController::class, 'index']);
    Route::post('/watchlist', [WatchlistController::class, 'store']);
    Route::delete('/watchlist/{ticker}', [WatchlistController::class, 'destroy']);

    Route::get('/dividends', [DividendController::class, 'index']);
    Route::get('/dividends/calendar', [DividendController::class, 'calendar']);
    Route::get('/dividends/upcoming', [DividendController::class, 'upcoming']);
    Route::get('/dividends/summary', [DividendController::class, 'summary']);
    Route::get('/dividends/history/{ticker}', [DividendController::class, 'history']);

    Route::get('/alerts', [AlertController::class, 'index']);
    Route::post('/alerts', [AlertController::class, 'store']);
    Route::put('/alerts/{alert}', [AlertController::class, 'update']);
    Route::delete('/alerts/{alert}', [AlertController::class, 'destroy']);

    Route::get('/settings', [SettingsController::class, 'show']);
    Route::put('/settings', [SettingsController::class, 'update']);
});
