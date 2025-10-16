<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Frontend\OrderStoreRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Shipping;
use App\Models\User;
use App\Models\Product;
use App\Models\PaymentGateway;
use App\Notifications\StatusNotification;
use Helper;
use PDF;
use Carbon\Carbon;
use Exception;

/**
 * OrderController handles order management operations
 * 
 * This controller manages order creation, viewing, updating, deletion,
 * tracking, PDF generation, and income analytics with secure validation
 * and proper error handling.
 */
class OrderController extends Controller
{
    /**
     * Display a listing of orders
     *
     * @return View
     */
    public function index(): View
    {
        try {
            $orders = Order::orderBy('id', 'DESC')->paginate(10);
            $shipping_charges = Shipping::select('id', 'price')->get();
        
        // Pre-calculate shipping charges for each order
        $orders_with_shipping = $orders->map(function($order) use ($shipping_charges) {
            $order->shipping_charge = $shipping_charges->where('id', $order->shipping_id)->first();
            return $order;
        });
        
        return view('backend.order.index', compact('orders', 'shipping_charges', 'orders_with_shipping'));
            
        } catch (Exception $e) {
            \Log::error('Error loading orders: ' . $e->getMessage());
            return view('backend.order.index', [
                'orders' => collect(),
                'shipping_charges' => collect(),
                'orders_with_shipping' => collect()
            ]);
        }
    }

    /**
     * Show the form for creating a new order
     * 
     * @return View
     */
    public function create(): View
    {
        try {
            return view('backend.order.create');
        } catch (Exception $e) {
            \Log::error('Error loading create order form: ' . $e->getMessage());
            abort(404, 'Create form not found');
        }
    }

