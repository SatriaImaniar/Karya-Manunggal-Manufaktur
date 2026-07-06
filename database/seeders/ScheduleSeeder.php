<?php

namespace Database\Seeders;

use App\Models\MaintenanceHistory;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Services\TbmCalculatorService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Generate jadwal maintenance berdasarkan data historis yang sudah ada.
     */
    public function run(): void
    {
        $calculator = new TbmCalculatorService();
        $teknisiIds = User::where('role', 'teknisi')->pluck('id')->toArray();
        $histories = MaintenanceHistory::with('machine')->get();

        foreach ($histories as $index => $history) {
            $machine = $history->machine;

            // Hitung tanggal jadwal berikutnya dari akhir periode pengamatan
            $nextDate = $calculator->calculateNextScheduleDate(
                $history->tpm_interval,
                Carbon::parse($history->period_end),
                $machine->operating_hours_per_day
            );

            // Assign ke teknisi secara bergantian (round-robin)
            $assignedTo = $teknisiIds[$index % count($teknisiIds)];

            MaintenanceSchedule::create([
                'machine_id' => $machine->id,
                'history_id' => $history->id,
                'assigned_to' => $assignedTo,
                'scheduled_date' => $nextDate,
                'priority' => $this->determinePriority($history->mtbf),
                'status' => 'pending',
                'description' => "Preventive Maintenance - {$machine->name} (berdasarkan kalkulasi TBM periode {$history->period_start->format('d/m/Y')} s.d. {$history->period_end->format('d/m/Y')})",
            ]);
        }
    }

    /**
     * Tentukan prioritas berdasarkan nilai MTBF.
     * Semakin rendah MTBF, semakin tinggi prioritas.
     */
    private function determinePriority(float $mtbf): string
    {
        return match (true) {
            $mtbf < 200 => 'critical',
            $mtbf < 500 => 'high',
            $mtbf < 1000 => 'medium',
            default => 'low',
        };
    }
}
