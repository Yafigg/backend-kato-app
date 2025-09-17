<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we need to modify the ENUM to include 'admin'
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('admin', 'petani', 'management', 'customer') DEFAULT 'petani'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'admin' from ENUM
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('petani', 'management', 'customer') DEFAULT 'petani'");
    }
};