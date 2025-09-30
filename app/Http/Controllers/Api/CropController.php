<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CropController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = Crop::forUser($user->id);

            // Filter by status if provided
            if ($request->has('status')) {
                $query->withStatus($request->status);
            }

            // Search by name if provided
            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Sort by planting_date (newest first by default)
            $sortBy = $request->get('sort_by', 'planting_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $crops = $query->get();

            return response()->json([
                'success' => true,
                'data' => $crops,
                'message' => 'Crops retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve crops',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'type' => 'required|string|in:Hidroponik,Greenhouse,Lahan Terbuka',
                'planting_date' => 'required|date',
                'status' => 'sometimes|string|in:Tumbuh,Siap Panen,Sakit,Mati',
                'notes' => 'nullable|string',
                'metadata' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $crop = Crop::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'type' => $request->type,
                'planting_date' => $request->planting_date,
                'status' => $request->status ?? 'Tumbuh',
                'notes' => $request->notes,
                'metadata' => $request->metadata,
            ]);

            return response()->json([
                'success' => true,
                'data' => $crop,
                'message' => 'Crop created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create crop',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $crop = Crop::forUser($user->id)->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $crop,
                'message' => 'Crop retrieved successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Crop not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve crop',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'type' => 'sometimes|string|in:Hidroponik,Greenhouse,Lahan Terbuka',
                'planting_date' => 'sometimes|date',
                'status' => 'sometimes|string|in:Tumbuh,Siap Panen,Sakit,Mati',
                'notes' => 'nullable|string',
                'metadata' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $crop = Crop::forUser($user->id)->findOrFail($id);

            $crop->update($request->only([
                'name', 'type', 'planting_date', 'status', 'notes', 'metadata'
            ]));

            return response()->json([
                'success' => true,
                'data' => $crop,
                'message' => 'Crop updated successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Crop not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update crop',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $crop = Crop::forUser($user->id)->findOrFail($id);
            $crop->delete();

            return response()->json([
                'success' => true,
                'message' => 'Crop deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Crop not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete crop',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get crop statistics for the authenticated user.
     */
    public function statistics(): JsonResponse
    {
        try {
            $user = Auth::user();
            $crops = Crop::forUser($user->id);

            $stats = [
                'total_crops' => $crops->count(),
                'ready_to_harvest' => $crops->withStatus('Siap Panen')->count(),
                'growing' => $crops->withStatus('Tumbuh')->count(),
                'sick' => $crops->withStatus('Sakit')->count(),
                'dead' => $crops->withStatus('Mati')->count(),
                'by_type' => $crops->selectRaw('type, COUNT(*) as count')
                    ->groupBy('type')
                    ->get()
                    ->pluck('count', 'type'),
                'by_status' => $crops->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->get()
                    ->pluck('count', 'status'),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Crop statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve crop statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
