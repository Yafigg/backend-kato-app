<?php
/**
 * Script to run migrations on Railway production
 * This script will be executed via Railway's build process
 */

echo "🚀 Running Laravel migrations on Railway...\n";

// Set environment variables for Railway
putenv('APP_ENV=production');
putenv('APP_DEBUG=false');

// Load Laravel application
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Run migrations
try {
    echo "📊 Running migrations...\n";
    $exitCode = Artisan::call('migrate', ['--force' => true]);
    
    if ($exitCode === 0) {
        echo "✅ Migrations completed successfully!\n";
    } else {
        echo "❌ Migrations failed with exit code: $exitCode\n";
    }
    
    echo "🌱 Running seeders...\n";
    $exitCode = Artisan::call('db:seed', ['--force' => true]);
    
    if ($exitCode === 0) {
        echo "✅ Seeders completed successfully!\n";
    } else {
        echo "❌ Seeders failed with exit code: $exitCode\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "🎉 Database setup completed!\n";

