<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Kpi;
use App\Models\Karyawan;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class KpiController extends Controller
{
    /**
     * Display a listing of kpi
     */
    public function index(Request $request)
    {
        try {
            $query = Kpi::with(['karyawan']);

            // Filter by karyawan
            if ($request->has('karyawan_id')) {
                $query->where('karyawan_id', $request->karyawan_id);
            }

            // Filter by bulan dan tahun
            if ($request->has('bulan')) {
                $query->where('bulan', $request->bulan);
            }
            if ($request->has('tahun')) {
                $query->where('tahun', $request->tahun);
            }

            // Filter by kategori
            if ($request->has('kategori')) {
                $query->where('kategori', $request->kategori);
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
            $kpi = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data KPI berhasil diambil',
                'data' => $kpi
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data KPI',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created kpi
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'karyawan_id' => 'required|exists:karyawan,id',
                'bulan' => 'required|integer|between:1,12',
                'tahun' => 'required|integer|min:2020|max:2030',
                'target_kehadiran' => 'required|integer|min:0',
                'target_penyelesaian_tugas' => 'required|integer|min:0|max:100',
                'nilai_kedisiplinan' => 'required|numeric|between:0,100',
                'nilai_kualitas_kerja' => 'required|numeric|between:0,100',
                'nilai_kerjasama' => 'required|numeric|between:0,100',
                'nilai_inisiatif' => 'required|numeric|between:0,100',
                'catatan' => 'nullable|string',
                'dinilai_oleh' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Check if KPI already exists
            $existingKpi = Kpi::where('karyawan_id', $request->karyawan_id)
                              ->where('bulan', $request->bulan)
                              ->where('tahun', $request->tahun)
                              ->first();

            if ($existingKpi) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Data KPI untuk karyawan ini pada bulan/tahun tersebut sudah ada',
                    'existing_data' => $existingKpi
                ], 422);
            }

            $karyawan = Karyawan::findOrFail($request->karyawan_id);

            $data = $request->only([
                'karyawan_id', 'bulan', 'tahun', 'target_kehadiran', 
                'target_penyelesaian_tugas', 'nilai_kedisiplinan',
                'nilai_kualitas_kerja', 'nilai_kerjasama', 'nilai_inisiatif',
                'catatan', 'dinilai_oleh'
            ]);

            $data['id'] = Str::uuid();
            $data['tanggal_penilaian'] = now();

            // Calculate actual attendance from absensi data
            $startDate = Carbon::create($request->tahun, $request->bulan, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            $absensiCount = Absensi::where('karyawan_id', $request->karyawan_id)
                                   ->whereBetween('tanggal', [$startDate, $endDate])
                                   ->where('status', 'HADIR')
                                   ->count();

            $data['realisasi_kehadiran'] = $absensiCount;
            $data['persen_kehadiran'] = $data['target_kehadiran'] > 0 ? 
                                       min(100, ($absensiCount / $data['target_kehadiran']) * 100) : 0;

            // For task completion, we'll set it to the target for now
            // This should ideally come from a task management system
            $data['realisasi_penyelesaian_tugas'] = $request->target_penyelesaian_tugas;
            $data['persen_penyelesaian_tugas'] = 100;

            // Calculate total score (weighted average)
            $scoreKehadiran = $data['persen_kehadiran'] * 0.2; // 20%
            $scoreTugas = $data['persen_penyelesaian_tugas'] * 0.2; // 20%
            $scoreKedisiplinan = $data['nilai_kedisiplinan'] * 0.15; // 15%
            $scoreKualitas = $data['nilai_kualitas_kerja'] * 0.25; // 25%
            $scoreKerjasama = $data['nilai_kerjasama'] * 0.1; // 10%
            $scoreInisiatif = $data['nilai_inisiatif'] * 0.1; // 10%

            $data['skor_total'] = $scoreKehadiran + $scoreTugas + $scoreKedisiplinan + 
                                 $scoreKualitas + $scoreKerjasama + $scoreInisiatif;

            // Determine category
            if ($data['skor_total'] >= 90) {
                $data['kategori'] = 'EXCELLENT';
                $data['bonus_kpi'] = 1000000; // 1M bonus
            } elseif ($data['skor_total'] >= 80) {
                $data['kategori'] = 'GOOD';
                $data['bonus_kpi'] = 750000; // 750K bonus
            } elseif ($data['skor_total'] >= 70) {
                $data['kategori'] = 'SATISFACTORY';
                $data['bonus_kpi'] = 500000; // 500K bonus
            } elseif ($data['skor_total'] >= 60) {
                $data['kategori'] = 'NEEDS_IMPROVEMENT';
                $data['bonus_kpi'] = 0;
            } else {
                $data['kategori'] = 'POOR';
                $data['bonus_kpi'] = 0;
            }

            $kpi = Kpi::create($data);
            $kpi->load('karyawan');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data KPI berhasil dibuat',
                'data' => $kpi
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat data KPI',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified kpi
     */
    public function show($id)
    {
        try {
            $kpi = Kpi::with(['karyawan'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Data KPI berhasil diambil',
                'data' => $kpi
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data KPI tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified kpi
     */
    public function update(Request $request, $id)
    {
        try {
            $kpi = Kpi::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'target_kehadiran' => 'sometimes|required|integer|min:0',
                'target_penyelesaian_tugas' => 'sometimes|required|integer|min:0|max:100',
                'realisasi_penyelesaian_tugas' => 'sometimes|required|integer|min:0|max:100',
                'nilai_kedisiplinan' => 'sometimes|required|numeric|between:0,100',
                'nilai_kualitas_kerja' => 'sometimes|required|numeric|between:0,100',
                'nilai_kerjasama' => 'sometimes|required|numeric|between:0,100',
                'nilai_inisiatif' => 'sometimes|required|numeric|between:0,100',
                'catatan' => 'nullable|string',
                'dinilai_oleh' => 'sometimes|required|exists:users,id',
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
                'target_kehadiran', 'target_penyelesaian_tugas', 'realisasi_penyelesaian_tugas',
                'nilai_kedisiplinan', 'nilai_kualitas_kerja', 'nilai_kerjasama', 
                'nilai_inisiatif', 'catatan', 'dinilai_oleh'
            ]);

            // Recalculate percentages if targets changed
            if (isset($data['target_kehadiran'])) {
                $data['persen_kehadiran'] = $data['target_kehadiran'] > 0 ? 
                    min(100, ($kpi->realisasi_kehadiran / $data['target_kehadiran']) * 100) : 0;
            }

            if (isset($data['target_penyelesaian_tugas']) || isset($data['realisasi_penyelesaian_tugas'])) {
                $targetTugas = $data['target_penyelesaian_tugas'] ?? $kpi->target_penyelesaian_tugas;
                $realisasiTugas = $data['realisasi_penyelesaian_tugas'] ?? $kpi->realisasi_penyelesaian_tugas;
                
                $data['persen_penyelesaian_tugas'] = $targetTugas > 0 ? 
                    min(100, ($realisasiTugas / $targetTugas) * 100) : 0;
            }

            // Recalculate total score if any component changed
            $needsRecalculation = array_intersect_key($data, array_flip([
                'target_kehadiran', 'target_penyelesaian_tugas', 'realisasi_penyelesaian_tugas',
                'nilai_kedisiplinan', 'nilai_kualitas_kerja', 'nilai_kerjasama', 'nilai_inisiatif'
            ]));

            if (!empty($needsRecalculation)) {
                // Get updated values
                $persenKehadiran = $data['persen_kehadiran'] ?? $kpi->persen_kehadiran;
                $persenTugas = $data['persen_penyelesaian_tugas'] ?? $kpi->persen_penyelesaian_tugas;
                $nilaiKedisiplinan = $data['nilai_kedisiplinan'] ?? $kpi->nilai_kedisiplinan;
                $nilaiKualitas = $data['nilai_kualitas_kerja'] ?? $kpi->nilai_kualitas_kerja;
                $nilaiKerjasama = $data['nilai_kerjasama'] ?? $kpi->nilai_kerjasama;
                $nilaiInisiatif = $data['nilai_inisiatif'] ?? $kpi->nilai_inisiatif;

                // Recalculate total score
                $scoreKehadiran = $persenKehadiran * 0.2;
                $scoreTugas = $persenTugas * 0.2;
                $scoreKedisiplinan = $nilaiKedisiplinan * 0.15;
                $scoreKualitas = $nilaiKualitas * 0.25;
                $scoreKerjasama = $nilaiKerjasama * 0.1;
                $scoreInisiatif = $nilaiInisiatif * 0.1;

                $data['skor_total'] = $scoreKehadiran + $scoreTugas + $scoreKedisiplinan + 
                                     $scoreKualitas + $scoreKerjasama + $scoreInisiatif;

                // Update category and bonus
                if ($data['skor_total'] >= 90) {
                    $data['kategori'] = 'EXCELLENT';
                    $data['bonus_kpi'] = 1000000;
                } elseif ($data['skor_total'] >= 80) {
                    $data['kategori'] = 'GOOD';
                    $data['bonus_kpi'] = 750000;
                } elseif ($data['skor_total'] >= 70) {
                    $data['kategori'] = 'SATISFACTORY';
                    $data['bonus_kpi'] = 500000;
                } elseif ($data['skor_total'] >= 60) {
                    $data['kategori'] = 'NEEDS_IMPROVEMENT';
                    $data['bonus_kpi'] = 0;
                } else {
                    $data['kategori'] = 'POOR';
                    $data['bonus_kpi'] = 0;
                }
            }

            $kpi->update($data);
            $kpi->load('karyawan');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data KPI berhasil diperbarui',
                'data' => $kpi
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data KPI',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified kpi
     */
    public function destroy($id)
    {
        try {
            $kpi = Kpi::findOrFail($id);
            $kpi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data KPI berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data KPI',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate bulk KPI for all active employees
     */
    public function generateBulk(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'bulan' => 'required|integer|between:1,12',
                'tahun' => 'required|integer|min:2020|max:2030',
                'dinilai_oleh' => 'required|exists:users,id',
                'karyawan_ids' => 'nullable|array',
                'karyawan_ids.*' => 'exists:karyawan,id',
                'default_target_kehadiran' => 'required|integer|min:1',
                'default_target_tugas' => 'required|integer|min:1|max:100',
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
            $results = ['success' => [], 'failed' => []];

            foreach ($karyawanList as $karyawan) {
                // Check if KPI already exists
                $existingKpi = Kpi::where('karyawan_id', $karyawan->id)
                                  ->where('bulan', $request->bulan)
                                  ->where('tahun', $request->tahun)
                                  ->first();

                if ($existingKpi) {
                    $results['failed'][] = [
                        'karyawan' => $karyawan->nama,
                        'reason' => 'Data KPI sudah ada'
                    ];
                    continue;
                }

                try {
                    // Generate KPI with default values
                    $subRequest = new Request([
                        'karyawan_id' => $karyawan->id,
                        'bulan' => $request->bulan,
                        'tahun' => $request->tahun,
                        'target_kehadiran' => $request->default_target_kehadiran,
                        'target_penyelesaian_tugas' => $request->default_target_tugas,
                        'nilai_kedisiplinan' => 75, // Default values
                        'nilai_kualitas_kerja' => 75,
                        'nilai_kerjasama' => 75,
                        'nilai_inisiatif' => 75,
                        'dinilai_oleh' => $request->dinilai_oleh,
                        'catatan' => 'Generated via bulk process'
                    ]);

                    $this->store($subRequest);
                    
                    $results['success'][] = [
                        'karyawan' => $karyawan->nama,
                        'nip' => $karyawan->nip
                    ];

                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'karyawan' => $karyawan->nama,
                        'reason' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bulk generate KPI selesai',
                'data' => $results
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate bulk KPI',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}