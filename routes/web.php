<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\KaryawanController;
use App\Http\Controllers\Web\AbsensiController;
use App\Http\Controllers\Web\GajiController;
use App\Http\Controllers\Web\AturanPerusahaanController;

// Homepage - Company Profile (Main)
Route::get('/', function () {
    try {
        return view('home'); // Main company homepage
    } catch (\Exception $e) {
        Log::error('Homepage error: ' . $e->getMessage());
        return response('Server temporarily unavailable', 503);
    }
})->name('home');

// Welcome/Alternative Route
Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');

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
    
    // Dashboard AJAX endpoints (optimized)
    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics'])->name('dashboard.statistics');
    Route::get('/dashboard/activities', [DashboardController::class, 'getRecentActivities'])->name('dashboard.activities');
    Route::get('/dashboard/attendance-chart', [DashboardController::class, 'getAttendanceChart'])->name('dashboard.attendance-chart');
    
    // Karyawan management routes
    Route::prefix('karyawan')->name('karyawan.')->group(function () {
        // View routes
        Route::get('/', [KaryawanController::class, 'index'])->name('index');
        Route::get('/create', [KaryawanController::class, 'create'])->name('create');
        Route::get('/{id}', [KaryawanController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [KaryawanController::class, 'edit'])->name('edit');
        
        // AJAX API routes
        Route::post('/store', [KaryawanController::class, 'store'])->name('store');
        Route::put('/{id}', [KaryawanController::class, 'update'])->name('update');
        Route::delete('/{id}', [KaryawanController::class, 'destroy'])->name('destroy');
        
        // API endpoints for AJAX data (now all handled by Web controller)
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/', [KaryawanController::class, 'index'])->name('index'); // List all karyawan
            Route::post('/', [KaryawanController::class, 'store'])->name('store'); // Create new karyawan
            Route::get('/data', [KaryawanController::class, 'getData'])->name('data');
            Route::get('/statistics', [KaryawanController::class, 'statistics'])->name('statistics');
            Route::get('/available-rfid', [KaryawanController::class, 'getAvailableRfid'])->name('available-rfid');
            Route::get('/{id}', [KaryawanController::class, 'getKaryawan'])->name('get');
            Route::get('/{id}/show', [KaryawanController::class, 'apiShow'])->name('show'); // Detailed show
            Route::put('/{id}', [KaryawanController::class, 'update'])->name('update');
            Route::delete('/{id}', [KaryawanController::class, 'destroy'])->name('delete');
            Route::post('/bulk-store', [KaryawanController::class, 'bulkStore'])->name('bulk-store');
            Route::post('/bulk-operation', [KaryawanController::class, 'bulkOperation'])->name('bulk');
            Route::get('/export', [KaryawanController::class, 'export'])->name('export');
            Route::post('/import', [KaryawanController::class, 'import'])->name('import');
        });
    });

    // Absensi Management Routes
    Route::resource('absensi', AbsensiController::class);

    // Absensi AJAX API Routes (consolidated)
    Route::prefix('absensi/api')->name('absensi.api.')->group(function () {
        Route::get('/data', [AbsensiController::class, 'getData'])->name('data');
        Route::get('/statistics', [AbsensiController::class, 'getStatistics'])->name('statistics');
        Route::post('/store', [AbsensiController::class, 'store'])->name('store');
        Route::get('/karyawan-list', [AbsensiController::class, 'getKaryawanList'])->name('karyawan-list');
        Route::get('/{id}', [AbsensiController::class, 'getAbsensi'])->name('get');
        Route::put('/{id}', [AbsensiController::class, 'update'])->name('update');
        Route::delete('/{id}/delete', [AbsensiController::class, 'destroy'])->name('delete');
        Route::post('/bulk-operation', [AbsensiController::class, 'bulkOperation'])->name('bulk');
        Route::post('/{id}/cancel', [AbsensiController::class, 'cancel'])->name('cancel');
        Route::get('/rekap/{karyawan_id}', [AbsensiController::class, 'getRekapKaryawan'])->name('rekap');
        Route::get('/attendance-stats/{karyawan_id}', [AbsensiController::class, 'getAttendanceStats'])->name('stats');
        Route::get('/bonus-eligibility', [AbsensiController::class, 'getBonusEligibility'])->name('bonus-eligibility');
        Route::get('/year-end-bonus', [AbsensiController::class, 'getYearEndBonus'])->name('year-end-bonus');
        Route::get('/company-rules', [AbsensiController::class, 'getCompanyRules'])->name('company-rules');
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

    // Aturan Perusahaan routes
    Route::prefix('aturan')->name('aturan.')->group(function () {
        Route::get('/', [AturanPerusahaanController::class, 'index'])->name('index');
        Route::get('/create', [AturanPerusahaanController::class, 'create'])->name('create');
        Route::post('/store', [AturanPerusahaanController::class, 'store'])->name('store');
        Route::get('/{id}', [AturanPerusahaanController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AturanPerusahaanController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AturanPerusahaanController::class, 'update'])->name('update');
        Route::delete('/{id}', [AturanPerusahaanController::class, 'destroy'])->name('destroy');
    });
    
    // Include RFID Management routes
    require __DIR__.'/rfid.php';
});

// API Routes (outside auth middleware)
Route::prefix('api')->group(function () {
    // Aturan Perusahaan API routes
    Route::prefix('aturan')->group(function () {
        Route::get('/', [AturanPerusahaanController::class, 'apiIndex']); // Get active rule
        Route::get('/all', [AturanPerusahaanController::class, 'apiAll']); // Get all rules
        Route::post('/', [AturanPerusahaanController::class, 'store']);
        Route::get('/{id}', [AturanPerusahaanController::class, 'show']);
        Route::put('/{id}', [AturanPerusahaanController::class, 'update']);
        Route::delete('/{id}', [AturanPerusahaanController::class, 'destroy']);
        Route::patch('/{id}/toggle', [AturanPerusahaanController::class, 'toggleActive']); // Toggle active status
    });
});
