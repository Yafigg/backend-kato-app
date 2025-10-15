<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Get customer dashboard data
     */
    public function dashboard()
    {
        try {
            $user = auth()->user();
            
            // Get customer statistics
            $totalOrders = Order::where('customer_id', $user->id)->count();
            $totalSpent = Order::where('customer_id', $user->id)
                ->where('status', 'delivered')
                ->sum('total_price');
            $pendingOrders = Order::where('customer_id', $user->id)
                ->whereIn('status', ['pending', 'approved', 'in_production', 'ready_for_delivery'])
                ->count();
            $wishlistItems = 8; // Placeholder for wishlist count
            
            // Get recent orders
            $recentOrders = Order::where('customer_id', $user->id)
                ->with(['inventory.user'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            // Get featured products (available inventory)
            $featuredProducts = Inventory::where('status', 'available')
                ->with(['user', 'crop'])
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->product_name,
                        'description' => $this->generateProductDescription($item),
                        'price' => $item->price_per_unit,
                        'original_price' => $item->price_per_unit * 1.2, // 20% markup
                        'discount_percentage' => 20,
                        'rating' => rand(40, 50) / 10, // Random rating 4.0-5.0
                        'review_count' => rand(50, 300),
                        'image_url' => 'assets/images/' . strtolower(str_replace(' ', '_', $item->product_name)) . '_powder.jpg',
                        'category' => $item->category,
                        'origin' => $item->user->address ?? 'Indonesia',
                        'weight' => $item->quantity . 'g',
                        'nutrition' => $this->getNutritionInfo($item->category),
                        'benefits' => $this->getBenefits($item->category),
                        'is_favorite' => false,
                        'in_stock' => $item->status === 'available',
                        'stock_quantity' => $item->quantity,
                        'is_featured' => true,
                    ];
                });
            
            // Get promotions
            $promotions = [
                [
                    'id' => 'PROMO001',
                    'title' => 'Flash Sale 50%',
                    'description' => 'Diskon hingga 50% untuk semua produk bubuk organik premium',
                    'discount_percentage' => 50,
                    'valid_until' => now()->addDays(15)->toISOString(),
                    'min_purchase' => 200000,
                    'max_discount' => 100000,
                    'image_url' => 'assets/images/promo_flash_sale.jpg',
                    'is_active' => true,
                ],
                [
                    'id' => 'PROMO002',
                    'title' => 'Free Shipping',
                    'description' => 'Gratis ongkir untuk pembelian minimal Rp 150.000',
                    'discount_percentage' => 0,
                    'valid_until' => now()->addDays(30)->toISOString(),
                    'min_purchase' => 150000,
                    'max_discount' => 25000,
                    'image_url' => 'assets/images/promo_free_shipping.jpg',
                    'is_active' => true,
                ],
                [
                    'id' => 'PROMO003',
                    'title' => 'Buy 2 Get 1',
                    'description' => 'Beli 2 gratis 1 untuk produk bubuk umbi-umbian pilihan',
                    'discount_percentage' => 33,
                    'valid_until' => now()->addDays(10)->toISOString(),
                    'min_purchase' => 0,
                    'max_discount' => 0,
                    'image_url' => 'assets/images/promo_buy2get1.jpg',
                    'is_active' => true,
                ],
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => [
                        'total_orders' => $totalOrders,
                        'total_spent' => $totalSpent,
                        'pending_orders' => $pendingOrders,
                        'wishlist_items' => $wishlistItems,
                    ],
                    'recent_orders' => $recentOrders,
                    'featured_products' => $featuredProducts,
                    'promotions' => $promotions,
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
     * Get all products with filters
     */
    public function getProducts(Request $request)
    {
        try {
            $query = Inventory::where('status', 'available')
                ->with(['user', 'crop']);
            
            // Apply filters
            if ($request->has('category') && $request->category !== 'All') {
                $query->where('category', $request->category);
            }
            
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%");
                });
            }
            
            if ($request->has('featured') && $request->featured) {
                $query->where('created_at', '>=', now()->subDays(7)); // Recent products
            }
            
            if ($request->has('in_stock') && $request->in_stock) {
                $query->where('quantity', '>', 0);
            }
            
            $products = $query->orderBy('created_at', 'desc')->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->product_name,
                        'description' => $this->generateProductDescription($item),
                        'price' => $item->price_per_unit,
                        'original_price' => $item->price_per_unit * 1.2,
                        'discount_percentage' => 20,
                        'rating' => rand(40, 50) / 10,
                        'review_count' => rand(50, 300),
                        'image_url' => 'assets/images/' . strtolower(str_replace(' ', '_', $item->product_name)) . '_powder.jpg',
                        'category' => $item->category,
                        'origin' => $item->user->address ?? 'Indonesia',
                        'weight' => $item->quantity . 'g',
                        'nutrition' => $this->getNutritionInfo($item->category),
                        'benefits' => $this->getBenefits($item->category),
                        'is_favorite' => false,
                        'in_stock' => $item->status === 'available',
                        'stock_quantity' => $item->quantity,
                        'is_featured' => $item->created_at >= now()->subDays(7),
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $products
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get product categories
     */
    public function getCategories()
    {
        try {
            $categories = [
                [
                    'id' => 'CAT001',
                    'name' => 'Sayuran',
                    'description' => 'Bubuk sayuran organik kaya nutrisi dan vitamin untuk kesehatan optimal',
                    'icon' => 'Icons.eco_rounded',
                    'product_count' => Inventory::where('category', 'Sayuran')->count(),
                    'color' => 0xFF4CAF50,
                ],
                [
                    'id' => 'CAT002',
                    'name' => 'Umbi',
                    'description' => 'Bubuk umbi-umbian alami dengan karbohidrat kompleks dan serat tinggi',
                    'icon' => 'Icons.agriculture_rounded',
                    'product_count' => Inventory::where('category', 'Umbi')->count(),
                    'color' => 0xFFFF9800,
                ],
                [
                    'id' => 'CAT003',
                    'name' => 'Rempah',
                    'description' => 'Bubuk rempah-rempah asli dengan khasiat obat dan cita rasa alami',
                    'icon' => 'Icons.local_fire_department_rounded',
                    'product_count' => Inventory::where('category', 'Rempah')->count(),
                    'color' => 0xFF9C27B0,
                ],
                [
                    'id' => 'CAT004',
                    'name' => 'Premium',
                    'description' => 'Bubuk premium dengan kualitas terbaik dan nutrisi maksimal',
                    'icon' => 'Icons.star_rounded',
                    'product_count' => Inventory::where('category', 'Premium')->count(),
                    'color' => 0xFF2196F3,
                ],
                [
                    'id' => 'CAT005',
                    'name' => 'Organik',
                    'description' => 'Bubuk organik bebas pestisida dan bahan kimia berbahaya',
                    'icon' => 'Icons.eco_rounded',
                    'product_count' => Inventory::where('category', 'Organik')->count(),
                    'color' => 0xFF4CAF50,
                ],
            ];
            
            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get customer orders
     */
    public function getOrders(Request $request)
    {
        try {
            $user = auth()->user();
            $query = Order::where('customer_id', $user->id)
                ->with(['inventory.user']);
            
            // Apply status filter
            if ($request->has('status') && $request->status !== 'All') {
                $query->where('status', $request->status);
            }
            
            $orders = $query->orderBy('created_at', 'desc')->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'order_date' => $order->created_at->toISOString(),
                        'status' => $order->status,
                        'total_amount' => $order->total_price,
                        'shipping_cost' => 15000, // Fixed shipping cost
                        'items' => [
                            [
                                'id' => $order->inventory->id,
                                'name' => $order->inventory->product_name,
                                'quantity' => $order->quantity,
                                'price' => $order->unit_price,
                                'image_url' => 'assets/images/' . strtolower(str_replace(' ', '_', $order->inventory->product_name)) . '_powder.jpg',
                            ]
                        ],
                        'shipping_address' => $order->shipping_address,
                        'notes' => $order->notes,
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create new order
     */
    public function createOrder(Request $request)
    {
        try {
            $user = auth()->user();
            
            $validated = $request->validate([
                'inventory_id' => 'required|exists:inventory,id',
                'quantity' => 'required|numeric|min:1',
                'shipping_address' => 'required|string',
                'notes' => 'nullable|string',
            ]);
            
            $inventory = Inventory::findOrFail($validated['inventory_id']);
            
            // Check if enough stock
            if ($inventory->quantity < $validated['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock'
                ], 400);
            }
            
            // Calculate total
            $subtotal = $inventory->price_per_unit * $validated['quantity'];
            $shippingCost = 15000; // Fixed shipping cost
            $total = $subtotal + $shippingCost;
            
            // Create order
            $order = Order::create([
                'customer_id' => $user->id,
                'supplier_id' => $inventory->user_id,
                'inventory_id' => $inventory->id,
                'order_number' => 'KATO-' . date('Y') . '-' . str_pad(Order::count() + 1, 3, '0', STR_PAD_LEFT),
                'quantity' => $validated['quantity'],
                'unit_price' => $inventory->price_per_unit,
                'total_price' => $total,
                'status' => 'pending',
                'requested_delivery_date' => now()->addDays(7),
                'notes' => $validated['notes'],
            ]);
            
            // Update inventory quantity
            $inventory->decrement('quantity', $validated['quantity']);
            if ($inventory->quantity <= 0) {
                $inventory->update(['status' => 'sold']);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate product description based on category
     */
    private function generateProductDescription($item)
    {
        $descriptions = [
            'Sayuran' => 'Bubuk sayuran organik kaya nutrisi dan vitamin untuk kesehatan optimal',
            'Umbi' => 'Bubuk umbi-umbian alami dengan karbohidrat kompleks dan serat tinggi',
            'Rempah' => 'Bubuk rempah-rempah asli dengan khasiat obat dan cita rasa alami',
            'Premium' => 'Bubuk premium dengan kualitas terbaik dan nutrisi maksimal',
            'Organik' => 'Bubuk organik bebas pestisida dan bahan kimia berbahaya',
        ];
        
        return $descriptions[$item->category] ?? 'Produk berkualitas tinggi dengan nutrisi alami';
    }
    
    /**
     * Get nutrition info based on category
     */
    private function getNutritionInfo($category)
    {
        $nutrition = [
            'Sayuran' => 'Tinggi Vitamin A',
            'Umbi' => 'Tinggi Karbohidrat',
            'Rempah' => 'Tinggi Antioksidan',
            'Premium' => 'Nutrisi Lengkap',
            'Organik' => 'Bebas Kimia',
        ];
        
        return $nutrition[$category] ?? 'Nutrisi Alami';
    }
    
    /**
     * Get benefits based on category
     */
    private function getBenefits($category)
    {
        $benefits = [
            'Sayuran' => ['Vitamin A', 'Serat', 'Antioksidan'],
            'Umbi' => ['Energi', 'Serat', 'Mineral'],
            'Rempah' => ['Anti-inflamasi', 'Imunitas', 'Antioksidan'],
            'Premium' => ['Nutrisi Lengkap', 'Kualitas Terbaik', 'Manfaat Maksimal'],
            'Organik' => ['Bebas Kimia', 'Alami', 'Sehat'],
        ];
        
        return $benefits[$category] ?? ['Nutrisi', 'Alami', 'Sehat'];
    }
}
