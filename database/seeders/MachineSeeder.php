<?php

namespace Database\Seeders;

use App\Models\Machine;
use Illuminate\Database\Seeder;

/**
 * Seed data mesin riil dari PT. Karya Manunggal Manufaktur.
 * Data sesuai foto Excel (tab: proses operasi).
 *
 * Proses Operasi = 4.225,5 jam/tahun untuk semua mesin.
 * Jam operasi per hari = 4225.5 / 365 ≈ 11.58 jam/hari.
 */
class MachineSeeder extends Seeder
{
    public function run(): void
    {
        $machines = [
            [
                'code'                    => 'AMD-60',
                'name'                    => 'Amada 60 Ton',
                'type'                    => 'Press',
                'location'                => 'Lantai Produksi',
                'operating_hours_per_day' => 11.58,  // 4225.5 jam / 365 hari
                'installation_date'       => '2018-01-01',
                'status'                  => 'active',
            ],
            [
                'code'                    => 'AMD-80',
                'name'                    => 'Amada 80 Ton',
                'type'                    => 'Press',
                'location'                => 'Lantai Produksi',
                'operating_hours_per_day' => 11.58,
                'installation_date'       => '2018-01-01',
                'status'                  => 'active',
            ],
            [
                'code'                    => 'KNT-110',
                'name'                    => 'Konatsu 110 Ton',
                'type'                    => 'Press',
                'location'                => 'Lantai Produksi',
                'operating_hours_per_day' => 11.58,
                'installation_date'       => '2018-01-01',
                'status'                  => 'active',
            ],
            [
                'code'                    => 'ADA-55',
                'name'                    => 'Aida 55 Ton',
                'type'                    => 'Press',
                'location'                => 'Lantai Produksi',
                'operating_hours_per_day' => 11.58,
                'installation_date'       => '2018-01-01',
                'status'                  => 'active',
            ],
            [
                'code'                    => 'ADA-60D',
                'name'                    => 'Aida 60 D',
                'type'                    => 'Press',
                'location'                => 'Lantai Produksi',
                'operating_hours_per_day' => 11.58,
                'installation_date'       => '2018-01-01',
                'status'                  => 'active',
            ],
            [
                'code'                    => 'ADA-60C',
                'name'                    => 'Aida 60 C',
                'type'                    => 'Press',
                'location'                => 'Lantai Produksi',
                'operating_hours_per_day' => 11.58,
                'installation_date'       => '2018-01-01',
                'status'                  => 'active',
            ],
        ];

        foreach ($machines as $machine) {
            Machine::create($machine);
        }
    }
}
