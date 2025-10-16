<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\User\ProductReviewStoreRequest;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductReview;
use App\Notifications\StatusNotification;
use Exception;

/**
 * ProductReviewController handles product review management operations
 * 
 * This controller manages product review creation, editing, deletion, and
 * notification handling with secure validation and proper error handling.
 */
class ProductReviewController extends Controller
{
    /**
     * Display a listing of product reviews
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            $reviews = ProductReview::with(['user', 'user_info', 'product'])->orderBy('id', 'DESC')->paginate(10);
            return view('backend.review.index', compact('reviews'));
            
        } catch (Exception $e) {
            \Log::error('Error loading product reviews: ' . $e->getMessage());
            return view('backend.review.index', ['reviews' => collect()]);
        }
    }

    /**
     * Show the form for creating a new product review
     * 
     * @return View
     */
    public function create(): View
    {
        try {
            return view('backend.review.create');
        } catch (Exception $e) {
            \Log::error('Error loading create review form: ' . $e->getMessage());
            abort(404, 'Create form not found');
        }
    }

    /**
     * Store a newly created product review
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(ProductReviewStoreRequest $request): RedirectResponse
    {
        try {
            // Check if user is authenticated
            if (!auth()->check()) {
                request()->session()->flash('error', 'Please login to submit a review');
                return redirect()->route('login');
            }

            $validatedData = $request->validated();

            // Get product information
            $productInfo = Product::getProductBySlug($validatedData['slug']);
            if (!$productInfo) {
                request()->session()->flash('error', 'Product not found');
                return redirect()->back();
            }

            // Check if user already reviewed this product
            $existingReview = ProductReview::where('user_id', auth()->id())
                ->where('product_id', $productInfo->id)
                ->first();

            if ($existingReview) {
                request()->session()->flash('error', 'You have already reviewed this product');
                return redirect()->back();
            }

            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['rate', 'review', 'name', 'email'];
            $data = $request->only($allowedFields);
            $data['product_id'] = $productInfo->id;
            $data['user_id'] = auth()->id();
            $data['status'] = 'active';

            // Create the review
            $review = ProductReview::create($data);

            if ($review) {
                // Send notification to admins
                $this->sendReviewNotification($productInfo);
                
                request()->session()->flash('success', 'Thank you for your feedback');
            } else {
                request()->session()->flash('error', 'Something went wrong! Please try again!');
            }

        } catch (Exception $e) {
            \Log::error('Error storing product review: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->only(['slug', 'rate'])
            ]);
            request()->session()->flash('error', 'An error occurred while submitting your review');
        }

        return redirect()->back();
    }

    /**
     * Send notification to admins about new review
     * 
     * @param Product $product
     * @return void
     */
    private function sendReviewNotification(Product $product): void
    {
        try {
            $admins = User::where('role', 'admin')->get();
            
            if ($admins->isNotEmpty()) {
                $details = [
                    'title' => 'New Product Rating!',
                    'actionURL' => route('product-detail', $product->slug),
                    'fas' => 'fa-star'
                ];
                
                Notification::send($admins, new StatusNotification($details));
            }
        } catch (Exception $e) {
            \Log::error('Error sending review notification: ' . $e->getMessage(), [
                'product_id' => $product->id
            ]);
        }
    }

    /**
     * Display the specified product review
     * 
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        try {
            $review = ProductReview::findOrFail($id);
            return view('backend.review.show', compact('review'));
            
        } catch (Exception $e) {
            \Log::error('Error loading review details: ' . $e->getMessage(), [
                'review_id' => $id
            ]);
            abort(404, 'Review not found');
        }
    }

    /**
     * Show the form for editing the specified product review
     * 
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        try {
            $review = ProductReview::findOrFail($id);
            return view('backend.review.edit', compact('review'));
            
        } catch (Exception $e) {
            \Log::error('Error loading edit review form: ' . $e->getMessage(), [
                'review_id' => $id
            ]);
            abort(404, 'Review not found');
        }
    }

    /**
     * Update the specified product review
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(\App\Http\Requests\User\ProductReviewUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            $review = ProductReview::findOrFail($id);
            
            $validatedData = $request->validated();
            $allowedFields = ['rate', 'review', 'name', 'email', 'status'];
            $data = array_intersect_key($validatedData, array_flip($allowedFields));

            $status = $review->update($data);
            
            if ($status) {
                request()->session()->flash('success', 'Review successfully updated');
            } else {
                request()->session()->flash('error', 'Something went wrong! Please try again!');
            }
            
        } catch (Exception $e) {
            \Log::error('Error updating review: ' . $e->getMessage(), [
                'review_id' => $id,
                'request_data' => $request->only(['rate', 'status'])
            ]);
            request()->session()->flash('error', 'An error occurred while updating the review');
        }

        return redirect()->route('review.index');
    }

    /**
     * Remove the specified product review from storage
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $review = ProductReview::findOrFail($id);
            
            $status = $review->delete();
            
            if ($status) {
                request()->session()->flash('success', 'Successfully deleted review');
            } else {
                request()->session()->flash('error', 'Something went wrong! Try again');
            }
            
        } catch (Exception $e) {
            \Log::error('Error deleting review: ' . $e->getMessage(), [
                'review_id' => $id
            ]);
            request()->session()->flash('error', 'An error occurred while deleting the review');
        }

        return redirect()->route('review.index');
    }
}
