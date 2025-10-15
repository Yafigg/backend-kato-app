<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductionController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CropController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\PemasaranController;

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
    
    // Outbound routes (accessible by management only)
    Route::middleware('role:management')->group(function () {
        Route::get('outbound-statistics', [App\Http\Controllers\Api\OutboundController::class, 'statistics']);
        Route::get('outbound-items', [App\Http\Controllers\Api\OutboundController::class, 'outboundItems']);
        Route::get('shipment-history', [App\Http\Controllers\Api\OutboundController::class, 'shipmentHistory']);
        Route::post('outbound-shipments', [App\Http\Controllers\Api\OutboundController::class, 'createShipment']);
        Route::put('inventory/{id}/mark-shipped', [App\Http\Controllers\Api\OutboundController::class, 'markAsShipped']);
        Route::put('outbound-shipments/{id}/status', [App\Http\Controllers\Api\OutboundController::class, 'updateShipmentStatus']);
    });
    
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

    // Crop routes (accessible by petani and management)
    Route::middleware('role:petani,management')->group(function () {
        Route::apiResource('crops', CropController::class);
        Route::get('crops-statistics', [CropController::class, 'statistics']);
    });

    // Transaction routes (accessible by all authenticated users)
    Route::apiResource('transactions', TransactionController::class);
    Route::get('transaction-statistics', [TransactionController::class, 'statistics']);

    // Notification routes (accessible by all authenticated users)
    Route::apiResource('notifications', NotificationController::class);
    Route::post('notifications/{id}/mark-read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('notification-statistics', [NotificationController::class, 'statistics']);

    // Profile routes (accessible by all authenticated users)
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::post('change-password', [ProfileController::class, 'changePassword']);
        Route::get('statistics', [ProfileController::class, 'statistics']);
    });

    // Management routes
    Route::middleware('role:management')->prefix('management')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'management']);
        
        // Gudang In routes
        Route::middleware('management.subrole:gudang_in')->prefix('gudang-in')->group(function () {
            Route::get('dashboard', [DashboardController::class, 'gudangIn']);
            Route::get('inventory', [InventoryController::class, 'index']);
            Route::get('inventory-statistics', [InventoryController::class, 'statistics']);
        });

        // Gudang Out routes
        Route::middleware('management.subrole:gudang_out')->prefix('gudang-out')->group(function () {
            Route::get('dashboard', [DashboardController::class, 'gudangOut']);
            Route::get('statistics', [OutboundController::class, 'statistics']);
            Route::get('outbound-items', [OutboundController::class, 'outboundItems']);
            Route::get('shipment-history', [OutboundController::class, 'shipmentHistory']);
            Route::post('shipments', [OutboundController::class, 'createShipment']);
            Route::put('inventory/{id}/mark-shipped', [OutboundController::class, 'markAsShipped']);
            Route::put('shipments/{id}/status', [OutboundController::class, 'updateShipmentStatus']);
        });

        // Produksi routes
        Route::middleware('management.subrole:produksi')->prefix('produksi')->group(function () {
            Route::get('dashboard', [DashboardController::class, 'produksi']);
            Route::apiResource('productions', ProductionController::class);
            Route::post('productions/start-stage', [ProductionController::class, 'startStage']);
            Route::post('productions/{id}/complete-stage', [ProductionController::class, 'completeStage']);
            Route::get('production-statistics', [ProductionController::class, 'statistics']);
        });

        // Pemasaran routes
        Route::middleware('management.subrole:pemasaran')->prefix('pemasaran')->group(function () {
            Route::get('dashboard', [PemasaranController::class, 'dashboard']);
            Route::get('campaigns', [PemasaranController::class, 'getCampaigns']);
            Route::get('customer-analytics', [PemasaranController::class, 'getCustomerAnalytics']);
            Route::get('sales-reports', [PemasaranController::class, 'getSalesReports']);
        });
    });

    // Customer routes
    Route::middleware('role:customer')->prefix('customer')->group(function () {
        Route::get('dashboard', [CustomerController::class, 'dashboard']);
        Route::get('products', [CustomerController::class, 'getProducts']);
        Route::get('categories', [CustomerController::class, 'getCategories']);
        Route::get('orders', [CustomerController::class, 'getOrders']);
        Route::post('orders', [CustomerController::class, 'createOrder']);
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

// Debug endpoint for inventory creation
Route::post('debug/inventory', function (Request $request) {
    try {
        // Test data
        $testData = [
            'user_id' => 1, // Use first user
            'product_name' => 'Test Product Debug',
            'category' => 'Sayuran',
            'quantity' => 100,
            'unit' => 'kg',
            'price_per_unit' => 50000,
            'harvest_date' => '2025-09-03',
            'estimated_ready_date' => '2025-09-05',
            'packaging_type' => 'Plastik',
            'delivery_method' => 'Pickup',
            'season' => 'Kemarau',
            'status' => 'available'
        ];
        
        // Test Laravel DB connection
        $user = \App\Models\User::first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No users found in database'
            ], 500);
        }
        
        $testData['user_id'] = $user->id;
        
        // Test inventory creation
        $inventory = \App\Models\Inventory::create($testData);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Inventory creation successful',
            'data' => $inventory
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Inventory creation failed',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
