<?php

namespace App\Models;

use App\Models\Cart;
use App\Models\Wishlist;
use App\Models\Compare;
use Illuminate\Support\Facades\Auth;

/**
 * Cart Helper Class
 * 
 * Provides helper methods for cart, wishlist, and compare functionality
 */
class CartHelper
{
    /**
     * Get cart count for user
     *
     * @param string $user_id
     * @return int
     */
    public static function getCartCount($user_id = '')
    {
        if (Auth::check()) {
            if ($user_id === '') {
                $user_id = auth()->user()->id;
            }
            return Cart::where('user_id', $user_id)->where('order_id', null)->sum('quantity');
        }
        return 0;
    }

    /**
     * Get all products from cart
     *
     * @param string $user_id
     * @return \Illuminate\Support\Collection
     */
    public static function getAllProductsFromCart($user_id = '')
    {
        if (Auth::check()) {
            if ($user_id === '') {
                $user_id = auth()->user()->id;
            }
            $carts = Cart::with('product')->where('user_id', $user_id)->where('order_id', null)->get();
            
            // Process cart items with photo arrays
            return $carts->map(function ($cart) {
                if ($cart->product && $cart->product->photo) {
                    $cart->product->photo_array = explode(',', $cart->product->photo);
                }
                return $cart;
            });
        }
        return collect();
    }

    /**
     * Get total cart price
     *
     * @param string $user_id
     * @return float
     */
    public static function getTotalCartPrice($user_id = '')
    {
        if (Auth::check()) {
            if ($user_id === '') {
                $user_id = auth()->user()->id;
            }
            return Cart::where('user_id', $user_id)->where('order_id', null)->sum('amount');
        }
        return 0;
    }

    /**
     * Get wishlist count for user
     *
     * @param string $user_id
     * @return int
     */
    public static function getWishlistCount($user_id = '')
    {
        if (Auth::check()) {
            if ($user_id === '') {
                $user_id = auth()->user()->id;
            }
            return Wishlist::where('user_id', $user_id)->where('cart_id', null)->sum('quantity');
        }
        return 0;
    }

    /**
     * Get all products from wishlist
     *
     * @param string $user_id
     * @return \Illuminate\Support\Collection
     */
    public static function getAllProductsFromWishlist($user_id = '')
    {
        if (Auth::check()) {
            if ($user_id === '') {
                $user_id = auth()->user()->id;
            }
            $wishlists = Wishlist::with('product')->where('user_id', $user_id)->where('cart_id', null)->get();
            
            // Process wishlist items with photo arrays
            return $wishlists->map(function ($wishlist) {
                if ($wishlist->product && $wishlist->product->photo) {
                    $wishlist->product->photo_array = explode(',', $wishlist->product->photo);
                }
                return $wishlist;
            });
        }
        return collect();
    }

    /**
     * Get total wishlist price
     *
     * @param string $user_id
     * @return float
     */
    public static function getTotalWishlistPrice($user_id = '')
    {
        if (Auth::check()) {
            if ($user_id === '') {
                $user_id = auth()->user()->id;
            }
            return Wishlist::where('user_id', $user_id)->where('cart_id', null)->sum('amount');
        }
        return 0;
    }

    /**
     * Get compare count for current user or session
     *
     * @return int
     */
    public static function getCompareCount()
    {
        try {
            $userId = auth()->id();
            $sessionId = session()->getId();
            
            return Compare::getCompareItems($userId, $sessionId)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get all products from compare list
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAllProductsFromCompare()
    {
        try {
            $userId = auth()->id();
            $sessionId = session()->getId();
            
            return Compare::getCompareItems($userId, $sessionId);
        } catch (\Exception $e) {
            return collect();
        }
    }
}
