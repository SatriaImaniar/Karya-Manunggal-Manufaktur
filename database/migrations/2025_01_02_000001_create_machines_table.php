<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();                          // Kode mesin (e.g., MCH-001)
            $table->string('name');                                     // Nama mesin
            $table->string('type')->nullable();                        // Tipe/model mesin
            $table->string('location')->nullable();                    // Lokasi mesin di pabrik
            $table->decimal('operating_hours_per_day', 4, 1)->default(24.0); // Jam operasi per hari
            $table->date('installation_date')->nullable();             // Tanggal instalasi
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
