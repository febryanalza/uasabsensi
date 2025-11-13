<?php

namespace App\Helpers;

use App\Models\Karyawan;
use App\Models\Absensi;
use App\Models\Lembur;
use App\Models\Kpi;
use App\Models\AturanPerusahaan;
use App\Models\HariLibur;
use Carbon\Carbon;

class GajiCalculatorHelper
{
    /**
     * Calculate complete salary for a karyawan in specific month/year
     */
    public static function calculateGaji($karyawanId, $bulan, $tahun)
    {
        try {
            $karyawan = Karyawan::findOrFail($karyawanId);
            $aturan = AturanPerusahaan::where('is_active', true)->first();
            
            $data = [
                'karyawan_id' => $karyawanId,
                'bulan' => $bulan,
                'tahun' => $tahun,
            ];

            // 1. Basic Salary Components from Karyawan Master Data
            $data['gaji_pokok'] = $karyawan->gaji_pokok ?? 0;
            $data['tunjangan_jabatan'] = $karyawan->tunjangan_jabatan ?? 0;
            $data['tunjangan_transport'] = $karyawan->tunjangan_transport ?? 0;
            $data['tunjangan_makan'] = $karyawan->tunjangan_makan ?? 0;

            // 2. Calculate Attendance Data
            $attendanceData = self::calculateAttendanceData($karyawanId, $bulan, $tahun);
            $data = array_merge($data, $attendanceData);

            // 3. Calculate Overtime Compensation
            $overtimeData = self::calculateOvertimeData($karyawanId, $bulan, $tahun);
            $data = array_merge($data, $overtimeData);

            // 4. Calculate KPI Bonus
            $kpiData = self::calculateKpiBonus($karyawanId, $bulan, $tahun);
            $data = array_merge($data, $kpiData);

            // 5. Calculate Attendance Bonus
            $attendanceBonus = self::calculateAttendanceBonus($data, $aturan);
            $data['bonus_kehadiran'] = $attendanceBonus;

            // 6. Calculate Deductions
            $deductionData = self::calculateDeductions($data, $aturan);
            $data = array_merge($data, $deductionData);

            // 7. Calculate Insurance & Tax
            $insuranceData = self::calculateInsurance($data);
            $data = array_merge($data, $insuranceData);

            // 8. Calculate Final Totals
            $finalData = self::calculateFinalTotals($data);
            $data = array_merge($data, $finalData);

            // 9. Add metadata
            $data['tanggal_dibuat'] = now();
            $data['status'] = 'DRAFT';

            return [
                'success' => true,
                'data' => $data
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculate attendance-related data from absensi table
     */
    private static function calculateAttendanceData($karyawanId, $bulan, $tahun)
    {
        $startDate = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $absensiData = Absensi::where('karyawan_id', $karyawanId)
                              ->whereBetween('tanggal', [$startDate, $endDate])
                              ->get();

        return [
            'jumlah_hadir' => $absensiData->where('status', 'HADIR')->count(),
            'jumlah_izin' => $absensiData->where('status', 'IZIN')->count(),
            'jumlah_sakit' => $absensiData->where('status', 'SAKIT')->count(),
            'jumlah_alpha' => $absensiData->where('status', 'ALPHA')->count(),
            'jumlah_terlambat' => $absensiData->where('menit_terlambat', '>', 0)->count(),
            'total_menit_terlambat' => $absensiData->sum('menit_terlambat'),
        ];
    }

    /**
     * Calculate overtime-related data from lembur table
     */
    private static function calculateOvertimeData($karyawanId, $bulan, $tahun)
    {
        $lemburData = Lembur::where('karyawan_id', $karyawanId)
                            ->where('status', 'DISETUJUI')
                            ->whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->get();

        return [
            'total_jam_lembur' => $lemburData->sum('durasi_jam'),
            'tunjangan_lembur' => $lemburData->sum('total_kompensasi'),
        ];
    }

    /**
     * Calculate KPI bonus from kpi table
     */
    private static function calculateKpiBonus($karyawanId, $bulan, $tahun)
    {
        $kpiData = Kpi::where('karyawan_id', $karyawanId)
                      ->where('bulan', $bulan)
                      ->where('tahun', $tahun)
                      ->first();

        return [
            'bonus_kpi' => $kpiData ? $kpiData->bonus_kpi : 0,
        ];
    }

    /**
     * Calculate attendance bonus based on company rules
     */
    private static function calculateAttendanceBonus($data, $aturan)
    {
        if (!$aturan || !isset($data['jumlah_hadir'])) {
            return 0;
        }

        $minimalHadir = $aturan->minimal_hadir_bonus ?? 0;
        $bonusAmount = $aturan->bonus_kehadiran_penuh ?? 0;

        return ($data['jumlah_hadir'] >= $minimalHadir) ? $bonusAmount : 0;
    }

    /**
     * Calculate various deductions (tardiness, absent)
     */
    private static function calculateDeductions($data, $aturan)
    {
        $deductions = [
            'potongan_terlambat' => 0,
            'potongan_alpha' => 0,
            'potongan_lainnya' => 0,
        ];

        if (!$aturan) {
            return $deductions;
        }

        // Calculate tardiness deduction
        if (isset($data['total_menit_terlambat']) && $data['total_menit_terlambat'] > 0) {
            $toleransi = $aturan->toleransi_terlambat ?? 0;
            $potonganPerMenit = $aturan->potongan_per_menit_terlambat ?? 0;
            
            $menitKenaPenalti = max(0, $data['total_menit_terlambat'] - ($toleransi * $data['jumlah_hadir']));
            $deductions['potongan_terlambat'] = $menitKenaPenalti * $potonganPerMenit;
        }

        // Calculate absent deduction
        if (isset($data['jumlah_alpha']) && $data['jumlah_alpha'] > 0) {
            $potonganPerHari = $aturan->potongan_per_hari_alpha ?? 0;
            $deductions['potongan_alpha'] = $data['jumlah_alpha'] * $potonganPerHari;
        }

        return $deductions;
    }

    /**
     * Calculate insurance and tax deductions
     */
    private static function calculateInsurance($data)
    {
        $grossSalary = $data['gaji_pokok'] + $data['tunjangan_jabatan'] + 
                      $data['tunjangan_transport'] + $data['tunjangan_makan'];

        // Insurance calculations (configurable rates)
        $bpjsKesehatanRate = config('payroll.bpjs_kesehatan_rate', 0.02); // 2%
        $bpjsKetenagakerjaanRate = config('payroll.bpjs_ketenagakerjaan_rate', 0.01); // 1%
        
        // Tax calculation (simplified progressive tax)
        $pph21 = self::calculatePph21($grossSalary);

        return [
            'bpjs_kesehatan' => $grossSalary * $bpjsKesehatanRate,
            'bpjs_ketenagakerjaan' => $grossSalary * $bpjsKetenagakerjaanRate,
            'pph21' => $pph21,
        ];
    }

    /**
     * Calculate PPh21 tax (simplified version)
     */
    private static function calculatePph21($grossSalary)
    {
        // Simplified PPh21 calculation
        // In production, this should use proper tax brackets
        
        $annualGross = $grossSalary * 12;
        $ptkp = 54000000; // Basic tax-free income (2024)
        
        if ($annualGross <= $ptkp) {
            return 0;
        }
        
        $taxableIncome = $annualGross - $ptkp;
        
        // Progressive tax rates (simplified)
        if ($taxableIncome <= 60000000) {
            $annualTax = $taxableIncome * 0.05;
        } elseif ($taxableIncome <= 250000000) {
            $annualTax = 60000000 * 0.05 + ($taxableIncome - 60000000) * 0.15;
        } else {
            $annualTax = 60000000 * 0.05 + 190000000 * 0.15 + ($taxableIncome - 250000000) * 0.25;
        }
        
        return $annualTax / 12; // Monthly tax
    }

    /**
     * Calculate final salary totals
     */
    private static function calculateFinalTotals($data)
    {
        $totalPendapatan = $data['gaji_pokok'] + $data['tunjangan_jabatan'] + 
                          $data['tunjangan_transport'] + $data['tunjangan_makan'] +
                          ($data['tunjangan_lembur'] ?? 0) + ($data['bonus_kehadiran'] ?? 0) + 
                          ($data['bonus_kpi'] ?? 0);

        $totalPotongan = ($data['potongan_terlambat'] ?? 0) + ($data['potongan_alpha'] ?? 0) + 
                        ($data['potongan_lainnya'] ?? 0) + ($data['bpjs_kesehatan'] ?? 0) + 
                        ($data['bpjs_ketenagakerjaan'] ?? 0) + ($data['pph21'] ?? 0);

        return [
            'total_pendapatan' => $totalPendapatan,
            'total_potongan' => $totalPotongan,
            'gaji_bersih' => $totalPendapatan - $totalPotongan,
        ];
    }

    /**
     * Bulk calculate salary for multiple employees
     */
    public static function bulkCalculateGaji($karyawanIds, $bulan, $tahun)
    {
        $results = ['success' => [], 'failed' => []];

        foreach ($karyawanIds as $karyawanId) {
            try {
                $karyawan = Karyawan::findOrFail($karyawanId);
                $calculation = self::calculateGaji($karyawanId, $bulan, $tahun);
                
                if ($calculation['success']) {
                    $results['success'][] = [
                        'karyawan_id' => $karyawanId,
                        'karyawan_name' => $karyawan->nama,
                        'data' => $calculation['data']
                    ];
                } else {
                    $results['failed'][] = [
                        'karyawan_id' => $karyawanId,
                        'karyawan_name' => $karyawan->nama,
                        'error' => $calculation['error']
                    ];
                }
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'karyawan_id' => $karyawanId,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Get summary statistics for salary calculation
     */
    public static function getSalarySummary($bulan, $tahun)
    {
        $startDate = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Working days calculation
        $workingDays = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if (!in_array($currentDate->dayOfWeek, [0, 6])) {
                // Check if not a holiday
                $isHoliday = HariLibur::whereDate('tanggal', $currentDate->format('Y-m-d'))->exists();
                if (!$isHoliday) {
                    $workingDays++;
                }
            }
            $currentDate->addDay();
        }

        $totalHolidays = HariLibur::whereMonth('tanggal', $bulan)
                                 ->whereYear('tanggal', $tahun)
                                 ->count();

        $activeEmployees = Karyawan::where('status', 'AKTIF')->count();

        return [
            'period' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'period_name' => Carbon::create($tahun, $bulan, 1)->format('F Y')
            ],
            'working_days' => $workingDays,
            'holidays_count' => $totalHolidays,
            'active_employees' => $activeEmployees,
            'calculation_ready' => $activeEmployees > 0
        ];
    }

    /**
     * Validate if salary calculation can be performed
     */
    public static function validateCalculationRequirements($karyawanId, $bulan, $tahun)
    {
        $errors = [];

        // Check if karyawan exists and active
        $karyawan = Karyawan::find($karyawanId);
        if (!$karyawan) {
            $errors[] = 'Karyawan tidak ditemukan';
        } elseif ($karyawan->status !== 'AKTIF') {
            $errors[] = 'Karyawan tidak aktif';
        }

        // Check if company rules are set
        $aturan = AturanPerusahaan::where('is_active', true)->first();
        if (!$aturan) {
            $errors[] = 'Aturan perusahaan belum dikonfigurasi';
        }

        // Check if period is valid
        $periodDate = Carbon::create($tahun, $bulan, 1);
        if ($periodDate->isFuture()) {
            $errors[] = 'Tidak dapat menghitung gaji untuk periode masa depan';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}