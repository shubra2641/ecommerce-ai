<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

/**
 * Product API Controller
 * 
 * Handles product-related API operations for frontend applications
 * including retrieving products with pagination and product details.
 */
class ProductApiController extends Controller
{
    /**
     * Get all active products with pagination
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $perPage = max(1, min(50, (int) $request->query('per_page', 12)));
            $products = Product::where('status', 'active')
                ->orderBy('id', 'desc')
                ->paginate($perPage);

            $products->getCollection()->transform(function ($product) {
                $product->photo_array = $product->photo !== '' ? explode(',', $product->photo) : [];
                $product->after_discount = ($product->price - (($product->price * ($product->discount ?? 0)) / 100));
                return $product;
            });

            return response()->json($products);
        } catch (\Exception $e) {
            Log::error('Product index API error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve products'], 500);
        }
    }

    /**
     * Get specific product by slug
     *
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($slug)
    {
        try {
            $product = Product::getProductBySlug($slug);
            
            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }
            
            $product->photo_array = $product->photo !== '' ? explode(',', $product->photo) : [];
            $product->after_discount = ($product->price - (($product->price * ($product->discount ?? 0)) / 100));
            
            return response()->json($product);
        } catch (\Exception $e) {
            Log::error('Product show API error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve product'], 500);
        }
    }
}