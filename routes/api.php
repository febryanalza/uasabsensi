<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RfidController;
use App\Http\Controllers\API\AbsensiController;
use App\Http\Controllers\API\AturanPerusahaanController;
use App\Http\Controllers\API\LemburController;
use App\Http\Controllers\API\GajiController;
use App\Http\Controllers\API\KpiController;
use App\Http\Controllers\API\HariLiburController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ============================================
// PUBLIC ROUTES - RFID (NodeMCU)
// ============================================

// RFID Card Registration (NodeMCU Auto-Register)
Route::post('/rfid', [RfidController::class, 'register']);
Route::get('/rfid', [RfidController::class, 'index']);
Route::get('/rfid/{cardNumber}', [RfidController::class, 'show']);
Route::delete('/rfid/{cardNumber}', [RfidController::class, 'destroy']);

// RFID Absensi Scan
Route::prefix('rfid')->group(function () {
    Route::post('/scan', [RfidController::class, 'scan']);
    Route::get('/status/{rfidCard}', [RfidController::class, 'status']);
    Route::get('/test', [RfidController::class, 'test']);
});

// ============================================
// PUBLIC ROUTES - Authentication
// ============================================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes (butuh autentikasi)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    
    // Test endpoint untuk cek autentikasi
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    });



    // ============================================
    // ABSENSI MANAGEMENT
    // ============================================
    Route::prefix('absensi')->group(function () {
        // List & Filter Absensi
        Route::get('/', [AbsensiController::class, 'index']);
        
        // Get Absensi Statistics
        Route::get('/statistics', [AbsensiController::class, 'statistics']);
        
        // Get Rekap Absensi per Karyawan (by month)
        Route::get('/rekap/{karyawanId}', [AbsensiController::class, 'rekapKaryawan']);
        
        // Get Single Absensi Detail
        Route::get('/{id}', [AbsensiController::class, 'show']);
        
        // Create New Absensi
        Route::post('/', [AbsensiController::class, 'store']);
        
        // Bulk Create Absensi (Import)
        Route::post('/bulk', [AbsensiController::class, 'bulkStore']);
        
        // Update Absensi
        Route::put('/{id}', [AbsensiController::class, 'update']);
        
        // Cancel/Batalkan Absensi
        Route::post('/{id}/cancel', [AbsensiController::class, 'cancel']);
        
        // Delete Absensi
        Route::delete('/{id}', [AbsensiController::class, 'destroy']);
        
        // Get Attendance Statistics
        Route::get('/stats/{karyawan_id}', [AbsensiController::class, 'getAttendanceStats']);
        
        // Check Bonus Eligibility
        Route::get('/bonus-eligibility', [AbsensiController::class, 'getBonusEligibility']);
        
        // Calculate Year-End Bonus
        Route::get('/year-end-bonus', [AbsensiController::class, 'getYearEndBonus']);
        
        // Get Company Rules
        Route::get('/company-rules', [AbsensiController::class, 'getCompanyRules']);
    });

    // ============================================
    // ATURAN PERUSAHAAN MANAGEMENT (ADMIN ONLY)
    // ============================================
    Route::prefix('aturan-perusahaan')->group(function () {
        // List All Rules
        Route::get('/', [AturanPerusahaanController::class, 'index']);
        
        // Get Active Rule
        Route::get('/active', [AturanPerusahaanController::class, 'getActive']);
        
        // Get Summary (calculations examples)
        Route::get('/summary', [AturanPerusahaanController::class, 'summary']);
        
        // Get Single Rule Detail
        Route::get('/{id}', [AturanPerusahaanController::class, 'show']);
        
        // Create New Rule
        Route::post('/', [AturanPerusahaanController::class, 'store']);
        
        // Update Rule
        Route::put('/{id}', [AturanPerusahaanController::class, 'update']);
        
        // Delete Rule
        Route::delete('/{id}', [AturanPerusahaanController::class, 'destroy']);
        
        // Activate Rule
        Route::post('/{id}/activate', [AturanPerusahaanController::class, 'activate']);
        
        // Deactivate Rule
        Route::post('/{id}/deactivate', [AturanPerusahaanController::class, 'deactivate']);
        
        // Duplicate Rule
        Route::post('/{id}/duplicate', [AturanPerusahaanController::class, 'duplicate']);
    });

    // ============================================
    // LEMBUR MANAGEMENT
    // ============================================
    Route::prefix('lembur')->group(function () {
        // List & Filter Lembur
        Route::get('/', [LemburController::class, 'index']);
        
        // Get Single Lembur Detail
        Route::get('/{id}', [LemburController::class, 'show']);
        
        // Create New Lembur Request
        Route::post('/', [LemburController::class, 'store']);
        
        // Update Lembur
        Route::put('/{id}', [LemburController::class, 'update']);
        
        // Approve/Reject Lembur
        Route::post('/{id}/approve', [LemburController::class, 'approve']);
        
        // Delete Lembur
        Route::delete('/{id}', [LemburController::class, 'destroy']);
    });

    // ============================================
    // GAJI MANAGEMENT
    // ============================================
    Route::prefix('gaji')->group(function () {
        // List & Filter Gaji
        Route::get('/', [GajiController::class, 'index']);
        
        // Get Salary Summary for Period
        Route::get('/summary', [GajiController::class, 'getSummary']);
        
        // Validate Salary Calculation Requirements
        Route::post('/validate', [GajiController::class, 'validateRequirements']);
        
        // Get Single Gaji Detail
        Route::get('/{id}', [GajiController::class, 'show']);
        
        // Generate Gaji for Single Employee
        Route::post('/', [GajiController::class, 'store']);
        
        // Generate Bulk Gaji for Multiple Employees
        Route::post('/bulk', [GajiController::class, 'generateBulk']);
        
        // Update Gaji (additional deductions, etc.)
        Route::put('/{id}', [GajiController::class, 'update']);
        
        // Delete Gaji (DRAFT only)
        Route::delete('/{id}', [GajiController::class, 'destroy']);
    });

    // ============================================
    // KPI MANAGEMENT
    // ============================================
    Route::prefix('kpi')->group(function () {
        // List & Filter KPI
        Route::get('/', [KpiController::class, 'index']);
        
        // Get Single KPI Detail
        Route::get('/{id}', [KpiController::class, 'show']);
        
        // Create New KPI Evaluation
        Route::post('/', [KpiController::class, 'store']);
        
        // Generate Bulk KPI for Multiple Employees
        Route::post('/bulk', [KpiController::class, 'generateBulk']);
        
        // Update KPI
        Route::put('/{id}', [KpiController::class, 'update']);
        
        // Delete KPI
        Route::delete('/{id}', [KpiController::class, 'destroy']);
    });

    // ============================================
    // HARI LIBUR MANAGEMENT
    // ============================================
    Route::prefix('hari-libur')->group(function () {
        // List & Filter Hari Libur
        Route::get('/', [HariLiburController::class, 'index']);
        
        // Get Holidays by Month/Year
        Route::get('/month', [HariLiburController::class, 'getByMonth']);
        
        // Get Upcoming Holidays (next 30 days)
        Route::get('/upcoming', [HariLiburController::class, 'getUpcoming']);
        
        // Check if specific date is holiday
        Route::post('/check', [HariLiburController::class, 'checkHoliday']);
        
        // Get Single Holiday Detail
        Route::get('/{id}', [HariLiburController::class, 'show']);
        
        // Create New Holiday
        Route::post('/', [HariLiburController::class, 'store']);
        
        // Create Bulk Holidays
        Route::post('/bulk', [HariLiburController::class, 'createBulk']);
        
        // Update Holiday
        Route::put('/{id}', [HariLiburController::class, 'update']);
        
        // Delete Holiday
        Route::delete('/{id}', [HariLiburController::class, 'destroy']);
    });
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now()
    ]);
});
