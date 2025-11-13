<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AturanPerusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AturanPerusahaanController extends Controller
{
    /**
     * Display a listing of aturan perusahaan
     */
    public function index(Request $request)
    {
        try {
            $query = AturanPerusahaan::query();

            // Filter by is_active status
            if ($request->has('is_active')) {
                $isActive = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
                $query->where('is_active', $isActive);
            }

            // Sort by created_at (newest first by default)
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
            $aturan = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data aturan perusahaan berhasil diambil',
                'data' => $aturan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data aturan perusahaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the active company rule
     */
    public function getActive()
    {
        try {
            $aturan = AturanPerusahaan::where('is_active', true)->first();

            if (!$aturan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada aturan perusahaan yang aktif',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Aturan perusahaan aktif berhasil diambil',
                'data' => $aturan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil aturan perusahaan aktif',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created aturan perusahaan
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'jam_masuk_kerja' => 'required|string|max:10',
                'jam_pulang_kerja' => 'required|string|max:10',
                'toleransi_terlambat' => 'nullable|integer|min:0',
                'potongan_per_menit_terlambat' => 'nullable|numeric|min:0',
                'potongan_per_hari_alpha' => 'nullable|numeric|min:0',
                'tarif_lembur_per_jam' => 'nullable|numeric|min:0',
                'tarif_lembur_libur' => 'nullable|numeric|min:0',
                'bonus_kehadiran_penuh' => 'nullable|numeric|min:0',
                'minimal_hadir_bonus' => 'nullable|integer|min:0',
                'hari_kerja_per_bulan' => 'nullable|integer|min:1',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validate time format (HH:MM)
            if (!$this->isValidTimeFormat($request->jam_masuk_kerja)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format jam_masuk_kerja tidak valid. Gunakan format HH:MM (contoh: 08:00)',
                    'errors' => ['jam_masuk_kerja' => ['Format harus HH:MM']]
                ], 422);
            }

            if (!$this->isValidTimeFormat($request->jam_pulang_kerja)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format jam_pulang_kerja tidak valid. Gunakan format HH:MM (contoh: 17:00)',
                    'errors' => ['jam_pulang_kerja' => ['Format harus HH:MM']]
                ], 422);
            }

            DB::beginTransaction();

            // If creating an active rule, deactivate all others
            if ($request->get('is_active', false)) {
                AturanPerusahaan::where('is_active', true)->update(['is_active' => false]);
            }

            $data = $request->all();
            $data['id'] = Str::uuid();

            // Set default values if not provided
            $data['toleransi_terlambat'] = $request->get('toleransi_terlambat', 15);
            $data['potongan_per_menit_terlambat'] = $request->get('potongan_per_menit_terlambat', 0);
            $data['potongan_per_hari_alpha'] = $request->get('potongan_per_hari_alpha', 0);
            $data['tarif_lembur_per_jam'] = $request->get('tarif_lembur_per_jam', 0);
            $data['tarif_lembur_libur'] = $request->get('tarif_lembur_libur', 0);
            $data['bonus_kehadiran_penuh'] = $request->get('bonus_kehadiran_penuh', 0);
            $data['minimal_hadir_bonus'] = $request->get('minimal_hadir_bonus', 22);
            $data['hari_kerja_per_bulan'] = $request->get('hari_kerja_per_bulan', 22);
            $data['is_active'] = $request->get('is_active', false);

            $aturan = AturanPerusahaan::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aturan perusahaan berhasil dibuat',
                'data' => $aturan
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat aturan perusahaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified aturan perusahaan
     */
    public function show($id)
    {
        try {
            $aturan = AturanPerusahaan::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail aturan perusahaan berhasil diambil',
                'data' => $aturan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Aturan perusahaan tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified aturan perusahaan
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'jam_masuk_kerja' => 'nullable|string|max:10',
                'jam_pulang_kerja' => 'nullable|string|max:10',
                'toleransi_terlambat' => 'nullable|integer|min:0',
                'potongan_per_menit_terlambat' => 'nullable|numeric|min:0',
                'potongan_per_hari_alpha' => 'nullable|numeric|min:0',
                'tarif_lembur_per_jam' => 'nullable|numeric|min:0',
                'tarif_lembur_libur' => 'nullable|numeric|min:0',
                'bonus_kehadiran_penuh' => 'nullable|numeric|min:0',
                'minimal_hadir_bonus' => 'nullable|integer|min:0',
                'hari_kerja_per_bulan' => 'nullable|integer|min:1',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validate time format if provided
            if ($request->has('jam_masuk_kerja') && !$this->isValidTimeFormat($request->jam_masuk_kerja)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format jam_masuk_kerja tidak valid. Gunakan format HH:MM',
                    'errors' => ['jam_masuk_kerja' => ['Format harus HH:MM']]
                ], 422);
            }

            if ($request->has('jam_pulang_kerja') && !$this->isValidTimeFormat($request->jam_pulang_kerja)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format jam_pulang_kerja tidak valid. Gunakan format HH:MM',
                    'errors' => ['jam_pulang_kerja' => ['Format harus HH:MM']]
                ], 422);
            }

            DB::beginTransaction();

            $aturan = AturanPerusahaan::findOrFail($id);

            // If activating this rule, deactivate all others
            if ($request->has('is_active') && $request->is_active) {
                AturanPerusahaan::where('id', '!=', $id)
                                ->where('is_active', true)
                                ->update(['is_active' => false]);
            }

            $data = $request->only([
                'jam_masuk_kerja',
                'jam_pulang_kerja',
                'toleransi_terlambat',
                'potongan_per_menit_terlambat',
                'potongan_per_hari_alpha',
                'tarif_lembur_per_jam',
                'tarif_lembur_libur',
                'bonus_kehadiran_penuh',
                'minimal_hadir_bonus',
                'hari_kerja_per_bulan',
                'is_active',
            ]);

            $aturan->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aturan perusahaan berhasil diupdate',
                'data' => $aturan->fresh()
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate aturan perusahaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified aturan perusahaan
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $aturan = AturanPerusahaan::findOrFail($id);

            // Prevent deleting if it's the only rule or the active rule
            if ($aturan->is_active) {
                $otherRules = AturanPerusahaan::where('id', '!=', $id)->count();
                
                if ($otherRules === 0) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak dapat menghapus aturan perusahaan yang sedang aktif dan satu-satunya. Buat aturan baru terlebih dahulu.'
                    ], 422);
                }

                // Auto-activate the most recent other rule
                $newActiveRule = AturanPerusahaan::where('id', '!=', $id)
                                                 ->orderBy('created_at', 'desc')
                                                 ->first();
                
                if ($newActiveRule) {
                    $newActiveRule->update(['is_active' => true]);
                }
            }

            $aturan->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aturan perusahaan berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus aturan perusahaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate a specific company rule (deactivate others)
     */
    public function activate($id)
    {
        try {
            DB::beginTransaction();

            $aturan = AturanPerusahaan::findOrFail($id);

            // Deactivate all other rules
            AturanPerusahaan::where('id', '!=', $id)
                            ->where('is_active', true)
                            ->update(['is_active' => false]);

            // Activate this rule
            $aturan->update(['is_active' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aturan perusahaan berhasil diaktifkan',
                'data' => $aturan->fresh()
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengaktifkan aturan perusahaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate a specific company rule
     */
    public function deactivate($id)
    {
        try {
            DB::beginTransaction();

            $aturan = AturanPerusahaan::findOrFail($id);

            // Check if there's another active rule
            $otherActiveRule = AturanPerusahaan::where('id', '!=', $id)
                                               ->where('is_active', true)
                                               ->exists();

            if (!$otherActiveRule && $aturan->is_active) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menonaktifkan aturan terakhir yang aktif. Aktifkan aturan lain terlebih dahulu.'
                ], 422);
            }

            $aturan->update(['is_active' => false]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aturan perusahaan berhasil dinonaktifkan',
                'data' => $aturan->fresh()
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menonaktifkan aturan perusahaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate an existing rule (create a copy)
     */
    public function duplicate($id)
    {
        try {
            DB::beginTransaction();

            $aturan = AturanPerusahaan::findOrFail($id);

            // Create a copy with is_active = false
            $newAturan = $aturan->replicate();
            $newAturan->id = Str::uuid();
            $newAturan->is_active = false;
            $newAturan->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aturan perusahaan berhasil diduplikasi',
                'data' => $newAturan
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menduplikasi aturan perusahaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get summary of how company rules affect calculations
     */
    public function summary()
    {
        try {
            $aturan = AturanPerusahaan::where('is_active', true)->first();

            if (!$aturan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada aturan perusahaan yang aktif',
                    'data' => null
                ], 404);
            }

            // Calculate example scenarios
            $summary = [
                'aturan_aktif' => $aturan,
                'contoh_perhitungan' => [
                    'keterlambatan' => [
                        'deskripsi' => 'Jika karyawan terlambat 30 menit',
                        'menit_terlambat' => 30,
                        'toleransi' => $aturan->toleransi_terlambat,
                        'menit_dihitung' => max(0, 30 - $aturan->toleransi_terlambat),
                        'potongan' => max(0, 30 - $aturan->toleransi_terlambat) * $aturan->potongan_per_menit_terlambat,
                        'format' => 'Rp ' . number_format(max(0, 30 - $aturan->toleransi_terlambat) * $aturan->potongan_per_menit_terlambat, 0, ',', '.')
                    ],
                    'alpha' => [
                        'deskripsi' => 'Jika karyawan alpha 1 hari',
                        'potongan_per_hari' => $aturan->potongan_per_hari_alpha,
                        'format' => 'Rp ' . number_format($aturan->potongan_per_hari_alpha, 0, ',', '.')
                    ],
                    'lembur' => [
                        'deskripsi' => 'Jika karyawan lembur 3 jam di hari kerja',
                        'jam_lembur' => 3,
                        'tarif_per_jam' => $aturan->tarif_lembur_per_jam,
                        'total_kompensasi' => 3 * $aturan->tarif_lembur_per_jam,
                        'format' => 'Rp ' . number_format(3 * $aturan->tarif_lembur_per_jam, 0, ',', '.')
                    ],
                    'lembur_libur' => [
                        'deskripsi' => 'Jika karyawan lembur 3 jam di hari libur',
                        'jam_lembur' => 3,
                        'tarif_per_jam' => $aturan->tarif_lembur_libur,
                        'total_kompensasi' => 3 * $aturan->tarif_lembur_libur,
                        'format' => 'Rp ' . number_format(3 * $aturan->tarif_lembur_libur, 0, ',', '.')
                    ],
                    'bonus_kehadiran' => [
                        'deskripsi' => 'Jika karyawan hadir penuh ' . $aturan->minimal_hadir_bonus . ' hari atau lebih',
                        'minimal_hadir' => $aturan->minimal_hadir_bonus,
                        'bonus' => $aturan->bonus_kehadiran_penuh,
                        'format' => 'Rp ' . number_format($aturan->bonus_kehadiran_penuh, 0, ',', '.')
                    ]
                ],
                'jam_kerja' => [
                    'masuk' => $aturan->jam_masuk_kerja,
                    'pulang' => $aturan->jam_pulang_kerja,
                    'total_jam_kerja' => $this->calculateWorkHours($aturan->jam_masuk_kerja, $aturan->jam_pulang_kerja)
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Summary aturan perusahaan berhasil diambil',
                'data' => $summary
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil summary aturan perusahaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate time format (HH:MM)
     */
    private function isValidTimeFormat($time)
    {
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time);
    }

    /**
     * Calculate work hours between two times
     */
    private function calculateWorkHours($jamMasuk, $jamPulang)
    {
        try {
            $masuk = \Carbon\Carbon::parse($jamMasuk);
            $pulang = \Carbon\Carbon::parse($jamPulang);
            
            $diffInMinutes = $pulang->diffInMinutes($masuk);
            $hours = floor($diffInMinutes / 60);
            $minutes = $diffInMinutes % 60;
            
            return $hours . ' jam ' . $minutes . ' menit';
        } catch (\Exception $e) {
            return 'Invalid';
        }
    }
}
