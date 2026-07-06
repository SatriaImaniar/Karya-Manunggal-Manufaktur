<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    /**
     * Atribut yang boleh diisi secara mass-assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'type',
        'location',
        'operating_hours_per_day',
        'installation_date',
        'status',
    ];

    /**
     * Tipe cast untuk atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'operating_hours_per_day' => 'decimal:1',
            'installation_date' => 'date',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Semua data historis kerusakan mesin ini.
     */
    public function maintenanceHistories(): HasMany
    {
        return $this->hasMany(MaintenanceHistory::class);
    }

    /**
     * Semua jadwal maintenance mesin ini.
     */
    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Badge warna untuk status mesin.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'maintenance' => 'warning',
            'inactive' => 'danger',
            default => 'secondary',
        };
    }
}
