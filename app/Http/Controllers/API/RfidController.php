<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AturanPerusahaan;
use App\Models\AvailableRfid;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RfidController extends Controller
{
    /**
     * Handle RFID scan dari NodeMCU
     * Endpoint: POST /api/rfid/scan
     * 
     * Request Body:
     * {
     *   "rfidCard": "A1B2C3D4",
     *   "lokasi": "Kantor"
     * }
     */
    public function scan(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'rfidCard' => 'required|string',
                'lokasi' => 'nullable|string',
            ]);

            $rfidCard = strtoupper(trim($request->rfidCard));
            $lokasi = $request->lokasi ?? 'Kantor';

            // Cari karyawan berdasarkan RFID
            $karyawan = Karyawan::where('rfid_card_number', $rfidCard)->first();

            if (!$karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kartu tidak terdaftar',
                    'rfid' => $rfidCard
                ], 404);
            }

            // Cek status karyawan
            if ($karyawan->status !== 'AKTIF') {
                return response()->json([
                    'success' => false,
                    'message' => 'Karyawan tidak aktif',
                    'nama' => $karyawan->nama
                ], 403);
            }

            // Ambil aturan perusahaan
            $aturan = AturanPerusahaan::active()->first();
            if (!$aturan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aturan belum diatur'
                ], 500);
            }

            $today = Carbon::today();
            $now = Carbon::now();

            // Cek absensi hari ini
            $absensi = Absensi::where('karyawan_id', $karyawan->id)
                ->where('tanggal', $today)
                ->first();

            // Tentukan jam masuk dan pulang
            $jamMasuk = Carbon::parse($today->format('Y-m-d') . ' ' . $aturan->jam_masuk_kerja);
            $jamPulang = Carbon::parse($today->format('Y-m-d') . ' ' . $aturan->jam_pulang_kerja);

            // LOGIKA: Jika belum ada absensi hari ini = ABSEN MASUK
            if (!$absensi) {
                $absensi = $this->createAbsensiMasuk(
                    $karyawan,
                    $now,
                    $rfidCard,
                    $lokasi,
                    $jamMasuk,
                    $aturan
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Absen masuk berhasil',
                    'type' => 'masuk',
                    'nama' => $karyawan->nama,
                    'nip' => $karyawan->nip,
                    'waktu' => $now->format('H:i:s'),
                    'status' => $absensi->status,
                    'terlambat' => $absensi->menit_terlambat > 0 ? $absensi->menit_terlambat . ' menit' : null
                ], 200);
            }

            // LOGIKA: Jika sudah ada absensi dan belum ada jam_keluar = ABSEN KELUAR
            if ($absensi && !$absensi->jam_keluar) {
                $this->updateAbsensiKeluar(
                    $absensi,
                    $now,
                    $rfidCard,
                    $jamPulang
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Absen keluar berhasil',
                    'type' => 'keluar',
                    'nama' => $karyawan->nama,
                    'nip' => $karyawan->nip,
                    'waktu' => $now->format('H:i:s'),
                    'jam_masuk' => Carbon::parse($absensi->jam_masuk)->format('H:i:s'),
                    'jam_keluar' => $now->format('H:i:s')
                ], 200);
            }

            // LOGIKA: Jika sudah absen masuk DAN keluar = Sudah lengkap
            return response()->json([
                'success' => false,
                'message' => 'Sudah absen lengkap',
                'nama' => $karyawan->nama,
                'jam_masuk' => Carbon::parse($absensi->jam_masuk)->format('H:i:s'),
                'jam_keluar' => Carbon::parse($absensi->jam_keluar)->format('H:i:s')
            ], 400);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buat record absensi masuk baru
     */
    private function createAbsensiMasuk($karyawan, $waktuMasuk, $rfidCard, $lokasi, $jamMasuk, $aturan)
    {
        $menitTerlambat = 0;
        $potonganTerlambat = 0;

        // Hitung keterlambatan
        if ($waktuMasuk->gt($jamMasuk->addMinutes($aturan->toleransi_terlambat))) {
            $menitTerlambat = $waktuMasuk->diffInMinutes($jamMasuk);
            $potonganTerlambat = $menitTerlambat * $aturan->potongan_per_menit_terlambat;
        }

        return Absensi::create([
            'id' => Str::uuid(),
            'karyawan_id' => $karyawan->id,
            'tanggal' => Carbon::today(),
            'jam_masuk' => $waktuMasuk,
            'jam_keluar' => null,
            'status' => 'HADIR',
            'lokasi' => $lokasi,
            'rfid_masuk' => $rfidCard,
            'menit_terlambat' => $menitTerlambat,
        ]);
    }

    /**
     * Update absensi dengan jam keluar
     */
    private function updateAbsensiKeluar($absensi, $waktuKeluar, $rfidCard, $jamPulang)
    {
        $menitPulangCepat = 0;

        // Hitung pulang cepat (jika ada aturan)
        if ($waktuKeluar->lt($jamPulang)) {
            $menitPulangCepat = $waktuKeluar->diffInMinutes($jamPulang);
        }

        $absensi->update([
            'jam_keluar' => $waktuKeluar,
            'rfid_keluar' => $rfidCard,
            'menit_pulang_cepat' => $menitPulangCepat,
        ]);

        return $absensi;
    }

    /**
     * Get status absensi karyawan hari ini
     * Endpoint: GET /api/rfid/status/{rfidCard}
     */
    public function status($rfidCard)
    {
        try {
            $rfidCard = strtoupper(trim($rfidCard));

            $karyawan = Karyawan::where('rfid_card_number', $rfidCard)->first();

            if (!$karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kartu tidak terdaftar'
                ], 404);
            }

            $today = Carbon::today();
            $absensi = Absensi::where('karyawan_id', $karyawan->id)
                ->where('tanggal', $today)
                ->first();

            if (!$absensi) {
                return response()->json([
                    'success' => true,
                    'message' => 'Belum absen hari ini',
                    'nama' => $karyawan->nama,
                    'sudah_masuk' => false,
                    'sudah_keluar' => false
                ], 200);
            }

            return response()->json([
                'success' => true,
                'nama' => $karyawan->nama,
                'nip' => $karyawan->nip,
                'sudah_masuk' => $absensi->jam_masuk ? true : false,
                'sudah_keluar' => $absensi->jam_keluar ? true : false,
                'jam_masuk' => $absensi->jam_masuk ? Carbon::parse($absensi->jam_masuk)->format('H:i:s') : null,
                'jam_keluar' => $absensi->jam_keluar ? Carbon::parse($absensi->jam_keluar)->format('H:i:s') : null,
                'status' => $absensi->status
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test endpoint untuk cek koneksi
     * Endpoint: GET /api/rfid/test
     */
    public function test()
    {
        return response()->json([
            'success' => true,
            'message' => 'RFID API Ready',
            'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
            'server_time' => Carbon::now()->format('H:i:s')
        ], 200);
    }

    /**
     * Auto-register RFID card
     * Endpoint: POST /api/rfid
     * 
     * Request Body:
     * {
     *   "rfidUid": "A1B2C3D4"
     * }
     * 
     * Response untuk NodeMCU (simple):
     * {
     *   "success": true,
     *   "message": "Kartu berhasil didaftarkan",
     *   "data": {
     *     "cardNumber": "A1B2C3D4",
     *     "cardType": "MIFARE",
     *     "status": "AVAILABLE",
     *     "isNew": true,
     *     "isAssigned": false
     *   }
     * }
     */
    public function register(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'rfidUid' => 'required|string',
            ]);

            $rfidUid = strtoupper(trim($request->rfidUid));

            // Cek apakah kartu sudah terdaftar
            $existingCard = AvailableRfid::where('card_number', $rfidUid)->first();

            if ($existingCard) {
                // Kartu sudah ada, cek apakah sudah di-assign ke karyawan
                $karyawan = null;
                $isAssigned = false;

                if ($existingCard->karyawan_id) {
                    $karyawan = Karyawan::find($existingCard->karyawan_id);
                    $isAssigned = true;
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Kartu sudah terdaftar',
                    'data' => [
                        'cardNumber' => $existingCard->card_number,
                        'cardType' => $existingCard->card_type ?? 'MIFARE',
                        'status' => $existingCard->status,
                        'isNew' => false,
                        'isAssigned' => $isAssigned,
                        'assignedAt' => $existingCard->assigned_at ? Carbon::parse($existingCard->assigned_at)->format('Y-m-d H:i:s') : null,
                        'karyawan' => $karyawan ? [
                            'id' => $karyawan->id,
                            'nip' => $karyawan->nip,
                            'nama' => $karyawan->nama,
                            'jabatan' => $karyawan->jabatan,
                            'departemen' => $karyawan->departemen,
                        ] : null
                    ]
                ], 200);
            }

            // Kartu baru, daftarkan ke database
            $newCard = AvailableRfid::create([
                'id' => Str::uuid(),
                'card_number' => $rfidUid,
                'card_type' => 'MIFARE', // Default type
                'status' => 'AVAILABLE',
                'assigned_at' => null,
                'notes' => 'Auto-registered via NodeMCU',
                'karyawan_id' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kartu berhasil didaftarkan',
                'data' => [
                    'cardNumber' => $newCard->card_number,
                    'cardType' => $newCard->card_type,
                    'status' => $newCard->status,
                    'isNew' => true,
                    'isAssigned' => false,
                    'registeredAt' => $newCard->created_at->format('Y-m-d H:i:s'),
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of registered RFID cards
     * Endpoint: GET /api/rfid
     * 
     * Query params:
     * - status: AVAILABLE, ASSIGNED, DAMAGED, LOST, INACTIVE
     * - page: pagination
     */
    public function index(Request $request)
    {
        try {
            $query = AvailableRfid::query();

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', strtoupper($request->status));
            }

            // Load karyawan relation if assigned
            $query->with('karyawan:id,nip,nama,jabatan,departemen');

            // Pagination
            $perPage = $request->get('per_page', 15);
            $cards = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data kartu RFID berhasil diambil',
                'data' => $cards->items(),
                'pagination' => [
                    'total' => $cards->total(),
                    'per_page' => $cards->perPage(),
                    'current_page' => $cards->currentPage(),
                    'last_page' => $cards->lastPage(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detail of specific RFID card
     * Endpoint: GET /api/rfid/{cardNumber}
     */
    public function show($cardNumber)
    {
        try {
            $cardNumber = strtoupper(trim($cardNumber));

            $card = AvailableRfid::where('card_number', $cardNumber)
                ->with('karyawan')
                ->first();

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kartu tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail kartu RFID',
                'data' => [
                    'id' => $card->id,
                    'cardNumber' => $card->card_number,
                    'cardType' => $card->card_type,
                    'status' => $card->status,
                    'assignedAt' => $card->assigned_at ? Carbon::parse($card->assigned_at)->format('Y-m-d H:i:s') : null,
                    'notes' => $card->notes,
                    'isAssigned' => $card->karyawan_id ? true : false,
                    'karyawan' => $card->karyawan ? [
                        'id' => $card->karyawan->id,
                        'nip' => $card->karyawan->nip,
                        'nama' => $card->karyawan->nama,
                        'email' => $card->karyawan->email,
                        'jabatan' => $card->karyawan->jabatan,
                        'departemen' => $card->karyawan->departemen,
                        'status' => $card->karyawan->status,
                    ] : null,
                    'createdAt' => $card->created_at->format('Y-m-d H:i:s'),
                    'updatedAt' => $card->updated_at->format('Y-m-d H:i:s'),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete RFID card
     * Endpoint: DELETE /api/rfid/{cardNumber}
     */
    public function destroy($cardNumber)
    {
        try {
            $cardNumber = strtoupper(trim($cardNumber));

            $card = AvailableRfid::where('card_number', $cardNumber)->first();

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kartu tidak ditemukan'
                ], 404);
            }

            // Cek apakah kartu sudah di-assign
            if ($card->status === 'ASSIGNED' && $card->karyawan_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kartu sedang digunakan, tidak dapat dihapus'
                ], 400);
            }

            $card->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kartu berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

