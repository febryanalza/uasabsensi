<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kpi extends Model
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
    protected $table = 'kpi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'karyawan_id',
        'bulan',
        'tahun',
        'target_kehadiran',
        'realisasi_kehadiran',
        'persen_kehadiran',
        'target_penyelesaian_tugas',
        'realisasi_penyelesaian_tugas',
        'persen_penyelesaian_tugas',
        'nilai_kedisiplinan',
        'nilai_kualitas_kerja',
        'nilai_kerjasama',
        'nilai_inisiatif',
        'skor_total',
        'kategori',
        'bonus_kpi',
        'catatan',
        'dinilai_oleh',
        'tanggal_penilaian',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'target_kehadiran' => 'integer',
        'realisasi_kehadiran' => 'integer',
        'persen_kehadiran' => 'decimal:2',
        'target_penyelesaian_tugas' => 'integer',
        'realisasi_penyelesaian_tugas' => 'integer',
        'persen_penyelesaian_tugas' => 'decimal:2',
        'nilai_kedisiplinan' => 'decimal:2',
        'nilai_kualitas_kerja' => 'decimal:2',
        'nilai_kerjasama' => 'decimal:2',
        'nilai_inisiatif' => 'decimal:2',
        'skor_total' => 'decimal:2',
        'bonus_kpi' => 'decimal:2',
        'tanggal_penilaian' => 'datetime',
    ];

    /**
     * Get the karyawan that owns the KPI.
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }

    /**
     * Get the user who evaluated this KPI.
     */
    public function evaluator()
    {
        return $this->belongsTo(User::class, 'dinilai_oleh', 'id');
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
     * Scope a query to filter by category.
     */
    public function scopeByKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }
}
