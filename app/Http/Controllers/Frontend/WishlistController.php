<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Frontend\WishlistAddRequest;
use App\Models\Product;
use App\Models\Wishlist;
use Exception;

/**
 * WishlistController handles wishlist management operations
 * 
 * This controller manages adding products to wishlist, removing items,
 * and displaying wishlist with secure validation and proper error handling.
 */
class WishlistController extends Controller
{
    /**
     * Create a new controller instance
     * 
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Add product to wishlist
     * 
     * @param WishlistAddRequest $request
     * @return RedirectResponse
     */
    public function wishlist(WishlistAddRequest $request): RedirectResponse
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                request()->session()->flash('error', 'Please login to add items to wishlist');
                return redirect()->route('login');
            }

            $validatedData = $request->validated();
            $slug = $validatedData['slug'] ?? $request->route('slug');
            $product = Product::where('slug', $slug)->first();
            if (!$product) {
                request()->session()->flash('error', 'Product not found');
                return back();
            }

            // Check if product is already in wishlist
            $existingWishlist = Wishlist::where('user_id', Auth::id())
                ->where('cart_id', null)
                ->where('product_id', $product->id)
                ->first();

            if ($existingWishlist) {
                request()->session()->flash('error', 'Product is already in your wishlist');
                return back();
            }

            // Check product stock
            if ($product->stock <= 0) {
                request()->session()->flash('error', 'Product is out of stock');
                return back();
            }

            // Calculate discounted price
            $discountedPrice = $this->calculateDiscountedPrice($product->price, $product->discount);

            // Create wishlist item
            $wishlist = Wishlist::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'price' => $discountedPrice,
                'quantity' => 1,
                'amount' => $discountedPrice * 1
            ]);

            if ($wishlist) {
                request()->session()->flash('success', 'Product successfully added to wishlist');
            } else {
                request()->session()->flash('error', 'Failed to add product to wishlist');
            }

        } catch (Exception $e) {
            \Log::error('Error adding product to wishlist: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'route_slug' => $request->route('slug'),
                'input_slug' => $request->input('slug')
            ]);
            request()->session()->flash('error', 'An error occurred while adding product to wishlist');
        }

        return back();
    }

    /**
     * Calculate discounted price
     * 
     * @param float $price
     * @param float|null $discount
     * @return float
     */
    private function calculateDiscountedPrice(float $price, ?float $discount): float
    {
        if ($discount && $discount > 0) {
            return $price - (($price * $discount) / 100);
        }
        return $price;
    }  
    
    /**
     * Remove item from wishlist
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function wishlistDelete(\App\Http\Requests\Frontend\WishlistDeleteRequest $request): RedirectResponse
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                request()->session()->flash('error', 'Please login to manage your wishlist');
                return redirect()->route('login');
            }

            $validatedData = $request->validated();
            $wishlist = Wishlist::where('id', $validatedData['id'])
                ->where('user_id', Auth::id())
                ->first();

            if (!$wishlist) {
                request()->session()->flash('error', 'Wishlist item not found');
                return back();
            }

            $status = $wishlist->delete();
            
            if ($status) {
                request()->session()->flash('success', 'Item successfully removed from wishlist');
            } else {
                request()->session()->flash('error', 'Error occurred while removing item');
            }

        } catch (Exception $e) {
            \Log::error('Error removing item from wishlist: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->only(['id'])
            ]);
            request()->session()->flash('error', 'An error occurred while removing item from wishlist');
        }

        return back();
    }

    /**
     * Display user's wishlist
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                return view('frontend.pages.wishlist', ['wishlists' => collect()]);
            }

            $wishlists = $this->getUserWishlist();
            
            // Process wishlist items with photo arrays
            $wishlists = $wishlists->map(function($wishlist) {
                if($wishlist->product && $wishlist->product->photo) {
                    $wishlist->product->photo_array = explode(',', $wishlist->product->photo);
                }
                return $wishlist;
            });
            
            return view('frontend.pages.wishlist', compact('wishlists'));
            
        } catch (Exception $e) {
            \Log::error('Error loading wishlist: ' . $e->getMessage(), [
                'user_id' => Auth::id()
            ]);
            return view('frontend.pages.wishlist', ['wishlists' => collect()]);
        }
    }

    /**
     * Get user's wishlist items
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getUserWishlist()
    {
        return Wishlist::with('product')
            ->where('user_id', Auth::id())
            ->where('cart_id', null)
            ->orderBy('created_at', 'desc')
            ->get();
    }     
}
