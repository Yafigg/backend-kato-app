<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Production;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard data
     */
    public function admin()
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'petani' => User::where('user_type', 'petani')->count(),
                'management' => User::where('user_type', 'management')->count(),
                'customer' => User::where('user_type', 'customer')->count(),
                'unverified' => User::where('is_verified', false)->count()
            ],
            'inventory' => [
                'total_items' => Inventory::count(),
                'available' => Inventory::where('status', 'available')->count(),
                'reserved' => Inventory::where('status', 'reserved')->count(),
                'processing' => Inventory::where('status', 'processing')->count(),
                'total_value' => Inventory::sum(DB::raw('quantity * price_per_unit'))
            ],
            'orders' => [
                'total' => Order::count(),
                'pending' => Order::where('status', 'pending')->count(),
                'processing' => Order::where('status', 'processing')->count(),
                'delivered' => Order::where('status', 'delivered')->count(),
                'total_revenue' => Order::where('status', 'delivered')->sum('total_price')
            ],
            'productions' => [
                'total' => Production::count(),
                'in_progress' => Production::where('status', 'in_progress')->count(),
                'completed' => Production::where('status', 'completed')->count()
            ]
        ];

        // Recent activities
        $recentOrders = Order::with(['supplier', 'customer', 'inventory'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentInventory = Inventory::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $stats,
                'recent_orders' => $recentOrders,
                'recent_inventory' => $recentInventory
            ]
        ]);
    }

    /**
     * Get petani dashboard data
     */
    public function petani(Request $request)
    {
        $userId = $request->user()->id;

        $stats = [
            'inventory' => [
                'total_items' => Inventory::where('user_id', $userId)->count(),
                'available' => Inventory::where('user_id', $userId)->where('status', 'available')->count(),
                'reserved' => Inventory::where('user_id', $userId)->where('status', 'reserved')->count(),
                'processing' => Inventory::where('user_id', $userId)->where('status', 'processing')->count(),
                'total_value' => Inventory::where('user_id', $userId)->sum(DB::raw('quantity * price_per_unit'))
            ],
            'orders' => [
                'total' => Order::where('supplier_id', $userId)->count(),
                'pending' => Order::where('supplier_id', $userId)->where('status', 'pending')->count(),
                'confirmed' => Order::where('supplier_id', $userId)->where('status', 'confirmed')->count(),
                'processing' => Order::where('supplier_id', $userId)->where('status', 'processing')->count(),
                'delivered' => Order::where('supplier_id', $userId)->where('status', 'delivered')->count(),
                'total_earnings' => Order::where('supplier_id', $userId)->where('status', 'delivered')->sum('total_price')
            ]
        ];

        // Recent activities
        $recentOrders = Order::with(['customer', 'inventory'])
            ->where('supplier_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentInventory = Inventory::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $stats,
                'recent_orders' => $recentOrders,
                'recent_inventory' => $recentInventory
            ]
        ]);
    }

    /**
     * Get management dashboard data
     */
    public function management(Request $request)
    {
        $user = $request->user();
        $subrole = $user->management_subrole;

        $stats = [
            'inventory' => [
                'total_items' => Inventory::count(),
                'available' => Inventory::where('status', 'available')->count(),
                'reserved' => Inventory::where('status', 'reserved')->count(),
                'processing' => Inventory::where('status', 'processing')->count()
            ],
            'orders' => [
                'total' => Order::count(),
                'processing' => Order::where('status', 'processing')->count(),
                'shipped' => Order::where('status', 'shipped')->count(),
                'delivered' => Order::where('status', 'delivered')->count()
            ],
            'productions' => [
                'total' => Production::count(),
                'in_progress' => Production::where('status', 'in_progress')->count(),
                'completed' => Production::where('status', 'completed')->count()
            ]
        ];

        // Sub-role specific data
        if ($subrole === 'gudang_in') {
            $stats['productions']['gudang_in'] = Production::where('stage', 'gudang_in')->count();
            $stats['productions']['gudang_in_in_progress'] = Production::where('stage', 'gudang_in')->where('status', 'in_progress')->count();
        } elseif ($subrole === 'produksi') {
            $stats['productions']['produksi'] = Production::where('stage', 'produksi')->count();
            $stats['productions']['produksi_in_progress'] = Production::where('stage', 'produksi')->where('status', 'in_progress')->count();
        } elseif ($subrole === 'gudang_out') {
            $stats['productions']['gudang_out'] = Production::where('stage', 'gudang_out')->count();
            $stats['productions']['gudang_out_in_progress'] = Production::where('stage', 'gudang_out')->where('status', 'in_progress')->count();
        } elseif ($subrole === 'pemasaran') {
            $stats['productions']['pemasaran'] = Production::where('stage', 'pemasaran')->count();
            $stats['productions']['pemasaran_in_progress'] = Production::where('stage', 'pemasaran')->where('status', 'in_progress')->count();
        }

        // Recent activities
        $recentOrders = Order::with(['supplier', 'customer', 'inventory'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentProductions = Production::with(['order.supplier', 'order.customer'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $stats,
                'recent_orders' => $recentOrders,
                'recent_productions' => $recentProductions,
                'user_subrole' => $subrole
            ]
        ]);
    }

    /**
     * Get customer dashboard data
     */
    public function customer(Request $request)
    {
        $userId = $request->user()->id;

        $stats = [
            'orders' => [
                'total' => Order::where('customer_id', $userId)->count(),
                'pending' => Order::where('customer_id', $userId)->where('status', 'pending')->count(),
                'confirmed' => Order::where('customer_id', $userId)->where('status', 'confirmed')->count(),
                'processing' => Order::where('customer_id', $userId)->where('status', 'processing')->count(),
                'shipped' => Order::where('customer_id', $userId)->where('status', 'shipped')->count(),
                'delivered' => Order::where('customer_id', $userId)->where('status', 'delivered')->count(),
                'total_spent' => Order::where('customer_id', $userId)->where('status', 'delivered')->sum('total_price')
            ]
        ];

        // Recent orders
        $recentOrders = Order::with(['supplier', 'inventory'])
            ->where('customer_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Available inventory for ordering
        $availableInventory = Inventory::with('user')
            ->where('status', 'available')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $stats,
                'recent_orders' => $recentOrders,
                'available_inventory' => $availableInventory
            ]
        ]);
    }
}
