<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model JenisKerusakan
 *
 * Representasi master data jenis/tipe kerusakan mesin.
 * Digunakan untuk mengkategorikan kerusakan berdasarkan jenisnya.
 */
class JenisKerusakan extends Model
{
    /**
     * Nama tabel yang digunakan.
     *
     * @var string
     */
    protected $table = 'jenis_kerusakan';

    /**
     * Atribut yang boleh diisi secara mass-assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_kerusakan',
        'deskripsi',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Semua data historis maintenance yang menggunakan jenis kerusakan ini.
     */
    public function maintenanceHistories(): HasMany
    {
        return $this->hasMany(MaintenanceHistory::class, 'jenis_kerusakan_id');
    }
}
