<?php
/**
 * Script to check environment variables on Railway
 */

echo "🔍 Checking Railway Environment Variables...\n\n";

// Check critical environment variables
$required_vars = [
    'APP_KEY',
    'DB_CONNECTION', 
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_PASSWORD'
];

echo "📋 Required Environment Variables:\n";
foreach ($required_vars as $var) {
    $value = getenv($var);
    if ($value) {
        // Hide sensitive values
        if (in_array($var, ['DB_PASSWORD', 'APP_KEY'])) {
            $display_value = substr($value, 0, 8) . '...';
        } else {
            $display_value = $value;
        }
        echo "✅ $var: $display_value\n";
    } else {
        echo "❌ $var: NOT SET\n";
    }
}

echo "\n🔗 Database Connection String:\n";
$db_host = getenv('DB_HOST');
$db_port = getenv('DB_PORT');
$db_database = getenv('DB_DATABASE');
$db_username = getenv('DB_USERNAME');

if ($db_host && $db_port && $db_database && $db_username) {
    echo "Host: $db_host:$db_port\n";
    echo "Database: $db_database\n";
    echo "Username: $db_username\n";
    echo "Password: " . (getenv('DB_PASSWORD') ? 'SET' : 'NOT SET') . "\n";
} else {
    echo "❌ Database connection info incomplete\n";
}

echo "\n🎯 Laravel App Info:\n";
echo "APP_ENV: " . (getenv('APP_ENV') ?: 'not set') . "\n";
echo "APP_DEBUG: " . (getenv('APP_DEBUG') ?: 'not set') . "\n";
echo "APP_URL: " . (getenv('APP_URL') ?: 'not set') . "\n";
