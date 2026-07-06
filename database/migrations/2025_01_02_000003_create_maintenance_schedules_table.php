<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel ini menampung jadwal preventive maintenance yang dihasilkan
     * dari kalkulasi Tpm interval. Teknisi bisa update status pengerjaan.
     */
    public function up(): void
    {
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->foreignId('history_id')->constrained('maintenance_histories')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->date('scheduled_date');                            // Tanggal maintenance terjadwal
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'overdue'])->default('pending');
            $table->text('description')->nullable();                   // Deskripsi pekerjaan
            $table->datetime('completed_at')->nullable();              // Waktu selesai
            $table->text('completion_notes')->nullable();              // Catatan teknisi setelah selesai

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
