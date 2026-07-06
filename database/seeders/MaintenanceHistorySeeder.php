<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\MaintenanceHistory;
use App\Models\MaintenanceSchedule;
use App\Services\TbmCalculatorService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seed data historis kerusakan mesin dari data riil pabrik.
 *
 * Data bersumber dari foto Excel PT. Karya Manunggal Manufaktur:
 *   T  (waktu_operasi_jam)   = 4.225,5 jam/tahun untuk semua mesin
 *   N  (jumlah_kerusakan)    = sesuai tab "jml kerusakan"
 *   Tr (waktu_perbaikan_jam) = sesuai tab "waktu perbaikan"
 *
 * Hasil kalkulasi TBM yang diharapkan (k = 0.075):
 * ┌─────────────────┬────────┬──────┬─────────┬──────────┬─────────────────┐
 * │ Mesin           │  MTBF  │ MTTR │ Avail%  │ Tpm(jam) │ Tpm(hari≈)      │
 * ├─────────────────┼────────┼──────┼─────────┼──────────┼─────────────────┤
 * │ Amada 60 Ton    │ 422.55 │  2.7 │ 99.37%  │  31.69   │ ~3 hari         │
 * │ Amada 80 Ton    │ 528.19 │  3.0 │ 99.44%  │  39.61   │ ~4 hari         │
 * │ Konatsu 110 Ton │ 603.64 │ 2.29 │ 99.62%  │  45.27   │ ~4 hari         │
 * │ Aida 55 Ton     │ 845.1  │  2.0 │ 99.76%  │  63.38   │ ~6 hari         │
 * │ Aida 60 D       │ 704.25 │ 3.17 │ 99.55%  │  52.82   │ ~5 hari         │
 * │ Aida 60 C       │ 1408.5 │ 2.33 │ 99.83%  │ 105.64   │ ~10 hari        │
 * └─────────────────┴────────┴──────┴─────────┴──────────┴─────────────────┘
 */
