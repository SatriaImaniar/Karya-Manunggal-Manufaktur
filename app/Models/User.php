<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Jadwal maintenance yang ditugaskan ke user (teknisi).
     */
    public function assignedSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class, 'assigned_to');
    }

    /**
     * Data historis yang dilaporkan oleh user.
     */
    public function reportedHistories(): HasMany
    {
        return $this->hasMany(MaintenanceHistory::class, 'reported_by');
    }

    // =========================================================================
    // ROLE HELPERS
    // =========================================================================

    /**
     * Cek apakah user adalah Admin/SPV.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Cek apakah user adalah Teknisi.
     */
    public function isTeknisi(): bool
    {
        return $this->role === 'teknisi';
    }
}
