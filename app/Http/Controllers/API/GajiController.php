<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Gaji;
use App\Models\Karyawan;
use App\Helpers\GajiCalculatorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GajiController extends Controller
{
    /**
     * Display a listing of gaji
     */
    public function index(Request $request)
    {
        try {
            $query = Gaji::with(['karyawan']);

            // Filter by karyawan
            if ($request->has('karyawan_id')) {
                $query->where('karyawan_id', $request->karyawan_id);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by bulan dan tahun
            if ($request->has('bulan')) {
                $query->where('bulan', $request->bulan);
            }
            if ($request->has('tahun')) {
                $query->where('tahun', $request->tahun);
            }

            // Filter by departemen
            if ($request->has('departemen')) {
                $query->whereHas('karyawan', function($q) use ($request) {
                    $q->where('departemen', $request->departemen);
                });
            }

            // Search by nama karyawan atau NIP
            if ($request->has('search')) {
                $search = $request->search;
                $query->whereHas('karyawan', function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%");
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'tahun');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            if ($sortBy !== 'bulan') {
                $query->orderBy('bulan', 'desc');
            }

            $perPage = $request->get('per_page', 15);
            $gaji = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data gaji berhasil diambil',
                'data' => $gaji
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data gaji',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created gaji atau generate otomatis
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'karyawan_id' => 'required|exists:karyawan,id',
                'bulan' => 'required|integer|between:1,12',
                'tahun' => 'required|integer|min:2020|max:2030',
                'dibuat_oleh' => 'required|exists:users,id',
                'catatan_admin' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Check if gaji already exists
            $existingGaji = Gaji::where('karyawan_id', $request->karyawan_id)
                                 ->where('bulan', $request->bulan)
                                 ->where('tahun', $request->tahun)
                                 ->first();

            if ($existingGaji) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Data gaji untuk karyawan ini pada bulan/tahun tersebut sudah ada',
                    'existing_data' => $existingGaji
                ], 422);
            }

            // Validate calculation requirements
            $validation = GajiCalculatorHelper::validateCalculationRequirements(
                $request->karyawan_id, 
                $request->bulan, 
                $request->tahun
            );

            if (!$validation['valid']) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Persyaratan perhitungan gaji tidak terpenuhi',
                    'errors' => $validation['errors']
                ], 422);
            }

            // Calculate salary using helper
            $calculation = GajiCalculatorHelper::calculateGaji(
                $request->karyawan_id, 
                $request->bulan, 
                $request->tahun
            );

            if (!$calculation['success']) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghitung gaji',
                    'error' => $calculation['error']
                ], 500);
            }

            // Add additional data from request
            $data = $calculation['data'];
            $data['id'] = Str::uuid();
            $data['dibuat_oleh'] = $request->dibuat_oleh;
            $data['catatan_admin'] = $request->catatan_admin;

            $gaji = Gaji::create($data);
            $gaji->load('karyawan');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data gaji berhasil digenerate',
                'data' => $gaji
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat data gaji',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified gaji
     */
    public function show($id)
    {
        try {
            $gaji = Gaji::with(['karyawan'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Data gaji berhasil diambil',
                'data' => $gaji
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data gaji tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified gaji
     */
    public function update(Request $request, $id)
    {
        try {
            $gaji = Gaji::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'potongan_lainnya' => 'nullable|numeric|min:0',
                'keterangan_potongan' => 'nullable|string',
                'status' => 'nullable|in:DRAFT,APPROVED,PAID',
                'tanggal_dibayar' => 'nullable|date',
                'catatan_admin' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $data = $request->only([
                'potongan_lainnya', 'keterangan_potongan', 'status',
                'tanggal_dibayar', 'catatan_admin'
            ]);

            // Recalculate if additional deductions changed
            if (isset($data['potongan_lainnya'])) {
                $totalPotongan = $gaji->potongan_terlambat + $gaji->potongan_alpha + 
                               $data['potongan_lainnya'] + $gaji->bpjs_kesehatan + 
                               $gaji->bpjs_ketenagakerjaan + $gaji->pph21;
                
                $data['total_potongan'] = $totalPotongan;
                $data['gaji_bersih'] = $gaji->total_pendapatan - $totalPotongan;
            }

            $gaji->update($data);
            $gaji->load('karyawan');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data gaji berhasil diperbarui',
                'data' => $gaji
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data gaji',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified gaji
     */
    public function destroy($id)
    {
        try {
            $gaji = Gaji::findOrFail($id);
            
            // Only allow deletion if status is DRAFT
            if ($gaji->status !== 'DRAFT') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya data gaji dengan status DRAFT yang dapat dihapus'
                ], 422);
            }

            $gaji->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data gaji berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data gaji',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate bulk gaji for all active employees
     */
    public function generateBulk(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'bulan' => 'required|integer|between:1,12',
                'tahun' => 'required|integer|min:2020|max:2030',
                'dibuat_oleh' => 'required|exists:users,id',
                'karyawan_ids' => 'nullable|array',
                'karyawan_ids.*' => 'exists:karyawan,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $query = Karyawan::where('status', 'AKTIF');
            
            if ($request->has('karyawan_ids')) {
                $query->whereIn('id', $request->karyawan_ids);
            }

            $karyawanList = $query->get();
            $karyawanIds = $karyawanList->pluck('id')->toArray();

            // Use helper for bulk calculation
            $results = GajiCalculatorHelper::bulkCalculateGaji($karyawanIds, $request->bulan, $request->tahun);

            // Create Gaji records for successful calculations
            foreach ($results['success'] as &$successItem) {
                try {
                    // Check if gaji already exists
                    $existingGaji = Gaji::where('karyawan_id', $successItem['karyawan_id'])
                                         ->where('bulan', $request->bulan)
                                         ->where('tahun', $request->tahun)
                                         ->first();

                    if ($existingGaji) {
                        $results['failed'][] = [
                            'karyawan_id' => $successItem['karyawan_id'],
                            'karyawan_name' => $successItem['karyawan_name'],
                            'reason' => 'Data gaji sudah ada'
                        ];
                        continue;
                    }

                    $data = $successItem['data'];
                    $data['id'] = Str::uuid();
                    $data['dibuat_oleh'] = $request->dibuat_oleh;

                    $gaji = Gaji::create($data);
                    $successItem['gaji_id'] = $gaji->id;
                    
                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'karyawan_id' => $successItem['karyawan_id'],
                        'karyawan_name' => $successItem['karyawan_name'],
                        'reason' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bulk generate gaji selesai',
                'data' => $results
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate bulk gaji',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get salary calculation summary for a specific period
     */
    public function getSummary(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'bulan' => 'required|integer|between:1,12',
                'tahun' => 'required|integer|min:2020|max:2030',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $summary = GajiCalculatorHelper::getSalarySummary($request->bulan, $request->tahun);

            return response()->json([
                'success' => true,
                'message' => 'Summary periode gaji berhasil diambil',
                'data' => $summary
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil summary gaji',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate salary calculation requirements
     */
    public function validateRequirements(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'karyawan_id' => 'required|exists:karyawan,id',
                'bulan' => 'required|integer|between:1,12',
                'tahun' => 'required|integer|min:2020|max:2030',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validation = GajiCalculatorHelper::validateCalculationRequirements(
                $request->karyawan_id, 
                $request->bulan, 
                $request->tahun
            );

            return response()->json([
                'success' => true,
                'message' => 'Validasi persyaratan perhitungan gaji',
                'data' => $validation
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal validasi persyaratan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}