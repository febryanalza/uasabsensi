<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lembur extends Model
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
    protected $table = 'lembur';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'durasi_jam',
        'tarif_per_jam',
        'total_kompensasi',
        'keterangan',
        'status',
        'disetujui_oleh',
        'tanggal_disetujui',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal' => 'date',
        'jam_mulai' => 'datetime',
        'jam_selesai' => 'datetime',
        'durasi_jam' => 'decimal:2',
        'tarif_per_jam' => 'decimal:2',
        'total_kompensasi' => 'decimal:2',
        'tanggal_disetujui' => 'datetime',
    ];

    /**
     * Get the karyawan that owns the lembur.
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }

    /**
     * Get the user who approved this lembur.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh', 'id');
    }

    /**
     * Scope a query to only include pending overtime.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    /**
     * Scope a query to only include approved overtime.
     */
    public function scopeDisetujui($query)
    {
        return $query->where('status', 'DISETUJUI');
    }

    /**
     * Scope a query to filter by month and year.
     */
    public function scopeByMonth($query, $month, $year)
    {
        return $query->whereMonth('tanggal', $month)
                     ->whereYear('tanggal', $year);
    }
}
