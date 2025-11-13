<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Helpers\GajiCalculatorHelper;
use Carbon\Carbon;
use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AbsensiController extends Controller
{
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('app.url') . '/api';
    }



    /**
     * Display a listing of absensi
     */
    public function index(Request $request)
    {
        return view('absensi.index');
    }

    /**
     * Show the form for creating a new absensi
     */
    public function create()
    {
        return view('absensi.create');
    }

    /**
     * Show the form for editing the specified absensi
     */
    public function edit($id)
    {
        return view('absensi.edit', compact('id'));
    }

    /**
     * Display the specified absensi
     */
    public function show($id)
    {
        return view('absensi.show', compact('id'));
    }

    /**
     * Get absensi data via direct model access (for AJAX requests)
     */
    public function getData(Request $request)
    {
        try {
            $query = Absensi::with(['karyawan']);

            // Filter by karyawan
            if ($request->has('karyawan_id') && $request->karyawan_id !== '') {
                $query->where('karyawan_id', $request->karyawan_id);
            }

            // Filter by status
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            // Filter by tanggal (date range)
            if ($request->has('tanggal_mulai') && $request->tanggal_mulai !== '') {
                $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
            }
            
            if ($request->has('tanggal_selesai') && $request->tanggal_selesai !== '') {
                $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
            }

            // Search by karyawan nama
            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->whereHas('karyawan', function($q) use ($search) {
                    $q->where('nama', 'LIKE', '%' . $search . '%')
                      ->orWhere('nip', 'LIKE', '%' . $search . '%');
                });
            }

            // Pagination
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);
            
            $absensi = $query->orderBy('tanggal', 'desc')
                            ->orderBy('jam_masuk', 'desc')
                            ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $absensi->items(),
                    'current_page' => $absensi->currentPage(),
                    'per_page' => $absensi->perPage(),
                    'total' => $absensi->total(),
                    'last_page' => $absensi->lastPage(),
                    'from' => $absensi->firstItem(),
                    'to' => $absensi->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching absensi data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get absensi statistics via direct model access
     */
    public function getStatistics(Request $request)
    {
        try {
            $query = Absensi::query();

            // Apply filters if provided
            if ($request->has('karyawan_id') && $request->karyawan_id !== '') {
                $query->where('karyawan_id', $request->karyawan_id);
            }

            if ($request->has('tanggal_mulai') && $request->tanggal_mulai !== '') {
                $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
            }
            
            if ($request->has('tanggal_selesai') && $request->tanggal_selesai !== '') {
                $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
            }

            // Calculate statistics using database queries for better performance
            $stats = [
                'total_absensi' => $query->count(),
                'hadir' => (clone $query)->where('status', 'HADIR')->count(),
                'izin' => (clone $query)->where('status', 'IZIN')->count(),
                'sakit' => (clone $query)->where('status', 'SAKIT')->count(),
                'alpha' => (clone $query)->where('status', 'ALPHA')->count(),
                'cuti' => (clone $query)->where('status', 'CUTI')->count(),
                'terlambat' => (clone $query)->where('menit_terlambat', '>', 0)->count()
            ];

            // Additional statistics for better insights
            $avgTerlambat = (clone $query)->where('menit_terlambat', '>', 0)->avg('menit_terlambat');
            $stats['avg_terlambat'] = round($avgTerlambat ?? 0, 2);

            // Top 5 karyawan with most attendance
            $topKaryawan = (clone $query)->with('karyawan')
                ->select('karyawan_id', DB::raw('COUNT(*) as total_hadir'))
                ->where('status', 'HADIR')
                ->groupBy('karyawan_id')
                ->orderBy('total_hadir', 'desc')
                ->limit(5)
                ->get();

            $stats['top_karyawan'] = $topKaryawan->map(function($item) {
                return [
                    'karyawan_id' => $item->karyawan_id,
                    'nama' => $item->karyawan->nama ?? 'Unknown',
                    'total_hadir' => $item->total_hadir
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching absensi statistics: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created absensi via API
     */
    public function store(Request $request)
    {
        try {
            $response = Http::timeout(30)->post($this->baseUrl . '/absensi', $request->all());

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Error creating absensi: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat absensi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single absensi via API
     */
    public function getAbsensi($id)
    {
        try {
            $response = Http::timeout(30)->get($this->baseUrl . '/absensi/' . $id);

            if ($response->successful()) {
                return $response->json();
            }

            return response()->json([
                'success' => false,
                'message' => 'Absensi tidak ditemukan'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error fetching absensi: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    /**
     * Update the specified absensi via API
     */
    public function update(Request $request, $id)
    {
        try {
            $response = Http::timeout(30)->put($this->baseUrl . '/absensi/' . $id, $request->all());

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Error updating absensi: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate absensi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified absensi via API
     */
    public function destroy($id)
    {
        try {
            $response = Http::timeout(30)->delete($this->baseUrl . '/absensi/' . $id);

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Error deleting absensi: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus absensi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get karyawan list for dropdown
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
                      ->orWhere('email', 'LIKE', '%' . $search . '%');
                });
            }
            
            $karyawan = $query->orderBy('nama', 'asc')
                             ->take($request->get('per_page', 100))
                             ->get(['id', 'nama', 'nip', 'departemen', 'status']);

            // Simplify data for dropdown
            $simplified = $karyawan->map(function($k) {
                return [
                    'id' => $k->id,
                    'nama' => $k->nama,
                    'nip' => $k->nip,
                    'departemen' => $k->departemen ?? '',
                    'status' => $k->status
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $simplified
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
     * Bulk operation for absensi
     */
    public function bulkOperation(Request $request)
    {
        try {
            $response = Http::timeout(60)->post($this->baseUrl . '/absensi/bulk-store', $request->all());

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Error bulk operation absensi: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan operasi bulk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel absensi
     */
    public function cancel(Request $request, $id)
    {
        try {
            $response = Http::timeout(30)->post($this->baseUrl . '/absensi/' . $id . '/cancel', $request->all());

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Error cancelling absensi: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan absensi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rekap absensi per karyawan
     */
    public function getRekapKaryawan(Request $request, $karyawanId)
    {
        try {
            $params = $request->all();
            $queryString = http_build_query($params);
            
            $response = Http::timeout(30)->get($this->baseUrl . '/absensi/rekap/' . $karyawanId . '?' . $queryString);

            if ($response->successful()) {
                return $response->json();
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil rekap absensi'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error fetching rekap absensi: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    /**
     * Get attendance stats for karyawan
     */
    public function getAttendanceStats($karyawanId)
    {
        try {
            $response = Http::timeout(30)->get($this->baseUrl . '/absensi/attendance-stats/' . $karyawanId);

            if ($response->successful()) {
                return $response->json();
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik kehadiran'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error fetching attendance stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    /**
     * Get bonus eligibility
     */
    public function getBonusEligibility(Request $request)
    {
        try {
            $params = $request->all();
            $queryString = http_build_query($params);
            
            $response = Http::timeout(30)->get($this->baseUrl . '/absensi/bonus-eligibility?' . $queryString);

            if ($response->successful()) {
                return $response->json();
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengecek kelayakan bonus'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error checking bonus eligibility: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    /**
     * Get year end bonus
     */
    public function getYearEndBonus(Request $request)
    {
        try {
            $params = $request->all();
            $queryString = http_build_query($params);
            
            $response = Http::timeout(30)->get($this->baseUrl . '/absensi/year-end-bonus?' . $queryString);

            if ($response->successful()) {
                return $response->json();
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung bonus akhir tahun'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error calculating year end bonus: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    /**
     * Get company rules
     */
    public function getCompanyRules()
    {
        try {
            $response = Http::timeout(30)->get($this->baseUrl . '/absensi/company-rules');

            if ($response->successful()) {
                return $response->json();
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil aturan perusahaan'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error fetching company rules: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    /**
     * Calculate salary based on attendance data
     */
    public function calculateSalaryFromAttendance(Request $request)
    {
        try {
            $validated = $request->validate([
                'karyawan_id' => 'required|string',
                'bulan' => 'required|integer|min:1|max:12',
                'tahun' => 'required|integer|min:2020'
            ]);

            // Get karyawan data
            $karyawanResponse = Http::timeout(30)->get($this->baseUrl . '/karyawan/' . $validated['karyawan_id']);
            
            if (!$karyawanResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data karyawan tidak ditemukan'
                ], 404);
            }

            $karyawan = $karyawanResponse->json()['data'];

            // Get attendance data for the month
            $attendanceParams = [
                'karyawan_id' => $validated['karyawan_id'],
                'bulan' => $validated['bulan'],
                'tahun' => $validated['tahun']
            ];

            $attendanceResponse = Http::timeout(30)->get($this->baseUrl . '/absensi', $attendanceParams);
            
            if (!$attendanceResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data absensi'
                ], 500);
            }

            $attendanceData = $attendanceResponse->json()['data']['data'] ?? [];

            // Calculate salary using helper
            $salaryCalculation = \App\Helpers\GajiCalculatorHelper::calculateGaji(
                $karyawan['id'],
                $validated['bulan'],
                $validated['tahun']
            );

            // Prepare detailed attendance summary
            $attendanceSummary = [
                'total_hari_kerja' => count($attendanceData),
                'hadir' => count(array_filter($attendanceData, fn($item) => $item['status'] === 'HADIR')),
                'izin' => count(array_filter($attendanceData, fn($item) => $item['status'] === 'IZIN')),
                'sakit' => count(array_filter($attendanceData, fn($item) => $item['status'] === 'SAKIT')),
                'alpha' => count(array_filter($attendanceData, fn($item) => $item['status'] === 'ALPHA')),
                'cuti' => count(array_filter($attendanceData, fn($item) => $item['status'] === 'CUTI')),
                'total_menit_terlambat' => array_sum(array_column($attendanceData, 'menit_terlambat')),
                'total_menit_pulang_cepat' => array_sum(array_column($attendanceData, 'menit_pulang_cepat')),
                'total_potongan_terlambat' => array_sum(array_column($attendanceData, 'potongan_terlambat')),
                'total_potongan_alpha' => array_sum(array_column($attendanceData, 'potongan_alpha'))
            ];

            return response()->json([
                'success' => true,
                'message' => 'Perhitungan gaji berhasil',
                'data' => [
                    'karyawan' => $karyawan,
                    'periode' => [
                        'bulan' => $validated['bulan'],
                        'tahun' => $validated['tahun'],
                        'bulan_nama' => $this->getBulanNama($validated['bulan'])
                    ],
                    'salary_calculation' => $salaryCalculation,
                    'attendance_summary' => $attendanceSummary,
                    'attendance_details' => $attendanceData
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error calculating salary from attendance: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung gaji',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk calculate salary for all employees
     */
    public function bulkCalculateSalary(Request $request)
    {
        try {
            $validated = $request->validate([
                'bulan' => 'required|integer|min:1|max:12',
                'tahun' => 'required|integer|min:2020',
                'karyawan_ids' => 'nullable|array',
                'karyawan_ids.*' => 'string'
            ]);

            // Get all karyawan or specific ones
            $karyawanParams = ['per_page' => 1000];
            $karyawanResponse = Http::timeout(30)->get($this->baseUrl . '/karyawan', $karyawanParams);
            
            if (!$karyawanResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data karyawan'
                ], 500);
            }

            $allKaryawan = $karyawanResponse->json()['data']['data'] ?? [];
            
            // Filter karyawan if specific IDs provided
            if (!empty($validated['karyawan_ids'])) {
                $allKaryawan = array_filter($allKaryawan, function($karyawan) use ($validated) {
                    return in_array($karyawan['id'], $validated['karyawan_ids']);
                });
            }

            $results = [];
            $errors = [];

            foreach ($allKaryawan as $karyawan) {
                try {
                    // Calculate salary for each employee
                    $salaryCalculation = \App\Helpers\GajiCalculatorHelper::calculateGaji(
                        $karyawan['id'],
                        $validated['bulan'],
                        $validated['tahun']
                    );

                    $results[] = [
                        'karyawan_id' => $karyawan['id'],
                        'nama' => $karyawan['nama'],
                        'nip' => $karyawan['nip'] ?? $karyawan['nik'],
                        'gaji_calculation' => $salaryCalculation
                    ];

                } catch (\Exception $e) {
                    $errors[] = [
                        'karyawan_id' => $karyawan['id'],
                        'nama' => $karyawan['nama'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($results) . ' perhitungan gaji berhasil dibuat',
                'data' => [
                    'periode' => [
                        'bulan' => $validated['bulan'],
                        'tahun' => $validated['tahun'],
                        'bulan_nama' => $this->getBulanNama($validated['bulan'])
                    ],
                    'successful_calculations' => $results,
                    'failed_calculations' => $errors,
                    'summary' => [
                        'total_processed' => count($allKaryawan),
                        'successful' => count($results),
                        'failed' => count($errors)
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error bulk calculating salary: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan perhitungan gaji bulk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get month name in Indonesian
     */
    private function getBulanNama($bulan)
    {
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return $namaBulan[$bulan] ?? 'Unknown';
    }
}