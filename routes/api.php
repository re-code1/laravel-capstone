<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// The registrar's front desk: anyone may ask for a token, but only 5 tries per minute.
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Everything else needs a valid token (the ID card).
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
