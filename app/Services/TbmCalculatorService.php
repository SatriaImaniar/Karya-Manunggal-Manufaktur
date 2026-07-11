<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * TBM Calculator Service
 *
 * Menghitung semua metrik Time-Based Maintenance (TBM) berdasarkan
 * data historis kerusakan mesin dari pabrik.
 *
 * ─── Formula yang digunakan ───────────────────────────────────────────────
 *
 *   MTBF (Mean Time Between Failure)
 *     = T / N
 *     T = Total waktu operasi (jam/periode)
 *     N = Jumlah kerusakan pada periode tersebut
 *
 *   MTTR (Mean Time To Repair)
 *     = Tr / N
 *     Tr = Total waktu perbaikan / downtime (jam/periode)
 *     N  = Jumlah kerusakan
 *
 *   Availability (Ketersediaan Mesin)
 *     = (MTBF / (MTBF + MTTR)) × 100%
 *
 *   Interval Tpm (Preventive Maintenance Interval)
 *     = k × MTBF
 *     k = Safety factor = 0.075 (terverifikasi dari data riil pabrik)
 *
 * ─── Verifikasi dari data riil ────────────────────────────────────────────
 *   Amada 60 Ton : T=4225.5, N=10, Tr=27
 *     MTBF = 4225.5/10 = 422.55 jam
 *     MTTR = 27/10     = 2.70 jam
 *     Avail = (422.55/(422.55+2.70))×100 = 99.36%
 *     Tpm  = 0.075 × 422.55              = 31.69 ≈ 31 Hari ✓
 */
class TbmCalculatorService
{
    /**
     * Safety factor (k) untuk kalkulasi interval Tpm.
     *
     * Nilai k = 0.075 terverifikasi dari data riil mesin pabrik:
     *   Amada 60T : Tpm=31   → k = 31/422.55   ≈ 0.0734
     *   Amada 80T : Tpm=39   → k = 39/528.19   ≈ 0.0738
     *   Konatsu   : Tpm=45   → k = 45/603.64   ≈ 0.0745
     *   Aida 55T  : Tpm=63   → k = 63/845.1    ≈ 0.0746
     *   Aida 60D  : Tpm=52   → k = 52/704.25   ≈ 0.0738
     *   Aida 60C  : Tpm=104  → k = 104/1408.5  ≈ 0.0739
     *   Rata-rata → k ≈ 0.075
     *
     * @var float
     */
    public const SAFETY_FACTOR = 0.075;

    // =========================================================================
    // KALKULASI INDIVIDUAL
    // =========================================================================

    /**
     * Hitung MTBF (Mean Time Between Failure).
     *
     * Rumus: MTBF = T / N
     *
     * @param float $totalOperatingTime  Total waktu operasi dalam jam (T)
     * @param int   $failureCount        Jumlah kerusakan (N), harus > 0
     *
     * @return float Nilai MTBF dalam jam, dibulatkan 2 desimal
     *
     * @throws \InvalidArgumentException jika failureCount <= 0
     */
    public function calculateMtbf(float $totalOperatingTime, int $failureCount): float
    {
        if ($failureCount <= 0) {
            throw new \InvalidArgumentException(
                'Jumlah kerusakan (N) harus lebih besar dari 0 untuk menghitung MTBF.'
            );
        }

        return round($totalOperatingTime / $failureCount, 2);
    }

    /**
     * Hitung MTTR (Mean Time To Repair).
     *
     * Rumus: MTTR = Tr / N
     *
     * @param float $totalRepairTime  Total downtime/waktu perbaikan dalam jam (Tr)
     * @param int   $failureCount     Jumlah kerusakan (N), harus > 0
     *
     * @return float Nilai MTTR dalam jam, dibulatkan 2 desimal
     *
     * @throws \InvalidArgumentException jika failureCount <= 0
     */
    public function calculateMttr(float $totalRepairTime, int $failureCount): float
    {
        if ($failureCount <= 0) {
            throw new \InvalidArgumentException(
                'Jumlah kerusakan (N) harus lebih besar dari 0 untuk menghitung MTTR.'
            );
        }

        return round($totalRepairTime / $failureCount, 2);
    }

    /**
     * Hitung Availability (Ketersediaan Mesin).
     *
     * Rumus: Availability = (MTBF / (MTBF + MTTR)) × 100%
     *
     * Interpretasi: persentase waktu mesin tersedia untuk beroperasi.
     * Contoh: Availability 99.36% → mesin hanya berhenti 0.64% dari waktu total.
     *
     * @param float $mtbf  Nilai MTBF dalam jam
     * @param float $mttr  Nilai MTTR dalam jam
     *
     * @return float Nilai Availability dalam persen (0–100), 2 desimal
     *
     * @throws \InvalidArgumentException jika MTBF + MTTR = 0
     */
    public function calculateAvailability(float $mtbf, float $mttr): float
    {
        $total = $mtbf + $mttr;

        if ($total <= 0) {
            throw new \InvalidArgumentException(
                'Total MTBF + MTTR harus lebih besar dari 0 untuk menghitung Availability.'
            );
        }

        return round(($mtbf / $total) * 100, 2);
    }

