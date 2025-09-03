<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductionController extends Controller
{
    /**
     * Get all production records
     */
    public function index(Request $request)
    {
        $query = Production::with(['order.supplier', 'order.customer', 'order.inventory']);

        // Filter by stage
        if ($request->has('stage')) {
            $query->where('stage', $request->stage);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $productions = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $productions
        ]);
    }

    /**
     * Get single production record
     */
    public function show($id)
    {
        $production = Production::with(['order.supplier', 'order.customer', 'order.inventory'])->find($id);

        if (!$production) {
            return response()->json([
                'success' => false,
                'message' => 'Production record not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $production
        ]);
    }

    /**
     * Create new production record
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'stage' => 'required|in:gudang_in,sorting,grading,drying,packaging,gudang_out,quality_check',
            'status' => 'required|in:in_progress,completed,paused',
            'temperature' => 'nullable|numeric',
            'humidity' => 'nullable|numeric',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::find($request->order_id);
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Check if order is in correct status for production
        if (!in_array($order->status, ['approved', 'in_production'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order must be approved or in production to start production tracking'
            ], 400);
        }

        // Create production record
        $production = Production::create([
            'order_id' => $request->order_id,
            'stage' => $request->stage,
            'status' => $request->status,
            'temperature' => $request->temperature,
            'humidity' => $request->humidity,
            'notes' => $request->notes,
            'started_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Production record created successfully',
            'data' => $production->load(['order.supplier', 'order.customer', 'order.inventory'])
        ], 201);
    }

    /**
     * Start production stage
     */
    public function startStage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'stage' => 'required|in:gudang_in,sorting,grading,drying,packaging,gudang_out,quality_check',
            'temperature' => 'nullable|numeric',
            'humidity' => 'nullable|numeric',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::find($request->order_id);
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Check if order is in correct status for production
        if (!in_array($order->status, ['processing', 'in_production'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order must be in processing or in_production status to start production'
            ], 400);
        }

        // Check if stage already exists for this order
        $existingProduction = Production::where('order_id', $request->order_id)
            ->where('stage', $request->stage)
            ->first();

        if ($existingProduction) {
            return response()->json([
                'success' => false,
                'message' => 'Production stage already exists for this order'
            ], 400);
        }

        // Create production record
        $production = Production::create([
            'order_id' => $request->order_id,
            'stage' => $request->stage,
            'status' => 'in_progress',
            'temperature' => $request->temperature,
            'humidity' => $request->humidity,
            'notes' => $request->notes,
            'started_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Production stage started successfully',
            'data' => $production->load(['order.supplier', 'order.customer', 'order.inventory'])
        ], 201);
    }

    /**
     * Complete production stage
     */
    public function completeStage(Request $request, $id)
    {
        $production = Production::find($id);

        if (!$production) {
            return response()->json([
                'success' => false,
                'message' => 'Production record not found'
            ], 404);
        }

        if ($production->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'Production stage is not in progress'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'quality_metrics' => 'nullable|array',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update production record
        $production->update([
            'status' => 'completed',
            'quality_metrics' => $request->quality_metrics,
            'notes' => $request->notes ?: $production->notes,
            'completed_at' => now()
        ]);

        // Check if all stages are completed
        $order = $production->order;
        $allStages = ['gudang_in', 'produksi', 'gudang_out', 'pemasaran'];
        $completedStages = Production::where('order_id', $order->id)
            ->where('status', 'completed')
            ->pluck('stage')
            ->toArray();

        if (count(array_intersect($allStages, $completedStages)) === count($allStages)) {
            // All stages completed, update order status
            $order->update(['status' => 'shipped']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Production stage completed successfully',
            'data' => $production->load(['order.supplier', 'order.customer', 'order.inventory'])
        ]);
    }

    /**
     * Update production record
     */
    public function update(Request $request, $id)
    {
        $production = Production::find($id);

        if (!$production) {
            return response()->json([
                'success' => false,
                'message' => 'Production record not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'temperature' => 'nullable|numeric',
            'humidity' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'quality_metrics' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $production->update($request->only(['temperature', 'humidity', 'notes', 'quality_metrics']));

        return response()->json([
            'success' => true,
            'message' => 'Production record updated successfully',
            'data' => $production->load(['order.supplier', 'order.customer', 'order.inventory'])
        ]);
    }

    /**
     * Get production statistics
     */
    public function statistics()
    {
        $stats = [
            'total_productions' => Production::count(),
            'in_progress' => Production::where('status', 'in_progress')->count(),
            'completed' => Production::where('status', 'completed')->count(),
            'gudang_in' => Production::where('stage', 'gudang_in')->count(),
            'produksi' => Production::where('stage', 'produksi')->count(),
            'gudang_out' => Production::where('stage', 'gudang_out')->count(),
            'pemasaran' => Production::where('stage', 'pemasaran')->count(),
            'avg_completion_time' => Production::where('status', 'completed')
                ->whereNotNull('started_at')
                ->whereNotNull('completed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, started_at, completed_at)) as avg_hours')
                ->value('avg_hours')
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
