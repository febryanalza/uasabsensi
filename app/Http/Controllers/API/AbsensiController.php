<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\AturanPerusahaan;
use App\Helpers\AbsensiCalculatorHelper;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AbsensiController extends Controller
{
    /**
     * Display a listing of absensi with filters
     */
    public function index(Request $request)
    {
        try {
            // Select only needed fields for API response
            $query = Absensi::select([
                'id', 'karyawan_id', 'tanggal', 'jam_masuk', 'jam_keluar',
                'status', 'keterangan', 'menit_terlambat', 'menit_pulang_cepat'
            ])->with(['karyawan:id,nip,nama,departemen,jabatan']);

            // Filter by karyawan
            if ($request->has('karyawan_id')) {
                $query->where('karyawan_id', $request->karyawan_id);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Optimize date range filtering
            if ($request->has('tanggal_dari') && $request->has('tanggal_sampai')) {
                $query->whereBetween('tanggal', [$request->tanggal_dari, $request->tanggal_sampai]);
            } elseif ($request->has('tanggal_dari')) {
                $query->where('tanggal', '>=', $request->tanggal_dari);
            } elseif ($request->has('tanggal_sampai')) {
                $query->where('tanggal', '<=', $request->tanggal_sampai);
            }

            // Filter by bulan dan tahun - use composite index
            if ($request->has('bulan') && $request->has('tahun')) {
                $query->whereRaw('YEAR(tanggal) = ? AND MONTH(tanggal) = ?', 
                    [$request->tahun, $request->bulan]);
            }

            // Filter by departemen - use join for better performance
            if ($request->has('departemen')) {
                $query->join('karyawan', 'absensi.karyawan_id', '=', 'karyawan.id')
                      ->where('karyawan.departemen', $request->departemen)
                      ->select('absensi.*'); // Re-select to avoid column conflicts
            }

            // Filter terlambat
            if ($request->boolean('terlambat')) {
                $query->where('menit_terlambat', '>', 0);
            }

            // Filter pulang cepat  
            if ($request->boolean('pulang_cepat')) {
                $query->where('menit_pulang_cepat', '>', 0);
            }

            // Search optimization - avoid whereHas for performance
            if ($request->has('search') && !empty(trim($request->search))) {
                $search = trim($request->search);
                $karyawanIds = DB::table('karyawan')
                    ->where('nama', 'LIKE', $search . '%')
                    ->orWhere('nip', 'LIKE', $search . '%')
                    ->pluck('id');
                    
                $query->whereIn('karyawan_id', $karyawanIds);
            }

            // Optimized sorting
            $sortBy = $request->get('sort_by', 'tanggal');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = min($request->get('per_page', 15), 100); // Limit max per_page
            $absensi = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data absensi berhasil diambil',
                'data' => $absensi
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data absensi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created absensi
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'karyawan_id' => 'required|exists:karyawan,id',
                'tanggal' => 'required|date',
                'jam_masuk' => 'nullable|date_format:Y-m-d H:i:s',
                'jam_keluar' => 'nullable|date_format:Y-m-d H:i:s|after:jam_masuk',
                'status' => 'required|in:HADIR,IZIN,SAKIT,ALPHA,CUTI',
                'keterangan' => 'nullable|string',
                'lokasi' => 'nullable|string|max:255',
                'foto_masuk' => 'nullable|string|max:255',
                'foto_keluar' => 'nullable|string|max:255',
                'rfid_masuk' => 'nullable|string|max:255',
                'rfid_keluar' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Check duplicate absensi untuk karyawan dan tanggal yang sama
            $existingAbsensi = Absensi::where('karyawan_id', $request->karyawan_id)
                                      ->whereDate('tanggal', $request->tanggal)
                                      ->first();

            if ($existingAbsensi) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Absensi untuk karyawan ini pada tanggal tersebut sudah ada',
                    'existing_data' => $existingAbsensi
                ], 422);
            }

            $karyawan = Karyawan::findOrFail($request->karyawan_id);
            $aturan = AturanPerusahaan::where('is_active', true)->first();

            if (!$aturan) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Aturan perusahaan belum dikonfigurasi. Hubungi administrator.'
                ], 422);
            }

            $data = $request->all();
            $data['id'] = Str::uuid();

            // Calculate attendance metrics using helper
            $attendanceMetrics = AbsensiCalculatorHelper::calculateAttendanceMetrics(
                $request->jam_masuk,
                $request->jam_keluar,
                $request->tanggal,
                $request->status
            );

            // Apply calculated values
            $data['menit_terlambat'] = $attendanceMetrics['menit_terlambat'];
            $data['menit_pulang_cepat'] = $attendanceMetrics['menit_pulang_cepat'];

            // Add calculation details for debugging/audit
            if (!empty($attendanceMetrics['calculations'])) {
                $existingKeterangan = $data['keterangan'] ?? '';
                $calculationDetails = implode('; ', $attendanceMetrics['calculations']);
                $data['keterangan'] = $existingKeterangan ? 
                    $existingKeterangan . ' | ' . $calculationDetails : 
                    $calculationDetails;
            }

            // Mark if attendance is on holiday
            if ($attendanceMetrics['is_holiday']) {
                $data['keterangan'] = ($data['keterangan'] ?? '') . ' | Hari Libur';
            }

            $absensi = Absensi::create($data);
            $absensi->load('karyawan');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil dicatat',
                'data' => $absensi
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat absensi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified absensi
     */
    public function show($id)
    {
        try {
            $absensi = Absensi::with(['karyawan'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail absensi berhasil diambil',
                'data' => $absensi
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified absensi
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'jam_masuk' => 'nullable|date_format:Y-m-d H:i:s',
                'jam_keluar' => 'nullable|date_format:Y-m-d H:i:s',
                'status' => 'nullable|in:HADIR,IZIN,SAKIT,ALPHA,CUTI',
                'keterangan' => 'nullable|string',
                'lokasi' => 'nullable|string|max:255',
                'foto_masuk' => 'nullable|string|max:255',
                'foto_keluar' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $absensi = Absensi::findOrFail($id);
            $aturan = AturanPerusahaan::where('is_active', true)->first();

            $data = $request->only([
                'jam_masuk', 'jam_keluar', 'status', 'keterangan',
                'lokasi', 'foto_masuk', 'foto_keluar'
            ]);

            // Recalculate keterlambatan jika jam_masuk diupdate
            if ($request->has('jam_masuk') && $request->jam_masuk && $aturan) {
                $status = $request->get('status', $absensi->status);
                
                if ($status === 'HADIR') {
                    $jamMasukKaryawan = Carbon::parse($request->jam_masuk);
                    $jamMasukAturan = Carbon::parse($absensi->tanggal->format('Y-m-d') . ' ' . $aturan->jam_masuk_kerja);

                    if ($jamMasukKaryawan->gt($jamMasukAturan)) {
                        $menitTerlambat = $jamMasukKaryawan->diffInMinutes($jamMasukAturan);
                        $data['menit_terlambat'] = $menitTerlambat;
                        
                        if ($aturan->potongan_per_menit > 0) {
                            $data['potongan_terlambat'] = $menitTerlambat * $aturan->potongan_per_menit;
                        }
                    } else {
                        $data['menit_terlambat'] = 0;
                        $data['potongan_terlambat'] = 0;
                    }
                }
            }

            // Recalculate pulang cepat jika jam_keluar diupdate
            if ($request->has('jam_keluar') && $request->jam_keluar && $aturan) {
                $jamMasuk = $request->get('jam_masuk', $absensi->jam_masuk);
                
                if ($jamMasuk) {
                    $jamKeluarKaryawan = Carbon::parse($request->jam_keluar);
                    $jamPulangAturan = Carbon::parse($absensi->tanggal->format('Y-m-d') . ' ' . $aturan->jam_pulang_kerja);

                    if ($jamKeluarKaryawan->lt($jamPulangAturan)) {
                        $menitPulangCepat = $jamPulangAturan->diffInMinutes($jamKeluarKaryawan);
                        $data['menit_pulang_cepat'] = $menitPulangCepat;
                    } else {
                        $data['menit_pulang_cepat'] = 0;
                    }
                }
            }

            // Update potongan alpha jika status berubah
            if ($request->has('status')) {
                if ($request->status === 'ALPHA' && $aturan && $aturan->potongan_alpha > 0) {
                    $data['potongan_alpha'] = $aturan->potongan_alpha;
                } else {
                    $data['potongan_alpha'] = 0;
                }
            }

            $absensi->update($data);
            $absensi->load('karyawan');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data absensi berhasil diupdate',
                'data' => $absensi
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate absensi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified absensi
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $absensi = Absensi::findOrFail($id);
            $karyawanNama = $absensi->karyawan->nama;
            $tanggal = $absensi->tanggal->format('Y-m-d');

            $absensi->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Absensi {$karyawanNama} tanggal {$tanggal} berhasil dihapus"
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus absensi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get absensi statistics
     */
    public function statistics(Request $request)
    {
        try {
            $query = Absensi::query();

            // Filter by date range
            if ($request->has('tanggal_dari')) {
                $query->whereDate('tanggal', '>=', $request->tanggal_dari);
            }
            if ($request->has('tanggal_sampai')) {
                $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
            }

            // Filter by bulan dan tahun
            if ($request->has('bulan') && $request->has('tahun')) {
                $query->whereMonth('tanggal', $request->bulan)
                      ->whereYear('tanggal', $request->tahun);
            }

            $totalAbsensi = $query->count();
            $hadirCount = (clone $query)->where('status', 'HADIR')->count();
            $izinCount = (clone $query)->where('status', 'IZIN')->count();
            $sakitCount = (clone $query)->where('status', 'SAKIT')->count();
            $alphaCount = (clone $query)->where('status', 'ALPHA')->count();
            $cutiCount = (clone $query)->where('status', 'CUTI')->count();
            
            $terlambatCount = (clone $query)->where('menit_terlambat', '>', 0)->count();
            $pulangCepatCount = (clone $query)->where('menit_pulang_cepat', '>', 0)->count();
            
            $totalPotTerlambat = (clone $query)->sum('potongan_terlambat');
            $totalPotAlpha = (clone $query)->sum('potongan_alpha');

            // Persentase kehadiran
            $persentaseHadir = $totalAbsensi > 0 ? round(($hadirCount / $totalAbsensi) * 100, 2) : 0;

            // Top 10 karyawan paling rajin
            $karyawanRajin = Absensi::select('karyawan_id', DB::raw('COUNT(*) as total_hadir'))
                ->where('status', 'HADIR')
                ->groupBy('karyawan_id')
                ->orderBy('total_hadir', 'desc')
                ->limit(10)
                ->with('karyawan:id,nip,nama,departemen')
                ->get();

            // Top 10 karyawan paling sering terlambat
            $karyawanTerlambat = Absensi::select('karyawan_id', 
                    DB::raw('COUNT(*) as jumlah_terlambat'),
                    DB::raw('SUM(menit_terlambat) as total_menit_terlambat'))
                ->where('menit_terlambat', '>', 0)
                ->groupBy('karyawan_id')
                ->orderBy('jumlah_terlambat', 'desc')
                ->limit(10)
                ->with('karyawan:id,nip,nama,departemen')
                ->get();

            // Statistik per departemen
            $byDepartemen = Absensi::join('karyawan', 'absensi.karyawan_id', '=', 'karyawan.id')
                ->select('karyawan.departemen',
                    DB::raw('COUNT(*) as total_absensi'),
                    DB::raw('SUM(CASE WHEN absensi.status = "HADIR" THEN 1 ELSE 0 END) as total_hadir'),
                    DB::raw('SUM(CASE WHEN absensi.status = "ALPHA" THEN 1 ELSE 0 END) as total_alpha'))
                ->groupBy('karyawan.departemen')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Statistik absensi berhasil diambil',
                'data' => [
                    'total_absensi' => $totalAbsensi,
                    'by_status' => [
                        'hadir' => $hadirCount,
                        'izin' => $izinCount,
                        'sakit' => $sakitCount,
                        'alpha' => $alphaCount,
                        'cuti' => $cutiCount,
                    ],
                    'persentase_hadir' => $persentaseHadir,
                    'kedisiplinan' => [
                        'total_terlambat' => $terlambatCount,
                        'total_pulang_cepat' => $pulangCepatCount,
                        'total_potongan_terlambat' => (float) $totalPotTerlambat,
                        'total_potongan_alpha' => (float) $totalPotAlpha,
                    ],
                    'karyawan_rajin' => $karyawanRajin,
                    'karyawan_terlambat' => $karyawanTerlambat,
                    'by_departemen' => $byDepartemen,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik absensi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk create absensi (for import)
     */
    public function bulkStore(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'absensi' => 'required|array',
                'absensi.*.karyawan_id' => 'required|exists:karyawan,id',
                'absensi.*.tanggal' => 'required|date',
                'absensi.*.status' => 'required|in:HADIR,IZIN,SAKIT,ALPHA,CUTI',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $created = [];
            $errors = [];
            $aturan = AturanPerusahaan::where('is_active', true)->first();

            foreach ($request->absensi as $index => $item) {
                try {
                    // Check duplicate
                    $exists = Absensi::where('karyawan_id', $item['karyawan_id'])
                                     ->whereDate('tanggal', $item['tanggal'])
                                     ->exists();

                    if ($exists) {
                        $errors[] = [
                            'index' => $index,
                            'data' => $item,
                            'error' => 'Absensi sudah ada untuk tanggal ini'
                        ];
                        continue;
                    }

                    $data = $item;
                    $data['id'] = Str::uuid();

                    // Calculate potongan if needed
                    if ($item['status'] === 'HADIR' && isset($item['jam_masuk']) && $aturan) {
                        $jamMasukKaryawan = Carbon::parse($item['jam_masuk']);
                        $jamMasukAturan = Carbon::parse($item['tanggal'] . ' ' . $aturan->jam_masuk_kerja);

                        if ($jamMasukKaryawan->gt($jamMasukAturan)) {
                            $menitTerlambat = $jamMasukKaryawan->diffInMinutes($jamMasukAturan);
                            $data['menit_terlambat'] = $menitTerlambat;
                            
                            if ($aturan->potongan_per_menit > 0) {
                                $data['potongan_terlambat'] = $menitTerlambat * $aturan->potongan_per_menit;
                            }
                        }
                    }

                    if ($item['status'] === 'ALPHA' && $aturan && $aturan->potongan_alpha > 0) {
                        $data['potongan_alpha'] = $aturan->potongan_alpha;
                    }

                    $absensi = Absensi::create($data);
                    $created[] = $absensi;

                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'data' => $item,
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($created) . ' absensi berhasil dibuat',
                'data' => [
                    'created' => $created,
                    'errors' => $errors
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan bulk create absensi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel/batalkan absensi (soft cancel with keterangan)
     */
    public function cancel(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'keterangan_batal' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $absensi = Absensi::findOrFail($id);
            
            // Update keterangan dengan prefix BATAL
            $keteranganBatal = "[DIBATALKAN] " . $request->keterangan_batal;
            if ($absensi->keterangan) {
                $keteranganBatal .= " | Keterangan sebelumnya: " . $absensi->keterangan;
            }

            $absensi->update([
                'keterangan' => $keteranganBatal,
                'status' => 'ALPHA', // Change status to ALPHA when cancelled
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil dibatalkan',
                'data' => $absensi
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
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
    public function rekapKaryawan(Request $request, $karyawanId)
    {
        try {
            $validator = Validator::make(array_merge($request->all(), ['karyawan_id' => $karyawanId]), [
                'karyawan_id' => 'required|exists:karyawan,id',
                'bulan' => 'required|integer|min:1|max:12',
                'tahun' => 'required|integer|min:2000|max:2100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $karyawan = Karyawan::findOrFail($karyawanId);
            $bulan = $request->bulan;
            $tahun = $request->tahun;

            $absensi = Absensi::where('karyawan_id', $karyawanId)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->orderBy('tanggal', 'asc')
                ->get();

            $totalHadir = $absensi->where('status', 'HADIR')->count();
            $totalIzin = $absensi->where('status', 'IZIN')->count();
            $totalSakit = $absensi->where('status', 'SAKIT')->count();
            $totalAlpha = $absensi->where('status', 'ALPHA')->count();
            $totalCuti = $absensi->where('status', 'CUTI')->count();
            
            $totalTerlambat = $absensi->where('menit_terlambat', '>', 0)->count();
            $totalMenitTerlambat = $absensi->sum('menit_terlambat');
            $totalPotTerlambat = $absensi->sum('potongan_terlambat');
            $totalPotAlpha = $absensi->sum('potongan_alpha');

            return response()->json([
                'success' => true,
                'message' => 'Rekap absensi berhasil diambil',
                'data' => [
                    'karyawan' => [
                        'id' => $karyawan->id,
                        'nip' => $karyawan->nip,
                        'nama' => $karyawan->nama,
                        'departemen' => $karyawan->departemen,
                        'jabatan' => $karyawan->jabatan,
                    ],
                    'periode' => [
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'nama_bulan' => Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y')
                    ],
                    'summary' => [
                        'total_hari' => $absensi->count(),
                        'hadir' => $totalHadir,
                        'izin' => $totalIzin,
                        'sakit' => $totalSakit,
                        'alpha' => $totalAlpha,
                        'cuti' => $totalCuti,
                        'total_terlambat' => $totalTerlambat,
                        'total_menit_terlambat' => $totalMenitTerlambat,
                        'total_potongan_terlambat' => (float) $totalPotTerlambat,
                        'total_potongan_alpha' => (float) $totalPotAlpha,
                        'total_potongan' => (float) ($totalPotTerlambat + $totalPotAlpha),
                    ],
                    'detail_absensi' => $absensi
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil rekap absensi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed attendance statistics for employee
     * 
     * @param string $karyawan_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAttendanceStats($karyawan_id)
    {
        try {
            // Check if karyawan exists
            $karyawan = Karyawan::find($karyawan_id);
            if (!$karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Karyawan tidak ditemukan'
                ], 404);
            }

            // Get active company rules
            $aturanPerusahaan = AturanPerusahaan::where('status', 'aktif')->first();
            if (!$aturanPerusahaan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aturan perusahaan aktif tidak ditemukan'
                ], 404);
            }

            // Calculate attendance statistics
            $bulan = now()->month;
            $tahun = now()->year;
            $stats = AbsensiCalculatorHelper::getDetailedAttendanceStats($karyawan_id, $bulan, $tahun);

            return response()->json([
                'success' => true,
                'message' => 'Statistik kehadiran berhasil diambil',
                'data' => [
                    'karyawan' => [
                        'id' => $karyawan->id,
                        'nama' => $karyawan->nama,
                        'nik' => $karyawan->nik
                    ],
                    'statistik_kehadiran' => $stats,
                    'aturan_perusahaan' => [
                        'jam_masuk_kerja' => $aturanPerusahaan->jam_masuk_kerja,
                        'jam_pulang_kerja' => $aturanPerusahaan->jam_pulang_kerja,
                        'toleransi_terlambat' => $aturanPerusahaan->toleransi_terlambat,
                        'minimal_hadir_bonus' => $aturanPerusahaan->minimal_hadir_bonus,
                        'bonus_kehadiran_penuh' => number_format($aturanPerusahaan->bonus_kehadiran_penuh, 0, ',', '.')
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik kehadiran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check monthly bonus eligibility for employee
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBonusEligibility(Request $request)
    {
        try {
            $validated = $request->validate([
                'karyawan_id' => 'required|string|exists:karyawan,id',
                'bulan' => 'nullable|integer|min:1|max:12',
                'tahun' => 'nullable|integer|min:2020'
            ]);

            $karyawan_id = $validated['karyawan_id'];
            $bulan = $validated['bulan'] ?? now()->month;
            $tahun = $validated['tahun'] ?? now()->year;

            // Get active company rules
            $aturanPerusahaan = AturanPerusahaan::where('status', 'aktif')->first();
            if (!$aturanPerusahaan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aturan perusahaan aktif tidak ditemukan'
                ], 404);
            }

            // Calculate bonus eligibility
            $eligibility = AbsensiCalculatorHelper::calculateAttendanceBonusEligibility(
                $karyawan_id, 
                $bulan, 
                $tahun
            );

            return response()->json([
                'success' => true,
                'message' => 'Kelayakan bonus berhasil dicek',
                'data' => [
                    'periode' => [
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'nama_bulan' => Carbon::create($tahun, $bulan, 1)->translatedFormat('F')
                    ],
                    'kelayakan_bonus' => $eligibility,
                    'aturan_bonus' => [
                        'minimal_kehadiran' => $aturanPerusahaan->minimal_hadir_bonus,
                        'nominal_bonus' => number_format($aturanPerusahaan->bonus_kehadiran_penuh, 0, ',', '.'),
                        'toleransi_terlambat' => $aturanPerusahaan->toleransi_terlambat
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengecek kelayakan bonus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate year-end attendance bonus for employee
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getYearEndBonus(Request $request)
    {
        try {
            $validated = $request->validate([
                'karyawan_id' => 'required|string|exists:karyawan,id',
                'tahun' => 'nullable|integer|min:2020'
            ]);

            $karyawan_id = $validated['karyawan_id'];
            $tahun = $validated['tahun'] ?? now()->year;

            // Get active company rules
            $aturanPerusahaan = AturanPerusahaan::where('status', 'aktif')->first();
            if (!$aturanPerusahaan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aturan perusahaan aktif tidak ditemukan'
                ], 404);
            }

            // Calculate year-end bonus
            $yearEndBonus = AbsensiCalculatorHelper::calculateYearEndAttendanceBonus(
                $karyawan_id, 
                $tahun
            );

            return response()->json([
                'success' => true,
                'message' => 'Bonus akhir tahun berhasil dihitung',
                'data' => [
                    'periode' => [
                        'tahun' => $tahun
                    ],
                    'bonus_akhir_tahun' => $yearEndBonus,
                    'aturan_bonus_akhir_tahun' => [
                        'minimal_bulan_memenuhi' => $aturanPerusahaan->minimal_bulan_bonus_tahunan ?? 12,
                        'multiplier_gaji' => $aturanPerusahaan->multiplier_bonus_tahunan ?? 1
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung bonus akhir tahun',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get company attendance rules
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompanyRules()
    {
        try {
            $aturanPerusahaan = AturanPerusahaan::where('status', 'aktif')->first();
            
            if (!$aturanPerusahaan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aturan perusahaan aktif tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Aturan perusahaan berhasil diambil',
                'data' => [
                    'id' => $aturanPerusahaan->id,
                    'nama' => $aturanPerusahaan->nama,
                    'jam_kerja' => [
                        'jam_masuk' => $aturanPerusahaan->jam_masuk_kerja,
                        'jam_pulang' => $aturanPerusahaan->jam_pulang_kerja,
                        'toleransi_terlambat' => $aturanPerusahaan->toleransi_terlambat . ' menit',
                        'toleransi_pulang_awal' => $aturanPerusahaan->toleransi_pulang_awal . ' menit'
                    ],
                    'bonus_kehadiran' => [
                        'minimal_hadir_bonus' => $aturanPerusahaan->minimal_hadir_bonus . ' hari',
                        'bonus_kehadiran_penuh' => 'Rp ' . number_format($aturanPerusahaan->bonus_kehadiran_penuh, 0, ',', '.'),
                        'minimal_bulan_bonus_tahunan' => $aturanPerusahaan->minimal_bulan_bonus_tahunan ?? 12,
                        'multiplier_bonus_tahunan' => $aturanPerusahaan->multiplier_bonus_tahunan ?? 1
                    ],
                    'potongan' => [
                        'potongan_per_hari_alpha' => 'Rp ' . number_format($aturanPerusahaan->potongan_per_hari_alpha, 0, ',', '.'),
                        'potongan_per_menit_terlambat' => 'Rp ' . number_format($aturanPerusahaan->potongan_per_menit_terlambat, 0, ',', '.'),
                        'potongan_pulang_awal' => 'Rp ' . number_format($aturanPerusahaan->potongan_pulang_awal, 0, ',', '.')
                    ],
                    'status' => $aturanPerusahaan->status,
                    'berlaku_sejak' => $aturanPerusahaan->created_at->translatedFormat('d F Y'),
                    'terakhir_diupdate' => $aturanPerusahaan->updated_at->translatedFormat('d F Y H:i')
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil aturan perusahaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
