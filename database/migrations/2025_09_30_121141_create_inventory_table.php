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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('crop_id')->nullable()->constrained()->onDelete('set null');
            $table->string('product_name'); // Nama produk (Padi Premium, Tomat Organik, dll)
            $table->string('category'); // Kategori (Sayuran, Buah, Bumbu, Biji-bijian)
            $table->string('subcategory')->nullable(); // Subkategori (Padi, Tomat, Cabai, dll)
            $table->decimal('quantity', 10, 2); // Jumlah stok
            $table->string('unit')->default('kg'); // Satuan (kg, gram, buah, dll)
            $table->decimal('price_per_unit', 10, 2); // Harga per satuan
            $table->date('harvest_date'); // Tanggal panen
            $table->date('estimated_ready_date')->nullable(); // Estimasi siap jual
            $table->string('packaging_type')->default('Kemasan Standar'); // Jenis kemasan
            $table->string('delivery_method')->default('Pickup'); // Metode pengiriman
            $table->string('season')->nullable(); // Musim tanam
            $table->json('photos')->nullable(); // Foto produk (array)
            $table->enum('status', ['available', 'sold_out', 'pending', 'draft'])->default('available');
            $table->text('notes')->nullable(); // Catatan tambahan
            $table->json('metadata')->nullable(); // Data tambahan dalam format JSON
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['category', 'status']);
            $table->index(['user_id', 'category']);
            $table->index(['status', 'created_at']);
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
