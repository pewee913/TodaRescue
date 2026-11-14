<?php
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/fare-calculator', [PageController::class, 'calculator'])->name('calculator');
Route::get('/find-a-toda', [PageController::class, 'find'])->name('find');
Route::get('/fare-tables', [PageController::class, 'tables'])->name('tables');
