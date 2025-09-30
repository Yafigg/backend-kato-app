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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User yang menerima notifikasi
            $table->string('title'); // Judul notifikasi
            $table->text('message'); // Pesan notifikasi
            $table->enum('type', ['order', 'payment', 'crop', 'system', 'promotion']); // Jenis notifikasi
            $table->enum('status', ['unread', 'read', 'archived'])->default('unread'); // Status notifikasi
            $table->json('data')->nullable(); // Data tambahan (order_id, dll)
            $table->timestamp('read_at')->nullable(); // Waktu dibaca
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['type', 'status']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
