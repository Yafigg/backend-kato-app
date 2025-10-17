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
        Schema::table('inventory', function (Blueprint $table) {
            $table->text('description')->nullable()->after('product_name');
            $table->decimal('original_price', 10, 2)->nullable()->after('price_per_unit');
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('original_price');
            $table->decimal('rating', 3, 2)->default(4.5)->after('discount_percentage');
            $table->integer('review_count')->default(0)->after('rating');
            $table->boolean('is_featured')->default(false)->after('review_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'original_price',
                'discount_percentage',
                'rating',
                'review_count',
                'is_featured'
            ]);
        });
    }
};