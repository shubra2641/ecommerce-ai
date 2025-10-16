<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\User\UserProfileUpdateRequest;
use App\Http\Requests\User\UserChangePasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\PostComment;
use App\Models\Shipping;
use App\Rules\MatchOldPassword;
use Exception;

/**
 * HomeController handles user dashboard and profile management
 * 
 * This controller manages user orders, reviews, comments, profile updates,
 * and password changes with secure validation and proper error handling.
 */
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            $orders = Order::where('user_id', auth()->id())
                ->orderBy('id', 'DESC')
                ->paginate(10);
            
            return view('user.index', compact('orders'));
            
        } catch (Exception $e) {
            \Log::error('Error loading user dashboard: ' . $e->getMessage(), [
                'user_id' => auth()->id()
            ]);
            
            return view('user.index', ['orders' => collect()]);
        }
    }

    /**
     * Display user profile
     * 
     * @return View
     */
    public function profile(): View
    {
        try {
            $profile = Auth::user();
            return view('user.users.profile', compact('profile'));
            
        } catch (Exception $e) {
            \Log::error('Error loading user profile: ' . $e->getMessage(), [
                'user_id' => auth()->id()
            ]);
            
            return view('user.users.profile', ['profile' => null]);
        }
    }

    /**
     * Update user profile
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function profileUpdate(UserProfileUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            // Validate that user can only update their own profile
            if (auth()->id() !== $id) {
                request()->session()->flash('error', 'Unauthorized access');
                return redirect()->back();
            }

            $validatedData = $request->validated();

            $user = User::findOrFail($id);
            
            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['name', 'email', 'phone', 'address', 'city', 'state', 'country', 'postcode'];
            $data = $request->only($allowedFields);
            
            $status = $user->update($data);
            
            if ($status) {
                request()->session()->flash('success', 'Successfully updated your profile');
            } else {
                request()->session()->flash('error', 'Please try again!');
            }
            
        } catch (Exception $e) {
            \Log::error('Error updating user profile: ' . $e->getMessage(), [
                'user_id' => $id,
                'request_data' => $request->only(['name', 'email'])
            ]);
            request()->session()->flash('error', 'An error occurred while updating your profile');
        }

        return redirect()->back();
    }

    /**
     * Display user orders
     * 
     * @return View
     */
    public function orderIndex(): View
    {
        try {
            $orders = Order::where('user_id', auth()->id())
                ->orderBy('id', 'DESC')
                ->paginate(10);
            
            return view('user.order.index', compact('orders'));
            
        } catch (Exception $e) {
            \Log::error('Error loading user orders: ' . $e->getMessage(), [
                'user_id' => auth()->id()
            ]);
            
            return view('user.order.index', ['orders' => collect()]);
        }
    }
    /**
     * Delete user order
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function userOrderDelete(int $id): RedirectResponse
    {
        try {
            $order = Order::findOrFail($id);
            
            // Verify ownership
            if ($order->user_id !== auth()->id()) {
                request()->session()->flash('error', 'Unauthorized access');
                return redirect()->back();
            }
            
            // Check if order can be deleted
            $nonDeletableStatuses = ['process', 'delivered', 'cancel'];
            if (in_array($order->status, $nonDeletableStatuses)) {
                request()->session()->flash('error', 'You cannot delete this order now');
                return redirect()->back();
            }
            
            $status = $order->delete();
            
            if ($status) {
                request()->session()->flash('success', 'Order successfully deleted');
            } else {
                request()->session()->flash('error', 'Order could not be deleted');
            }
            
            return redirect()->route('user.order.index');
            
        } catch (Exception $e) {
            \Log::error('Error deleting user order: ' . $e->getMessage(), [
                'order_id' => $id,
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'Order not found or could not be deleted');
            return redirect()->back();
        }
    }

    /**
     * Display order details
     * 
     * @param int $id
     * @return View
     */
    public function orderShow(int $id): View
    {
        try {
            $order = Order::findOrFail($id);
            
            // Verify ownership
            if ($order->user_id !== auth()->id()) {
                abort(403, 'Unauthorized access');
            }
            
            // Get shipping charge using Eloquent
            $shipping_charge = Shipping::where('id', $order->shipping_id)->pluck('price');
            
            return view('user.order.show', compact('order', 'shipping_charge'));
            
        } catch (Exception $e) {
            \Log::error('Error loading order details: ' . $e->getMessage(), [
                'order_id' => $id,
                'user_id' => auth()->id()
            ]);
            abort(404, 'Order not found');
        }
    }
    /**
     * Display user product reviews
     * 
     * @return View
     */
    public function productReviewIndex(): View
    {
        try {
            $reviews = ProductReview::with(['user', 'product'])->where('user_id', auth()->id())->orderBy('id', 'DESC')->paginate(10);
            return view('user.review.index', compact('reviews'));
            
        } catch (Exception $e) {
            \Log::error('Error loading user reviews: ' . $e->getMessage(), [
                'user_id' => auth()->id()
            ]);
            
            return view('user.review.index', ['reviews' => collect()]);
        }
    }

    /**
     * Display edit review form
     * 
     * @param int $id
     * @return View
     */
    public function productReviewEdit(int $id): View
    {
        try {
            $review = ProductReview::findOrFail($id);
            
            // Verify ownership
            if ($review->user_id !== auth()->id()) {
                abort(403, 'Unauthorized access');
            }
            
            return view('user.review.edit', compact('review'));
            
        } catch (Exception $e) {
            \Log::error('Error loading review edit form: ' . $e->getMessage(), [
                'review_id' => $id,
                'user_id' => auth()->id()
            ]);
            abort(404, 'Review not found');
        }
    }

    /**
     * Update product review
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function productReviewUpdate(\App\Http\Requests\User\ProductReviewUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            $review = ProductReview::findOrFail($id);
            
            // Verify ownership
            if ($review->user_id !== auth()->id()) {
                request()->session()->flash('error', 'Unauthorized access');
                return redirect()->route('user.productreview.index');
            }

            $validatedData = $request->validated();
            $allowedFields = ['rate', 'review', 'status'];
            $data = array_intersect_key($validatedData, array_flip($allowedFields));
            
            $status = $review->update($data);
            
            if ($status) {
                request()->session()->flash('success', 'Review successfully updated');
            } else {
                request()->session()->flash('error', 'Something went wrong! Please try again!');
            }
            
        } catch (Exception $e) {
            \Log::error('Error updating product review: ' . $e->getMessage(), [
                'review_id' => $id,
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'Review not found or could not be updated');
        }

        return redirect()->route('user.productreview.index');
    }

    /**
     * Delete product review
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function productReviewDelete(int $id): RedirectResponse
    {
        try {
            $review = ProductReview::findOrFail($id);
            
            // Verify ownership
            if ($review->user_id !== auth()->id()) {
                request()->session()->flash('error', 'Unauthorized access');
                return redirect()->route('user.productreview.index');
            }
            
            $status = $review->delete();
            
            if ($status) {
                request()->session()->flash('success', 'Successfully deleted review');
            } else {
                request()->session()->flash('error', 'Something went wrong! Try again');
            }
            
        } catch (Exception $e) {
            \Log::error('Error deleting product review: ' . $e->getMessage(), [
                'review_id' => $id,
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'Review not found or could not be deleted');
        }
        
        return redirect()->route('user.productreview.index');
    }

    /**
     * Display user comments
     * 
     * @return View
     */
    public function userComment(): View
    {
        try {
            $comments = PostComment::with(['user', 'post'])->where('user_id', auth()->id())->orderBy('id', 'DESC')->paginate(10);
            return view('user.comment.index', compact('comments'));
            
        } catch (Exception $e) {
            \Log::error('Error loading user comments: ' . $e->getMessage(), [
                'user_id' => auth()->id()
            ]);
            
            return view('user.comment.index', ['comments' => collect()]);
        }
    }
    /**
     * Delete user comment
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function userCommentDelete(int $id): RedirectResponse
    {
        try {
            $comment = PostComment::findOrFail($id);
            
            // Verify ownership
            if ($comment->user_id !== auth()->id()) {
                request()->session()->flash('error', 'Unauthorized access');
                return redirect()->back();
            }
            
            $status = $comment->delete();
            
            if ($status) {
                request()->session()->flash('success', 'Post comment successfully deleted');
            } else {
                request()->session()->flash('error', 'Error occurred please try again');
            }
            
        } catch (Exception $e) {
            \Log::error('Error deleting user comment: ' . $e->getMessage(), [
                'comment_id' => $id,
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'Post comment not found');
        }
        
        return redirect()->back();
    }
    /**
     * Display edit comment form
     * 
     * @param int $id
     * @return View
     */
    public function userCommentEdit(int $id): View
    {
        try {
            $comment = PostComment::findOrFail($id);
            
            // Verify ownership
            if ($comment->user_id !== auth()->id()) {
                abort(403, 'Unauthorized access');
            }
            
            return view('user.comment.edit', compact('comment'));
            
        } catch (Exception $e) {
            \Log::error('Error loading comment edit form: ' . $e->getMessage(), [
                'comment_id' => $id,
                'user_id' => auth()->id()
            ]);
            abort(404, 'Comment not found');
        }
    }

    /**
     * Update user comment
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function userCommentUpdate(\App\Http\Requests\User\PostCommentUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            $comment = PostComment::findOrFail($id);
            
            // Verify ownership
            if ($comment->user_id !== auth()->id()) {
                request()->session()->flash('error', 'Unauthorized access');
                return redirect()->route('user.post-comment.index');
            }

            $validatedData = $request->validated();
            $allowedFields = ['comment', 'status'];
            $data = array_intersect_key($validatedData, array_flip($allowedFields));
            
            $status = $comment->update($data);
            
            if ($status) {
                request()->session()->flash('success', 'Comment successfully updated');
            } else {
                request()->session()->flash('error', 'Something went wrong! Please try again!');
            }
            
            return redirect()->route('user.post-comment.index');
            
        } catch (Exception $e) {
            \Log::error('Error updating user comment: ' . $e->getMessage(), [
                'comment_id' => $id,
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'Comment not found or could not be updated');
            return redirect()->back();
        }
    }

    /**
     * Display change password form
     * 
     * @return View
     */
    public function changePassword(): View
    {
        try {
            return view('user.layouts.userPasswordChange');
        } catch (Exception $e) {
            \Log::error('Error loading change password form: ' . $e->getMessage());
            abort(404, 'Change password form not found');
        }
    }
    /**
     * Store new password
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function changPasswordStore(UserChangePasswordRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            $user = User::findOrFail(auth()->id());
            $user->update(['password' => Hash::make($validatedData['new_password'])]);
            
            return redirect()->route('user')->with('success', 'Password successfully changed');
            
        } catch (Exception $e) {
            \Log::error('Error changing password: ' . $e->getMessage(), [
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'An error occurred while changing password');
        }
    }

    /**
     * Display create user form
     * 
     * @return View
     */
    public function userUsersCreate(): View
    {
        try {
            // Get unique roles using Eloquent
            $roles = User::select('role')->distinct()->get();
            return view('user.users.create', compact('roles'));
            
        } catch (Exception $e) {
            \Log::error('Error loading create user form: ' . $e->getMessage());
            return view('user.users.create', ['roles' => collect()]);
        }
    }

    /**
     * Display edit user form
     * 
     * @param int $id
     * @return View
     */
    public function userUsersEdit(int $id): View
    {
        try {
            $user = User::findOrFail($id);
            
            // Get unique roles using Eloquent
            $roles = User::select('role')->distinct()->get();
            
            return view('user.users.edit', compact('user', 'roles'));
            
        } catch (Exception $e) {
            \Log::error('Error loading edit user form: ' . $e->getMessage(), [
                'user_id' => $id
            ]);
            abort(404, 'User not found');
        }
    }
}
