<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductionController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

// Add a simple login route for middleware redirect
Route::get('login', function () {
    return response()->json(['message' => 'Please login via POST /api/auth/login'], 401);
})->name('login');

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Inventory routes (accessible by all authenticated users)
    Route::apiResource('inventory', InventoryController::class);
    Route::get('inventory-statistics', [InventoryController::class, 'statistics']);
    
    // Order routes (accessible by all authenticated users)
    Route::apiResource('orders', OrderController::class);
    Route::put('orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::get('order-statistics', [OrderController::class, 'statistics']);
    
    // Production routes (accessible by management only)
    Route::middleware('role:management')->group(function () {
        Route::apiResource('productions', ProductionController::class);
        Route::post('productions/start-stage', [ProductionController::class, 'startStage']);
        Route::post('productions/{id}/complete-stage', [ProductionController::class, 'completeStage']);
        Route::get('production-statistics', [ProductionController::class, 'statistics']);
    });

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'admin']);
        Route::get('users', [AuthController::class, 'getAllUsers']);
        Route::post('users', [AuthController::class, 'createUser']);
        Route::patch('users/{userId}/verify', [AuthController::class, 'verifyUser']);
    });

    // Petani routes
    Route::middleware('role:petani')->prefix('petani')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'petani']);
    });

    // Management routes
    Route::middleware('role:management')->prefix('management')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'management']);
        
        // Gudang In routes
        Route::middleware('management.subrole:gudang_in')->prefix('gudang-in')->group(function () {
            Route::get('dashboard', function () {
                return response()->json(['message' => 'Gudang In dashboard']);
            });
        });

        // Gudang Out routes
        Route::middleware('management.subrole:gudang_out')->prefix('gudang-out')->group(function () {
            Route::get('dashboard', function () {
                return response()->json(['message' => 'Gudang Out dashboard']);
            });
        });

        // Produksi routes
        Route::middleware('management.subrole:produksi')->prefix('produksi')->group(function () {
            Route::get('dashboard', function () {
                return response()->json(['message' => 'Produksi dashboard']);
            });
        });

        // Pemasaran routes
        Route::middleware('management.subrole:pemasaran')->prefix('pemasaran')->group(function () {
            Route::get('dashboard', function () {
                return response()->json(['message' => 'Pemasaran dashboard']);
            });
        });
    });

    // Customer routes
    Route::middleware('role:customer')->prefix('customer')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'customer']);
    });
});

// Test route
Route::get('test', function () {
    return response()->json([
        'success' => true,
        'message' => 'Kato App API is working!',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// Health check endpoint for Railway
Route::get('health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// Debug endpoint for environment variables
Route::get('debug/env', function () {
    $env_vars = [
        'APP_KEY' => getenv('APP_KEY') ? substr(getenv('APP_KEY'), 0, 8) . '...' : 'NOT SET',
        'DB_CONNECTION' => getenv('DB_CONNECTION') ?: 'NOT SET',
        'DB_HOST' => getenv('DB_HOST') ?: 'NOT SET',
        'DB_PORT' => getenv('DB_PORT') ?: 'NOT SET',
        'DB_DATABASE' => getenv('DB_DATABASE') ?: 'NOT SET',
        'DB_USERNAME' => getenv('DB_USERNAME') ?: 'NOT SET',
        'DB_PASSWORD' => getenv('DB_PASSWORD') ? 'SET' : 'NOT SET',
        'APP_ENV' => getenv('APP_ENV') ?: 'NOT SET',
        'APP_DEBUG' => getenv('APP_DEBUG') ?: 'NOT SET',
        'APP_URL' => getenv('APP_URL') ?: 'NOT SET',
    ];
    
    return response()->json([
        'status' => 'debug',
        'environment_variables' => $env_vars,
        'php_version' => PHP_VERSION,
        'pdo_available' => extension_loaded('pdo'),
        'pdo_pgsql_available' => extension_loaded('pdo_pgsql'),
    ]);
});

// Debug endpoint for database connection
Route::get('debug/db', function () {
    try {
        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT');
        $database = getenv('DB_DATABASE');
        $username = getenv('DB_USERNAME');
        $password = getenv('DB_PASSWORD');
        
        if (!$host || !$port || !$database || !$username || !$password) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database connection info incomplete',
                'missing' => [
                    'host' => !$host,
                    'port' => !$port,
                    'database' => !$database,
                    'username' => !$username,
                    'password' => !$password,
                ]
            ], 500);
        }
        
        $dsn = "pgsql:host=$host;port=$port;dbname=$database";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Test a simple query
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Database connection successful',
            'users_count' => $result['count'],
            'connection_info' => [
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'username' => $username,
            ]
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Database connection failed',
            'error' => $e->getMessage()
        ], 500);
    }
});
