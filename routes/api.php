<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\ProfileController;

Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [ProfileController::class, 'profile']);
    Route::post('/change-photo', [ProfileController::class, 'changePhoto']);
});
