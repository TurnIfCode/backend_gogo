<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\CoinController;
use App\Http\Controllers\API\UserTopupTransactionController;
use App\Http\Controllers\API\LiveStreamController;

Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);

Route::middleware('auth:api')->group(function () {
    Route::get('/profile/{id}', [ProfileController::class, 'profile']);
    Route::post('/change-photo', [ProfileController::class, 'changePhoto']);
    Route::get('/wallet/{user_id}', [WalletController::class, 'getWalletByUserId']);
    Route::post('/topup', [UserTopupTransactionController::class, 'topup']);
    Route::get('/topup-list', [UserTopupTransactionController::class, 'list']);
    Route::post('/topup/{id}/cancel', [UserTopupTransactionController::class, 'cancel']);
    Route::post('/topup/{id}/upload', [UserTopupTransactionController::class, 'upload']);
    Route::post('/live-stream/start', [LiveStreamController::class, 'start']);
    Route::post('/live-stream/end', [LiveStreamController::class, 'end']);
    Route::get('/live-stream/list', [LiveStreamController::class, 'list']);
});

Route::get('/coin', [CoinController::class, 'coin']);