    /**
     * Hitung Interval Tpm (Preventive Maintenance Interval).
     *
     * Rumus: Tpm = k × MTBF
     *
     * Catatan satuan:
     *   Hasil perhitungan ini digunakan langsung sebagai interval dalam HARI
     *   untuk keperluan penjadwalan maintenance berikutnya.
     *   Contoh: Tpm = 31.69 → jadwal maintenance dibuat 32 hari kemudian.
     *
     * @param float $mtbf          Nilai MTBF
     * @param float $safetyFactor  Safety factor k (default 0.075)
     *
     * @return float Interval Tpm (digunakan sebagai hari), dibulatkan 2 desimal
     */
    public function calculateTpmInterval(float $mtbf, float $safetyFactor = self::SAFETY_FACTOR): float
    {
        return round($safetyFactor * $mtbf, 2);
    }

    /**
     * Hitung tanggal jadwal maintenance berikutnya.
     *
     * Menggunakan nilai Tpm langsung sebagai jumlah hari.
     * (tpm_interval = k × MTBF, digunakan sebagai interval hari)
     *
     * Contoh:
     *   Tpm = 31.69 → ceil(31.69) = 32 hari dari period_end
     *
     * @param float  $tpmIntervalDays  Interval Tpm (dalam hari)
     * @param Carbon $fromDate         Tanggal referensi (akhir periode)
     * @param float  $operatingHoursPerDay  Tidak digunakan (deprecated, tetap ada untuk backward compat)
     *
     * @return Carbon Tanggal maintenance berikutnya
     */
    public function calculateNextScheduleDate(
        float $tpmIntervalDays,
        Carbon $fromDate,
        float $operatingHoursPerDay = 24.0
    ): Carbon {
        $days = (int) ceil($tpmIntervalDays);

        return $fromDate->copy()->addDays($days);
    }

    // =========================================================================
    // KALKULASI LENGKAP (ONE-CALL)
    // =========================================================================

    /**
     * Jalankan seluruh kalkulasi TBM sekaligus dan kembalikan semua hasil.
     *
     * Input langsung dari form (data riil pabrik):
     *   T  = $totalOperatingTime   (jam operasi total pada periode)
     *   N  = $failureCount         (jumlah kerusakan pada periode)
     *   Tr = $totalRepairTime      (total downtime/waktu perbaikan pada periode)
     *
     * @param float  $totalOperatingTime   Waktu operasi total (T) dalam jam
     * @param int    $failureCount         Jumlah kerusakan (N)
     * @param float  $totalRepairTime      Total waktu perbaikan (Tr) dalam jam
     * @param Carbon $periodEnd            Tanggal akhir periode pengamatan
     * @param float  $operatingHoursPerDay Jam operasi mesin per hari
     * @param float  $safetyFactor         Safety factor k (default 0.075)
     *
     * @return array{
     *   mtbf: float,
     *   mttr: float,
     *   availability: float,
     *   tpm_interval: float,
     *   next_schedule_date: Carbon
     * }
     */
    public function calculateAll(
        float  $totalOperatingTime,
        int    $failureCount,
        float  $totalRepairTime,
        Carbon $periodEnd,
        float  $operatingHoursPerDay = 24.0,
        float  $safetyFactor = self::SAFETY_FACTOR
    ): array {
        // Step 1 — MTBF = T / N
        $mtbf = $this->calculateMtbf($totalOperatingTime, $failureCount);

        // Step 2 — MTTR = Tr / N
        $mttr = $this->calculateMttr($totalRepairTime, $failureCount);

        // Step 3 — Availability = (MTBF / (MTBF + MTTR)) × 100%
        $availability = $this->calculateAvailability($mtbf, $mttr);

        // Step 4 — Tpm = k × MTBF
        $tpmInterval = $this->calculateTpmInterval($mtbf, $safetyFactor);

        // Step 5 — Tanggal jadwal maintenance berikutnya
        $nextScheduleDate = $this->calculateNextScheduleDate(
            $tpmInterval,
            $periodEnd,
            $operatingHoursPerDay
        );

        return [
            'mtbf'               => $mtbf,
            'mttr'               => $mttr,
            'availability'       => $availability,
            'tpm_interval'       => $tpmInterval,
            'next_schedule_date' => $nextScheduleDate,
        ];
    }
}
