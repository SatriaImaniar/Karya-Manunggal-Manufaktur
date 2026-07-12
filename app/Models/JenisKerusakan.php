<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model JenisKerusakan
 *
 * Master data jenis/tipe kerusakan mesin beserta estimasi downtime-nya.
 * Digunakan untuk mengkategorikan kerusakan berdasarkan jenisnya
 * dan menyimpan estimasi waktu downtime untuk perencanaan maintenance.
 *
 * Contoh data:
 *   - "Trafo terbakar"   → machine: Amada 60T → estimasi: 8 jam
 *   - "Bearing rusak"    → machine: Konatsu   → estimasi: 4 jam
 *   - "Kerusakan Panel"  → (generik)          → estimasi: 2 jam
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
        'machine_id',
        'nama_kerusakan',
        'deskripsi',
        'estimasi_downtime_jam',
    ];

    /**
     * Tipe cast untuk atribut.
     */
    protected function casts(): array
    {
        return [
            'estimasi_downtime_jam' => 'decimal:2',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Mesin yang terkait dengan jenis kerusakan ini (nullable = generik).
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Semua data historis maintenance yang menggunakan jenis kerusakan ini.
     */
    public function maintenanceHistories(): HasMany
    {
        return $this->hasMany(MaintenanceHistory::class, 'jenis_kerusakan_id');
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Label estimasi downtime dengan satuan jam.
     * Contoh: "4.50 jam" atau "-" jika tidak diisi.
     */
    public function getEstimasiLabelAttribute(): string
    {
        return $this->estimasi_downtime_jam !== null
            ? number_format((float) $this->estimasi_downtime_jam, 2) . ' jam'
            : '-';
    }
}
