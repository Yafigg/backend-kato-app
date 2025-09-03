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
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->enum('stage', ['gudang_in', 'sorting', 'grading', 'drying', 'packaging', 'gudang_out', 'quality_check'])->default('gudang_in');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
            $table->decimal('temperature', 5, 2)->nullable(); // Suhu gudang
            $table->decimal('humidity', 5, 2)->nullable(); // Kelembapan gudang
            $table->text('notes')->nullable();
            $table->json('quality_metrics')->nullable(); // Metrik kualitas
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};
