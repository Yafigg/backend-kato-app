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
        // For PostgreSQL, we need to use ALTER TYPE instead of MODIFY COLUMN
        if (DB::getDriverName() === 'pgsql') {
            // Remove default value first
            DB::statement("ALTER TABLE users ALTER COLUMN user_type DROP DEFAULT");
            
            // Create new enum type
            DB::statement("CREATE TYPE user_type_new AS ENUM ('admin', 'petani', 'management', 'customer')");
            
            // Update column to use new enum type
            DB::statement("ALTER TABLE users ALTER COLUMN user_type TYPE user_type_new USING user_type::text::user_type_new");
            
            // Set new default value
            DB::statement("ALTER TABLE users ALTER COLUMN user_type SET DEFAULT 'petani'");
            
            // Drop old enum type if it exists
            DB::statement("DROP TYPE IF EXISTS user_type");
            
            // Rename new enum type
            DB::statement("ALTER TYPE user_type_new RENAME TO user_type");
        } else {
            // For MySQL
            DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('admin', 'petani', 'management', 'customer') DEFAULT 'petani'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For PostgreSQL, revert enum type
        if (DB::getDriverName() === 'pgsql') {
            // Create old enum type
            DB::statement("CREATE TYPE user_type_old AS ENUM ('petani', 'management', 'customer')");
            
            // Update column to use old enum type
            DB::statement("ALTER TABLE users ALTER COLUMN user_type TYPE user_type_old USING user_type::text::user_type_old");
            
            // Drop current enum type
            DB::statement("DROP TYPE user_type");
            
            // Rename old enum type
            DB::statement("ALTER TYPE user_type_old RENAME TO user_type");
        } else {
            // For MySQL
            DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('petani', 'management', 'customer') DEFAULT 'petani'");
        }
    }
};
