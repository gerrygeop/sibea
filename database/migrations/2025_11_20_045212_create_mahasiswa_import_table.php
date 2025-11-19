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
        Schema::create('mahasiswa_import', function (Blueprint $table) {
            $table->id();
            $table->string('nim');
            $table->string('batch_id')->index();
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'skipped'])->default('pending');
            $table->text('message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index(['batch_id', 'status']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa_import');
    }
};
