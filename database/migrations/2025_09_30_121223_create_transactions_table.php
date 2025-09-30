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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique(); // Nomor transaksi (TXN-20250930-001)
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // Relasi ke order
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User yang melakukan transaksi
            $table->enum('type', ['income', 'expense', 'refund']); // Jenis transaksi
            $table->decimal('amount', 10, 2); // Jumlah transaksi
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('payment_method')->default('cash'); // Metode pembayaran
            $table->string('payment_reference')->nullable(); // Referensi pembayaran
            $table->text('description')->nullable(); // Deskripsi transaksi
            $table->json('metadata')->nullable(); // Data tambahan
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'type']);
            $table->index(['order_id']);
            $table->index(['status', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
