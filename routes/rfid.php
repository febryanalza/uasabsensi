<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\RfidController;

// Temporary test routes without auth for debugging
Route::get('/rfid-test-page', function() { return view('rfid.test'); });
Route::prefix('rfid-test')->name('rfid.test.')->group(function () {
    Route::get('/statistics', [RfidController::class, 'getStatistics'])->name('statistics');
    Route::get('/data', [RfidController::class, 'getData'])->name('data');
    Route::get('/count', [RfidController::class, 'testCount'])->name('count');
});

// RFID Management Routes
Route::middleware(['auth'])->prefix('rfid')->name('rfid.')->group(function () {
    // API Routes for AJAX (must be before {id} routes to avoid conflicts)
    Route::get('/data', [RfidController::class, 'getData'])->name('data');
    Route::get('/statistics', [RfidController::class, 'getStatistics'])->name('statistics');
    Route::get('/available-employees', [RfidController::class, 'getAvailableEmployees'])->name('available-employees');
    Route::post('/bulk-operation', [RfidController::class, 'bulkOperation'])->name('bulk-operation');
    
    // Debug routes
    Route::get('/debug', [RfidController::class, 'debug'])->name('debug');
    Route::get('/test-count', [RfidController::class, 'testCount'])->name('test-count');
    
    // Web Routes with dynamic {id} (must be last)
    Route::get('/', [RfidController::class, 'index'])->name('index');
    Route::get('/{id}/edit', [RfidController::class, 'edit'])->name('edit');
    Route::get('/{id}', [RfidController::class, 'show'])->name('show');
    Route::put('/{id}', [RfidController::class, 'update'])->name('update');
    Route::delete('/{id}', [RfidController::class, 'destroy'])->name('destroy');
});