<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model MaintenanceHistory
 *
 * Merepresentasikan satu record data historis kerusakan mesin pada suatu periode.
 * Setiap record mengandung input riil dari pabrik dan hasil kalkulasi TBM otomatis.
 *
 * Kolom input (diisi user):
 *   waktu_operasi_jam   — T  (jam): total waktu operasi mesin pada periode
 *   waktu_perbaikan_jam — Tr (jam): total downtime/waktu perbaikan pada periode
 *   jumlah_kerusakan    — N        : jumlah kejadian kerusakan pada periode
 *   jenis_kerusakan_id  — FK ke tabel jenis_kerusakan (opsional)
 *
 * Kolom hasil kalkulasi (diisi otomatis oleh TbmCalculatorService):
 *   mtbf                 — Mean Time Between Failure (jam)
 *   mttr                 — Mean Time To Repair (jam)
 *   availability_%       — Ketersediaan mesin (%)
 *   tpm_interval         — Interval preventive maintenance (jam)
 *
 * @property int         $id
 * @property int         $machine_id
 * @property int|null    $reported_by
 * @property float       $waktu_operasi_jam
 * @property float       $waktu_perbaikan_jam
 * @property int         $jumlah_kerusakan
 * @property int|null    $jenis_kerusakan_id
 * @property string      $period_start
 * @property string      $period_end
 * @property float|null  $mtbf
 * @property float|null  $mttr
 * @property float|null  $availability_percentage
 * @property float|null  $tpm_interval
 * @property string|null $notes
 */
class MaintenanceHistory extends Model
{
    /**
     * Atribut yang boleh diisi secara mass-assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'machine_id',
        'reported_by',
        // ── Input data riil pabrik ──
        'waktu_operasi_jam',
        'waktu_perbaikan_jam',
        'jumlah_kerusakan',
        'jenis_kerusakan_id',
        // ── Periode pengamatan ──
        'period_start',
        'period_end',
        // ── Hasil kalkulasi TBM (auto) ──
        'mtbf',
        'mttr',
        'availability_percentage',
        'tpm_interval',
        'notes',
    ];

    /**
     * Tipe cast untuk atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu_operasi_jam'      => 'decimal:2',
            'waktu_perbaikan_jam'    => 'decimal:2',
            'jumlah_kerusakan'       => 'integer',
            'mtbf'                   => 'decimal:2',
            'mttr'                   => 'decimal:2',
            'availability_percentage'=> 'decimal:2',
            'tpm_interval'           => 'decimal:2',
            'period_start'           => 'date',
            'period_end'             => 'date',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Mesin yang terkait dengan histori ini.
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Jenis kerusakan yang dikategorikan untuk histori ini.
     */
    public function jenisKerusakan(): BelongsTo
    {
        return $this->belongsTo(JenisKerusakan::class, 'jenis_kerusakan_id');
    }

    /**
     * User yang memasukkan data historis ini.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Jadwal-jadwal maintenance yang dihasilkan dari histori ini.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class, 'history_id');
    }

    // =========================================================================
    // ACCESSORS / COMPUTED
    // =========================================================================

    /**
     * Label teks availability dengan unit persen.
     *
     * @return string  Contoh: "99.36%"
     */
    public function getAvailabilityLabelAttribute(): string
    {
        return $this->availability_percentage !== null
            ? number_format((float) $this->availability_percentage, 2) . '%'
            : '-';
    }

    /**
     * Label periode dalam format "Jan 2024 – Des 2024".
     *
     * @return string
     */
    public function getPeriodeLabelAttribute(): string
    {
        if ($this->period_start && $this->period_end) {
            return $this->period_start->translatedFormat('M Y')
                . ' – '
                . $this->period_end->translatedFormat('M Y');
        }

        return '-';
    }
}
