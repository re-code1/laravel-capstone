<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\ReportCardController;
use App\Http\Controllers\Api\SectionController;
use Illuminate\Support\Facades\Route;

// The registrar's front desk: anyone may ask for a token, but only 5 tries per minute.
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Everything else needs a valid token (the ID card).
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/sections', [SectionController::class, 'index']);
    Route::get('/sections/{section}/students', [SectionController::class, 'students']);
    Route::get('/students/{student}/report-card', [ReportCardController::class, 'show']);

    Route::put('/grades/{grade}', [GradeController::class, 'update']);
});
