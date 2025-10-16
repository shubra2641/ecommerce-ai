<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Models\Coupon;
use App\Models\Cart;
use Exception;

/**
 * CouponController handles coupon application operations
 * 
 * This controller manages coupon application to user's cart
 * with secure validation and proper error handling.
 */
class CouponController extends Controller
{
    /**
     * Apply a coupon to the user's cart
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function couponStore(\App\Http\Requests\Frontend\CouponApplyRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $couponCode = trim($validated['code']);

            // Find active coupon
            $coupon = Coupon::where('code', $couponCode)
                ->where('status', 'active')
                ->first();

            if (!$coupon) {
                request()->session()->flash('error', 'Invalid or inactive coupon code');
                return back();
            }

            // Check if coupon is expired
            if ($coupon->expiry_date && $coupon->expiry_date < now()) {
                request()->session()->flash('error', 'This coupon has expired');
                return back();
            }

            // Get user's cart total
            $userId = auth()->id();
            $totalPrice = Cart::where('user_id', $userId)
                ->where('order_id', null)
                ->sum('price');

            // Check minimum amount requirement
            if ($coupon->minimum_amount && $totalPrice < $coupon->minimum_amount) {
                request()->session()->flash('error', 'Minimum order amount not met for this coupon');
                return back();
            }

            // Check usage limit
            if ($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit) {
                request()->session()->flash('error', 'This coupon has reached its usage limit');
                return back();
            }

            // Calculate discount
            $discountValue = $coupon->discount($totalPrice);

            // Store coupon in session
            session()->put('coupon', [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'value' => $discountValue,
                'type' => $coupon->type,
                'original_value' => $coupon->value
            ]);

            request()->session()->flash('success', 'Coupon successfully applied');

        } catch (Exception $e) {
            \Log::error('Error applying coupon: ' . $e->getMessage(), [
                'coupon_code' => $request->input('code'),
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'An error occurred while applying the coupon');
        }

        return redirect()->back();
    }
}
