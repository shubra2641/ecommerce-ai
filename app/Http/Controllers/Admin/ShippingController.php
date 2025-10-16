<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Shipping;
use App\Models\Coupon;
use Exception;
use App\Http\Requests\Admin\ShippingStoreRequest;
use App\Http\Requests\Admin\ShippingStoreRequest as ShippingUpdateRequest;

/**
 * ShippingController handles shipping management operations
 * 
 * This controller manages shipping method creation, editing, deletion, and
 * pricing with secure validation and proper error handling.
 */
class ShippingController extends Controller
{
    /**
     * Display a listing of shipping methods
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            $shippings = Shipping::orderBy('id', 'DESC')->paginate(10);
            return view('backend.shipping.index', compact('shippings'));
            
        } catch (Exception $e) {
            \Log::error('Error loading shipping methods: ' . $e->getMessage());
            return view('backend.shipping.index', ['shippings' => collect()]);
        }
    }

    /**
     * Show the form for creating a new shipping method
     * 
     * @return View
     */
    public function create(): View
    {
        try {
            return view('backend.shipping.create');
        } catch (Exception $e) {
            \Log::error('Error loading create shipping form: ' . $e->getMessage());
            abort(404, 'Create form not found');
        }
    }

    /**
     * Store a newly created shipping method
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(ShippingStoreRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['type', 'price', 'status', 'description'];
            $data = array_intersect_key($validatedData, array_flip($allowedFields));

            // Create the shipping method
            $shipping = Shipping::create($data);

            if ($shipping) {
                request()->session()->flash('success', 'Shipping successfully created');
            } else {
                request()->session()->flash('error', 'Error, Please try again');
            }

        } catch (Exception $e) {
            \Log::error('Error storing shipping method: ' . $e->getMessage(), [
                'request_data' => $request->only(['type', 'price', 'status'])
            ]);
            request()->session()->flash('error', 'An error occurred while creating the shipping method');
        }

        return redirect()->route('shipping.index');
    }

    /**
     * Display the specified shipping method
     * 
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        try {
            $shipping = Shipping::findOrFail($id);
            return view('backend.shipping.show', compact('shipping'));
            
        } catch (Exception $e) {
            \Log::error('Error loading shipping details: ' . $e->getMessage(), [
                'shipping_id' => $id
            ]);
            abort(404, 'Shipping method not found');
        }
    }

    /**
     * Show the form for editing the specified shipping method
     * 
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        try {
            $shipping = Shipping::findOrFail($id);
            return view('backend.shipping.edit', compact('shipping'));
            
        } catch (Exception $e) {
            \Log::error('Error loading edit shipping form: ' . $e->getMessage(), [
                'shipping_id' => $id
            ]);
            abort(404, 'Shipping method not found');
        }
    }

    /**
     * Update the specified shipping method
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(ShippingUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            $shipping = Shipping::findOrFail($id);
            $validatedData = $request->validated();

            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['type', 'price', 'status', 'description'];
            $data = array_intersect_key($validatedData, array_flip($allowedFields));

            $status = $shipping->update($data);
            
            if ($status) {
                request()->session()->flash('success', 'Shipping successfully updated');
            } else {
                request()->session()->flash('error', 'Error, Please try again');
            }
            
        } catch (Exception $e) {
            \Log::error('Error updating shipping method: ' . $e->getMessage(), [
                'shipping_id' => $id,
                'request_data' => $request->only(['type', 'price', 'status'])
            ]);
            request()->session()->flash('error', 'An error occurred while updating the shipping method');
        }

        return redirect()->route('shipping.index');
    }

    /**
     * Remove the specified shipping method from storage
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $shipping = Shipping::findOrFail($id);
            
            $status = $shipping->delete();
            
            if ($status) {
                request()->session()->flash('success', 'Shipping successfully deleted');
            } else {
                request()->session()->flash('error', 'Error, Please try again');
            }
            
        } catch (Exception $e) {
            \Log::error('Error deleting shipping method: ' . $e->getMessage(), [
                'shipping_id' => $id
            ]);
            request()->session()->flash('error', 'An error occurred while deleting the shipping method');
        }

        return redirect()->route('shipping.index');
    }
}
