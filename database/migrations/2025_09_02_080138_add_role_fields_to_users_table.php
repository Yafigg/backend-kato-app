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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('bank_account')->nullable();
            $table->enum('user_type', ['petani', 'management', 'customer'])->default('petani');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'address', 'bank_account', 'user_type', 'is_verified', 'verified_at']);
        });
    }
};
