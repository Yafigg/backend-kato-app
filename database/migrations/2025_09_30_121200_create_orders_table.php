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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // Nomor pesanan (ORD-20250930-001)
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade'); // Pembeli
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade'); // Penjual (petani)
            $table->foreignId('inventory_id')->constrained()->onDelete('cascade'); // Produk yang dipesan
            $table->decimal('quantity', 10, 2); // Jumlah yang dipesan
            $table->decimal('unit_price', 10, 2); // Harga per unit saat pesan
            $table->decimal('total_amount', 10, 2); // Total harga
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'])->default('pending');
            $table->string('delivery_address')->nullable(); // Alamat pengiriman
            $table->string('delivery_method')->default('pickup'); // Metode pengiriman
            $table->date('delivery_date')->nullable(); // Tanggal pengiriman
            $table->text('notes')->nullable(); // Catatan pesanan
            $table->json('metadata')->nullable(); // Data tambahan
            $table->timestamps();
            
            // Indexes
            $table->index(['buyer_id', 'status']);
            $table->index(['seller_id', 'status']);
            $table->index(['inventory_id']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
