<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel maintenance_histories — menyimpan data historis kerusakan mesin
     * beserta hasil kalkulasi TBM (MTBF, MTTR, Availability, Tpm).
     *
     * ─── Input dari user (data riil pabrik) ─────────────────────────────────
     *   T  = waktu_operasi_jam     (Total waktu operasi per periode, jam)
     *   Tr = waktu_perbaikan_jam   (Total downtime/waktu perbaikan, jam)
     *   N  = jumlah_kerusakan      (Jumlah kejadian kerusakan pada periode)
     *
     * ─── Hasil kalkulasi otomatis (TbmCalculatorService) ────────────────────
     *   MTBF              = T / N
     *   MTTR              = Tr / N
     *   availability_%    = (MTBF / (MTBF + MTTR)) × 100
     *   tpm_interval      = k × MTBF    (k = 0.075)
     */
    public function up(): void
    {
        Schema::create('maintenance_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();

            // ── Input Data Riil Pabrik ────────────────────────────────────────
            $table->decimal('waktu_operasi_jam', 10, 2);     // T  — jam/periode
            $table->decimal('waktu_perbaikan_jam', 10, 2);   // Tr — jam/periode (total downtime)
            $table->integer('jumlah_kerusakan');              // N  — jumlah kejadian kerusakan

            // ── Kategori Kerusakan (opsional) ─────────────────────────────────
            $table->foreignId('jenis_kerusakan_id')
                  ->nullable()
                  ->constrained('jenis_kerusakan')
                  ->nullOnDelete();

            // ── Periode Pengamatan ────────────────────────────────────────────
            $table->date('period_start');   // Awal periode (mis. 01-01-2024)
            $table->date('period_end');     // Akhir periode (mis. 31-12-2024)

            // ── Hasil Kalkulasi TBM (diisi otomatis saat store) ──────────────
            $table->decimal('mtbf', 10, 2)->nullable();                // MTBF = T / N (jam)
            $table->decimal('mttr', 10, 2)->nullable();                // MTTR = Tr / N (jam)
            $table->decimal('availability_percentage', 5, 2)->nullable(); // Availability (%)
            $table->decimal('tpm_interval', 10, 2)->nullable();        // Tpm = k × MTBF (jam)

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_histories');
    }
};
