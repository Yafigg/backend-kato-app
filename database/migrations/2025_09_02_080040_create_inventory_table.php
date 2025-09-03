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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Petani/Pengepul
            $table->string('product_name'); // Bayam, Wortel, dll
            $table->string('category'); // Sayuran, Rempah-rempah, Umbi
            $table->string('subcategory')->nullable(); // Jenis spesifik
            $table->decimal('quantity', 10, 2); // Jumlah dalam Kg/Ton
            $table->string('unit'); // Kg, Ton, Kw
            $table->decimal('price_per_unit', 10, 2); // Harga per unit
            $table->date('harvest_date'); // Tanggal panen
            $table->date('estimated_ready_date'); // Estimasi siap kirim
            $table->string('packaging_type'); // Kardus, Karung, Palet
            $table->string('delivery_method'); // Sepeda Motor, Pickup, Truk
            $table->string('season'); // Kemarau, Penghujan
            $table->json('photos')->nullable(); // Array of photo URLs
            $table->enum('status', ['available', 'reserved', 'sold', 'expired'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
