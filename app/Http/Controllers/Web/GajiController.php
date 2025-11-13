<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Gaji;
use App\Models\Karyawan;
use App\Helpers\GajiCalculatorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GajiController extends Controller
{
    /**
     * Display the gaji management page
     */
    public function index()
    {
        return view('gaji.index');
    }

    /**
     * Show the form for creating/calculating new gaji
     */
    public function create()
    {
        return view('gaji.create');
    }

    /**
     * Show the form for editing the specified gaji
     */
    public function edit($id)
    {
        return view('gaji.edit', compact('id'));
    }

    /**
     * Display the specified gaji detail
     */
    public function show($id)
    {
        return view('gaji.show', compact('id'));
    }

    /**
     * Get gaji data for listing (AJAX)
     */
    public function getData(Request $request)
    {
        try {
            $query = Gaji::with(['karyawan']);

            // Filter by karyawan
            if ($request->has('karyawan_id') && $request->karyawan_id !== '') {
                $query->where('karyawan_id', $request->karyawan_id);
            }

            // Filter by status
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            // Filter by bulan dan tahun
            if ($request->has('bulan') && $request->bulan !== '') {
                $query->where('bulan', $request->bulan);
            }
            if ($request->has('tahun') && $request->tahun !== '') {
                $query->where('tahun', $request->tahun);
            }

            // Filter by departemen
            if ($request->has('departemen') && $request->departemen !== '') {
                $query->whereHas('karyawan', function($q) use ($request) {
                    $q->where('departemen', $request->departemen);
                });
            }

            // Search by nama karyawan atau NIP
            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->whereHas('karyawan', function($q) use ($search) {
                    $q->where('nama', 'LIKE', "%{$search}%")
                      ->orWhere('nip', 'LIKE', "%{$search}%");
                });
            }

            // Pagination
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);
            
            $gajis = $query->orderBy('tahun', 'desc')
                          ->orderBy('bulan', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $gajis->items(),
                    'current_page' => $gajis->currentPage(),
                    'per_page' => $gajis->perPage(),
                    'total' => $gajis->total(),
                    'last_page' => $gajis->lastPage(),
                    'from' => $gajis->firstItem(),
                    'to' => $gajis->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching gaji data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get gaji statistics (AJAX)
     */
    public function getStatistics(Request $request)
    {
        try {
            $query = Gaji::query();

            // Apply filters if provided
            if ($request->has('bulan') && $request->bulan !== '') {
                $query->where('bulan', $request->bulan);
            }
            if ($request->has('tahun') && $request->tahun !== '') {
                $query->where('tahun', $request->tahun);
            }
            if ($request->has('departemen') && $request->departemen !== '') {
                $query->whereHas('karyawan', function($q) use ($request) {
                    $q->where('departemen', $request->departemen);
                });
            }

            // Calculate statistics
            $stats = [
                'total_gaji' => $query->count(),
                'gaji_draft' => (clone $query)->where('status', 'DRAFT')->count(),
                'gaji_final' => (clone $query)->where('status', 'FINAL')->count(),
                'gaji_dibayar' => (clone $query)->where('status', 'DIBAYAR')->count(),
                'total_pendapatan' => (clone $query)->sum('total_pendapatan'),
                'total_potongan' => (clone $query)->sum('total_potongan'),
                'total_gaji_bersih' => (clone $query)->sum('gaji_bersih'),
                'rata_gaji_bersih' => (clone $query)->avg('gaji_bersih')
            ];

            // Format currency values
            $stats['total_pendapatan_formatted'] = number_format($stats['total_pendapatan'], 0, ',', '.');
            $stats['total_potongan_formatted'] = number_format($stats['total_potongan'], 0, ',', '.');
            $stats['total_gaji_bersih_formatted'] = number_format($stats['total_gaji_bersih'], 0, ',', '.');
            $stats['rata_gaji_bersih_formatted'] = number_format($stats['rata_gaji_bersih'], 0, ',', '.');

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching gaji statistics: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate gaji for specific karyawan and period
     */
    public function calculateGaji(Request $request)
    {
        try {
            $request->validate([
                'karyawan_id' => 'required|exists:karyawan,id',
                'bulan' => 'required|integer|between:1,12',
                'tahun' => 'required|integer|min:2020|max:2030'
            ]);

            // Check if calculation can be performed
            $validation = GajiCalculatorHelper::validateCalculationRequirements(
                $request->karyawan_id, 
                $request->bulan, 
                $request->tahun
            );

            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validation['errors']
                ], 422);
            }

            // Check if gaji already exists
            $existingGaji = Gaji::where('karyawan_id', $request->karyawan_id)
                                ->where('bulan', $request->bulan)
                                ->where('tahun', $request->tahun)
                                ->first();

            if ($existingGaji && $request->get('force') !== true) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gaji untuk periode ini sudah ada',
                    'existing_gaji' => $existingGaji
                ], 409);
            }

            // Calculate gaji using helper
            $calculation = GajiCalculatorHelper::calculateGaji(
                $request->karyawan_id, 
                $request->bulan, 
                $request->tahun
            );

            if (!$calculation['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghitung gaji',
                    'error' => $calculation['error']
                ], 500);
            }

            // Save or update gaji data
            if ($existingGaji) {
                $existingGaji->update($calculation['data']);
                $gaji = $existingGaji;
            } else {
                $gaji = Gaji::create($calculation['data']);
            }

            // Load with relationship
            $gaji->load('karyawan');

            return response()->json([
                'success' => true,
                'message' => 'Gaji berhasil dihitung',
                'data' => $gaji
            ]);

        } catch (\Exception $e) {
            Log::error('Error calculating gaji: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung gaji',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk calculate gaji for multiple karyawan
     */
    public function bulkCalculate(Request $request)
    {
        try {
            $request->validate([
                'karyawan_ids' => 'required|array',
                'karyawan_ids.*' => 'exists:karyawan,id',
                'bulan' => 'required|integer|between:1,12',
                'tahun' => 'required|integer|min:2020|max:2030'
            ]);

            $results = GajiCalculatorHelper::bulkCalculateGaji(
                $request->karyawan_ids,
                $request->bulan,
                $request->tahun
            );

            // Save successful calculations
            foreach ($results['success'] as $result) {
                $existingGaji = Gaji::where('karyawan_id', $result['karyawan_id'])
                                   ->where('bulan', $request->bulan)
                                   ->where('tahun', $request->tahun)
                                   ->first();

                if ($existingGaji) {
                    $existingGaji->update($result['data']);
                } else {
                    Gaji::create($result['data']);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Bulk calculation completed',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Error bulk calculating gaji: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan bulk calculation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single gaji detail
     */
    public function getGaji($id)
    {
        try {
            $gaji = Gaji::with('karyawan')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $gaji
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching gaji detail: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gaji tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update gaji status or manual adjustments
     */
    public function updateGaji(Request $request, $id)
    {
        try {
            $gaji = Gaji::findOrFail($id);

            $request->validate([
                'status' => 'sometimes|in:DRAFT,FINAL,DIBAYAR',
                'catatan_admin' => 'sometimes|nullable|string',
                'potongan_lainnya' => 'sometimes|numeric|min:0',
                'keterangan_potongan' => 'sometimes|nullable|string'
            ]);

            // Update fields
            if ($request->has('status')) {
                $gaji->status = $request->status;
                
                if ($request->status === 'DIBAYAR') {
                    $gaji->tanggal_dibayar = now();
                }
            }

            if ($request->has('catatan_admin')) {
                $gaji->catatan_admin = $request->catatan_admin;
            }

            if ($request->has('potongan_lainnya')) {
                $gaji->potongan_lainnya = $request->potongan_lainnya;
                
                // Recalculate totals
                $gaji->total_potongan = $gaji->potongan_terlambat + $gaji->potongan_alpha + 
                                       $gaji->potongan_lainnya + $gaji->bpjs_kesehatan + 
                                       $gaji->bpjs_ketenagakerjaan + $gaji->pph21;
                $gaji->gaji_bersih = $gaji->total_pendapatan - $gaji->total_potongan;
            }

            if ($request->has('keterangan_potongan')) {
                $gaji->keterangan_potongan = $request->keterangan_potongan;
            }

            $gaji->save();
            $gaji->load('karyawan');

            return response()->json([
                'success' => true,
                'message' => 'Gaji berhasil diupdate',
                'data' => $gaji
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating gaji: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate gaji',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete gaji record
     */
    public function deleteGaji($id)
    {
        try {
            $gaji = Gaji::findOrFail($id);
            
            // Check if can be deleted (only draft status)
            if ($gaji->status !== 'DRAFT') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya gaji dengan status DRAFT yang dapat dihapus'
                ], 422);
            }

            $gaji->delete();

            return response()->json([
                'success' => true,
                'message' => 'Gaji berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting gaji: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus gaji',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of available karyawan for gaji calculation
     */
    public function getKaryawanList(Request $request)
    {
        try {
            $query = Karyawan::where('status', 'AKTIF');
            
            // Search functionality
            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'LIKE', '%' . $search . '%')
                      ->orWhere('nip', 'LIKE', '%' . $search . '%')
                      ->orWhere('departemen', 'LIKE', '%' . $search . '%');
                });
            }
            
            $karyawan = $query->orderBy('nama', 'asc')
                             ->take($request->get('per_page', 100))
                             ->get(['id', 'nama', 'nip', 'departemen', 'jabatan', 'status']);

            return response()->json([
                'success' => true,
                'data' => $karyawan
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching karyawan list: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get salary summary for specific period
     */
    public function getSalarySummary(Request $request)
    {
        try {
            $bulan = $request->get('bulan', date('n'));
            $tahun = $request->get('tahun', date('Y'));
            
            $summary = GajiCalculatorHelper::getSalarySummary($bulan, $tahun);
            
            return response()->json([
                'success' => true,
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching salary summary: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}