<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom machine_id dan estimasi_downtime_jam ke tabel jenis_kerusakan.
     *
     * Tabel ini sebelumnya hanya punya nama_kerusakan dan deskripsi.
     * Sekarang ditambah:
     *   machine_id            — FK ke mesin spesifik (nullable: bisa lintas mesin)
     *   estimasi_downtime_jam — estimasi durasi downtime jika jenis kerusakan ini terjadi
     */
    public function up(): void
    {
        Schema::table('jenis_kerusakan', function (Blueprint $table) {
            // Foreign key ke machines (nullable — jenis kerusakan bisa generik)
            $table->foreignId('machine_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('machines')
                  ->nullOnDelete();

            // Estimasi downtime dalam jam (float untuk presisi desimal, mis. 2.5 jam)
            $table->decimal('estimasi_downtime_jam', 8, 2)
                  ->nullable()
                  ->after('deskripsi')
                  ->comment('Estimasi durasi downtime dalam jam jika kerusakan ini terjadi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jenis_kerusakan', function (Blueprint $table) {
            $table->dropForeign(['machine_id']);
            $table->dropColumn(['machine_id', 'estimasi_downtime_jam']);
        });
    }
};
