<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\RfidController;

// RFID Management Routes
Route::middleware(['auth'])->prefix('rfid')->name('rfid.')->group(function () {
    // Web Routes
    Route::get('/', [RfidController::class, 'index'])->name('index');
    Route::get('/{id}', [RfidController::class, 'show'])->name('show');
    Route::delete('/{id}', [RfidController::class, 'destroy'])->name('destroy');
    
    // API Routes for AJAX
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/statistics', [RfidController::class, 'getStatistics'])->name('statistics');
        Route::get('/data', [RfidController::class, 'getData'])->name('data');
        Route::get('/available-employees', [RfidController::class, 'getAvailableEmployees'])->name('available_employees');
        Route::put('/{id}', [RfidController::class, 'update'])->name('update');
        Route::delete('/{id}', [RfidController::class, 'delete'])->name('delete');
        Route::post('/bulk-operation', [RfidController::class, 'bulkOperation'])->name('bulk_operation');
    });
});