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
        Schema::create('periode_berkas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_beasiswa_id')->constrained('periode_beasiswas')->cascadeOnDelete();
            $table->foreignId('berkas_wajib_id')->constrained('berkas_wajibs')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periode_berkas');
    }
};
