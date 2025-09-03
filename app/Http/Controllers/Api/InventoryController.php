<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Get all inventory items
     */
    public function index(Request $request)
    {
        $query = Inventory::with('user');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by user (for petani to see their own products)
        if ($request->user()->user_type === 'petani') {
            $query->where('user_id', $request->user()->id);
        }

        $inventory = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $inventory
        ]);
    }

    /**
     * Get single inventory item
     */
    public function show($id)
    {
        $inventory = Inventory::with('user')->find($id);

        if (!$inventory) {
            return response()->json([
                'success' => false,
                'message' => 'Inventory item not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $inventory
        ]);
    }

    /**
     * Create new inventory item (Petani only)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'quantity' => 'required|numeric|min:0',
            'price_per_unit' => 'required|numeric|min:0',
            'harvest_date' => 'required|date',
            'packaging_type' => 'required|string|max:100',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['user_id'] = $request->user()->id;
        $data['status'] = 'available';

        // Handle photo uploads
        if ($request->hasFile('photos')) {
            $photoPaths = [];
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('inventory', 'public');
                $photoPaths[] = $path;
            }
            $data['photos'] = json_encode($photoPaths);
        }

        $inventory = Inventory::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Inventory item created successfully',
            'data' => $inventory->load('user')
        ], 201);
    }

    /**
     * Update inventory item
     */
    public function update(Request $request, $id)
    {
        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json([
                'success' => false,
                'message' => 'Inventory item not found'
            ], 404);
        }

        // Check ownership (Petani can only update their own)
        if ($request->user()->user_type === 'petani' && $inventory->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this inventory item'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:100',
            'quantity' => 'sometimes|numeric|min:0',
            'price_per_unit' => 'sometimes|numeric|min:0',
            'harvest_date' => 'sometimes|date',
            'packaging_type' => 'sometimes|string|max:100',
            'status' => 'sometimes|in:available,reserved,processing,completed',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // Handle photo uploads
        if ($request->hasFile('photos')) {
            $photoPaths = [];
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('inventory', 'public');
                $photoPaths[] = $path;
            }
            $data['photos'] = json_encode($photoPaths);
        }

        $inventory->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Inventory item updated successfully',
            'data' => $inventory->load('user')
        ]);
    }

    /**
     * Delete inventory item
     */
    public function destroy($id)
    {
        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json([
                'success' => false,
                'message' => 'Inventory item not found'
            ], 404);
        }

        // Check ownership (Petani can only delete their own)
        if (request()->user()->user_type === 'petani' && $inventory->user_id !== request()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this inventory item'
            ], 403);
        }

        $inventory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inventory item deleted successfully'
        ]);
    }

    /**
     * Get inventory statistics
     */
    public function statistics()
    {
        $stats = [
            'total_items' => Inventory::count(),
            'available_items' => Inventory::where('status', 'available')->count(),
            'reserved_items' => Inventory::where('status', 'reserved')->count(),
            'processing_items' => Inventory::where('status', 'processing')->count(),
            'completed_items' => Inventory::where('status', 'completed')->count(),
            'total_value' => Inventory::sum(DB::raw('quantity * price_per_unit')),
            'categories' => Inventory::select('category')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('category')
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
