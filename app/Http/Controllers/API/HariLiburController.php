<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class HariLiburController extends Controller
{
    /**
     * Display a listing of hari libur
     */
    public function index(Request $request)
    {
        try {
            $query = HariLibur::query();

            // Filter by is_nasional
            if ($request->has('is_nasional')) {
                $isNasional = filter_var($request->is_nasional, FILTER_VALIDATE_BOOLEAN);
                $query->where('is_nasional', $isNasional);
            }

            // Filter by year
            if ($request->has('tahun')) {
                $query->whereYear('tanggal', $request->tahun);
            }

            // Filter by month and year
            if ($request->has('bulan') && $request->has('tahun')) {
                $query->whereMonth('tanggal', $request->bulan)
                      ->whereYear('tanggal', $request->tahun);
            }

            // Filter by date range
            if ($request->has('tanggal_dari')) {
                $query->whereDate('tanggal', '>=', $request->tanggal_dari);
            }
            if ($request->has('tanggal_sampai')) {
                $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
            }

            // Search by nama
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('nama', 'like', "%{$search}%");
            }

            // Sort
            $sortBy = $request->get('sort_by', 'tanggal');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
            $hariLibur = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data hari libur berhasil diambil',
                'data' => $hariLibur
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data hari libur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created hari libur
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tanggal' => 'required|date|unique:hari_libur,tanggal',
                'nama' => 'required|string|max:255',
                'deskripsi' => 'nullable|string',
                'is_nasional' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $data = $request->all();
            $data['id'] = Str::uuid();
            $data['is_nasional'] = $data['is_nasional'] ?? false;

            $hariLibur = HariLibur::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data hari libur berhasil dibuat',
                'data' => $hariLibur
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat data hari libur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified hari libur
     */
    public function show($id)
    {
        try {
            $hariLibur = HariLibur::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Data hari libur berhasil diambil',
                'data' => $hariLibur
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data hari libur tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified hari libur
     */
    public function update(Request $request, $id)
    {
        try {
            $hariLibur = HariLibur::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'tanggal' => 'sometimes|required|date|unique:hari_libur,tanggal,' . $id,
                'nama' => 'sometimes|required|string|max:255',
                'deskripsi' => 'nullable|string',
                'is_nasional' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $data = $request->only(['tanggal', 'nama', 'deskripsi', 'is_nasional']);
            $hariLibur->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data hari libur berhasil diperbarui',
                'data' => $hariLibur
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data hari libur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified hari libur
     */
    public function destroy($id)
    {
        try {
            $hariLibur = HariLibur::findOrFail($id);
            $hariLibur->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data hari libur berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data hari libur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get holidays for specific month and year
     */
    public function getByMonth(Request $request)
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

            $hariLibur = HariLibur::whereMonth('tanggal', $request->bulan)
                                  ->whereYear('tanggal', $request->tahun)
                                  ->orderBy('tanggal', 'asc')
                                  ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data hari libur berhasil diambil',
                'data' => $hariLibur
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data hari libur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get upcoming holidays (next 30 days)
     */
    public function getUpcoming()
    {
        try {
            $today = Carbon::today();
            $nextMonth = $today->copy()->addDays(30);

            $hariLibur = HariLibur::whereBetween('tanggal', [$today, $nextMonth])
                                  ->orderBy('tanggal', 'asc')
                                  ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data hari libur mendatang berhasil diambil',
                'data' => $hariLibur
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data hari libur mendatang',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if a specific date is a holiday
     */
    public function checkHoliday(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tanggal' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $hariLibur = HariLibur::whereDate('tanggal', $request->tanggal)->first();

            if ($hariLibur) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tanggal tersebut adalah hari libur',
                    'data' => [
                        'is_holiday' => true,
                        'holiday_data' => $hariLibur
                    ]
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Tanggal tersebut bukan hari libur',
                    'data' => [
                        'is_holiday' => false,
                        'holiday_data' => null
                    ]
                ], 200);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa hari libur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create bulk holidays
     */
    public function createBulk(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'holidays' => 'required|array|min:1',
                'holidays.*.tanggal' => 'required|date',
                'holidays.*.nama' => 'required|string|max:255',
                'holidays.*.deskripsi' => 'nullable|string',
                'holidays.*.is_nasional' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $results = ['success' => [], 'failed' => []];

            foreach ($request->holidays as $holidayData) {
                // Check if holiday already exists
                $existingHoliday = HariLibur::whereDate('tanggal', $holidayData['tanggal'])->first();

                if ($existingHoliday) {
                    $results['failed'][] = [
                        'tanggal' => $holidayData['tanggal'],
                        'nama' => $holidayData['nama'],
                        'reason' => 'Hari libur pada tanggal tersebut sudah ada'
                    ];
                    continue;
                }

                try {
                    $data = $holidayData;
                    $data['id'] = Str::uuid();
                    $data['is_nasional'] = $data['is_nasional'] ?? false;

                    HariLibur::create($data);
                    
                    $results['success'][] = [
                        'tanggal' => $holidayData['tanggal'],
                        'nama' => $holidayData['nama']
                    ];

                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'tanggal' => $holidayData['tanggal'],
                        'nama' => $holidayData['nama'],
                        'reason' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bulk create hari libur selesai',
                'data' => $results
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal create bulk hari libur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}