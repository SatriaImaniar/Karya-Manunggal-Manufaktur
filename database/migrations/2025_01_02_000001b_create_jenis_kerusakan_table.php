<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel ini menampung master data jenis kerusakan mesin.
     * Digunakan untuk mengkategorikan kerusakan berdasarkan tipe/jenis.
     */
    public function up(): void
    {
        Schema::create('jenis_kerusakan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kerusakan');                  // Nama jenis kerusakan (e.g., "Kerusakan Mekanik")
            $table->text('deskripsi')->nullable();             // Deskripsi detail jenis kerusakan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_kerusakan');
    }
};
