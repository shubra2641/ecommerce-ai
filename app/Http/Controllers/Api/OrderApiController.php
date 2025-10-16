<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\OrderStoreRequest;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Order API Controller
 * 
 * Handles order-related API operations for frontend applications
 * including creating, viewing, and managing user orders.
 */
class OrderApiController extends Controller
{
    /**
     * Get user orders with pagination
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = max(1, min(50, (int) $request->query('per_page', 10)));
            
            $orders = Order::where('user_id', $user->id)
                ->with(['carts.product', 'carts.variant'])
                ->orderBy('id', 'desc')
                ->paginate($perPage);

            return response()->json($orders);
        } catch (\Exception $e) {
            Log::error('Order index API error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve orders'], 500);
        }
    }

    /**
     * Get specific order by ID
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            $order = Order::where('id', $id)
                ->where('user_id', $user->id)
                ->with(['carts.product', 'carts.variant'])
                ->first();

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            return response()->json($order);
        } catch (\Exception $e) {
            Log::error('Order show API error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve order'], 500);
        }
    }

    /**
     * Create new order from cart items
     *
     * @param OrderStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(OrderStoreRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $user = $request->user();
            
            return DB::transaction(function () use ($validatedData, $user, $request) {
                // Get cart items
                $carts = Cart::where('user_id', $user->id)
                    ->whereNull('order_id')
                    ->get();

                if ($carts->isEmpty()) {
                    return response()->json(['message' => 'Cart is empty'], 400);
                }

                // Calculate total
                $sub_total = $carts->sum('amount');
                $total_amount = $sub_total; // Add shipping, tax, etc. if needed

                // Create order
                $order = Order::create([
                    'order_number' => 'ORD-' . strtoupper(uniqid()),
                    'user_id' => $user->id,
                    'sub_total' => $sub_total,
                    'total_amount' => $total_amount,
                    'quantity' => $carts->sum('quantity'),
                    'payment_method' => $validatedData['payment_method'],
                    'payment_status' => $validatedData['payment_status'],
                    'condition' => 'pending',
                    'delivery_charge' => 0,
                    'first_name' => $validatedData['first_name'],
                    'last_name' => $validatedData['last_name'],
                    'email' => $validatedData['email'],
                    'phone' => $validatedData['phone'],
                    'country' => $validatedData['country'],
                    'address1' => $validatedData['address1'],
                    'address2' => $validatedData['address2'],
                    'state' => $validatedData['state'],
                    'city' => $validatedData['city'],
                    'postcode' => $request->input('postcode'),
                ]);

                // Update cart items with order_id
                Cart::where('user_id', $user->id)
                    ->whereNull('order_id')
                    ->update(['order_id' => $order->id]);

                $order->load(['carts.product', 'carts.variant']);

                return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
            });
        } catch (\Exception $e) {
            Log::error('Order store API error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create order'], 500);
        }
    }

    /**
     * Cancel pending order
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            $order = Order::where('id', $id)
                ->where('user_id', $user->id)
                ->where('condition', 'pending')
                ->first();

            if (!$order) {
                return response()->json(['message' => 'Order not found or cannot be cancelled'], 404);
            }

            $order->update(['condition' => 'cancelled']);

            return response()->json(['message' => 'Order cancelled successfully']);
        } catch (\Exception $e) {
            Log::error('Order cancel API error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to cancel order'], 500);
        }
    }
}