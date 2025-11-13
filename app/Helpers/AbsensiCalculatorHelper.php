<?php

namespace App\Helpers;

use App\Models\AturanPerusahaan;
use App\Models\HariLibur;
use Carbon\Carbon;

class AbsensiCalculatorHelper
{
    /**
     * Calculate all attendance-related values based on company rules
     */
    public static function calculateAttendanceMetrics($jamMasuk, $jamKeluar, $tanggal, $status)
    {
        $aturan = AturanPerusahaan::where('is_active', true)->first();
        
        $result = [
            'menit_terlambat' => 0,
            'menit_pulang_cepat' => 0,
            'is_holiday' => false,
            'calculations' => []
        ];

        if (!$aturan) {
            $result['calculations'][] = 'Aturan perusahaan tidak ditemukan';
            return $result;
        }

        // Check if date is holiday
        $result['is_holiday'] = HariLibur::whereDate('tanggal', $tanggal)->exists();

        // Only calculate for HADIR status
        if ($status !== 'HADIR') {
            return $result;
        }

        // Calculate tardiness
        if ($jamMasuk) {
            $tardiness = self::calculateTardiness($jamMasuk, $tanggal, $aturan);
            $result['menit_terlambat'] = $tardiness['minutes'];
            $result['calculations'] = array_merge($result['calculations'], $tardiness['details']);
        }

        // Calculate early departure
        if ($jamKeluar) {
            $earlyDeparture = self::calculateEarlyDeparture($jamKeluar, $tanggal, $aturan);
            $result['menit_pulang_cepat'] = $earlyDeparture['minutes'];
            $result['calculations'] = array_merge($result['calculations'], $earlyDeparture['details']);
        }

        return $result;
    }

    /**
     * Calculate tardiness based on company rules
     */
    private static function calculateTardiness($jamMasuk, $tanggal, $aturan)
    {
        $jamMasukKaryawan = Carbon::parse($jamMasuk);
        $jamMasukAturan = Carbon::parse($tanggal . ' ' . $aturan->jam_masuk_kerja);
        
        $details = [
            "Jam masuk karyawan: " . $jamMasukKaryawan->format('H:i:s'),
            "Jam masuk aturan: " . $jamMasukAturan->format('H:i:s'),
            "Toleransi keterlambatan: " . $aturan->toleransi_terlambat . " menit"
        ];

        if ($jamMasukKaryawan->gt($jamMasukAturan)) {
            $totalMenitTerlambat = $jamMasukKaryawan->diffInMinutes($jamMasukAturan);
            $menitKenaPenalti = max(0, $totalMenitTerlambat - $aturan->toleransi_terlambat);
            
            $details[] = "Total terlambat: " . $totalMenitTerlambat . " menit";
            $details[] = "Menit kena penalti: " . $menitKenaPenalti . " menit";
            
            return [
                'minutes' => $totalMenitTerlambat,
                'penalty_minutes' => $menitKenaPenalti,
                'details' => $details
            ];
        }

        $details[] = "Tidak terlambat";
        return [
            'minutes' => 0,
            'penalty_minutes' => 0,
            'details' => $details
        ];
    }

    /**
     * Calculate early departure based on company rules
     */
    private static function calculateEarlyDeparture($jamKeluar, $tanggal, $aturan)
    {
        $jamKeluarKaryawan = Carbon::parse($jamKeluar);
        $jamPulangAturan = Carbon::parse($tanggal . ' ' . $aturan->jam_pulang_kerja);

        $details = [
            "Jam keluar karyawan: " . $jamKeluarKaryawan->format('H:i:s'),
            "Jam pulang aturan: " . $jamPulangAturan->format('H:i:s')
        ];

        if ($jamKeluarKaryawan->lt($jamPulangAturan)) {
            $menitPulangCepat = $jamPulangAturan->diffInMinutes($jamKeluarKaryawan);
            $details[] = "Pulang lebih cepat: " . $menitPulangCepat . " menit";
            
            return [
                'minutes' => $menitPulangCepat,
                'details' => $details
            ];
        }

        $details[] = "Tidak pulang cepat";
        return [
            'minutes' => 0,
            'details' => $details
        ];
    }

