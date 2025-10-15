<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PemasaranController extends Controller
{
    /**
     * Get pemasaran dashboard data
     */
    public function dashboard()
    {
        try {
            $user = auth()->user();
            
            // Get marketing statistics
            $totalSales = Order::where('status', 'delivered')
                ->sum('total_price');
            $totalOrders = Order::count();
            $activeCampaigns = 3; // Placeholder
            $customerGrowth = User::where('user_type', 'customer')
                ->where('created_at', '>=', now()->subMonth())
                ->count();
            
            // Get recent campaigns
            $campaigns = [
                [
                    'id' => 'CAMP001',
                    'title' => 'Flash Sale 50%',
                    'description' => 'Diskon hingga 50% untuk semua produk bubuk organik premium',
                    'status' => 'active',
                    'start_date' => now()->subDays(5)->toISOString(),
                    'end_date' => now()->addDays(10)->toISOString(),
                    'budget' => 5000000,
                    'spent' => 2500000,
                    'reach' => 15000,
                    'conversions' => 450,
                    'roi' => 2.3,
                ],
                [
                    'id' => 'CAMP002',
                    'title' => 'Free Shipping Campaign',
                    'description' => 'Gratis ongkir untuk pembelian minimal Rp 150.000',
                    'status' => 'active',
                    'start_date' => now()->subDays(10)->toISOString(),
                    'end_date' => now()->addDays(20)->toISOString(),
                    'budget' => 3000000,
                    'spent' => 1200000,
                    'reach' => 12000,
                    'conversions' => 320,
                    'roi' => 1.8,
                ],
                [
                    'id' => 'CAMP003',
                    'title' => 'Buy 2 Get 1',
                    'description' => 'Beli 2 gratis 1 untuk produk bubuk umbi-umbian pilihan',
                    'status' => 'completed',
                    'start_date' => now()->subDays(20)->toISOString(),
                    'end_date' => now()->subDays(5)->toISOString(),
                    'budget' => 2000000,
                    'spent' => 2000000,
                    'reach' => 8000,
                    'conversions' => 280,
                    'roi' => 1.5,
                ],
            ];
            
            // Get customer analytics
            $customerAnalytics = [
                'total_customers' => User::where('user_type', 'customer')->count(),
                'new_customers_this_month' => User::where('user_type', 'customer')
                    ->where('created_at', '>=', now()->subMonth())
                    ->count(),
                'repeat_customers' => Order::select('customer_id')
                    ->groupBy('customer_id')
                    ->havingRaw('COUNT(*) > 1')
                    ->count(),
                'average_order_value' => Order::where('status', 'delivered')
                    ->avg('total_price'),
            ];
            
            // Get sales reports
            $salesReports = [
                'monthly_sales' => $this->getMonthlySales(),
                'top_selling_products' => $this->getTopSellingProducts(),
                'marketing_channels' => $this->getMarketingChannelsPerformance(),
                'customer_segments' => $this->getCustomerSegments(),
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => [
                        'total_sales' => $totalSales,
                        'total_orders' => $totalOrders,
                        'active_campaigns' => $activeCampaigns,
                        'customer_growth' => $customerGrowth,
                    ],
                    'campaigns' => $campaigns,
                    'customer_analytics' => $customerAnalytics,
                    'sales_reports' => $salesReports,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get marketing campaigns
     */
    public function getCampaigns()
    {
        try {
            $campaigns = [
                [
                    'id' => 'CAMP001',
                    'title' => 'Flash Sale 50%',
                    'description' => 'Diskon hingga 50% untuk semua produk bubuk organik premium',
                    'status' => 'active',
                    'start_date' => now()->subDays(5)->toISOString(),
                    'end_date' => now()->addDays(10)->toISOString(),
                    'budget' => 5000000,
                    'spent' => 2500000,
                    'reach' => 15000,
                    'conversions' => 450,
                    'roi' => 2.3,
                    'channels' => ['Social Media', 'Email', 'Website'],
                ],
                [
                    'id' => 'CAMP002',
                    'title' => 'Free Shipping Campaign',
                    'description' => 'Gratis ongkir untuk pembelian minimal Rp 150.000',
                    'status' => 'active',
                    'start_date' => now()->subDays(10)->toISOString(),
                    'end_date' => now()->addDays(20)->toISOString(),
                    'budget' => 3000000,
                    'spent' => 1200000,
                    'reach' => 12000,
                    'conversions' => 320,
                    'roi' => 1.8,
                    'channels' => ['Google Ads', 'Facebook', 'Instagram'],
                ],
                [
                    'id' => 'CAMP003',
                    'title' => 'Buy 2 Get 1',
                    'description' => 'Beli 2 gratis 1 untuk produk bubuk umbi-umbian pilihan',
                    'status' => 'completed',
                    'start_date' => now()->subDays(20)->toISOString(),
                    'end_date' => now()->subDays(5)->toISOString(),
                    'budget' => 2000000,
                    'spent' => 2000000,
                    'reach' => 8000,
                    'conversions' => 280,
                    'roi' => 1.5,
                    'channels' => ['WhatsApp', 'Website', 'Email'],
                ],
            ];
            
            return response()->json([
                'success' => true,
                'data' => $campaigns
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch campaigns',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get customer analytics
     */
    public function getCustomerAnalytics()
    {
        try {
            $analytics = [
                'total_customers' => User::where('user_type', 'customer')->count(),
                'new_customers_this_month' => User::where('user_type', 'customer')
                    ->where('created_at', '>=', now()->subMonth())
                    ->count(),
                'repeat_customers' => Order::select('customer_id')
                    ->groupBy('customer_id')
                    ->havingRaw('COUNT(*) > 1')
                    ->count(),
                'average_order_value' => Order::where('status', 'delivered')
                    ->avg('total_price'),
                'customer_retention_rate' => 75.5,
                'lifetime_value' => 850000,
            ];
            
            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch customer analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get sales reports
     */
    public function getSalesReports()
    {
        try {
            $reports = [
                'monthly_sales' => $this->getMonthlySales(),
                'top_selling_products' => $this->getTopSellingProducts(),
                'marketing_channels' => $this->getMarketingChannelsPerformance(),
                'customer_segments' => $this->getCustomerSegments(),
            ];
            
            return response()->json([
                'success' => true,
                'data' => $reports
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sales reports',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get monthly sales data
     */
    private function getMonthlySales()
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $sales = Order::where('status', 'delivered')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_price');
            
            $months[] = [
                'month' => $date->format('M Y'),
                'sales' => $sales,
                'orders' => Order::where('status', 'delivered')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }
        
        return $months;
    }
    
    /**
     * Get top selling products
     */
    private function getTopSellingProducts()
    {
        $products = Order::select('inventory_id', 'inventory.product_name', 'inventory.category')
            ->join('inventory', 'orders.inventory_id', '=', 'inventory.id')
            ->where('orders.status', 'delivered')
            ->selectRaw('SUM(orders.quantity) as total_quantity, SUM(orders.total_price) as total_revenue')
            ->groupBy('inventory_id', 'inventory.product_name', 'inventory.category')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->inventory_id,
                    'name' => $item->product_name,
                    'category' => $item->category,
                    'total_quantity' => $item->total_quantity,
                    'total_revenue' => $item->total_revenue,
                    'image_url' => 'assets/images/' . strtolower(str_replace(' ', '_', $item->product_name)) . '_powder.jpg',
                ];
            });
        
        return $products;
    }
    
    /**
     * Get marketing channels performance
     */
    private function getMarketingChannelsPerformance()
    {
        return [
            [
                'channel' => 'Social Media',
                'leads' => 1250,
                'conversions' => 180,
                'conversion_rate' => 14.4,
                'cost' => 1500000,
                'revenue' => 4500000,
                'roi' => 2.0,
            ],
            [
                'channel' => 'Google Ads',
                'leads' => 980,
                'conversions' => 156,
                'conversion_rate' => 15.9,
                'cost' => 2000000,
                'revenue' => 5200000,
                'roi' => 1.6,
            ],
            [
                'channel' => 'Email Marketing',
                'leads' => 2100,
                'conversions' => 210,
                'conversion_rate' => 10.0,
                'cost' => 500000,
                'revenue' => 3800000,
                'roi' => 6.6,
            ],
            [
                'channel' => 'WhatsApp',
                'leads' => 750,
                'conversions' => 95,
                'conversion_rate' => 12.7,
                'cost' => 300000,
                'revenue' => 2200000,
                'roi' => 6.3,
            ],
        ];
    }
    
    /**
     * Get customer segments
     */
    private function getCustomerSegments()
    {
        return [
            [
                'segment' => 'New Customers',
                'count' => User::where('user_type', 'customer')
                    ->where('created_at', '>=', now()->subMonth())
                    ->count(),
                'percentage' => 35.2,
                'avg_order_value' => 150000,
                'color' => '#4CAF50',
            ],
            [
                'segment' => 'Regular Customers',
                'count' => User::where('user_type', 'customer')
                    ->whereHas('customerOrders', function ($query) {
                        $query->where('created_at', '>=', now()->subMonths(3));
                    })
                    ->count(),
                'percentage' => 45.8,
                'avg_order_value' => 280000,
                'color' => '#2196F3',
            ],
            [
                'segment' => 'VIP Customers',
                'count' => User::where('user_type', 'customer')
                    ->whereHas('customerOrders', function ($query) {
                        $query->where('total_price', '>=', 500000);
                    })
                    ->count(),
                'percentage' => 19.0,
                'avg_order_value' => 750000,
                'color' => '#FF9800',
            ],
        ];
    }
}
