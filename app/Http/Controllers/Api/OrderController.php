<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Get all orders
     */
    public function index(Request $request)
    {
        $query = Order::with(['buyer', 'seller', 'inventory']);

        // Filter by user role
        if ($request->user()->user_type === 'customer') {
            $query->where('buyer_id', $request->user()->id);
        } elseif ($request->user()->user_type === 'petani') {
            $query->where('seller_id', $request->user()->id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Get single order
     */
    public function show($id)
    {
        $order = Order::with(['buyer', 'seller', 'inventory', 'transactions'])->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Check access permissions
        $user = request()->user();
        if ($user->user_type === 'customer' && $order->buyer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this order'
            ], 403);
        } elseif ($user->user_type === 'petani' && $order->seller_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this order'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Create new order (Customer only)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'inventory_id' => 'required|exists:inventory,id',
            'quantity' => 'required|numeric|min:1',
            'delivery_address' => 'nullable|string|max:500',
            'delivery_method' => 'nullable|string|in:pickup,delivery',
            'delivery_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check inventory availability
        $inventory = Inventory::find($request->inventory_id);
        if (!$inventory || $inventory->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'Inventory item not available'
            ], 400);
        }

        if ($inventory->quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient inventory quantity'
            ], 400);
        }

        // Calculate total amount
        $totalAmount = $inventory->price_per_unit * $request->quantity;

        // Create order
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'buyer_id' => $request->user()->id,
            'seller_id' => $inventory->user_id,
            'inventory_id' => $request->inventory_id,
            'quantity' => $request->quantity,
            'unit_price' => $inventory->price_per_unit,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'delivery_address' => $request->delivery_address,
            'delivery_method' => $request->delivery_method ?? 'pickup',
            'delivery_date' => $request->delivery_date,
            'notes' => $request->notes
        ]);

        // Update inventory quantity
        $inventory->decrement('quantity', $request->quantity);
        
        // If quantity becomes 0, mark as sold out
        if ($inventory->fresh()->quantity <= 0) {
            $inventory->update(['status' => 'sold_out']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => $order->load(['buyer', 'seller', 'inventory'])
        ], 201);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $newStatus = $request->status;

        // Check permissions based on role and current status
        if ($user->user_type === 'customer') {
            // Customer can only cancel pending orders
            if ($newStatus === 'cancelled' && $order->status === 'pending') {
                $order->update(['status' => $newStatus]);
                // Return inventory quantity
                $order->inventory->increment('quantity', $order->quantity);
                $order->inventory->update(['status' => 'available']);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update order status'
                ], 403);
            }
        } elseif ($user->user_type === 'petani') {
            // Petani can confirm/process/ship/deliver orders
            if (($newStatus === 'confirmed' && $order->status === 'pending') ||
                ($newStatus === 'processing' && $order->status === 'confirmed') ||
                ($newStatus === 'shipped' && $order->status === 'processing') ||
                ($newStatus === 'delivered' && $order->status === 'shipped')) {
                $order->update(['status' => $newStatus]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update order status'
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => $order->load(['buyer', 'seller', 'inventory'])
        ]);
    }

    /**
     * Get order statistics
     */
    public function statistics(Request $request)
    {
        $query = Order::query();

        // Filter by user role
        if ($request->user()->user_type === 'customer') {
            $query->where('buyer_id', $request->user()->id);
        } elseif ($request->user()->user_type === 'petani') {
            $query->where('seller_id', $request->user()->id);
        }

        $stats = [
            'total_orders' => $query->count(),
            'pending_orders' => $query->where('status', 'pending')->count(),
            'confirmed_orders' => $query->where('status', 'confirmed')->count(),
            'processing_orders' => $query->where('status', 'processing')->count(),
            'shipped_orders' => $query->where('status', 'shipped')->count(),
            'delivered_orders' => $query->where('status', 'delivered')->count(),
            'cancelled_orders' => $query->where('status', 'cancelled')->count(),
            'refunded_orders' => $query->where('status', 'refunded')->count(),
            'total_revenue' => $query->where('status', 'delivered')->sum('total_amount'),
            'pending_revenue' => $query->whereIn('status', ['pending', 'confirmed', 'processing', 'shipped'])->sum('total_amount')
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
