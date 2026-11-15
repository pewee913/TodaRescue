<?php
use App\Http\Controllers\Api\{FareController, TodaController};
use Illuminate\Support\Facades\Route;

Route::get('/todas', [TodaController::class, 'index']);
Route::post('/fare', [FareController::class, 'calculate']);
