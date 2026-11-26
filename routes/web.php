<?php

use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\SectionController;
use Illuminate\Support\Facades\Route;

// The host at the front door: reads the URL and sends the visitor to the right waiter.
Route::redirect('/', '/sections');

Route::get('/sections', [SectionController::class, 'index']);
Route::get('/sections/{section}', [SectionController::class, 'show']);
Route::get('/students/{student}', [ReportCardController::class, 'show']);
