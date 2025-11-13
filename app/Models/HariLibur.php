<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
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
    protected $table = 'hari_libur';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tanggal',
        'nama',
        'deskripsi',
        'is_nasional',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal' => 'date',
        'is_nasional' => 'boolean',
    ];

    /**
     * Scope a query to only include national holidays.
     */
    public function scopeNasional($query)
    {
        return $query->where('is_nasional', true);
    }

    /**
     * Scope a query to filter by year.
     */
    public function scopeByYear($query, $year)
    {
        return $query->whereYear('tanggal', $year);
    }

    /**
     * Scope a query to filter by month and year.
     */
    public function scopeByMonth($query, $month, $year)
    {
        return $query->whereMonth('tanggal', $month)
                     ->whereYear('tanggal', $year);
    }

    /**
     * Check if a date is a holiday.
     */
    public static function isHoliday($date)
    {
        return self::where('tanggal', $date)->exists();
    }
}
