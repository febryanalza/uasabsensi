<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AturanPerusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AturanPerusahaanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $aturan = AturanPerusahaan::orderBy('created_at', 'desc')->paginate(10);
        $activeRule = AturanPerusahaan::active()->first();
        
        return view('aturan.index', compact('aturan', 'activeRule'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('aturan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jam_masuk_kerja' => 'required|string|max:10',
            'jam_pulang_kerja' => 'required|string|max:10',
            'toleransi_terlambat' => 'required|integer|min:0',
            'potongan_per_menit_terlambat' => 'required|numeric|min:0',
            'potongan_per_hari_alpha' => 'required|numeric|min:0',
            'tarif_lembur_per_jam' => 'required|numeric|min:0',
            'tarif_lembur_libur' => 'required|numeric|min:0',
            'bonus_kehadiran_penuh' => 'required|numeric|min:0',
            'minimal_hadir_bonus' => 'required|integer|min:0',
            'hari_kerja_per_bulan' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Nonaktifkan aturan yang sedang aktif sebelum membuat yang baru (jika aturan baru aktif)
            if ($request->is_active) {
                AturanPerusahaan::where('is_active', true)->update(['is_active' => false]);
            }
            
            $aturan = AturanPerusahaan::create($validator->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Aturan perusahaan berhasil ditambahkan',
                    'data' => $aturan
                ], 201);
            }

            return redirect()->route('aturan.index')
                ->with('success', 'Aturan perusahaan berhasil ditambahkan');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan aturan perusahaan',
                    'error' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal menambahkan aturan perusahaan');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $aturan = AturanPerusahaan::findOrFail($id);
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $aturan
                ]);
            }
            
            return view('aturan.show', compact('aturan'));
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aturan tidak ditemukan'
                ], 404);
            }
            return redirect()->route('aturan.index')->with('error', 'Aturan tidak ditemukan');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $aturan = AturanPerusahaan::findOrFail($id);
            return view('aturan.edit', compact('aturan'));
        } catch (\Exception $e) {
            return redirect()->route('aturan.index')->with('error', 'Aturan tidak ditemukan');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'jam_masuk_kerja' => 'required|string|max:10',
            'jam_pulang_kerja' => 'required|string|max:10',
            'toleransi_terlambat' => 'required|integer|min:0',
            'potongan_per_menit_terlambat' => 'required|numeric|min:0',
            'potongan_per_hari_alpha' => 'required|numeric|min:0',
            'tarif_lembur_per_jam' => 'required|numeric|min:0',
            'tarif_lembur_libur' => 'required|numeric|min:0',
            'bonus_kehadiran_penuh' => 'required|numeric|min:0',
            'minimal_hadir_bonus' => 'required|integer|min:0',
            'hari_kerja_per_bulan' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $aturan = AturanPerusahaan::findOrFail($id);
            
            // Jika aturan ini akan diaktifkan, nonaktifkan yang lain terlebih dahulu
            if ($request->is_active) {
                AturanPerusahaan::where('id', '!=', $id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
            
            $aturan->update($validator->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Aturan perusahaan berhasil diperbarui',
                    'data' => $aturan
                ]);
            }

            return redirect()->route('aturan.index')
                ->with('success', 'Aturan perusahaan berhasil diperbarui');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui aturan perusahaan',
                    'error' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal memperbarui aturan perusahaan');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $aturan = AturanPerusahaan::findOrFail($id);
            $aturan->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Aturan perusahaan berhasil dihapus'
                ]);
            }

            return redirect()->route('aturan.index')
                ->with('success', 'Aturan perusahaan berhasil dihapus');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus aturan perusahaan',
                    'error' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal menghapus aturan perusahaan');
        }
    }

    // API Methods

    /**
     * Get active company rule via API
     */
    public function apiIndex()
    {
        try {
            $aturan = AturanPerusahaan::active()->first();
            
            return response()->json([
                'success' => true,
                'data' => $aturan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data aturan perusahaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all company rules (for admin) via API
     */
    public function apiAll()
    {
        try {
            $aturan = AturanPerusahaan::orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $aturan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil semua data aturan perusahaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle active status via API
     */
    public function toggleActive(string $id)
    {
        try {
            $aturan = AturanPerusahaan::findOrFail($id);
            
            if (!$aturan->is_active) {
                // Nonaktifkan semua aturan lain sebelum mengaktifkan yang ini
                AturanPerusahaan::where('id', '!=', $id)->update(['is_active' => false]);
                $aturan->update(['is_active' => true]);
                $message = 'Aturan perusahaan berhasil diaktifkan';
            } else {
                $aturan->update(['is_active' => false]);
                $message = 'Aturan perusahaan berhasil dinonaktifkan';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $aturan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status aturan perusahaan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
