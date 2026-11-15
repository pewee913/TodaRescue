<?php
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\AdminTodaController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/fare-calculator', [PageController::class, 'calculator'])->name('calculator');
Route::get('/find-a-toda', [PageController::class, 'find'])->name('find');
Route::get('/fare-tables', [PageController::class, 'tables'])->name('tables');

// Admin TODA pinpoint management
Route::get('/admin/login', [AdminController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'authenticate'])->name('admin.authenticate');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

Route::middleware('admin')->group(function () {
    Route::get('/admin', [AdminController::class, 'map'])->name('admin.map');
    Route::get('/admin/todas', [AdminTodaController::class, 'index']);
    Route::post('/admin/todas', [AdminTodaController::class, 'store']);
    Route::put('/admin/todas/{toda}', [AdminTodaController::class, 'update']);
    Route::delete('/admin/todas/{toda}', [AdminTodaController::class, 'destroy']);
});
