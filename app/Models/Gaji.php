<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gaji extends Model
{
    use HasFactory, HasUuids;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gaji';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'karyawan_id',
        'bulan',
        'tahun',
        'gaji_pokok',
        'tunjangan_jabatan',
        'tunjangan_transport',
        'tunjangan_makan',
        'tunjangan_lembur',
        'bonus_kehadiran',
        'bonus_kpi',
        'potongan_terlambat',
        'potongan_alpha',
        'potongan_lainnya',
        'keterangan_potongan',
        'bpjs_kesehatan',
        'bpjs_ketenagakerjaan',
        'pph21',
        'total_pendapatan',
        'total_potongan',
        'gaji_bersih',
        'jumlah_hadir',
        'jumlah_izin',
        'jumlah_sakit',
        'jumlah_alpha',
        'jumlah_terlambat',
        'total_jam_lembur',
        'status',
        'tanggal_dibuat',
        'tanggal_dibayar',
        'dibuat_oleh',
        'catatan_admin',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'gaji_pokok' => 'decimal:2',
        'tunjangan_jabatan' => 'decimal:2',
        'tunjangan_transport' => 'decimal:2',
        'tunjangan_makan' => 'decimal:2',
        'tunjangan_lembur' => 'decimal:2',
        'bonus_kehadiran' => 'decimal:2',
        'bonus_kpi' => 'decimal:2',
        'potongan_terlambat' => 'decimal:2',
        'potongan_alpha' => 'decimal:2',
        'potongan_lainnya' => 'decimal:2',
        'bpjs_kesehatan' => 'decimal:2',
        'bpjs_ketenagakerjaan' => 'decimal:2',
        'pph21' => 'decimal:2',
        'total_pendapatan' => 'decimal:2',
        'total_potongan' => 'decimal:2',
        'gaji_bersih' => 'decimal:2',
        'jumlah_hadir' => 'integer',
        'jumlah_izin' => 'integer',
        'jumlah_sakit' => 'integer',
        'jumlah_alpha' => 'integer',
        'jumlah_terlambat' => 'integer',
        'total_jam_lembur' => 'decimal:2',
        'tanggal_dibuat' => 'datetime',
        'tanggal_dibayar' => 'datetime',
    ];

    /**
     * Get the karyawan that owns the gaji.
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }

    /**
     * Get the user who created this gaji record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh', 'id');
    }

    /**
     * Scope a query to filter by period (month and year).
     */
    public function scopeByPeriod($query, $month, $year)
    {
        return $query->where('bulan', $month)
                     ->where('tahun', $year);
    }

    /**
     * Scope a query to only include final salary records.
     */
    public function scopeFinal($query)
    {
        return $query->where('status', 'FINAL');
    }

    /**
     * Scope a query to only include paid salary records.
     */
    public function scopeDibayar($query)
    {
        return $query->where('status', 'DIBAYAR');
    }
}
