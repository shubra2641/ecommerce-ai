<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Coupon;
use App\Models\Cart;
use Exception;
use App\Http\Requests\Admin\CouponStoreRequest;
use App\Http\Requests\Admin\CouponStoreRequest as CouponUpdateRequest;

/**
 * CouponController handles coupon management operations
 * 
 * This controller manages coupon creation, editing, deletion, and application
 * with secure validation and proper error handling.
 */
class CouponController extends Controller
{
    /**
     * Display a listing of coupons
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            $coupons = Coupon::orderBy('id', 'DESC')->paginate(10);
            return view('backend.coupon.index', compact('coupons'));
        } catch (Exception $e) {
            \Log::error('Error fetching coupons: ' . $e->getMessage());
            request()->session()->flash('error', 'Unable to load coupons');
            return view('backend.coupon.index', ['coupons' => collect()]);
        }
    }

    /**
     * Show the form for creating a new coupon
     * 
     * @return View
     */
    public function create(): View
    {
        try {
            return view('backend.coupon.create');
        } catch (Exception $e) {
            \Log::error('Error loading create coupon form: ' . $e->getMessage());
            request()->session()->flash('error', 'Unable to load create form');
            return redirect()->route('coupon.index');
        }
    }

    /**
     * Store a newly created coupon in storage
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(CouponStoreRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['code', 'type', 'value', 'status', 'expiry_date', 'usage_limit', 'minimum_amount'];
            $data = array_intersect_key($validatedData, array_flip($allowedFields));

            $coupon = Coupon::create($data);

            if ($coupon) {
                request()->session()->flash('success', 'Coupon successfully added');
            } else {
                request()->session()->flash('error', 'Error occurred while creating coupon');
            }

        } catch (Exception $e) {
            \Log::error('Error creating coupon: ' . $e->getMessage(), [
                'request_data' => $request->only(['code', 'type', 'value', 'status']),
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'An error occurred while creating the coupon');
        }

        return redirect()->route('coupon.index');
    }

    /**
     * Display the specified coupon
     * 
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        try {
            $coupon = Coupon::findOrFail($id);
            return view('backend.coupon.show', compact('coupon'));
        } catch (Exception $e) {
            \Log::error('Error fetching coupon: ' . $e->getMessage());
            request()->session()->flash('error', 'Coupon not found');
            return redirect()->route('coupon.index');
        }
    }

    /**
     * Show the form for editing the specified coupon
     * 
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        try {
            $coupon = Coupon::findOrFail($id);
            return view('backend.coupon.edit', compact('coupon'));
        } catch (Exception $e) {
            \Log::error('Error loading edit coupon form: ' . $e->getMessage());
            request()->session()->flash('error', 'Coupon not found');
            return redirect()->route('coupon.index');
        }
    }

    /**
     * Update the specified coupon in storage
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(CouponUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $validatedData = $request->validated();

            // When updating, allow code uniqueness except current id handled in FormRequest if needed
            $allowedFields = ['code', 'type', 'value', 'status', 'expiry_date', 'usage_limit', 'minimum_amount'];
            $data = array_intersect_key($validatedData, array_flip($allowedFields));

            $status = $coupon->update($data);

            if ($status) {
                request()->session()->flash('success', 'Coupon successfully updated');
            } else {
                request()->session()->flash('error', 'Error occurred while updating coupon');
            }

        } catch (Exception $e) {
            \Log::error('Error updating coupon: ' . $e->getMessage(), [
                'coupon_id' => $id,
                'request_data' => $request->only(['code', 'type', 'value', 'status']),
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'An error occurred while updating the coupon');
        }

        return redirect()->route('coupon.index');
    }

    /**
     * Remove the specified coupon from storage
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $status = $coupon->delete();

            if ($status) {
                request()->session()->flash('success', 'Coupon successfully deleted');
            } else {
                request()->session()->flash('error', 'Error occurred while deleting coupon');
            }

        } catch (Exception $e) {
            \Log::error('Error deleting coupon: ' . $e->getMessage(), [
                'coupon_id' => $id,
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'Coupon not found or could not be deleted');
        }

        return redirect()->route('coupon.index');
    }

    /**
     * Apply a coupon to the user's cart
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function couponStore(\App\Http\Requests\Admin\CouponApplyRequest $request): RedirectResponse
    {
        try {
            // Use validated data from FormRequest
            $validatedData = $request->validated();
            $couponCode = $validatedData['code'];

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
