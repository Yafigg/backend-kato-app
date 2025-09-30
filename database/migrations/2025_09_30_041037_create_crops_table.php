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
        Schema::create('crops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Nama tanaman (Padi, Tomat, Cabai, dll)
            $table->string('type'); // Metode tanam (Hidroponik, Greenhouse, Lahan Terbuka)
            $table->date('planting_date'); // Tanggal tanam
            $table->string('status')->default('Tumbuh'); // Status (Tumbuh, Siap Panen, Sakit, Mati)
            $table->text('notes')->nullable(); // Catatan tambahan
            $table->json('metadata')->nullable(); // Data tambahan dalam format JSON
            $table->timestamps();
            
            // Index untuk performa query
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'planting_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crops');
    }
};
