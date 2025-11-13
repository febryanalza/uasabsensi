<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Lembur;
use App\Models\Karyawan;
use App\Models\AturanPerusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LemburController extends Controller
{
    /**
     * Display a listing of lembur
     */
    public function index(Request $request)
    {
        try {
            $query = Lembur::with(['karyawan']);

            // Filter by karyawan
            if ($request->has('karyawan_id')) {
                $query->where('karyawan_id', $request->karyawan_id);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by tanggal range
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
            $sortBy = $request->get('sort_by', 'tanggal');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
            $lembur = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data lembur berhasil diambil',
                'data' => $lembur
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data lembur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created lembur
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'karyawan_id' => 'required|exists:karyawan,id',
                'tanggal' => 'required|date',
                'jam_mulai' => 'required|date_format:Y-m-d H:i:s',
                'jam_selesai' => 'required|date_format:Y-m-d H:i:s|after:jam_mulai',
                'keterangan' => 'nullable|string',
                'status' => 'nullable|in:PENDING,DISETUJUI,DITOLAK',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $karyawan = Karyawan::findOrFail($request->karyawan_id);
            $aturan = AturanPerusahaan::where('is_active', true)->first();

            $data = $request->all();
            $data['id'] = Str::uuid();
            $data['status'] = $data['status'] ?? 'PENDING';

            // Hitung durasi lembur
            $jamMulai = Carbon::parse($request->jam_mulai);
            $jamSelesai = Carbon::parse($request->jam_selesai);
            $durasiJam = $jamSelesai->diffInHours($jamMulai, true);
            $data['durasi_jam'] = $durasiJam;

            // Hitung tarif dan kompensasi berdasarkan aturan perusahaan
            if ($aturan) {
                // Cek apakah tanggal lembur adalah hari libur
                $isLibur = \App\Models\HariLibur::whereDate('tanggal', $request->tanggal)->exists();
                
                if ($isLibur && $aturan->tarif_lembur_libur > 0) {
                    $data['tarif_per_jam'] = $aturan->tarif_lembur_libur;
                } else if ($aturan->tarif_lembur_per_jam > 0) {
                    $data['tarif_per_jam'] = $aturan->tarif_lembur_per_jam;
                }

                if (isset($data['tarif_per_jam'])) {
                    $data['total_kompensasi'] = $durasiJam * $data['tarif_per_jam'];
                }
            }

            $lembur = Lembur::create($data);
            $lembur->load('karyawan');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data lembur berhasil dicatat',
                'data' => $lembur
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat lembur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified lembur
     */
    public function show($id)
    {
        try {
            $lembur = Lembur::with(['karyawan'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Data lembur berhasil diambil',
                'data' => $lembur
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data lembur tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified lembur
     */
    public function update(Request $request, $id)
    {
        try {
            $lembur = Lembur::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'karyawan_id' => 'sometimes|required|exists:karyawan,id',
                'tanggal' => 'sometimes|required|date',
                'jam_mulai' => 'sometimes|required|date_format:Y-m-d H:i:s',
                'jam_selesai' => 'sometimes|required|date_format:Y-m-d H:i:s|after:jam_mulai',
                'keterangan' => 'nullable|string',
                'status' => 'nullable|in:PENDING,DISETUJUI,DITOLAK',
                'disetujui_oleh' => 'nullable|exists:users,id',
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
                'karyawan_id', 'tanggal', 'jam_mulai', 'jam_selesai', 
                'keterangan', 'status', 'disetujui_oleh'
            ]);

            // Recalculate if time changes
            if (isset($data['jam_mulai']) || isset($data['jam_selesai'])) {
                $jamMulai = Carbon::parse($data['jam_mulai'] ?? $lembur->jam_mulai);
                $jamSelesai = Carbon::parse($data['jam_selesai'] ?? $lembur->jam_selesai);
                $durasiJam = $jamSelesai->diffInHours($jamMulai, true);
                $data['durasi_jam'] = $durasiJam;

                // Recalculate compensation
                $aturan = AturanPerusahaan::where('is_active', true)->first();
                if ($aturan) {
                    $tanggal = $data['tanggal'] ?? $lembur->tanggal;
                    $isLibur = \App\Models\HariLibur::whereDate('tanggal', $tanggal)->exists();
                    
                    if ($isLibur && $aturan->tarif_lembur_libur > 0) {
                        $data['tarif_per_jam'] = $aturan->tarif_lembur_libur;
                    } else if ($aturan->tarif_lembur_per_jam > 0) {
                        $data['tarif_per_jam'] = $aturan->tarif_lembur_per_jam;
                    }

                    if (isset($data['tarif_per_jam'])) {
                        $data['total_kompensasi'] = $durasiJam * $data['tarif_per_jam'];
                    }
                }
            }

            // Set approval timestamp if approved/rejected
            if (isset($data['status']) && in_array($data['status'], ['DISETUJUI', 'DITOLAK'])) {
                $data['tanggal_disetujui'] = now();
            }

            $lembur->update($data);
            $lembur->load('karyawan');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data lembur berhasil diperbarui',
                'data' => $lembur
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data lembur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified lembur
     */
    public function destroy($id)
    {
        try {
            $lembur = Lembur::findOrFail($id);
            
            // Only allow deletion if status is PENDING
            if ($lembur->status !== 'PENDING') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya data lembur dengan status PENDING yang dapat dihapus'
                ], 422);
            }

            $lembur->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data lembur berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data lembur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve or reject lembur
     */
    public function approve(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:DISETUJUI,DITOLAK',
                'disetujui_oleh' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $lembur = Lembur::findOrFail($id);

            if ($lembur->status !== 'PENDING') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya lembur dengan status PENDING yang dapat diproses'
                ], 422);
            }

            $lembur->update([
                'status' => $request->status,
                'disetujui_oleh' => $request->disetujui_oleh,
                'tanggal_disetujui' => now()
            ]);

            $lembur->load('karyawan');

            $message = $request->status === 'DISETUJUI' ? 'Lembur berhasil disetujui' : 'Lembur ditolak';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $lembur
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses persetujuan lembur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}