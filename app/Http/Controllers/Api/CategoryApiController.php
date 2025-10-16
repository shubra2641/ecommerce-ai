<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

/**
 * Category API Controller
 * 
 * Handles category-related API operations for frontend applications
 * including retrieving categories, category details, and category products.
 */
class CategoryApiController extends Controller
{
    /**
     * Get all active parent categories
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $categories = Category::where('status', 'active')
                ->where('is_parent', 1)
                ->with('child_cat')
                ->orderBy('title', 'ASC')
                ->get();

            $categories->transform(function ($category) {
                $category->photo_url = $category->photo ? asset('storage/' . $category->photo) : null;
                return $category;
            });

            return response()->json(['categories' => $categories]);
        } catch (\Exception $e) {
            Log::error('Category index API error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve categories'], 500);
        }
    }

    /**
     * Get specific category by slug
     *
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($slug)
    {
        try {
            $category = Category::where('slug', $slug)
                ->where('status', 'active')
                ->with(['products', 'child_cat'])
                ->first();

            if (!$category) {
                return response()->json(['message' => 'Category not found'], 404);
            }

            $category->photo_url = $category->photo ? asset('storage/' . $category->photo) : null;
            
            // Transform products
            $category->products->transform(function ($product) {
                $product->photo_array = $product->photo !== '' ? explode(',', $product->photo) : [];
                $product->after_discount = ($product->price - (($product->price * ($product->discount ?? 0)) / 100));
                return $product;
            });

            return response()->json($category);
        } catch (\Exception $e) {
            Log::error('Category show API error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve category'], 500);
        }
    }

    /**
     * Get products for specific category with pagination
     *
     * @param Request $request
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function products(Request $request, $slug)
    {
        try {
            $perPage = max(1, min(50, (int) $request->query('per_page', 12)));
            
            $category = Category::where('slug', $slug)
                ->where('status', 'active')
                ->first();

            if (!$category) {
                return response()->json(['message' => 'Category not found'], 404);
            }

            $products = $category->products()
                ->where('status', 'active')
                ->orderBy('id', 'desc')
                ->paginate($perPage);

            $products->getCollection()->transform(function ($product) {
                $product->photo_array = $product->photo !== '' ? explode(',', $product->photo) : [];
                $product->after_discount = ($product->price - (($product->price * ($product->discount ?? 0)) / 100));
                return $product;
            });

            return response()->json($products);
        } catch (\Exception $e) {
            Log::error('Category products API error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve category products'], 500);
        }
    }
}
