<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\CartAddRequest;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

/**
 * Cart API Controller
 * 
 * Handles shopping cart operations for API clients including adding items,
 * viewing cart contents, and removing items with proper validation.
 */
class CartApiController extends Controller
{
    /**
     * Add item to cart
     *
     * @param CartAddRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(CartAddRequest $request)
    {
        try {
            $data = $request->validated();
            $user = $request->user();

            $product = Product::find($data['product_id']);
            
            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            $price = $product->price;

            $cart = Cart::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => (int) $data['quantity'],
                'price' => $price,
                'amount' => $price * (int) $data['quantity'],
                'variant_id' => $data['variant_id'] ?? null,
                'status' => 'pending',
            ]);

            return response()->json(['message' => 'Added to cart', 'cart' => $cart], 201);
        } catch (\Exception $e) {
            Log::error('Cart add error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to add item to cart'], 500);
        }
    }

    /**
     * View current cart
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $items = Cart::with(['product', 'variant'])
                ->where('user_id', $user->id)
                ->whereNull('order_id')
                ->get();

            return response()->json(['items' => $items]);
        } catch (\Exception $e) {
            Log::error('Cart index error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve cart items'], 500);
        }
    }

    /**
     * Remove item from cart
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(Request $request, $id)
    {
        try {
            $user = $request->user();
            $item = Cart::where('id', $id)
                ->where('user_id', $user->id)
                ->first();
                
            if (!$item) {
                return response()->json(['message' => 'Item not found'], 404);
            }
            
            $item->delete();
            return response()->json(['message' => 'Item removed from cart']);
        } catch (\Exception $e) {
            Log::error('Cart remove error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to remove item from cart'], 500);
        }
    }
}