    /**
     * Calculate monthly attendance bonus eligibility
     */
    public static function calculateAttendanceBonusEligibility($karyawanId, $bulan, $tahun)
    {
        $aturan = AturanPerusahaan::where('is_active', true)->first();
        
        if (!$aturan) {
            return [
                'eligible' => false,
                'reason' => 'Aturan perusahaan tidak ditemukan',
                'bonus_amount' => 0
            ];
        }

        // Get attendance data for the month
        $startDate = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendanceData = \App\Models\Absensi::where('karyawan_id', $karyawanId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $jumlahHadir = $attendanceData->where('status', 'HADIR')->count();
        $jumlahSakit = $attendanceData->where('status', 'SAKIT')->count();
        $jumlahIzin = $attendanceData->where('status', 'IZIN')->count();

        // Calculate working days in month (excluding weekends and holidays)
        $workingDays = self::calculateWorkingDaysInMonth($bulan, $tahun);
        
        // Sakit and izin don't affect bonus eligibility, only actual presence matters
        $attendanceRate = $workingDays > 0 ? ($jumlahHadir / $workingDays) * 100 : 0;
        $minimalHadir = $aturan->minimal_hadir_bonus;
        $bonusAmount = $aturan->bonus_kehadiran_penuh;

        $eligible = $jumlahHadir >= $minimalHadir;

        return [
            'eligible' => $eligible,
            'jumlah_hadir' => $jumlahHadir,
            'minimal_hadir_required' => $minimalHadir,
            'working_days_in_month' => $workingDays,
            'attendance_rate' => round($attendanceRate, 2),
            'bonus_amount' => $eligible ? $bonusAmount : 0,
            'reason' => $eligible ? 
                'Memenuhi syarat bonus kehadiran' : 
                "Hadir {$jumlahHadir} hari, minimal {$minimalHadir} hari"
        ];
    }

    /**
     * Calculate working days in a month (excluding weekends and holidays)
     */
    public static function calculateWorkingDaysInMonth($bulan, $tahun)
    {
        $startDate = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $workingDays = 0;
        $currentDate = $startDate->copy();

        // Get all holidays in this month
        $holidays = HariLibur::whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->pluck('tanggal')
                            ->map(function($date) {
                                return Carbon::parse($date)->format('Y-m-d');
                            })
                            ->toArray();

        while ($currentDate <= $endDate) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if (!in_array($currentDate->dayOfWeek, [0, 6])) {
                // Skip holidays
                if (!in_array($currentDate->format('Y-m-d'), $holidays)) {
                    $workingDays++;
                }
            }
            $currentDate->addDay();
        }

        return $workingDays;
    }

    /**
     * Calculate year-end perfect attendance bonus
     */
    public static function calculateYearEndAttendanceBonus($karyawanId, $tahun)
    {
        $aturan = AturanPerusahaan::where('is_active', true)->first();
        
        if (!$aturan) {
            return [
                'eligible' => false,
                'reason' => 'Aturan perusahaan tidak ditemukan',
                'bonus_amount' => 0
            ];
        }

        $monthlyData = [];
        $totalEligibleMonths = 0;
        
        // Check each month in the year
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $monthBonus = self::calculateAttendanceBonusEligibility($karyawanId, $bulan, $tahun);
            $monthlyData[$bulan] = $monthBonus;
            
            if ($monthBonus['eligible']) {
                $totalEligibleMonths++;
            }
        }

        // Perfect attendance = eligible for bonus in all 12 months
        $isPerfectAttendance = $totalEligibleMonths === 12;
        
        // Year-end bonus could be 12x monthly bonus or special amount
        $yearEndBonus = $isPerfectAttendance ? ($aturan->bonus_kehadiran_penuh * 12) : 0;

        return [
            'eligible' => $isPerfectAttendance,
            'eligible_months' => $totalEligibleMonths,
            'required_months' => 12,
            'bonus_amount' => $yearEndBonus,
            'monthly_breakdown' => $monthlyData,
            'reason' => $isPerfectAttendance ? 
                'Memenuhi syarat bonus kehadiran penuh setahun' : 
                "Memenuhi syarat di {$totalEligibleMonths} bulan dari 12 bulan"
        ];
    }

    /**
     * Get detailed attendance statistics with all calculations
     */
    public static function getDetailedAttendanceStats($karyawanId, $bulan, $tahun)
    {
        $startDate = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendanceData = \App\Models\Absensi::where('karyawan_id', $karyawanId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $stats = [
            'jumlah_hadir' => $attendanceData->where('status', 'HADIR')->count(),
            'jumlah_izin' => $attendanceData->where('status', 'IZIN')->count(),
            'jumlah_sakit' => $attendanceData->where('status', 'SAKIT')->count(),
            'jumlah_alpha' => $attendanceData->where('status', 'ALPHA')->count(),
            'jumlah_cuti' => $attendanceData->where('status', 'CUTI')->count(),
            'jumlah_terlambat' => $attendanceData->where('menit_terlambat', '>', 0)->count(),
            'total_menit_terlambat' => $attendanceData->sum('menit_terlambat'),
            'jumlah_pulang_cepat' => $attendanceData->where('menit_pulang_cepat', '>', 0)->count(),
            'total_menit_pulang_cepat' => $attendanceData->sum('menit_pulang_cepat'),
            'working_days_in_month' => self::calculateWorkingDaysInMonth($bulan, $tahun)
        ];

        // Calculate bonus eligibility
        $bonusEligibility = self::calculateAttendanceBonusEligibility($karyawanId, $bulan, $tahun);
        $stats['bonus_eligibility'] = $bonusEligibility;

        // Calculate attendance rate
        $stats['attendance_rate'] = $stats['working_days_in_month'] > 0 ? 
            round(($stats['jumlah_hadir'] / $stats['working_days_in_month']) * 100, 2) : 0;

        return $stats;
    }
}