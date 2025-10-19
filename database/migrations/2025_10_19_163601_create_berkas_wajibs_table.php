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
        Schema::create('berkas_wajibs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_berkas'); // e.g., 'KTM', 'Transkrip'
            $table->string('deskripsi')->nullable(); // Deskripsi singkat tentang berkas
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berkas_wajibs');
    }
};
