<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payroll Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for salary calculation and payroll processing
    |
    */

    // Insurance Rates (as decimal, e.g., 0.02 = 2%)
    'bpjs_kesehatan_rate' => env('BPJS_KESEHATAN_RATE', 0.02), // 2%
    'bpjs_ketenagakerjaan_rate' => env('BPJS_KETENAGAKERJAAN_RATE', 0.01), // 1%

    // Tax Settings
    'ptkp_annual' => env('PTKP_ANNUAL', 54000000), // Annual tax-free income
    'tax_brackets' => [
        ['min' => 0, 'max' => 60000000, 'rate' => 0.05],
        ['min' => 60000000, 'max' => 250000000, 'rate' => 0.15],
        ['min' => 250000000, 'max' => null, 'rate' => 0.25],
    ],

    // Salary Calculation Settings
    'round_to_nearest' => env('PAYROLL_ROUND_TO_NEAREST', 100), // Round to nearest 100
    'minimum_working_days' => env('MINIMUM_WORKING_DAYS', 15),
    
    // Overtime Settings
    'max_overtime_hours_per_day' => env('MAX_OVERTIME_HOURS_PER_DAY', 4),
    'max_overtime_hours_per_month' => env('MAX_OVERTIME_HOURS_PER_MONTH', 40),

    // Attendance Bonus Settings
    'perfect_attendance_bonus' => env('PERFECT_ATTENDANCE_BONUS', 500000),
    'minimum_days_for_bonus' => env('MINIMUM_DAYS_FOR_BONUS', 22),

    // Deduction Settings
    'late_tolerance_minutes' => env('LATE_TOLERANCE_MINUTES', 15),
    'deduction_per_late_minute' => env('DEDUCTION_PER_LATE_MINUTE', 5000),
    'deduction_per_absent_day' => env('DEDUCTION_PER_ABSENT_DAY', 100000),
];