<?php

namespace Database\Seeders;

use App\Models\JenisKerusakan;
use Illuminate\Database\Seeder;

/**
 * Seed jenis kerusakan berdasarkan data riil dari foto Excel
 * (tab: jenis kerusakan & waktu perbaikan).
 *
 * Kerusakan per mesin dari foto:
 *   Amada 60 Ton  : Trufo terbakar, kabel pedal putus, tombol tangan terbakar,
 *                   MCB terbakar, mesin mau jalan, tali pemanpung aus,
 *                   dan lainnya (10 kejadian)
 *   Amada 80 Ton  : Bearing rusak, seal O-ring bocor, solenoid bocor,
 *                   kunigan aus, mesin nyeket (8 kejadian)
 *   Konatsu 110T  : Bearing rusak, seal pedal putus, MCB terbakar,
 *                   mesin ngepres, as pemanpung aus, baut ule setting patah,
 *                   laher terbakar (7 kejadian)
 *   Aida 55 Ton   : V-Belt aus, pedal mati, tombol flatasan rusak,
 *                   mesin overload, kampas aus (5 kejadian)
 *   Aida 60 D     : Solenoid bocor, baut ule setting patah, pedal mati (6 kejadian)
 *   Aida 60 C     : Baut engkol patah, kunigan aus, gip, laher setting,
 *                   soket terbakar, bearing rusak (3 kejadian)
 */
class JenisKerusakanSeeder extends Seeder
{
    public function run(): void
    {
        $jenisKerusakan = [
            // ── Kerusakan Elektrik / Elektronik ─────────────────────────────
            [
                'nama_kerusakan' => 'Trufo Terbakar',
                'deskripsi'      => 'Transformator/trufo mengalami panas berlebih hingga terbakar.',
            ],
            [
                'nama_kerusakan' => 'MCB Terbakar',
                'deskripsi'      => 'Miniature Circuit Breaker (MCB) mengalami trip atau terbakar akibat arus berlebih.',
            ],
            [
                'nama_kerusakan' => 'Tombol Tangan Terbakar',
                'deskripsi'      => 'Tombol operasi tangan mengalami kerusakan akibat arus pendek atau panas berlebih.',
            ],
            [
                'nama_kerusakan' => 'Kabel Pedal Putus',
                'deskripsi'      => 'Kabel pada pedal kontrol putus akibat penggunaan berulang atau tekanan mekanis.',
            ],
            [
                'nama_kerusakan' => 'Soket / Konektor Terbakar',
                'deskripsi'      => 'Soket atau konektor listrik terbakar akibat hubungan arus pendek atau kontak longgar.',
            ],
            // ── Kerusakan Mekanik ────────────────────────────────────────────
            [
                'nama_kerusakan' => 'Bearing Rusak',
                'deskripsi'      => 'Bantalan (bearing) aus atau pecah sehingga menimbulkan getaran dan bunyi tidak normal.',
            ],
            [
                'nama_kerusakan' => 'Seal / O-Ring Bocor',
                'deskripsi'      => 'Seal atau O-ring mengalami kebocoran sehingga fluida (oli/udara) keluar dari sistem.',
            ],
            [
                'nama_kerusakan' => 'Solenoid Bocor',
                'deskripsi'      => 'Solenoid valve mengalami kebocoran sehingga tekanan sistem tidak optimal.',
            ],
            [
                'nama_kerusakan' => 'Tali / Belt Aus',
                'deskripsi'      => 'V-Belt, tali, atau sabuk penggerak mengalami keausan sehingga slip atau putus.',
            ],
            [
                'nama_kerusakan' => 'Pedal Mati / Rusak',
                'deskripsi'      => 'Pedal kontrol tidak berfungsi akibat kerusakan mekanis atau elektrik.',
            ],
            [
                'nama_kerusakan' => 'Baut Patah',
                'deskripsi'      => 'Baut engkol, baut ule setting, atau komponen pengikat lain patah akibat beban berlebih.',
            ],
            [
                'nama_kerusakan' => 'Kunigan (Bushing Kuningan) Aus',
                'deskripsi'      => 'Bushing atau ring kuningan pada mekanisme press mengalami keausan.',
            ],
            [
                'nama_kerusakan' => 'Kampas Aus',
                'deskripsi'      => 'Kampas rem atau kampas kopling mengalami keausan sehingga daya cengkram berkurang.',
            ],
            [
                'nama_kerusakan' => 'Mesin Nyeket / Macet',
                'deskripsi'      => 'Mesin tidak bisa bergerak (macet) akibat komponen mekanik tersangkut atau kering pelumas.',
            ],
            [
                'nama_kerusakan' => 'Mesin Overload',
                'deskripsi'      => 'Mesin berhenti beroperasi akibat beban produksi melebihi kapasitas yang ditetapkan.',
            ],
            [
                'nama_kerusakan' => 'Laher / Bantalan Terbakar',
                'deskripsi'      => 'Laher (bearing) mengalami panas berlebih hingga terbakar, biasanya karena kekurangan pelumas.',
            ],
            [
                'nama_kerusakan' => 'As / Poros Pemanpung Aus',
                'deskripsi'      => 'As atau poros komponen pemanpung (penampung) mengalami keausan pada permukaannya.',
            ],
            [
                'nama_kerusakan' => 'Flatasan / Gip Rusak',
                'deskripsi'      => 'Komponen flatasan atau gip (guide/pengarah) pada mekanisme press mengalami kerusakan.',
            ],
        ];

        foreach ($jenisKerusakan as $jenis) {
            JenisKerusakan::create($jenis);
        }
    }
}
