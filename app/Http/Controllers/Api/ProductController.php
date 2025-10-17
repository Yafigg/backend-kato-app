<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Get all products for customer
     */
    public function index(Request $request)
    {
        $query = Inventory::where('status', 'available')
            ->where('quantity', '>', 0);

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Search by product name
        if ($request->has('search')) {
            $query->where('product_name', 'like', '%' . $request->search . '%');
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['product_name', 'price_per_unit', 'created_at', 'rating'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('limit', 20);
        $products = $query->paginate($perPage);

        // Transform data for customer
        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->product_name,
                'description' => $product->description ?? 'Produk organik berkualitas tinggi',
                'category' => $product->category,
                'price' => $product->price_per_unit,
                'original_price' => $product->original_price ?? $product->price_per_unit,
                'discount_percentage' => $product->discount_percentage ?? 0,
                'rating' => $product->rating ?? 4.5,
                'review_count' => $product->review_count ?? 0,
                'is_featured' => $product->is_featured ?? false,
                'in_stock' => $product->quantity > 0,
                'stock_quantity' => $product->quantity,
                'image_url' => $this->getProductImageUrl($product),
                'images' => $this->getProductImages($product),
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        ]);
    }

    /**
     * Get single product
     */
    public function show($id)
    {
        $product = Inventory::where('status', 'available')
            ->where('quantity', '>', 0)
            ->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $productData = [
            'id' => $product->id,
            'name' => $product->product_name,
            'description' => $product->description ?? 'Produk organik berkualitas tinggi',
            'category' => $product->category,
            'price' => $product->price_per_unit,
            'original_price' => $product->original_price ?? $product->price_per_unit,
            'discount_percentage' => $product->discount_percentage ?? 0,
            'rating' => $product->rating ?? 4.5,
            'review_count' => $product->review_count ?? 0,
            'is_featured' => $product->is_featured ?? false,
            'in_stock' => $product->quantity > 0,
            'stock_quantity' => $product->quantity,
            'image_url' => $this->getProductImageUrl($product),
            'images' => $this->getProductImages($product),
            'harvest_date' => $product->harvest_date,
            'packaging_type' => $product->packaging_type,
            'season' => $product->season,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
        ];

        return response()->json([
            'success' => true,
            'data' => $productData
        ]);
    }

    /**
     * Upload product image (Production/Marketing only)
     */
    public function uploadImage(Request $request, $id)
    {
        // Check if user is production or marketing
        if (!in_array($request->user()->user_type, ['produksi', 'pemasaran'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to upload product images'
            ], 403);
        }

        $product = Inventory::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_primary' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Store image
        $path = $request->file('image')->store('products', 'public');
        
        // Get existing images
        $images = $product->photos ? json_decode($product->photos, true) : [];
        
        // Add new image
        $imageData = [
            'path' => $path,
            'is_primary' => $request->get('is_primary', false),
            'uploaded_by' => $request->user()->user_type,
            'uploaded_at' => now()->toISOString()
        ];
        
        $images[] = $imageData;
        
        // If this is primary, unset others as primary
        if ($imageData['is_primary']) {
            foreach ($images as &$img) {
                if ($img !== $imageData) {
                    $img['is_primary'] = false;
                }
            }
        }
        
        $product->update(['photos' => json_encode($images)]);

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'data' => [
                'image_url' => Storage::url($path),
                'is_primary' => $imageData['is_primary']
            ]
        ]);
    }

    /**
     * Set primary image
     */
    public function setPrimaryImage(Request $request, $id, $imageIndex)
    {
        // Check if user is production or marketing
        if (!in_array($request->user()->user_type, ['produksi', 'pemasaran'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to manage product images'
            ], 403);
        }

        $product = Inventory::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $images = $product->photos ? json_decode($product->photos, true) : [];
        
        if (!isset($images[$imageIndex])) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found'
            ], 404);
        }

        // Set all images as not primary
        foreach ($images as &$img) {
            $img['is_primary'] = false;
        }
        
        // Set selected image as primary
        $images[$imageIndex]['is_primary'] = true;
        
        $product->update(['photos' => json_encode($images)]);

        return response()->json([
            'success' => true,
            'message' => 'Primary image updated successfully'
        ]);
    }

    /**
     * Delete product image
     */
    public function deleteImage(Request $request, $id, $imageIndex)
    {
        // Check if user is production or marketing
        if (!in_array($request->user()->user_type, ['produksi', 'pemasaran'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to manage product images'
            ], 403);
        }

        $product = Inventory::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $images = $product->photos ? json_decode($product->photos, true) : [];
        
        if (!isset($images[$imageIndex])) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found'
            ], 404);
        }

        // Delete file from storage
        $imagePath = $images[$imageIndex]['path'];
        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }

        // Remove from array
        unset($images[$imageIndex]);
        $images = array_values($images); // Re-index array

        $product->update(['photos' => json_encode($images)]);

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully'
        ]);
    }

    /**
     * Get product categories
     */
    public function categories()
    {
        $categories = Inventory::where('status', 'available')
            ->where('quantity', '>', 0)
            ->select('category')
            ->selectRaw('COUNT(*) as product_count')
            ->groupBy('category')
            ->orderBy('product_count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->category,
                    'product_count' => $item->product_count,
                    'slug' => strtolower(str_replace(' ', '-', $item->category))
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get featured products
     */
    public function featured()
    {
        $products = Inventory::where('status', 'available')
            ->where('quantity', '>', 0)
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->product_name,
                    'description' => $product->description ?? 'Produk organik berkualitas tinggi',
                    'category' => $product->category,
                    'price' => $product->price_per_unit,
                    'original_price' => $product->original_price ?? $product->price_per_unit,
                    'discount_percentage' => $product->discount_percentage ?? 0,
                    'rating' => $product->rating ?? 4.5,
                    'review_count' => $product->review_count ?? 0,
                    'is_featured' => true,
                    'in_stock' => $product->quantity > 0,
                    'image_url' => $this->getProductImageUrl($product),
                    'created_at' => $product->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get product image URL
     */
    private function getProductImageUrl($product)
    {
        if ($product->photos) {
            $images = json_decode($product->photos, true);
            if (!empty($images)) {
                // Find primary image
                $primaryImage = collect($images)->firstWhere('is_primary', true);
                if ($primaryImage) {
                    return Storage::url($primaryImage['path']);
                }
                // If no primary, use first image
                return Storage::url($images[0]['path']);
            }
        }
        
        // Return default placeholder
        return asset('images/placeholder-product.png');
    }

    /**
     * Get all product images
     */
    private function getProductImages($product)
    {
        if ($product->photos) {
            $images = json_decode($product->photos, true);
            return collect($images)->map(function ($image) {
                return [
                    'url' => Storage::url($image['path']),
                    'is_primary' => $image['is_primary'] ?? false,
                    'uploaded_by' => $image['uploaded_by'] ?? 'unknown',
                    'uploaded_at' => $image['uploaded_at'] ?? null
                ];
            })->toArray();
        }
        
        return [];
    }
}
