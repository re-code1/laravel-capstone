<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\ReportCardController;
use App\Http\Controllers\Api\SectionController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/sections', [SectionController::class, 'index']);
    Route::get('/students/{student}/report-card', [ReportCardController::class, 'show']);
    Route::put('/grades/{grade}', [GradeController::class, 'update']);
});
