<?php
/**
 * Debug script to check environment variables and database connection
 */

echo "🔍 DEBUGGING ENVIRONMENT VARIABLES AND DATABASE CONNECTION\n";
echo "========================================================\n\n";

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

echo "📋 Environment Variables Check:\n";
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

echo "\n🔗 Database Connection Test:\n";
try {
    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT');
    $database = getenv('DB_DATABASE');
    $username = getenv('DB_USERNAME');
    $password = getenv('DB_PASSWORD');
    
    if (!$host || !$port || !$database || !$username || !$password) {
        echo "❌ Database connection info incomplete\n";
        exit(1);
    }
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$database";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection successful!\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Database query successful! Users count: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n🎯 Laravel App Info:\n";
echo "APP_ENV: " . (getenv('APP_ENV') ?: 'not set') . "\n";
echo "APP_DEBUG: " . (getenv('APP_DEBUG') ?: 'not set') . "\n";
echo "APP_URL: " . (getenv('APP_URL') ?: 'not set') . "\n";

echo "\n🔧 PHP Info:\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "PDO Available: " . (extension_loaded('pdo') ? 'Yes' : 'No') . "\n";
echo "PDO PostgreSQL: " . (extension_loaded('pdo_pgsql') ? 'Yes' : 'No') . "\n";
