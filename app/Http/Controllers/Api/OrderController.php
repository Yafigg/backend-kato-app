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
        $query = Order::with(['supplier', 'customer', 'inventory']);

        // Filter by user role
        if ($request->user()->user_type === 'customer') {
            $query->where('customer_id', $request->user()->id);
        } elseif ($request->user()->user_type === 'petani') {
            $query->where('supplier_id', $request->user()->id);
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
        $order = Order::with(['supplier', 'customer', 'inventory', 'productions'])->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Check access permissions
        $user = request()->user();
        if ($user->user_type === 'customer' && $order->customer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this order'
            ], 403);
        } elseif ($user->user_type === 'petani' && $order->supplier_id !== $user->id) {
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
            'requested_delivery_date' => 'required|date|after:today'
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

        // Calculate total price
        $totalPrice = $inventory->price_per_unit * $request->quantity;

        // Create order
        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'supplier_id' => $inventory->user_id,
            'customer_id' => $request->user()->id,
            'inventory_id' => $request->inventory_id,
            'quantity' => $request->quantity,
            'unit_price' => $inventory->price_per_unit,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'requested_delivery_date' => $request->requested_delivery_date
        ]);

        // Update inventory status
        $inventory->update(['status' => 'reserved']);

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => $order->load(['supplier', 'customer', 'inventory'])
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
            'status' => 'required|in:pending,approved,rejected,in_production,ready_for_delivery,delivered,completed,cancelled'
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
                // Release inventory
                $order->inventory->update(['status' => 'available']);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update order status'
                ], 403);
            }
        } elseif ($user->user_type === 'petani') {
            // Petani can approve/reject pending orders
            if (($newStatus === 'approved' && $order->status === 'pending') ||
                ($newStatus === 'rejected' && $order->status === 'pending')) {
                $order->update(['status' => $newStatus]);
                
                if ($newStatus === 'rejected') {
                    // Release inventory
                    $order->inventory->update(['status' => 'available']);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update order status'
                ], 403);
            }
        } elseif ($user->user_type === 'management') {
            // Management can update production and delivery status
            if (($newStatus === 'in_production' && $order->status === 'approved') ||
                ($newStatus === 'ready_for_delivery' && $order->status === 'in_production') ||
                ($newStatus === 'delivered' && $order->status === 'ready_for_delivery') ||
                ($newStatus === 'completed' && $order->status === 'delivered')) {
                $order->update(['status' => $newStatus]);
                
                if ($newStatus === 'completed') {
                    $order->inventory->update(['status' => 'completed']);
                }
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
            'data' => $order->load(['supplier', 'customer', 'inventory'])
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
            $query->where('customer_id', $request->user()->id);
        } elseif ($request->user()->user_type === 'petani') {
            $query->where('supplier_id', $request->user()->id);
        }

        $stats = [
            'total_orders' => $query->count(),
            'pending_orders' => $query->where('status', 'pending')->count(),
            'approved_orders' => $query->where('status', 'approved')->count(),
            'rejected_orders' => $query->where('status', 'rejected')->count(),
            'in_production_orders' => $query->where('status', 'in_production')->count(),
            'ready_for_delivery_orders' => $query->where('status', 'ready_for_delivery')->count(),
            'delivered_orders' => $query->where('status', 'delivered')->count(),
            'completed_orders' => $query->where('status', 'completed')->count(),
            'cancelled_orders' => $query->where('status', 'cancelled')->count(),
            'total_revenue' => $query->where('status', 'completed')->sum('total_price'),
            'pending_revenue' => $query->whereIn('status', ['pending', 'approved', 'in_production', 'ready_for_delivery', 'delivered'])->sum('total_price')
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
