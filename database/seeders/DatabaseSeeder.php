<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Urutan seeder penting karena ada dependensi antar tabel:
     * 1. Users → dibutuhkan oleh histories (reported_by) dan schedules (assigned_to)
     * 2. Machines → dibutuhkan oleh histories dan schedules
     * 3. JenisKerusakan → dibutuhkan oleh histories (jenis_kerusakan_id)
     * 4. MaintenanceHistories → dibutuhkan oleh schedules (history_id)
     * 5. Schedules → tergantung semua tabel di atas
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            MachineSeeder::class,
            JenisKerusakanSeeder::class,
            MaintenanceHistorySeeder::class,
            ScheduleSeeder::class,
        ]);
    }
}
