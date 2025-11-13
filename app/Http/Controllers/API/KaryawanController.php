<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\User;
use App\Models\AvailableRfid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class KaryawanController extends Controller
{
    /**
     * Display a listing of karyawan.
     */
    public function index(Request $request)
    {
        try {
            // Select only needed fields for better performance
            $query = Karyawan::select([
                'id', 'nip', 'nama', 'email', 'jabatan', 'departemen', 
                'telepon', 'status', 'tanggal_masuk', 'created_at'
            ]);

            // Eager load only necessary relations with specific fields
            if ($request->get('with_user', false)) {
                $query->with('user:id,karyawan_id,email,role,is_active');
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by departemen
            if ($request->has('departemen')) {
                $query->where('departemen', $request->departemen);
            }

            // Filter by jabatan - optimize LIKE query
            if ($request->has('jabatan')) {
                $jabatan = $request->jabatan;
                $query->where('jabatan', 'LIKE', $jabatan . '%'); // Remove leading wildcard for index usage
            }

            // Search - optimize with compound index
            if ($request->has('search')) {
                $search = trim($request->search);
                if (!empty($search)) {
                    $query->where(function($q) use ($search) {
                        $q->where('nama', 'LIKE', $search . '%')  // Optimize for index
                          ->orWhere('nip', 'LIKE', $search . '%')
                          ->orWhere('email', 'LIKE', $search . '%');
                    });
                }
            }

            // Pagination with optimized ordering
            $perPage = min($request->input('per_page', 15), 100); // Limit max per_page
            $orderBy = $request->get('order_by', 'nama'); // Default to nama for better UX
            $orderDir = $request->get('order_dir', 'asc');
            
            $karyawan = $query->orderBy($orderBy, $orderDir)->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data karyawan berhasil diambil',
                'data' => $karyawan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data karyawan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created karyawan with user account.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nip' => 'required|string|max:100|unique:karyawan,nip',
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:karyawan,email|unique:users,email',
            'jabatan' => 'required|string|max:255',
            'departemen' => 'required|string|max:255',
            'telepon' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'tanggal_masuk' => 'nullable|date',
            'status' => 'nullable|in:AKTIF,CUTI,RESIGN',
            'rfid_card_number' => 'nullable|string|exists:available_rfid,card_number',
            
            // Salary fields (master data)
            'gaji_pokok' => 'nullable|numeric|min:0|max:999999999999.99',
            'tunjangan_jabatan' => 'nullable|numeric|min:0|max:999999999999.99',
            'tunjangan_transport' => 'nullable|numeric|min:0|max:999999999999.99',
            'tunjangan_makan' => 'nullable|numeric|min:0|max:999999999999.99',
            
            // User account fields
            'password' => 'required|string|min:8',
            'role' => 'nullable|in:ADMIN,MANAGER,USER',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create karyawan
            $karyawan = Karyawan::create([
                'id' => Str::uuid(),
                'nip' => $request->nip,
                'nama' => $request->nama,
                'email' => $request->email,
                'jabatan' => $request->jabatan,
                'departemen' => $request->departemen,
                'telepon' => $request->telepon,
                'alamat' => $request->alamat,
                'tanggal_masuk' => $request->tanggal_masuk ?? now(),
                'status' => $request->status ?? 'AKTIF',
                'rfid_card_number' => $request->rfid_card_number,
                'gaji_pokok' => $request->gaji_pokok ?? 0,
                'tunjangan_jabatan' => $request->tunjangan_jabatan ?? 0,
                'tunjangan_transport' => $request->tunjangan_transport ?? 0,
                'tunjangan_makan' => $request->tunjangan_makan ?? 0,
            ]);

            // Create user account
            $user = User::create([
                'id' => Str::uuid(),
                'karyawan_id' => $karyawan->id,
                'name' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'USER',
            ]);

            // Update RFID card status if provided
            if ($request->rfid_card_number) {
                $rfid = AvailableRfid::where('card_number', $request->rfid_card_number)->first();
                if ($rfid) {
                    $rfid->update([
                        'karyawan_id' => $karyawan->id,
                        'status' => 'ASSIGNED',
                        'assigned_at' => now(),
                    ]);
                }
            }

            DB::commit();

            // Load relationships
            $karyawan->load(['user', 'rfidCard']);

            return response()->json([
                'success' => true,
                'message' => 'Karyawan dan akun login berhasil dibuat',
                'data' => [
                    'karyawan' => $karyawan,
                    'user' => $user,
                    'login_credentials' => [
                        'email' => $user->email,
                        'password' => '(hidden for security)'
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat karyawan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified karyawan.
     */
    public function show($id)
    {
        try {
            $karyawan = Karyawan::with(['user', 'rfidCard', 'absensi', 'lembur', 'gaji', 'kpi'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail karyawan berhasil diambil',
                'data' => $karyawan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified karyawan.
     */
    public function update(Request $request, $id)
    {
        try {
            $karyawan = Karyawan::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nip' => 'sometimes|string|max:100|unique:karyawan,nip,' . $id,
                'nama' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255|unique:karyawan,email,' . $id . '|unique:users,email,' . optional($karyawan->user)->id,
                'jabatan' => 'sometimes|string|max:255',
                'departemen' => 'sometimes|string|max:255',
                'telepon' => 'nullable|string|max:50',
                'alamat' => 'nullable|string',
                'tanggal_masuk' => 'nullable|date',
                'status' => 'nullable|in:AKTIF,CUTI,RESIGN',
                'rfid_card_number' => 'nullable|string|exists:available_rfid,card_number',
                
                // Salary fields (master data)
                'gaji_pokok' => 'nullable|numeric|min:0|max:999999999999.99',
                'tunjangan_jabatan' => 'nullable|numeric|min:0|max:999999999999.99',
                'tunjangan_transport' => 'nullable|numeric|min:0|max:999999999999.99',
                'tunjangan_makan' => 'nullable|numeric|min:0|max:999999999999.99',
                
                // User account fields
                'password' => 'nullable|string|min:8',
                'role' => 'nullable|in:ADMIN,MANAGER,USER',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Handle RFID card change
            $oldRfidCard = $karyawan->rfid_card_number;
            $newRfidCard = $request->rfid_card_number;

            // Update karyawan
            $karyawan->update($request->only([
                'nip',
                'nama',
                'email',
                'jabatan',
                'departemen',
                'telepon',
                'alamat',
                'tanggal_masuk',
                'status',
                'rfid_card_number',
                'gaji_pokok',
                'tunjangan_jabatan',
                'tunjangan_transport',
                'tunjangan_makan',
            ]));

            // Update user account if exists
            if ($karyawan->user) {
                $userData = [];
                
                if ($request->has('nama')) {
                    $userData['name'] = $request->nama;
                }
                
                if ($request->has('email')) {
                    $userData['email'] = $request->email;
                }
                
                if ($request->has('password')) {
                    $userData['password'] = Hash::make($request->password);
                }
                
                if ($request->has('role')) {
                    $userData['role'] = $request->role;
                }

                if (!empty($userData)) {
                    $karyawan->user->update($userData);
                }
            }

            // Update RFID card assignments
            if ($oldRfidCard !== $newRfidCard) {
                // Release old RFID card
                if ($oldRfidCard) {
                    $oldRfid = AvailableRfid::where('card_number', $oldRfidCard)->first();
                    if ($oldRfid) {
                        $oldRfid->update([
                            'karyawan_id' => null,
                            'status' => 'AVAILABLE',
                            'assigned_at' => null,
                        ]);
                    }
                }

                // Assign new RFID card
                if ($newRfidCard) {
                    $newRfid = AvailableRfid::where('card_number', $newRfidCard)->first();
                    if ($newRfid) {
                        $newRfid->update([
                            'karyawan_id' => $karyawan->id,
                            'status' => 'ASSIGNED',
                            'assigned_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();

            // Load relationships
            $karyawan->load(['user', 'rfidCard']);

            return response()->json([
                'success' => true,
                'message' => 'Data karyawan berhasil diupdate',
                'data' => $karyawan
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate karyawan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified karyawan from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $karyawan = Karyawan::findOrFail($id);

            // Store RFID card number before deletion
            $rfidCardNumber = $karyawan->rfid_card_number;

            // Delete user account first
            if ($karyawan->user) {
                $karyawan->user->delete();
            }

            // Release RFID card
            if ($rfidCardNumber) {
                $rfid = AvailableRfid::where('card_number', $rfidCardNumber)->first();
                if ($rfid) {
                    $rfid->update([
                        'karyawan_id' => null,
                        'status' => 'AVAILABLE',
                        'assigned_at' => null,
                    ]);
                }
            }

            // Delete karyawan (cascade will delete related records)
            $karyawan->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Karyawan dan akun login berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus karyawan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get karyawan statistics.
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_karyawan' => Karyawan::count(),
                'karyawan_aktif' => Karyawan::where('status', 'AKTIF')->count(),
                'karyawan_cuti' => Karyawan::where('status', 'CUTI')->count(),
                'karyawan_resign' => Karyawan::where('status', 'RESIGN')->count(),
                'by_departemen' => Karyawan::select('departemen', DB::raw('count(*) as total'))
                    ->groupBy('departemen')
                    ->get(),
                'by_jabatan' => Karyawan::select('jabatan', DB::raw('count(*) as total'))
                    ->groupBy('jabatan')
                    ->orderBy('total', 'desc')
                    ->limit(10)
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistik karyawan berhasil diambil',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk import karyawan from array.
     */
    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'karyawan' => 'required|array|min:1',
            'karyawan.*.nip' => 'required|string|max:100|unique:karyawan,nip',
            'karyawan.*.nama' => 'required|string|max:255',
            'karyawan.*.email' => 'required|email|max:255|unique:karyawan,email|unique:users,email',
            'karyawan.*.jabatan' => 'required|string|max:255',
            'karyawan.*.departemen' => 'required|string|max:255',
            'karyawan.*.password' => 'required|string|min:8',
            'karyawan.*.gaji_pokok' => 'nullable|numeric|min:0|max:999999999999.99',
            'karyawan.*.tunjangan_jabatan' => 'nullable|numeric|min:0|max:999999999999.99',
            'karyawan.*.tunjangan_transport' => 'nullable|numeric|min:0|max:999999999999.99',
            'karyawan.*.tunjangan_makan' => 'nullable|numeric|min:0|max:999999999999.99',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $created = [];
            $errors = [];

            foreach ($request->karyawan as $index => $data) {
                try {
                    // Create karyawan
                    $karyawan = Karyawan::create([
                        'id' => Str::uuid(),
                        'nip' => $data['nip'],
                        'nama' => $data['nama'],
                        'email' => $data['email'],
                        'jabatan' => $data['jabatan'],
                        'departemen' => $data['departemen'],
                        'telepon' => $data['telepon'] ?? null,
                        'alamat' => $data['alamat'] ?? null,
                        'tanggal_masuk' => $data['tanggal_masuk'] ?? now(),
                        'status' => $data['status'] ?? 'AKTIF',
                        'gaji_pokok' => $data['gaji_pokok'] ?? 0,
                        'tunjangan_jabatan' => $data['tunjangan_jabatan'] ?? 0,
                        'tunjangan_transport' => $data['tunjangan_transport'] ?? 0,
                        'tunjangan_makan' => $data['tunjangan_makan'] ?? 0,
                    ]);

                    // Create user account
                    User::create([
                        'id' => Str::uuid(),
                        'karyawan_id' => $karyawan->id,
                        'name' => $data['nama'],
                        'email' => $data['email'],
                        'password' => Hash::make($data['password']),
                        'role' => $data['role'] ?? 'USER',
                    ]);

                    $created[] = $karyawan;
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'data' => $data,
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($created) . ' karyawan berhasil dibuat',
                'data' => [
                    'created' => $created,
                    'errors' => $errors
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat karyawan bulk',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
