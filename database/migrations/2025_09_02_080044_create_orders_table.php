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
            $table->string('order_number')->unique(); // PO-2025-001
            $table->foreignId('supplier_id')->constrained('users')->onDelete('cascade'); // Petani/Pengepul
            $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('cascade'); // Customer B2B
            $table->foreignId('inventory_id')->constrained('inventory')->onDelete('cascade');
            $table->decimal('quantity', 10, 2); // Jumlah yang dipesan
            $table->decimal('unit_price', 10, 2); // Harga per unit
            $table->decimal('total_price', 10, 2); // Total harga
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_production', 'ready_for_delivery', 'delivered', 'completed', 'cancelled'])->default('pending');
            $table->date('requested_delivery_date');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
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
