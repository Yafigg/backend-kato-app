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
        Schema::create('outbound_shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_code')->unique();
            $table->string('customer_name');
            $table->text('customer_address');
            $table->string('customer_phone');
            $table->string('delivery_method');
            $table->date('estimated_delivery_date');
            $table->enum('status', ['ready_for_pickup', 'in_transit', 'delivered', 'cancelled'])->default('ready_for_pickup');
            $table->text('notes')->nullable();
            $table->integer('total_items')->default(0);
            $table->decimal('total_weight', 10, 2)->default(0);
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbound_shipments');
    }
};
