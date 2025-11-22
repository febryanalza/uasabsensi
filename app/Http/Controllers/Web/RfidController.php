<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AvailableRfid;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RfidController extends Controller
{
    /**
     * Display a listing of RFID cards
     */
    public function index()
    {
        return view('rfid.index');
    }

    /**
     * Debug page for RFID management
     */
    public function debug()
    {
        return view('rfid.debug');
    }

    /**
     * Test direct count for debugging
     */
    public function testCount()
    {
        try {
            $count = AvailableRfid::count();
            $allCards = AvailableRfid::all();
            $firstCard = AvailableRfid::first();
            
            return response()->json([
                'success' => true,
                'message' => 'Direct database query test',
                'total_count' => $count,
                'all_cards_count' => $allCards->count(),
                'first_card' => $firstCard,
                'table_name' => (new AvailableRfid)->getTable(),
            ]);
        } catch (\Exception $e) {
            Log::error('Test count error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get RFID cards data for listing (AJAX)
     */
    public function getData(Request $request)
    {
        try {
            Log::info('RFID getData called', ['request' => $request->all()]);
            $query = AvailableRfid::with(['karyawan:id,nip,nama,departemen,jabatan']);

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('card_number', 'LIKE', "%{$search}%")
                      ->orWhere('card_type', 'LIKE', "%{$search}%")
                      ->orWhere('notes', 'LIKE', "%{$search}%")
                      ->orWhereHas('karyawan', function ($kq) use ($search) {
                          $kq->where('nama', 'LIKE', "%{$search}%")
                             ->orWhere('nip', 'LIKE', "%{$search}%");
                      });
                });
            }

            // Filter by status
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            // Filter by assigned status
            if ($request->has('assigned')) {
                if ($request->assigned === 'true') {
                    $query->whereNotNull('karyawan_id');
                } elseif ($request->assigned === 'false') {
                    $query->whereNull('karyawan_id');
                }
            }

            // Sorting
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            
            $allowedSortFields = ['card_number', 'card_type', 'status', 'assigned_at', 'created_at'];
            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortDirection);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $cards = $query->paginate($perPage);

            Log::info('RFID getData result', [
                'total' => $cards->total(),
                'items_count' => count($cards->items()),
                'first_item' => $cards->items() ? $cards->items()[0] ?? null : null
            ]);

            return response()->json([
                'success' => true,
                'data' => $cards->items(),
                'pagination' => [
                    'current_page' => $cards->currentPage(),
                    'last_page' => $cards->lastPage(),
                    'per_page' => $cards->perPage(),
                    'total' => $cards->total(),
                    'from' => $cards->firstItem(),
                    'to' => $cards->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting RFID data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data RFID'
            ], 500);
        }
    }

    /**
     * Get RFID statistics
     */
    public function getStatistics()
    {
        try {
            Log::info('RFID getStatistics called');
            $stats = [
                'total_cards' => AvailableRfid::count(),
                'available_cards' => AvailableRfid::where('status', 'AVAILABLE')->count(),
                'assigned_cards' => AvailableRfid::where('status', 'ASSIGNED')->count(),
                'damaged_cards' => AvailableRfid::where('status', 'DAMAGED')->count(),
                'lost_cards' => AvailableRfid::where('status', 'LOST')->count(),
                'inactive_cards' => AvailableRfid::where('status', 'INACTIVE')->count(),
                'unassigned_available' => AvailableRfid::where('status', 'AVAILABLE')
                                                     ->whereNull('karyawan_id')
                                                     ->count(),
            ];

            // Recent activities (last 10 registered cards)
            $recentCards = AvailableRfid::with(['karyawan:id,nama,nip'])
                                      ->orderBy('created_at', 'desc')
                                      ->limit(10)
                                      ->get();

            Log::info('RFID getStatistics result', ['stats' => $stats, 'recent_count' => $recentCards->count()]);

            return response()->json([
                'success' => true,
                'statistics' => $stats,
                'recent_cards' => $recentCards
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting RFID statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil statistik RFID'
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified RFID card
     */
    public function edit($id)
    {
        return view('rfid.edit', compact('id'));
    }

    /**
     * Get specific RFID card data
     */
    public function show($id)
    {
        try {
            $card = AvailableRfid::with(['karyawan'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $card
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting RFID card: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'RFID card tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update the specified RFID card
     */
    public function update(Request $request, $id)
    {
        try {
            $card = AvailableRfid::findOrFail($id);

            $request->validate([
                'status' => 'required|in:AVAILABLE,ASSIGNED,DAMAGED,LOST,INACTIVE',
                'karyawan_id' => 'nullable|exists:karyawan,id',
                'notes' => 'nullable|string|max:1000',
            ]);

            DB::beginTransaction();

            $updateData = [
                'status' => $request->status,
                'notes' => $request->notes,
            ];

            // Handle karyawan assignment
            if ($request->status === 'ASSIGNED' && $request->karyawan_id) {
                // Check if karyawan already has an RFID card
                $existingCard = AvailableRfid::where('karyawan_id', $request->karyawan_id)
                                           ->where('id', '!=', $id)
                                           ->first();

                if ($existingCard) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Karyawan sudah memiliki kartu RFID lain'
                    ], 422);
                }

                $updateData['karyawan_id'] = $request->karyawan_id;
                $updateData['assigned_at'] = now();

                // Update karyawan's rfid_card_number field
                $karyawan = Karyawan::find($request->karyawan_id);
                if ($karyawan) {
                    $karyawan->update(['rfid_card_number' => $card->card_number]);
                }
            } else {
                // If unassigning or changing status
                if ($card->karyawan_id) {
                    $karyawan = Karyawan::find($card->karyawan_id);
                    if ($karyawan) {
                        $karyawan->update(['rfid_card_number' => null]);
                    }
                }
                
                $updateData['karyawan_id'] = null;
                $updateData['assigned_at'] = null;
            }

            $card->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'RFID card berhasil diperbarui',
                'data' => $card->load('karyawan')
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating RFID card: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui RFID card'
            ], 500);
        }
    }

    /**
     * Remove the specified RFID card from storage
     */
    public function destroy($id)
    {
        try {
            $card = AvailableRfid::findOrFail($id);

            DB::beginTransaction();

            // If card is assigned to karyawan, remove the assignment
            if ($card->karyawan_id) {
                $karyawan = Karyawan::find($card->karyawan_id);
                if ($karyawan) {
                    $karyawan->update(['rfid_card_number' => null]);
                }
            }

            $cardNumber = $card->card_number;
            $card->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "RFID card {$cardNumber} berhasil dihapus"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting RFID card: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus RFID card'
            ], 500);
        }
    }

    /**
     * Delete RFID card via API (alias for destroy)
     */
    public function delete($id)
    {
        return $this->destroy($id);
    }

    /**
     * Get available employees for assignment
     */
    public function getAvailableEmployees()
    {
        try {
            $employees = Karyawan::select('id', 'nip', 'nama', 'departemen', 'jabatan')
                               ->where('status', 'AKTIF')
                               ->whereNull('rfid_card_number')
                               ->orderBy('nama')
                               ->get();

            return response()->json([
                'success' => true,
                'data' => $employees
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting available employees: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data karyawan'
            ], 500);
        }
    }

    /**
     * Bulk operations for RFID cards
     */
    public function bulkOperation(Request $request)
    {
        try {
            $request->validate([
                'operation' => 'required|in:delete,change_status',
                'card_ids' => 'required|array|min:1',
                'card_ids.*' => 'required|exists:available_rfid,id',
                'new_status' => 'required_if:operation,change_status|in:AVAILABLE,DAMAGED,LOST,INACTIVE'
            ]);

            DB::beginTransaction();

            $cardIds = $request->card_ids;
            $operation = $request->operation;

            if ($operation === 'delete') {
                // Remove karyawan assignments before deleting
                $assignedCards = AvailableRfid::whereIn('id', $cardIds)
                                            ->whereNotNull('karyawan_id')
                                            ->get();

                foreach ($assignedCards as $card) {
                    $karyawan = Karyawan::find($card->karyawan_id);
                    if ($karyawan) {
                        $karyawan->update(['rfid_card_number' => null]);
                    }
                }

                $deletedCount = AvailableRfid::whereIn('id', $cardIds)->delete();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "{$deletedCount} RFID card berhasil dihapus"
                ]);

            } elseif ($operation === 'change_status') {
                $newStatus = $request->new_status;

                // If changing to non-ASSIGNED status, remove karyawan assignments
                if ($newStatus !== 'ASSIGNED') {
                    $assignedCards = AvailableRfid::whereIn('id', $cardIds)
                                                ->whereNotNull('karyawan_id')
                                                ->get();

                    foreach ($assignedCards as $card) {
                        $karyawan = Karyawan::find($card->karyawan_id);
                        if ($karyawan) {
                            $karyawan->update(['rfid_card_number' => null]);
                        }
                    }

                    AvailableRfid::whereIn('id', $cardIds)
                                ->update([
                                    'status' => $newStatus,
                                    'karyawan_id' => null,
                                    'assigned_at' => null
                                ]);
                } else {
                    AvailableRfid::whereIn('id', $cardIds)
                                ->update(['status' => $newStatus]);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => count($cardIds) . " RFID card berhasil diubah statusnya ke {$newStatus}"
                ]);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk operation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat melakukan operasi bulk'
            ], 500);
        }
    }
}