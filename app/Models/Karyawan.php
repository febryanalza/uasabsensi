<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Karyawan extends Model
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
    protected $table = 'karyawan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nip',
        'rfid_card_number',
        'nama',
        'email',
        'jabatan',
        'departemen',
        'telepon',
        'alamat',
        'tanggal_masuk',
        'status',
        'gaji_pokok',
        'tunjangan_jabatan',
        'tunjangan_transport',
        'tunjangan_makan',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_masuk' => 'date',
        'gaji_pokok' => 'decimal:2',
        'tunjangan_jabatan' => 'decimal:2',
        'tunjangan_transport' => 'decimal:2',
        'tunjangan_makan' => 'decimal:2',
    ];

    /**
     * Get the RFID card associated with the karyawan.
     */
    public function rfidCard()
    {
        return $this->belongsTo(AvailableRfid::class, 'rfid_card_number', 'card_number');
    }

    /**
     * Get the user account associated with the karyawan.
     */
    public function user()
    {
        return $this->hasOne(User::class, 'karyawan_id', 'id');
    }

    /**
     * Get the absensi records for the karyawan.
     */
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'karyawan_id', 'id');
    }

    /**
     * Get the lembur records for the karyawan.
     */
    public function lembur()
    {
        return $this->hasMany(Lembur::class, 'karyawan_id', 'id');
    }

    /**
     * Get the gaji records for the karyawan.
     */
    public function gaji()
    {
        return $this->hasMany(Gaji::class, 'karyawan_id', 'id');
    }

    /**
     * Get the KPI records for the karyawan.
     */
    public function kpi()
    {
        return $this->hasMany(Kpi::class, 'karyawan_id', 'id');
    }

    /**
     * Scope a query to only include active employees.
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'AKTIF');
    }

    /**
     * Scope a query to filter by department.
     */
    public function scopeByDepartemen($query, $departemen)
    {
        return $query->where('departemen', $departemen);
    }

    /**
     * Scope a query to filter by position.
     */
    public function scopeByJabatan($query, $jabatan)
    {
        return $query->where('jabatan', $jabatan);
    }
}