    /**
     * Store a newly created order
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(OrderStoreRequest $request): RedirectResponse
    {
        try {
            // Get base validated data from FormRequest
            $validatedData = $request->validated();

            // Perform additional dynamic validation (e.g., payment gateway requirements)
            $gatewaySpecific = $this->validateOrderRequest($request);
            // merge gateway-specific validated fields if any
            $validatedData = array_merge($validatedData, is_array($gatewaySpecific) ? $gatewaySpecific : []);

            // Check if cart is not empty
            if (!$this->hasValidCart()) {
                request()->session()->flash('error', 'Cart is Empty!');
                return redirect()->back();
            }

            // Create order data
            $orderData = $this->prepareOrderData($request, $validatedData);

            // Create the order
            $order = Order::create($orderData);

            if ($order) {
                // Send notification to admin
                $this->sendOrderNotification($order);

                // Handle payment method specific logic
                if ($validatedData['payment_method'] === 'paypal') {
                    return redirect()->route('payment')->with(['id' => $order->id]);
                } else {
                    // Clear cart and coupon sessions
                    session()->forget(['cart', 'coupon']);
                    
                    // Update cart items with order ID
                    Cart::where('user_id', auth()->id())
                        ->where('order_id', null)
                        ->update(['order_id' => $order->id]);
                }

                request()->session()->flash('success', 'Your product successfully placed in order');
                return redirect()->route('home');
            } else {
                request()->session()->flash('error', 'Failed to create order');
                return redirect()->back();
            }

        } catch (Exception $e) {
            \Log::error('Error storing order: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->only(['first_name', 'last_name', 'email', 'payment_method'])
            ]);
            request()->session()->flash('error', 'An error occurred while creating the order');
            return redirect()->back();
        }
    }

    /**
     * Validate order request data
     * 
     * @param Request $request
     * @return array
     */
    private function validateOrderRequest(Request $request): array
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address1' => 'required|string|max:500',
            'address2' => 'nullable|string|max:500',
            'coupon' => 'nullable|numeric',
            'phone' => 'required|string|min:10|max:20',
            'post_code' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'shipping' => 'nullable|integer|exists:shippings,id',
            'payment_method' => 'required|string|in:paypal,cod'
        ];

        // Add payment gateway validation if needed
        $selectedMethod = $request->validated()['payment_method'] ?? null;
        if ($selectedMethod && !in_array($selectedMethod, ['paypal', 'cod'])) {
            $gateway = PaymentGateway::where('slug', $selectedMethod)->first();
            if ($gateway) {
                $rules['payment_method'] = 'required|string|exists:payment_gateways,slug';
                if ($gateway->require_proof) {
            $rules['payment_proof'] = 'required|image|max:2048';
        } else {
            $rules['payment_proof'] = 'nullable|image|max:2048';
        }
            }
        }

    return \Illuminate\Support\Facades\Validator::make($request->all(), $rules)->validate();
    }

    /**
     * Check if user has valid cart items
     * 
     * @return bool
     */
    private function hasValidCart(): bool
    {
        return Cart::where('user_id', auth()->id())
            ->where('order_id', null)
            ->exists();
    }

    /**
     * Prepare order data for creation
     * 
     * @param Request $request
     * @param array $validatedData
     * @return array
     */
    private function prepareOrderData(Request $request, array $validatedData): array
    {
        $orderData = $validatedData;

        $orderData['order_number'] = 'ORD-' . strtoupper(Str::random(10));
        $orderData['user_id'] = auth()->id();
        $orderData['shipping_id'] = $validatedData['shipping'] ?? null;
        $orderData['sub_total'] = Helper::totalCartPrice();
        $orderData['quantity'] = Helper::cartCount();
        $orderData['status'] = 'new';

        // Handle coupon
        if (session('coupon')) {
            $orderData['coupon'] = session('coupon')['value'];
        }

        // Calculate total amount
        $orderData['total_amount'] = $this->calculateTotalAmount($validatedData);

        // Handle payment method
        $this->setPaymentDetails($orderData, $validatedData);

        return $orderData;
    }

    /**
     * Calculate total amount including shipping and coupon
     * 
     * @param Request $request
     * @return float
     */
    private function calculateTotalAmount(array $validatedData): float
    {
        $subTotal = Helper::totalCartPrice();
        $shippingCost = 0;
        $couponDiscount = 0;

        // Add shipping cost
        if (!empty($validatedData['shipping'])) {
            $shipping = Shipping::find($validatedData['shipping']);
            if ($shipping) {
                $shippingCost = $shipping->price;
            }
        }

        // Subtract coupon discount
        if (session('coupon')) {
            $couponDiscount = session('coupon')['value'];
        }

        return $subTotal + $shippingCost - $couponDiscount;
    }

    /**
     * Set payment method and status details
     * 
     * @param array $orderData
     * @param Request $request
     * @return void
     */
    private function setPaymentDetails(array &$orderData, array $validatedData): void
    {
        $paymentMethod = $validatedData['payment_method'];

        if ($paymentMethod === 'paypal') {
            $orderData['payment_method'] = 'paypal';
            $orderData['payment_status'] = 'paid';
        } elseif ($paymentMethod === 'cod') {
            $orderData['payment_method'] = 'cod';
            $orderData['payment_status'] = 'Unpaid';
        } else {
            // Handle custom payment gateway
            $gateway = PaymentGateway::where('slug', $paymentMethod)->first();
            if ($gateway) {
                $orderData['payment_method'] = $gateway->slug;
                $orderData['payment_status'] = 'Unpaid';

                if ($gateway->transfer_details) {
                    $orderData['payment_details'] = $gateway->transfer_details;
                }

                if ($gateway->require_proof && $request->hasFile('payment_proof')) {
                    $file = $request->file('payment_proof');
                    $path = $file->store('payment_proofs', 'public');
                    $orderData['payment_proof'] = $path;
                }
            }
        }
    }

    /**
     * Send order notification to admin
     * 
     * @param Order $order
     * @return void
     */
    private function sendOrderNotification(Order $order): void
    {
        try {
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                $details = [
                    'title' => 'New order created',
                    'actionURL' => route('order.show', $order->id),
                    'fas' => 'fa-file-alt'
                ];
                Notification::send($admin, new StatusNotification($details));
            }
        } catch (Exception $e) {
            \Log::error('Error sending order notification: ' . $e->getMessage(), [
                'order_id' => $order->id
            ]);
        }
    }

    /**
     * Display the specified order
     * 
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        try {
            $order = Order::findOrFail($id);
            
            // Compute payment method label for display
            $pmLabel = $this->getPaymentMethodLabel($order->payment_method);

            return view('backend.order.show', compact('order', 'pmLabel'));
            
        } catch (Exception $e) {
            \Log::error('Error loading order details: ' . $e->getMessage(), [
                'order_id' => $id
            ]);
            abort(404, 'Order not found');
        }
    }

    /**
     * Get payment method display label
     * 
     * @param string|null $paymentMethod
     * @return string
     */
    private function getPaymentMethodLabel(?string $paymentMethod): string
    {
        if (!$paymentMethod) {
            return 'Unknown';
        }

        if ($paymentMethod === 'cod') {
            return 'Cash on Delivery';
        }

        if ($paymentMethod === 'paypal') {
            return 'PayPal';
        }

        // Try to get gateway name
        $gateway = PaymentGateway::where('slug', $paymentMethod)->first();
        if ($gateway) {
            return $gateway->name;
        }

        // Fallback to formatted string
        return ucfirst(str_replace(['_', '-'], ' ', $paymentMethod));
    }

    /**
     * Show the form for editing the specified order
     * 
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        try {
            $order = Order::findOrFail($id);
            return view('backend.order.edit', compact('order'));
            
        } catch (Exception $e) {
            \Log::error('Error loading edit order form: ' . $e->getMessage(), [
                'order_id' => $id
            ]);
            abort(404, 'Order not found');
        }
    }

    /**
     * Update the specified order
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(\App\Http\Requests\Admin\OrderUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            $order = Order::findOrFail($id);
            
            // Use validated data from FormRequest
            $validatedData = $request->validated();

            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['status'];
            $data = array_intersect_key($validatedData, array_flip($allowedFields));

            // Handle stock reduction when order is delivered
            if ($request->status === 'delivered' && $order->status !== 'delivered') {
                $this->reduceProductStock($order);
            }

            $status = $order->update($data);
            
            if ($status) {
                request()->session()->flash('success', 'Successfully updated order');
            } else {
                request()->session()->flash('error', 'Error while updating order');
            }
            
        } catch (Exception $e) {
            \Log::error('Error updating order: ' . $e->getMessage(), [
                'order_id' => $id,
                'request_data' => $request->only(['status'])
            ]);
            request()->session()->flash('error', 'An error occurred while updating the order');
        }

        return redirect()->route('order.index');
    }

    /**
     * Reduce product stock when order is delivered
     * 
     * @param Order $order
     * @return void
     */
    private function reduceProductStock(Order $order): void
    {
        try {
            foreach ($order->cart as $cart) {
                $product = $cart->product;
                if ($product && $product->stock >= $cart->quantity) {
                    $product->stock -= $cart->quantity;
                    $product->save();
                } else {
                    \Log::warning('Insufficient stock for product', [
                        'product_id' => $product->id ?? null,
                        'required_quantity' => $cart->quantity,
                        'available_stock' => $product->stock ?? 0
                    ]);
                }
            }
        } catch (Exception $e) {
            \Log::error('Error reducing product stock: ' . $e->getMessage(), [
                'order_id' => $order->id
            ]);
        }
    }

    /**
     * Remove the specified order from storage
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $order = Order::findOrFail($id);
            
            $status = $order->delete();
            
            if ($status) {
                request()->session()->flash('success', 'Order successfully deleted');
            } else {
                request()->session()->flash('error', 'Order could not be deleted');
            }
            
            return redirect()->route('order.index');
            
        } catch (Exception $e) {
            \Log::error('Error deleting order: ' . $e->getMessage(), [
                'order_id' => $id
            ]);
            request()->session()->flash('error', 'Order not found or could not be deleted');
            return redirect()->back();
        }
    }

    /**
     * Display order tracking form
     * 
     * @return View
     */
    public function orderTrack(): View
    {
        try {
        return view('frontend.pages.order-track');
        } catch (Exception $e) {
            \Log::error('Error loading order track form: ' . $e->getMessage());
            abort(404, 'Order track page not found');
        }
    }

    /**
     * Track order by order number
     * 
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function productTrackOrder(\App\Http\Requests\Admin\OrderTrackRequest $request)
    {
        try {
            // Use validated data from FormRequest
            $validatedData = $request->validated();

            // Check if user is authenticated
            if (!auth()->check()) {
                request()->session()->flash('error', 'Please login to track your order');
                return redirect()->route('login');
            }

            $order = Order::where('user_id', auth()->id())
                ->where('order_number', $validatedData['order_number'])
                    ->first();

            if (!$order) {
                request()->session()->flash('error', 'Invalid order number please try again');
                return redirect()->back();
        }

        // Define canonical statuses in order
            $statuses = [
                'new' => 'New', 
                'process' => 'Processing', 
                'delivered' => 'Delivered', 
                'cancel' => 'Canceled'
            ];

        // Compute which statuses have been reached
            $reached = $this->calculateOrderProgress($order->status, $statuses);

            return view('frontend.pages.order-track', compact('order', 'statuses', 'reached'));
            
        } catch (Exception $e) {
            \Log::error('Error tracking order: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'order_number' => $request->input('order_number')
            ]);
            request()->session()->flash('error', 'An error occurred while tracking the order');
            return redirect()->back();
        }
    }

    /**
     * Calculate order progress statuses
     * 
     * @param string $currentStatus
     * @param array $statuses
     * @return array
     */
    private function calculateOrderProgress(string $currentStatus, array $statuses): array
    {
        $reached = [];
        $statusKeys = array_keys($statuses);
        
        foreach ($statusKeys as $status) {
            $reached[$status] = false;
            if ($status === $currentStatus) {
                $reached[$status] = true;
                break;
            }
            $reached[$status] = true;
        }

        return $reached;
    }

    /**
     * Generate PDF for order
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function pdf(\App\Http\Requests\Admin\OrderPdfRequest $request)
    {
        try {
            // Use validated data from FormRequest
            $validatedData = $request->validated();

            $order = Order::getAllOrder($validatedData['id']);
            
            if (!$order) {
                abort(404, 'Order not found');
            }

            $fileName = $order->order_number . '-' . $order->first_name . '.pdf';
            
            // Pre-calculate product info for each cart item using Eloquent
        $order->cart_info = $order->cart_info->map(function($cart) {
                $cart->product = Product::select('title')->where('id', $cart->product_id)->get();
            return $cart;
        });
        
            // Pre-calculate shipping charge using Eloquent
            $shippingCharge = Shipping::where('id', $order->shipping_id)->pluck('price');
            
            $pdf = PDF::loadView('backend.order.pdf', compact('order', 'shippingCharge'));
            return $pdf->download($fileName);
            
        } catch (Exception $e) {
            \Log::error('Error generating order PDF: ' . $e->getMessage(), [
                'order_id' => $request->input('id')
            ]);
            abort(500, 'Error generating PDF');
        }
    }
    /**
     * Generate income chart data
     * 
     * @param Request $request
     * @return array
     */
    public function incomeChart(Request $request): array
    {
        try {
            $year = Carbon::now()->year;
            
            $items = Order::with(['cart_info'])
                ->whereYear('created_at', $year)
                ->where('status', 'delivered')
                ->get()
                ->groupBy(function($order) {
                    return Carbon::parse($order->created_at)->format('m');
                });

            $result = [];
            foreach ($items as $month => $itemCollections) {
                foreach ($itemCollections as $item) {
                    $amount = $item->cart_info->sum('amount');
                    $monthNumber = intval($month);
                    $result[$monthNumber] = ($result[$monthNumber] ?? 0) + $amount;
                }
            }

            $data = [];
            for ($i = 1; $i <= 12; $i++) {
                $monthName = date('F', mktime(0, 0, 0, $i, 1));
                $data[$monthName] = isset($result[$i]) 
                    ? number_format((float)$result[$i], 2, '.', '') 
                    : '0.00';
            }

            return $data;
            
        } catch (Exception $e) {
            \Log::error('Error generating income chart: ' . $e->getMessage());
            return array_fill_keys([
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ], '0.00');
        }
    }
}
