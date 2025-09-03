<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

// Set database configuration for Supabase
Config::set('database.connections.pgsql', [
    'driver' => 'pgsql',
    'host' => 'aws-1-ap-southeast-1.pooler.supabase.com',
    'port' => '6543',
    'database' => 'postgres',
    'username' => 'postgres.wmkbpfasrklwdzhbyjzf',
    'password' => 'Socrates2536@',
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'public',
    'sslmode' => 'prefer',
]);

// Set default connection
Config::set('database.default', 'pgsql');

echo "🚀 Starting database migration to Supabase...\n\n";

// Test connection
try {
    $pdo = DB::connection('pgsql')->getPdo();
    echo "✅ Database connection successful!\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Run migrations
echo "\n📊 Running migrations...\n";
try {
    $exitCode = 0;
    passthru('php artisan migrate --force', $exitCode);
    if ($exitCode === 0) {
        echo "✅ Migrations completed successfully!\n";
    } else {
        echo "❌ Migrations failed!\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ Migration error: " . $e->getMessage() . "\n";
    exit(1);
}

// Run seeders
echo "\n🌱 Running seeders...\n";
try {
    $exitCode = 0;
    passthru('php artisan db:seed --force', $exitCode);
    if ($exitCode === 0) {
        echo "✅ Seeders completed successfully!\n";
    } else {
        echo "❌ Seeders failed!\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ Seeder error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🎉 Database setup completed successfully!\n";
echo "Your Kato App backend is now ready for production!\n";
