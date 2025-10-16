<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Frontend\AddToCartRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\Cart;
use App\Models\ProductVariant;
use App\Models\PaymentGateway;
use Illuminate\Support\Str;
use Helper;
use Exception;

/**
 * CartController handles shopping cart operations
 * 
 * This controller manages cart functionality including adding products,
 * updating quantities, removing items, and checkout process with
 * secure data handling and stock validation.
 */
class CartController extends Controller
{
    /**
     * Product model instance
     * 
     * @var Product
     */
    protected $product = null;

    /**
     * Constructor to inject Product model
     * 
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Display the shopping cart page
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            // Get active shipping options using Eloquent
            $shipping = DB::table('shippings')
                ->where('status', 'active')
                ->limit(1)
                ->get();
            
            // Pre-calculate total amount
            $total_amount = Helper::totalCartPrice();
            if (session()->has('coupon')) {
                $total_amount = $total_amount - session('coupon')['value'];
            }
            
            // Process cart items with photo arrays
            $cart_items = Helper::getAllProductFromCart();
            $cart_items = $cart_items->map(function($cart) {
                if ($cart->product && $cart->product->photo) {
                    $cart->product->photo_array = explode(',', $cart->product->photo);
                }
                return $cart;
            });
            
            return view('frontend.pages.cart', compact('shipping', 'total_amount', 'cart_items'));
        } catch (Exception $e) {
            \Log::error('Error loading cart page: ' . $e->getMessage());
            request()->session()->flash('error', 'Unable to load cart');
            return view('frontend.pages.cart', [
                'shipping' => collect(),
                'total_amount' => 0,
                'cart_items' => collect()
            ]);
        }
    }

    /**
     * Add a product to the cart
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function addToCart(AddToCartRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            $product = Product::where('slug', $validatedData['slug'])->first();
            if (!$product) {
                request()->session()->flash('error', 'Product not found');
                return back();
            }

            $variant = null;
            if ($request->has('variant_id') && $request->variant_id) {
                $variant = ProductVariant::find($request->variant_id);
                if (!$variant) {
                    request()->session()->flash('error', 'Product variant not found');
                    return back();
                }
            }

            $already_cart = Cart::where('user_id', auth()->user()->id)
                ->where('order_id', null)
                ->where('product_id', $product->id)
                ->where('variant_id', $variant->id ?? null)
                ->first();

            if ($already_cart) {
                $already_cart->quantity = $already_cart->quantity + 1;
                $price = $variant ? ($variant->price ?? $product->price) : $product->price;
                $already_cart->amount = $price + $already_cart->amount;
                
                $available = $variant ? $variant->stock : $already_cart->product->stock;
                if ($available < $already_cart->quantity || $available <= 0) {
                    request()->session()->flash('error', 'Stock not sufficient!');
                    return back();
                }
                $already_cart->save();
            } else {
                $cart = new Cart();
                $cart->user_id = auth()->user()->id;
                $cart->product_id = $product->id;
                $price = $variant ? ($variant->price ?? $product->price) : $product->price;
                $cart->variant_id = $variant->id ?? null;
                $cart->price = $price;
                $cart->quantity = 1;
                $cart->amount = $cart->price * $cart->quantity;
                
                $available = $variant ? $variant->stock : $cart->product->stock;
                if ($available < $cart->quantity || $available <= 0) {
                    request()->session()->flash('error', 'Stock not sufficient!');
                    return back();
                }
                $cart->save();
                
                // Update wishlist if exists
                Wishlist::where('user_id', auth()->user()->id)
                    ->where('cart_id', null)
                    ->update(['cart_id' => $cart->id]);
            }
            
            request()->session()->flash('success', 'Product successfully added to cart');
            
        } catch (Exception $e) {
            \Log::error('Error adding product to cart: ' . $e->getMessage(), [
                'request_data' => $request->only(['slug', 'variant_id']),
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'An error occurred while adding product to cart');
        }
        
        return back();
    }  

    /**
     * Add a single product to cart with specific quantity
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function singleAddToCart(\App\Http\Requests\Frontend\SingleAddToCartRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            $product = Product::where('slug', $request->slug)->first();
            if (!$product) {
                request()->session()->flash('error', 'Product not found');
                return back();
            }

            $variant = null;
            if ($request->has('variant_id') && $request->variant_id) {
                $variant = ProductVariant::find($request->variant_id);
                if (!$variant) {
                    request()->session()->flash('error', 'Product variant not found');
                    return back();
                }
            }

            $quantity = $validated['quant'][1] ?? 1;
            $available = $variant ? $variant->stock : $product->stock;
            
            if ($available < $quantity) {
                request()->session()->flash('error', 'Out of stock, You can add other products.');
                return back();
            }

            if ($quantity < 1) {
                request()->session()->flash('error', 'Invalid quantity');
                return back();
            }

            $already_cart = Cart::where('user_id', auth()->user()->id)
                ->where('order_id', null)
                ->where('product_id', $product->id)
                ->where('variant_id', $variant->id ?? null)
                ->first();

            $price = $variant ? ($variant->price ?? $product->price) : $product->price;
            
            if ($already_cart) {
                $already_cart->quantity = $already_cart->quantity + $quantity;
                $already_cart->amount = ($price * $quantity) + $already_cart->amount;

                $available = $variant ? $variant->stock : $already_cart->product->stock;
                if ($available < $already_cart->quantity || $available <= 0) {
                    request()->session()->flash('error', 'Stock not sufficient!');
                    return back();
                }

                $already_cart->save();
            } else {
                $cart = new Cart();
                $cart->user_id = auth()->user()->id;
                $cart->product_id = $product->id;
                $cart->variant_id = $variant->id ?? null;
                $cart->price = $price;
                $cart->quantity = $quantity;
                $cart->amount = $price * $quantity;
                
                $available = $variant ? $variant->stock : $cart->product->stock;
                if ($available < $cart->quantity || $available <= 0) {
                    request()->session()->flash('error', 'Stock not sufficient!');
                    return back();
                }
                $cart->save();
            }
            
            request()->session()->flash('success', 'Product successfully added to cart.');
            
        } catch (Exception $e) {
            \Log::error('Error adding single product to cart: ' . $e->getMessage(), [
                'request_data' => $request->only(['slug', 'quant', 'variant_id']),
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'An error occurred while adding product to cart');
        }
        
        return back();
    } 
    
    /**
     * Delete an item from the cart
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function cartDelete(\App\Http\Requests\Frontend\CartDeleteRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $cart = Cart::findOrFail($validated['id']);
            
            // Verify ownership
            if ($cart->user_id !== auth()->user()->id) {
                request()->session()->flash('error', 'Unauthorized access');
                return back();
            }
            
            $cart->delete();
            request()->session()->flash('success', 'Cart item successfully removed');
            
        } catch (Exception $e) {
            \Log::error('Error deleting cart item: ' . $e->getMessage(), [
                'cart_id' => $request->id,
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'An error occurred while removing the item');
        }
        
        return back();
    }     

    /**
     * Update cart quantities
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function cartUpdate(\App\Http\Requests\Frontend\CartUpdateRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            if ($validated['quant']) {
                $errors = [];
                $success = '';
                
                foreach ($validated['quant'] as $k => $quant) {
                    $id = $validated['qty_id'][$k];
                    $cart = Cart::findOrFail($id);
                    
                    // Verify ownership
                    if ($cart->user_id !== auth()->user()->id) {
                        $errors[] = 'Unauthorized access to cart item';
                        continue;
                    }
                    
                    if ($quant > 0 && $cart) {
                        if ($cart->product->stock < $quant) {
                            request()->session()->flash('error', 'Out of stock');
                            return back();
                        }
                        
                        $cart->quantity = ($cart->product->stock > $quant) ? $quant : $cart->product->stock;
                        
                        if ($cart->product->stock <= 0) {
                            continue;
                        }
                        
                        $after_price = $cart->product->price - ($cart->product->price * $cart->product->discount) / 100;
                        $cart->amount = $after_price * $quant;
                        $cart->save();
                        $success = 'Cart successfully updated!';
                    } else {
                        $errors[] = 'Invalid cart item!';
                    }
                }
                
                if (!empty($errors)) {
                    request()->session()->flash('error', implode(', ', $errors));
                }
                
                if ($success) {
                    request()->session()->flash('success', $success);
                }
            } else {
                request()->session()->flash('error', 'Invalid cart data!');
            }
            
        } catch (Exception $e) {
            \Log::error('Error updating cart: ' . $e->getMessage(), [
                'request_data' => $request->only(['quant', 'qty_id']),
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'An error occurred while updating the cart');
        }
        
        return back();
    }

    /**
     * Display checkout page
     * 
     * @param Request $request
     * @return View
     */
    public function checkout(Request $request): View
    {
        try {
            // Pre-calculate total amount
            $total_amount = Helper::totalCartPrice();
            if (session('coupon')) {
                $total_amount = $total_amount - session('coupon')['value'];
            }

            // Get payment gateways using the service
            // Get available payment gateways
            $online_gateways = PaymentGateway::where('enabled', true)
                ->whereIn('slug', ['paypal', 'stripe', 'tap'])
                ->orderBy('sort_order')
                ->get();
            
            $offline_gateways = PaymentGateway::where('enabled', true)
                ->whereIn('slug', ['cod', 'offline'])
                ->orderBy('sort_order')
                ->get();

            $has_gateways = $online_gateways->count() || $offline_gateways->count();

            return view('frontend.pages.checkout', compact(
                'total_amount', 
                'online_gateways', 
                'offline_gateways', 
                'has_gateways'
            ));
            
        } catch (Exception $e) {
            \Log::error('Error loading checkout page: ' . $e->getMessage());
            request()->session()->flash('error', 'Unable to load checkout page');
            return view('frontend.pages.checkout', [
                'total_amount' => 0,
                'online_gateways' => collect(),
                'offline_gateways' => collect(),
                'has_gateways' => false
            ]);
        }
    }
}
