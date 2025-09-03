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
