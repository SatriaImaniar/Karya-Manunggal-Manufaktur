<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceSchedule extends Model
{
    /**
     * Atribut yang boleh diisi secara mass-assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'machine_id',
        'history_id',
        'assigned_to',
        'scheduled_date',
        'priority',
        'status',
        'description',
        'completed_at',
        'completion_notes',
    ];

    /**
     * Tipe cast untuk atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Mesin yang terkait dengan jadwal ini.
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Data historis yang menjadi sumber kalkulasi jadwal ini.
     */
    public function history(): BelongsTo
    {
        return $this->belongsTo(MaintenanceHistory::class, 'history_id');
    }

    /**
     * Teknisi yang ditugaskan untuk jadwal ini.
     */
    public function assignedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Badge warna untuk status jadwal.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'in_progress' => 'info',
            'completed' => 'success',
            'overdue' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Badge warna untuk prioritas.
     */
    public function getPriorityBadgeAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'secondary',
            'medium' => 'primary',
            'high' => 'warning',
            'critical' => 'danger',
            default => 'secondary',
        };
    }
}
