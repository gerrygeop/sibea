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
        Schema::create('periode_beasiswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beasiswa_id')->constrained('beasiswas')->cascadeOnDelete();

            // Detail Periode
            $table->string('nama_periode');
            $table->date('tanggal_mulai_daftar');
            $table->date('tanggal_akhir_daftar');
            $table->unsignedInteger('besar_beasiswa'); // in Rupiah
            $table->boolean('is_aktif')->default(false); // Flag untuk ditampilkan di menu Mahasiswa

            // KOLOM JSON DARI FILAMENT REPEATER
            // Menyimpan daftar aturan cek otomatis (IPK min, Semester min)
            $table->json('persyaratans_json')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periode_beasiswas');
    }
};
