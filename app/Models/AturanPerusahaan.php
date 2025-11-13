<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AturanPerusahaan extends Model
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
    protected $table = 'aturan_perusahaan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'jam_masuk_kerja',
        'jam_pulang_kerja',
        'toleransi_terlambat',
        'potongan_per_menit_terlambat',
        'potongan_per_hari_alpha',
        'tarif_lembur_per_jam',
        'tarif_lembur_libur',
        'bonus_kehadiran_penuh',
        'minimal_hadir_bonus',
        'hari_kerja_per_bulan',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'toleransi_terlambat' => 'integer',
        'potongan_per_menit_terlambat' => 'decimal:2',
        'potongan_per_hari_alpha' => 'decimal:2',
        'tarif_lembur_per_jam' => 'decimal:2',
        'tarif_lembur_libur' => 'decimal:2',
        'bonus_kehadiran_penuh' => 'decimal:2',
        'minimal_hadir_bonus' => 'integer',
        'hari_kerja_per_bulan' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active rules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the active company rule.
     */
    public static function getActiveRule()
    {
        return self::where('is_active', true)->first();
    }
}
