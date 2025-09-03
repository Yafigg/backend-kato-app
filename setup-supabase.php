<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

// Set database configuration for Supabase
Config::set('database.connections.pgsql', [
    'driver' => 'pgsql',
    'host' => 'db.wmkbpfasrklwdzhbyjzf.supabase.co',
    'port' => '5432',
    'database' => 'postgres',
    'username' => 'postgres',
    'password' => 'Socrates2536@',
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'public',
    'sslmode' => 'prefer',
]);

// Test connection
try {
    $pdo = DB::connection('pgsql')->getPdo();
    echo "✅ Database connection successful!\n";
    echo "Connected to: " . $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🚀 Ready to run migrations and seeders!\n";
echo "Run: php artisan migrate --force\n";
echo "Run: php artisan db:seed --force\n";
