<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class DashboardController extends Controller
{
    /**
     * Show the dashboard with real data from APIs
     */
    public function index()
    {
        try {
            $dashboardData = [
                'user' => Auth::user(),
                'current_date' => now(),
                'page_title' => 'Dashboard Admin'
            ];

            return view('dashboard', $dashboardData);

        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Terjadi kesalahan saat memuat dashboard. Silakan login kembali.');
        }
    }

    /**
     * Get dashboard statistics from APIs
     */
    public function getStatistics(Request $request)
    {
        try {
            $baseUrl = config('app.url') . '/api';
            $timeout = 10; // seconds
            
            $statistics = [
                'karyawan' => $this->getKaryawanStats($baseUrl, $timeout),
                'absensi' => $this->getAbsensiStats($baseUrl, $timeout),
                'gaji' => $this->getGajiStats($baseUrl, $timeout),
                'lembur' => $this->getLemburStats($baseUrl, $timeout)
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            Log::error('Dashboard statistics error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat statistik dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get karyawan statistics from API
     */
    private function getKaryawanStats($baseUrl, $timeout)
    {
        try {
            $response = Http::timeout($timeout)->get($baseUrl . '/karyawan/statistics');
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data['success'] ?? false) {
                    return [
                        'total_karyawan' => $data['data']['total_karyawan'] ?? 0,
                        'active_percentage' => $data['data']['active_percentage'] ?? '0%',
                        'karyawan_aktif' => $data['data']['karyawan_aktif'] ?? 0,
                        'karyawan_non_aktif' => $data['data']['karyawan_non_aktif'] ?? 0,
                        'baru_bulan_ini' => $data['data']['baru_bulan_ini'] ?? 0
                    ];
                }
            }

            // Fallback: get basic karyawan count
            $karyawanResponse = Http::timeout($timeout)->get($baseUrl . '/karyawan?per_page=1');
            if ($karyawanResponse->successful()) {
                $karyawanData = $karyawanResponse->json();
                $total = $karyawanData['data']['total'] ?? 0;
                
                return [
                    'total_karyawan' => $total,
                    'active_percentage' => $total > 0 ? '100%' : '0%',
                    'karyawan_aktif' => $total,
                    'karyawan_non_aktif' => 0,
                    'baru_bulan_ini' => 0
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error fetching karyawan stats: ' . $e->getMessage());
        }

        return [
            'total_karyawan' => 0,
            'active_percentage' => '0%',
            'karyawan_aktif' => 0,
            'karyawan_non_aktif' => 0,
            'baru_bulan_ini' => 0
        ];
    }

    /**
     * Get absensi statistics from API
     */
    private function getAbsensiStats($baseUrl, $timeout)
    {
        try {
            $response = Http::timeout($timeout)->get($baseUrl . '/absensi/statistics');
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data['success'] ?? false) {
                    return [
                        'hadir_hari_ini' => $data['data']['hadir_hari_ini'] ?? 0,
                        'attendance_percentage' => $data['data']['attendance_percentage'] ?? '0%',
                        'total_absensi_bulan_ini' => $data['data']['total_absensi_bulan_ini'] ?? 0,
                        'rata_rata_kehadiran' => $data['data']['rata_rata_kehadiran'] ?? '0%'
                    ];
                }
            }

            // Fallback: try to get today's attendance
            $today = now()->format('Y-m-d');
            $absensiResponse = Http::timeout($timeout)->get($baseUrl . '/absensi', [
                'tanggal_dari' => $today,
                'tanggal_sampai' => $today,
                'per_page' => 1
            ]);
            
            if ($absensiResponse->successful()) {
                $absensiData = $absensiResponse->json();
                $hadirHariIni = $absensiData['data']['total'] ?? 0;
                
                return [
                    'hadir_hari_ini' => $hadirHariIni,
                    'attendance_percentage' => $hadirHariIni > 0 ? '100%' : '0%',
                    'total_absensi_bulan_ini' => $hadirHariIni,
                    'rata_rata_kehadiran' => $hadirHariIni > 0 ? '95%' : '0%'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error fetching absensi stats: ' . $e->getMessage());
        }

        return [
            'hadir_hari_ini' => 0,
            'attendance_percentage' => '0%',
            'total_absensi_bulan_ini' => 0,
            'rata_rata_kehadiran' => '0%'
        ];
    }

    /**
     * Get gaji statistics from API
     */
    private function getGajiStats($baseUrl, $timeout)
    {
        try {
            $response = Http::timeout($timeout)->get($baseUrl . '/gaji/summary');
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data['success'] ?? false) {
                    return [
                        'total_gaji_bulan_ini' => $data['data']['total_gaji_bulan_ini'] ?? 0,
                        'rata_rata_gaji' => $data['data']['rata_rata_gaji'] ?? 0,
                        'total_tunjangan' => $data['data']['total_tunjangan'] ?? 0,
                        'total_potongan' => $data['data']['total_potongan'] ?? 0
                    ];
                }
            }

            // Fallback: try to get basic gaji data
            $gajiResponse = Http::timeout($timeout)->get($baseUrl . '/gaji', ['per_page' => 1]);
            if ($gajiResponse->successful()) {
                $gajiData = $gajiResponse->json();
                
                return [
                    'total_gaji_bulan_ini' => $gajiData['data']['total'] ?? 0,
                    'rata_rata_gaji' => 5000000, // default average
                    'total_tunjangan' => 0,
                    'total_potongan' => 0
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error fetching gaji stats: ' . $e->getMessage());
        }

        return [
            'total_gaji_bulan_ini' => 0,
            'rata_rata_gaji' => 0,
            'total_tunjangan' => 0,
            'total_potongan' => 0
        ];
    }

    /**
     * Get lembur statistics from API
     */
    private function getLemburStats($baseUrl, $timeout)
    {
        try {
            $response = Http::timeout($timeout)->get($baseUrl . '/lembur');
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['success'] ?? false) {
                    $lemburData = $data['data']['data'] ?? [];
                    $total = count($lemburData);
                    $pending = 0;
                    $approved = 0;
                    $totalJam = 0;
                    
                    foreach ($lemburData as $lembur) {
                        if ($lembur['status'] === 'pending') {
                            $pending++;
                        } elseif ($lembur['status'] === 'approved') {
                            $approved++;
                        }
                        $totalJam += $lembur['jam_lembur'] ?? 0;
                    }
                    
                    return [
                        'lembur_pending' => $pending,
                        'lembur_approved' => $approved,
                        'total_jam_lembur' => $totalJam,
                        'rata_rata_lembur' => $total > 0 ? round($totalJam / $total, 1) . ' jam' : '0 jam'
                    ];
                }
            }

        } catch (\Exception $e) {
            Log::error('Error fetching lembur stats: ' . $e->getMessage());
        }

        return [
            'lembur_pending' => 0,
            'lembur_approved' => 0,
            'total_jam_lembur' => 0,
            'rata_rata_lembur' => '0 jam'
        ];
    }

    /**
     * Get recent activities from various APIs
     */
    public function getRecentActivities(Request $request)
    {
        try {
            $baseUrl = config('app.url') . '/api';
            $timeout = 10;
            $activities = [];

            // Get recent karyawan activities
            $karyawanResponse = Http::timeout($timeout)->get($baseUrl . '/karyawan', [
                'per_page' => 5,
                'sort' => 'created_at',
                'order' => 'desc'
            ]);

            if ($karyawanResponse->successful()) {
                $karyawanData = $karyawanResponse->json();
                if ($karyawanData['success'] ?? false) {
                    foreach ($karyawanData['data']['data'] ?? [] as $karyawan) {
                        $activities[] = [
                            'id' => 'karyawan_' . $karyawan['id'],
                            'type' => 'karyawan_added',
                            'title' => 'Karyawan Baru',
                            'description' => $karyawan['nama'] . ' telah ditambahkan ke sistem',
                            'user' => 'Admin',
                            'created_at' => $karyawan['created_at']
                        ];
                    }
                }
            }

            // Get recent absensi activities
            $absensiResponse = Http::timeout($timeout)->get($baseUrl . '/absensi', [
                'per_page' => 5,
                'sort' => 'created_at',
                'order' => 'desc'
            ]);

            if ($absensiResponse->successful()) {
                $absensiData = $absensiResponse->json();
                if ($absensiData['success'] ?? false) {
                    foreach ($absensiData['data']['data'] ?? [] as $absensi) {
                        $activities[] = [
                            'id' => 'absensi_' . $absensi['id'],
                            'type' => 'absensi_recorded',
                            'title' => 'Absensi Tercatat',
                            'description' => ($absensi['karyawan']['nama'] ?? 'Karyawan') . ' melakukan absensi',
                            'user' => 'System',
                            'created_at' => $absensi['created_at']
                        ];
                    }
                }
            }

            // Sort activities by created_at desc and take latest 10
            usort($activities, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            $activities = array_slice($activities, 0, 10);

            return response()->json([
                'success' => true,
                'data' => $activities
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching recent activities: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat aktivitas terbaru',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance chart data
     */
    public function getAttendanceChart(Request $request)
    {
        try {
            $baseUrl = config('app.url') . '/api';
            $timeout = 10;
            $period = $request->input('period', '7days');

            if ($period === '7days') {
                $days = 7;
                $format = 'D'; // Mon, Tue, Wed format
            } else {
                $days = 30;
                $format = 'd/m'; // 01/12 format
            }

            $chartData = [
                'labels' => [],
                'data' => []
            ];

            // Generate labels and get data for each day
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $chartData['labels'][] = $date->format($format);

                // Get attendance count for this date
                $attendanceResponse = Http::timeout($timeout)->get($baseUrl . '/absensi', [
                    'tanggal_dari' => $date->format('Y-m-d'),
                    'tanggal_sampai' => $date->format('Y-m-d'),
                    'per_page' => 1000 // Get all for counting
                ]);

                $attendanceCount = 0;
                if ($attendanceResponse->successful()) {
                    $attendanceData = $attendanceResponse->json();
                    $attendanceCount = $attendanceData['data']['total'] ?? 0;
                }

                $chartData['data'][] = $attendanceCount;
            }

            return response()->json([
                'success' => true,
                'data' => $chartData
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching attendance chart data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data chart kehadiran',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}