<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\KaryawanController;
use App\Http\Controllers\Web\AbsensiController;
use App\Http\Controllers\Web\GajiController;

// Homepage - Company Profile
Route::get('/', function () {
    return view('home');
})->name('home');

// Authentication routes
Route::group(['middleware' => 'guest'], function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout route (for authenticated users)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected dashboard routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Dashboard AJAX endpoints
    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics'])->name('dashboard.statistics');
    Route::get('/dashboard/activities', [DashboardController::class, 'getRecentActivities'])->name('dashboard.activities');
    Route::get('/dashboard/attendance-chart', [DashboardController::class, 'getAttendanceChart'])->name('dashboard.attendance-chart');
    
// Karyawan Management Routes
Route::resource('karyawan', KaryawanController::class);

// Karyawan API Proxy Routes
Route::prefix('karyawan/api')->group(function () {
    Route::get('/data', [KaryawanController::class, 'getData']);
    Route::get('/statistics', [KaryawanController::class, 'getStatistics']);
    Route::post('/store', [KaryawanController::class, 'store']);
    Route::get('/{id}', [KaryawanController::class, 'getKaryawan']);
    Route::put('/{id}', [KaryawanController::class, 'update']);
    Route::delete('/{id}/delete', [KaryawanController::class, 'destroy']);
    Route::post('/bulk-operation', [KaryawanController::class, 'bulkOperation']);
});

// Absensi Management Routes
Route::resource('absensi', AbsensiController::class);

// Absensi API Proxy Routes
Route::prefix('absensi/api')->group(function () {
    Route::get('/data', [AbsensiController::class, 'getData']);
    Route::get('/statistics', [AbsensiController::class, 'getStatistics']);
    Route::post('/store', [AbsensiController::class, 'store']);
    Route::get('/karyawan-list', [AbsensiController::class, 'getKaryawanList']);
    Route::get('/{id}', [AbsensiController::class, 'getAbsensi']);
    Route::put('/{id}', [AbsensiController::class, 'update']);
    Route::delete('/{id}/delete', [AbsensiController::class, 'destroy']);
    Route::post('/bulk-operation', [AbsensiController::class, 'bulkOperation']);
    Route::post('/{id}/cancel', [AbsensiController::class, 'cancel']);
    Route::get('/rekap/{karyawan_id}', [AbsensiController::class, 'getRekapKaryawan']);
    Route::get('/attendance-stats/{karyawan_id}', [AbsensiController::class, 'getAttendanceStats']);
    Route::get('/bonus-eligibility', [AbsensiController::class, 'getBonusEligibility']);
    Route::get('/year-end-bonus', [AbsensiController::class, 'getYearEndBonus']);
    Route::get('/company-rules', [AbsensiController::class, 'getCompanyRules']);
});

    // Gaji management routes
    Route::prefix('gaji')->name('gaji.')->group(function () {
        Route::get('/', [GajiController::class, 'index'])->name('index');
        Route::get('/create', [GajiController::class, 'create'])->name('create');
        Route::get('/{gaji}', [GajiController::class, 'show'])->name('show');
        Route::get('/{gaji}/edit', [GajiController::class, 'edit'])->name('edit');
    });

    // Gaji AJAX API routes
    Route::prefix('gaji/api')->name('gaji.api.')->group(function () {
        Route::get('/data', [GajiController::class, 'getData']);
        Route::get('/statistics', [GajiController::class, 'getStatistics']);
        Route::get('/karyawan-list', [GajiController::class, 'getKaryawanList']);
        Route::get('/salary-summary', [GajiController::class, 'getSalarySummary']);
        Route::post('/calculate', [GajiController::class, 'calculateGaji']);
        Route::post('/bulk-calculate', [GajiController::class, 'bulkCalculate']);
        Route::get('/{id}', [GajiController::class, 'getGaji']);
        Route::put('/{id}', [GajiController::class, 'updateGaji']);
        Route::delete('/{id}/delete', [GajiController::class, 'deleteGaji']);
    });

    // Future routes untuk management lain
    // Route::prefix('lembur')->name('lembur.')->group(function () {
    //     Route::get('/', [LemburController::class, 'index'])->name('index');
    // });
    
    // Route::prefix('aturan-perusahaan')->name('aturan.')->group(function () {
    //     Route::get('/', [AturanPerusahaanController::class, 'index'])->name('index');
    // });
});