class MaintenanceHistorySeeder extends Seeder
{
    public function run(): void
    {
        $calculator = new TbmCalculatorService();

        /**
         * Data riil dari foto Excel.
         *
         * Format:
         *   machine_code         → kode mesin di tabel machines
         *   waktu_operasi_jam    → T  = total jam operasi per tahun
         *   jumlah_kerusakan     → N  = jumlah kejadian kerusakan
         *   waktu_perbaikan_jam  → Tr = total downtime / waktu perbaikan (jam)
         *   period_start/end     → periode pengamatan (tahun 2024)
         */
        $histories = [
            [
                'machine_code'        => 'AMD-60',
                'waktu_operasi_jam'   => 4225.5,   // T
                'jumlah_kerusakan'    => 10,        // N
                'waktu_perbaikan_jam' => 27.0,      // Tr (27 jam downtime total)
                'period_start'        => '2024-01-01',
                'period_end'          => '2024-12-31',
                'notes'               => 'Data tahun 2024. Jenis kerusakan: trufo terbakar, kabel pedal putus, tombol tangan terbakar, MCB terbakar, mesin mau jalan, tali pemanpung aus, dll.',
            ],
            [
                'machine_code'        => 'AMD-80',
                'waktu_operasi_jam'   => 4225.5,
                'jumlah_kerusakan'    => 8,
                'waktu_perbaikan_jam' => 24.0,      // Tr (24 jam)
                'period_start'        => '2024-01-01',
                'period_end'          => '2024-12-31',
                'notes'               => 'Data tahun 2024. Jenis kerusakan: bearing rusak, seal O-ring bocor, solenoid bocor, kunigan aus, mesin nyeket.',
            ],
            [
                'machine_code'        => 'KNT-110',
                'waktu_operasi_jam'   => 4225.5,
                'jumlah_kerusakan'    => 7,
                'waktu_perbaikan_jam' => 16.03,     // Tr ≈ 16 jam (MTTR=2.29 → Tr=N×MTTR=7×2.29=16.03)
                'period_start'        => '2024-01-01',
                'period_end'          => '2024-12-31',
                'notes'               => 'Data tahun 2024. Jenis kerusakan: bearing rusak, seal pedal putus, MCB terbakar, mesin ngepres, as pemanpung aus, baut ule setting patah, laher terbakar.',
            ],
            [
                'machine_code'        => 'ADA-55',
                'waktu_operasi_jam'   => 4225.5,
                'jumlah_kerusakan'    => 5,
                'waktu_perbaikan_jam' => 10.0,      // Tr (MTTR=2.0 → Tr=5×2=10)
                'period_start'        => '2024-01-01',
                'period_end'          => '2024-12-31',
                'notes'               => 'Data tahun 2024. Jenis kerusakan: V-Belt aus, pedal mati, tombol flatasan rusak, mesin overload, kampas aus.',
            ],
            [
                'machine_code'        => 'ADA-60D',
                'waktu_operasi_jam'   => 4225.5,
                'jumlah_kerusakan'    => 6,
                'waktu_perbaikan_jam' => 19.0,      // Tr (MTTR≈3.17 → Tr=6×3.17=19.02≈19)
                'period_start'        => '2024-01-01',
                'period_end'          => '2024-12-31',
                'notes'               => 'Data tahun 2024. Jenis kerusakan: solenoid bocor, baut ule setting patah, pedal mati.',
            ],
            [
                'machine_code'        => 'ADA-60C',
                'waktu_operasi_jam'   => 4225.5,
                'jumlah_kerusakan'    => 3,
                'waktu_perbaikan_jam' => 7.0,       // Tr (MTTR≈2.33 → Tr=3×2.33=6.99≈7)
                'period_start'        => '2024-01-01',
                'period_end'          => '2024-12-31',
                'notes'               => 'Data tahun 2024. Jenis kerusakan: baut engkol patah, kunigan aus, laher setting.',
            ],
        ];

        foreach ($histories as $data) {
            $machine = Machine::where('code', $data['machine_code'])->first();

            if (! $machine) {
                $this->command->warn("Mesin dengan kode {$data['machine_code']} tidak ditemukan. Dilewati.");
                continue;
            }

            // ── Kalkulasi TBM otomatis ────────────────────────────────────────
            $result = $calculator->calculateAll(
                totalOperatingTime:   $data['waktu_operasi_jam'],
                failureCount:         $data['jumlah_kerusakan'],
                totalRepairTime:      $data['waktu_perbaikan_jam'],
                periodEnd:            Carbon::parse($data['period_end']),
                operatingHoursPerDay: $machine->operating_hours_per_day
            );

            // ── Simpan histori dengan kolom canonical baru ────────────────────
            $history = MaintenanceHistory::create([
                'machine_id'              => $machine->id,
                'reported_by'             => 1,   // Admin SPV (dari UserSeeder)
                'waktu_operasi_jam'       => $data['waktu_operasi_jam'],
                'waktu_perbaikan_jam'     => $data['waktu_perbaikan_jam'],
                'jumlah_kerusakan'        => $data['jumlah_kerusakan'],
                'jenis_kerusakan_id'      => null, // Tidak di-assign per histori (data agregat tahunan)
                'period_start'            => $data['period_start'],
                'period_end'              => $data['period_end'],
                'mtbf'                    => $result['mtbf'],
                'mttr'                    => $result['mttr'],
                'availability_percentage' => $result['availability'],
                'tpm_interval'            => $result['tpm_interval'],
                'notes'                   => $data['notes'],
            ]);

            // ── Generate jadwal preventive maintenance otomatis ───────────────
            MaintenanceSchedule::create([
                'machine_id'     => $machine->id,
                'history_id'     => $history->id,
                'scheduled_date' => $result['next_schedule_date'],
                'priority'       => $this->determinePriority($result['mtbf']),
                'status'         => 'pending',
                'description'    => "Preventive Maintenance — {$machine->name} | Tpm = {$result['tpm_interval']} jam | MTBF = {$result['mtbf']} jam | Availability = {$result['availability']}%",
            ]);

            $this->command->info(
                "{$machine->name}: MTBF={$result['mtbf']} | MTTR={$result['mttr']} | Avail={$result['availability']}% | Tpm={$result['tpm_interval']}jam"
            );
        }
    }

    /**
     * Tentukan prioritas berdasarkan MTBF.
     */
    private function determinePriority(float $mtbf): string
    {
        return match (true) {
            $mtbf < 200  => 'critical',
            $mtbf < 500  => 'high',
            $mtbf < 1000 => 'medium',
            default      => 'low',
        };
    }
}
