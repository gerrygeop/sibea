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
        Schema::create('pendaftarans', function (Blueprint $table) {
            $table->id();

            // Kolom Relasi
            $table->foreignId('periode_beasiswa_id')->constrained('periode_beasiswas')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();

            // Status Pendaftaran
            $table->enum('status', ['draft', 'mendaftar', 'verifikasi', 'diterima', 'ditolak'])->default('draft');

            // Kolom lain (opsional)
            $table->string('note')->nullable(); // Feedback dari admin saat verifikasi

            $table->timestamps();
            $table->softDeletes();

            // Unik: 1 Mahasiswa hanya bisa mendaftar 1 kali per periode
            $table->unique(['periode_beasiswa_id', 'mahasiswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftarans');
    }
};
