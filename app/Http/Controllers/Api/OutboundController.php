<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\OutboundShipment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OutboundController extends Controller
{
    /**
     * Get outbound statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $today = now()->format('Y-m-d');
            $thisMonth = now()->format('Y-m');
            
            $stats = [
                'total_ready_items' => Inventory::where('status', 'ready_for_shipment')->count(),
                'total_shipped_today' => OutboundShipment::whereDate('shipped_at', $today)->count(),
                'total_shipments_this_month' => OutboundShipment::where('shipped_at', 'like', $thisMonth . '%')->count(),
                'total_weight_shipped_today' => OutboundShipment::whereDate('shipped_at', $today)->sum('total_weight'),
                'total_weight_shipped_month' => OutboundShipment::where('shipped_at', 'like', $thisMonth . '%')->sum('total_weight'),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch outbound statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get outbound items (ready for shipment)
     */
    public function outboundItems(): JsonResponse
    {
        try {
            $items = Inventory::where('status', 'ready_for_shipment')
                ->with('user:id,name')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch outbound items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get shipment history
     */
    public function shipmentHistory(): JsonResponse
    {
        try {
            $shipments = OutboundShipment::with(['inventoryItems.inventory'])
                ->orderBy('shipped_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $shipments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch shipment history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new shipment
     */
    public function createShipment(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_name' => 'required|string|max:255',
                'customer_address' => 'required|string',
                'customer_phone' => 'required|string',
                'inventory_items' => 'required|array|min:1',
                'inventory_items.*.inventory_id' => 'required|exists:inventory,id',
                'inventory_items.*.quantity' => 'required|numeric|min:0.01',
                'delivery_method' => 'required|string',
                'estimated_delivery_date' => 'required|date|after:today',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Create shipment
            $shipment = OutboundShipment::create([
                'shipment_code' => 'OUT-' . now()->format('Y') . '-' . str_pad(OutboundShipment::count() + 1, 3, '0', STR_PAD_LEFT),
                'customer_name' => $request->customer_name,
                'customer_address' => $request->customer_address,
                'customer_phone' => $request->customer_phone,
                'delivery_method' => $request->delivery_method,
                'estimated_delivery_date' => $request->estimated_delivery_date,
                'status' => 'ready_for_pickup',
                'notes' => $request->notes,
                'user_id' => auth()->id()
            ]);

            $totalWeight = 0;
            $totalItems = 0;

            // Add inventory items to shipment
            foreach ($request->inventory_items as $item) {
                $inventory = Inventory::find($item['inventory_id']);
                
                if ($inventory && $inventory->quantity >= $item['quantity']) {
                    $shipment->inventoryItems()->create([
                        'inventory_id' => $item['inventory_id'],
                        'quantity' => $item['quantity'],
                        'unit' => $inventory->unit,
                        'weight' => ($inventory->weight_per_unit ?? 1) * $item['quantity']
                    ]);

                    $totalWeight += ($inventory->weight_per_unit ?? 1) * $item['quantity'];
                    $totalItems += $item['quantity'];

                    // Update inventory quantity
                    $inventory->decrement('quantity', $item['quantity']);
                }
            }

            // Update shipment totals
            $shipment->update([
                'total_items' => $totalItems,
                'total_weight' => $totalWeight
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Shipment created successfully',
                'data' => $shipment->load('inventoryItems.inventory')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create shipment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark item as shipped
     */
    public function markAsShipped(Request $request, $id): JsonResponse
    {
        try {
            $inventory = Inventory::findOrFail($id);
            
            if ($inventory->status !== 'ready_for_shipment') {
                return response()->json([
                    'success' => false,
                    'message' => 'Item is not ready for shipment'
                ], 400);
            }

            $inventory->update([
                'status' => 'shipped',
                'shipped_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item marked as shipped successfully',
                'data' => $inventory
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark item as shipped',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update shipment status
     */
    public function updateShipmentStatus(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:ready_for_pickup,in_transit,delivered,cancelled'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $shipment = OutboundShipment::findOrFail($id);
            
            $updateData = ['status' => $request->status];
            
            if ($request->status === 'in_transit') {
                $updateData['shipped_at'] = now();
            } elseif ($request->status === 'delivered') {
                $updateData['delivered_at'] = now();
            }

            $shipment->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Shipment status updated successfully',
                'data' => $shipment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shipment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
