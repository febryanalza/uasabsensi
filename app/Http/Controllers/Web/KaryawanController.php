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
use Illuminate\Support\Str;

class KaryawanController extends Controller
{
    /**
     * Display a listing of karyawan
     */
    public function index(Request $request)
    {
        return view('karyawan.index');
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
     * Get karyawan data with performance optimization
     */
    public function getData(Request $request)
    {
        try {
            // Create cache key based on request parameters
            $cacheKey = 'karyawan_data_' . md5(serialize($request->all()));
            
            $result = cache()->remember($cacheKey, 300, function () use ($request) { // 5 minutes cache
                // Select only needed fields for web interface
                $query = Karyawan::select([
                    'id', 'nip', 'nama', 'email', 'jabatan', 'departemen',
                    'telepon', 'status', 'tanggal_masuk', 'gaji_pokok'
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

                // Optimized pagination
                $perPage = min($request->get('per_page', 15), 50);
                $orderBy = $request->get('order_by', 'nama');
                $orderDir = in_array($request->get('order_dir'), ['asc', 'desc']) ? $request->get('order_dir') : 'asc';
                
                return $query->orderBy($orderBy, $orderDir)->paginate($perPage);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data karyawan berhasil diambil',
                'data' => $result
            ])->header('Cache-Control', 'public, max-age=300');

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
                    'total_karyawan' => Karyawan::count(),
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
     * Store a newly created karyawan
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
                'is_active' => true,
            ]);

            // Update RFID card status if assigned
            if ($request->rfid_card_number) {
                AvailableRfid::where('card_number', $request->rfid_card_number)
                           ->update(['status' => 'ASSIGNED']);
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
     * Get single karyawan data
     */
    public function getKaryawan($id)
    {
        try {
            $karyawan = Karyawan::with(['user:id,karyawan_id,email,role,is_active'])
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
}