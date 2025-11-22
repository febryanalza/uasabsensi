<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Karyawan;
use App\Models\User;
use App\Models\AvailableRfid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class KaryawanController extends Controller
{
    /**
     * Display a listing of karyawan (API compatible)
     */
    public function index(Request $request)
    {
        try {
            // For API requests, return JSON data
            if ($request->wantsJson() || $request->is('*/api/*')) {
                $query = Karyawan::with(['user', 'rfidCard']);

                // Apply filters
                if ($request->search) {
                    $search = $request->search;
                    $query->where(function($q) use ($search) {
                        $q->where('nama', 'like', "%{$search}%")
                          ->orWhere('nip', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                          ->orWhere('jabatan', 'like', "%{$search}%")
                          ->orWhere('departemen', 'like', "%{$search}%");
                    });
                }

                if ($request->status) {
                    $query->where('status', $request->status);
                }

                if ($request->departemen) {
                    $query->where('departemen', $request->departemen);
                }

                if ($request->jabatan) {
                    $query->where('jabatan', $request->jabatan);
                }

                // Sorting - default terbaru dulu untuk real-time updates
                $sortBy = $request->get('sort_by', 'created_at');
                $sortOrder = $request->get('sort_order', 'desc');
                $query->orderBy($sortBy, $sortOrder);
                
                // Secondary sort by nama untuk consistency
                if ($sortBy !== 'nama') {
                    $query->orderBy('nama', 'asc');
                }

                // Pagination
                $perPage = $request->get('per_page', 10);
                $karyawan = $query->paginate($perPage);

                return response()->json([
                    'success' => true,
                    'message' => 'Data karyawan berhasil diambil',
                    'data' => $karyawan
                ]);
            }

            // For web requests, return view
            return view('karyawan.index');

        } catch (\Exception $e) {
            Log::error('Error getting karyawan list: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->is('*/api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data karyawan',
                    'error' => $e->getMessage()
                ], 500);
            }

            return view('karyawan.index')->with('error', 'Gagal mengambil data karyawan');
        }
    }

    /**
     * Show the form for creating a new karyawan
     */
    public function create()
    {
        return view('karyawan.create');
    }

    /**
     * Show the specified karyawan
     */
    public function show($id)
    {
        return view('karyawan.show', compact('id'));
    }

    /**
     * Show the form for editing the specified karyawan
     */
    public function edit($id)
    {
        return view('karyawan.edit', compact('id'));
    }

    /**
     * Get karyawan data with real-time updates (no caching)
     */
    public function getData(Request $request)
    {
        try {
            // Select only needed fields for web interface dengan created_at untuk sorting
            $query = Karyawan::select([
                'id', 'nip', 'nama', 'email', 'jabatan', 'departemen',
                'telepon', 'status', 'tanggal_masuk', 'gaji_pokok', 'created_at'
            ]);

            // Conditionally load relations
            if ($request->get('with_user', false)) {
                $query->with('user:id,karyawan_id,email,role,is_active');
            }

            // Optimized filters
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            if ($request->has('departemen') && $request->departemen !== '') {
                $query->where('departemen', $request->departemen);
            }

            if ($request->has('jabatan') && $request->jabatan !== '') {
                $query->where('jabatan', 'LIKE', $request->jabatan . '%');
            }

            // Optimized search
            if ($request->has('search') && !empty(trim($request->search))) {
                $search = trim($request->search);
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'LIKE', $search . '%')
                      ->orWhere('nip', 'LIKE', $search . '%');
                });
            }

            // Sorting - default terbaru dulu untuk real-time updates
            $orderBy = $request->get('order_by', 'created_at');
            $orderDir = in_array($request->get('order_dir'), ['asc', 'desc']) ? $request->get('order_dir') : 'desc';
            $query->orderBy($orderBy, $orderDir);
            
            // Secondary sort by nama untuk consistency
            if ($orderBy !== 'nama') {
                $query->orderBy('nama', 'asc');
            }

            // Optimized pagination
            $perPage = min($request->get('per_page', 15), 50);
            $result = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data karyawan berhasil diambil',
                'data' => $result
            ])->header('Cache-Control', 'no-cache, no-store, must-revalidate')
              ->header('Pragma', 'no-cache')
              ->header('Expires', '0');

        } catch (\Exception $e) {
            Log::error('Error getting karyawan data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data karyawan'
            ], 500);
        }
    }

    /**
     * Get statistics data for dashboard with caching
     */
    public function getStatistics(Request $request)
    {
        try {
            // Cache statistics for 10 minutes since they don't change frequently
            $stats = cache()->remember('karyawan_statistics', 600, function () {
                return [
                    'total' => Karyawan::count(),
                    'aktif' => Karyawan::where('status', 'AKTIF')->count(),
                    'cuti' => Karyawan::where('status', 'CUTI')->count(),
                    'resign' => Karyawan::where('status', 'RESIGN')->count(),
                    'departemen' => Karyawan::select('departemen')
                                           ->selectRaw('count(*) as jumlah')
                                           ->groupBy('departemen')
                                           ->get()
                                           ->keyBy('departemen')
                                           ->map->jumlah,
                    'jabatan' => Karyawan::select('jabatan')
                                        ->selectRaw('count(*) as jumlah') 
                                        ->groupBy('jabatan')
                                        ->get()
                                        ->keyBy('jabatan')
                                        ->map->jumlah,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Statistik karyawan berhasil diambil',
                'data' => $stats
            ])->header('Cache-Control', 'public, max-age=600');

        } catch (\Exception $e) {
            Log::error('Error getting karyawan statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik karyawan'
            ], 500);
        }
    }

    /**
     * Get available RFID cards for dropdown
     */
    public function getAvailableRfid()
    {
        try {
            $rfidCards = AvailableRfid::where('status', 'AVAILABLE')
                ->orderBy('card_number')
                ->get(['id', 'card_number', 'card_type', 'status']);

            return response()->json([
                'success' => true,
                'data' => $rfidCards
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data RFID',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created karyawan via API
     */
    public function store(Request $request)
    {
        Log::info('Karyawan store method called', ['request_data' => $request->all()]);
        
        // Handle field mapping: rfid_card -> rfid_card_number
        if ($request->has('rfid_card') && !$request->has('rfid_card_number')) {
            $request->merge(['rfid_card_number' => $request->rfid_card]);
        }
        
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
            'gaji_pokok' => 'nullable|numeric|min:0',
            'tunjangan_jabatan' => 'nullable|numeric|min:0',
            'tunjangan_transport' => 'nullable|numeric|min:0',
            'tunjangan_makan' => 'nullable|numeric|min:0',
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

        try {
            DB::beginTransaction();

            // Create karyawan
            $karyawan = Karyawan::create([
                'nip' => $request->nip,
                'rfid_card_number' => $request->rfid_card_number,
                'nama' => $request->nama,
                'email' => $request->email,
                'jabatan' => $request->jabatan,
                'departemen' => $request->departemen,
                'telepon' => $request->telepon,
                'alamat' => $request->alamat,
                'tanggal_masuk' => $request->tanggal_masuk,
                'status' => $request->status ?? 'AKTIF',
                'gaji_pokok' => $request->gaji_pokok ?? 0,
                'tunjangan_jabatan' => $request->tunjangan_jabatan ?? 0,
                'tunjangan_transport' => $request->tunjangan_transport ?? 0,
                'tunjangan_makan' => $request->tunjangan_makan ?? 0,
            ]);

            // Create user account
            $user = User::create([
                'karyawan_id' => $karyawan->id,
                'name' => $karyawan->nama,
                'email' => $karyawan->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'USER',
            ]);

            // Update RFID card status if assigned
            if ($request->rfid_card_number) {
                $rfid = AvailableRfid::where('card_number', $request->rfid_card_number)
                                   ->where('status', 'AVAILABLE')
                                   ->first();
                if ($rfid) {
                    $rfid->update([
                        'karyawan_id' => $karyawan->id,
                        'status' => 'ASSIGNED',
                        'assigned_at' => now(),
                    ]);
                } else {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Kartu RFID tidak tersedia atau sudah digunakan'
                    ], 422);
                }
            }

            DB::commit();

            // Clear cache
            cache()->forget('karyawan_statistics');

            return response()->json([
                'success' => true,
                'message' => 'Data karyawan berhasil disimpan',
                'data' => $karyawan->load('user')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating karyawan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data karyawan'
            ], 500);
        }
    }

    /**
     * Get single karyawan data (API compatible)
     */
    public function getKaryawan($id)
    {
        try {
            $karyawan = Karyawan::with(['user:id,karyawan_id,email,role,is_active', 'rfidCard'])
                              ->find($id);

            if (!$karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data karyawan tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data karyawan berhasil diambil',
                'data' => $karyawan
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting karyawan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data karyawan'
            ], 500);
        }
    }

    /**
     * API compatible show method with full details
     */
    public function apiShow($id)
    {
        try {
            $karyawan = Karyawan::with(['user', 'rfidCard', 'absensi', 'lembur'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail karyawan berhasil diambil',
                'data' => $karyawan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified karyawan
     */
    public function update(Request $request, $id)
    {
        $karyawan = Karyawan::find($id);
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nip' => 'required|string|max:100|unique:karyawan,nip,' . $id,
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:karyawan,email,' . $id,
            'jabatan' => 'required|string|max:255',
            'departemen' => 'required|string|max:255',
            'telepon' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'tanggal_masuk' => 'nullable|date',
            'status' => 'nullable|in:AKTIF,CUTI,RESIGN',
            'gaji_pokok' => 'nullable|numeric|min:0',
            'tunjangan_jabatan' => 'nullable|numeric|min:0',
            'tunjangan_transport' => 'nullable|numeric|min:0',
            'tunjangan_makan' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $karyawan->update($validator->validated());

            // Clear cache
            cache()->forget('karyawan_statistics');

            return response()->json([
                'success' => true,
                'message' => 'Data karyawan berhasil diperbarui',
                'data' => $karyawan->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating karyawan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data karyawan'
            ], 500);
        }
    }

    /**
     * Remove the specified karyawan
     */
    public function destroy($id)
    {
        try {
            $karyawan = Karyawan::find($id);
            if (!$karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data karyawan tidak ditemukan'
                ], 404);
            }

            DB::beginTransaction();

            // Delete related user account
            if ($karyawan->user) {
                $karyawan->user->delete();
            }

            // Free RFID card if assigned
            if ($karyawan->rfid_card_number) {
                AvailableRfid::where('card_number', $karyawan->rfid_card_number)
                           ->update(['status' => 'AVAILABLE']);
            }

            $karyawan->delete();

            DB::commit();

            // Clear cache
            cache()->forget('karyawan_statistics');

            return response()->json([
                'success' => true,
                'message' => 'Data karyawan berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting karyawan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data karyawan'
            ], 500);
        }
    }

    /**
     * Get karyawan statistics for API compatibility
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
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk import karyawan from array (API compatible)
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
                        'nip' => $data['nip'],
                        'nama' => $data['nama'],
                        'email' => $data['email'],
                        'jabatan' => $data['jabatan'],
                        'departemen' => $data['departemen'],
                        'telepon' => $data['telepon'] ?? null,
                        'alamat' => $data['alamat'] ?? null,
                        'tanggal_masuk' => $data['tanggal_masuk'] ?? now(),
                        'status' => $data['status'] ?? 'AKTIF',
                        'rfid_card_number' => $data['rfid_card_number'] ?? null,
                        'gaji_pokok' => $data['gaji_pokok'] ?? 0,
                        'tunjangan_jabatan' => $data['tunjangan_jabatan'] ?? 0,
                        'tunjangan_transport' => $data['tunjangan_transport'] ?? 0,
                        'tunjangan_makan' => $data['tunjangan_makan'] ?? 0,
                    ]);

                    // Create user account
                    User::create([
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

            // Clear cache
            cache()->forget('karyawan_statistics');

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
            Log::error('Error bulk creating karyawan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat karyawan bulk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk operations
     */
    public function bulkOperation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:delete,activate,deactivate',
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:karyawan,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $action = $request->action;
            $ids = $request->ids;

            switch ($action) {
                case 'delete':
                    Karyawan::whereIn('id', $ids)->delete();
                    $message = 'Data karyawan berhasil dihapus';
                    break;
                    
                case 'activate':
                    Karyawan::whereIn('id', $ids)->update(['status' => 'AKTIF']);
                    $message = 'Karyawan berhasil diaktifkan';
                    break;
                    
                case 'deactivate':
                    Karyawan::whereIn('id', $ids)->update(['status' => 'RESIGN']);
                    $message = 'Karyawan berhasil dinonaktifkan';
                    break;
            }

            // Clear cache
            cache()->forget('karyawan_statistics');

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Error in bulk operation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan operasi bulk'
            ], 500);
        }
    }

    /**
     * Export karyawan to Excel/CSV format
     */
    public function export(Request $request)
    {
        try {
            $karyawan = Karyawan::with('user')->get();
            
            $data = $karyawan->map(function($k) {
                return [
                    'NIP' => $k->nip,
                    'Nama' => $k->nama,
                    'Email' => $k->email,
                    'Jabatan' => $k->jabatan,
                    'Departemen' => $k->departemen,
                    'Telepon' => $k->telepon,
                    'Alamat' => $k->alamat,
                    'Status' => $k->status,
                    'Tanggal Masuk' => $k->tanggal_masuk,
                    'RFID Card' => $k->rfid_card_number,
                    'Gaji Pokok' => $k->gaji_pokok,
                    'Tunjangan Jabatan' => $k->tunjangan_jabatan,
                    'Tunjangan Transport' => $k->tunjangan_transport,
                    'Tunjangan Makan' => $k->tunjangan_makan,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diekspor',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error exporting karyawan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengekspor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import karyawan from uploaded file
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Process file import logic here
            // For now, return success message
            return response()->json([
                'success' => true,
                'message' => 'File berhasil diimpor (placeholder - implementasi import akan ditambahkan kemudian)'
            ]);

        } catch (\Exception $e) {
            Log::error('Error importing karyawan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor file',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}