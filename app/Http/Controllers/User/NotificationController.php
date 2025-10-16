<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use Exception;

/**
 * NotificationController handles notification management operations
 * 
 * This controller manages notification viewing, deletion, and user-specific
 * notification handling with secure validation and proper error handling.
 */
class NotificationController extends Controller
{
    /**
     * Display a listing of notifications
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            return view('backend.notification.index');
        } catch (Exception $e) {
            \Log::error('Error loading notifications index: ' . $e->getMessage());
            abort(404, 'Notifications page not found');
        }
    }
    /**
     * Show and mark notification as read, then redirect to action URL
     * 
     * @param \App\Http\Requests\User\NotificationShowRequest $request
     * @return RedirectResponse
     */
    public function show(\App\Http\Requests\User\NotificationShowRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            // Check if user is authenticated
            if (!Auth::check()) {
                request()->session()->flash('error', 'Authentication required');
                return redirect()->route('login');
            }

            $notification = Auth::user()->notifications()
                ->where('id', $validatedData['id'])
                ->first();

            if ($notification) {
                // Mark notification as read
                $notification->markAsRead();
                
                // Check if actionURL exists in notification data
                if (isset($notification->data['actionURL']) && !empty($notification->data['actionURL'])) {
                    return redirect($notification->data['actionURL']);
                } else {
                    request()->session()->flash('warning', 'Notification has no action URL');
                    return redirect()->back();
                }
            } else {
                request()->session()->flash('error', 'Notification not found');
                return redirect()->back();
            }
            
        } catch (Exception $e) {
            \Log::error('Error showing notification: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'notification_id' => $request->input('id')
            ]);
            request()->session()->flash('error', 'An error occurred while processing the notification');
            return redirect()->back();
        }
    }
    /**
     * Delete a notification
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function delete(int $id): RedirectResponse
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                request()->session()->flash('error', 'Authentication required');
                return redirect()->route('login');
            }

            // Find notification belonging to the authenticated user
            $notification = Auth::user()->notifications()
                ->where('id', $id)
                ->first();

            if ($notification) {
                $status = $notification->delete();
                
                if ($status) {
                    request()->session()->flash('success', 'Notification successfully deleted');
                } else {
                    request()->session()->flash('error', 'Error occurred please try again');
                }
            } else {
                request()->session()->flash('error', 'Notification not found or access denied');
            }
            
        } catch (Exception $e) {
            \Log::error('Error deleting notification: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'notification_id' => $id
            ]);
            request()->session()->flash('error', 'An error occurred while deleting the notification');
        }

        return redirect()->back();
    }

    /**
     * Mark all notifications as read for the authenticated user
     * 
     * @return RedirectResponse
     */
    public function markAllAsRead(): RedirectResponse
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                request()->session()->flash('error', 'Authentication required');
                return redirect()->route('login');
            }

            $unreadCount = Auth::user()->unreadNotifications()->count();
            
            if ($unreadCount > 0) {
                Auth::user()->unreadNotifications()->update(['read_at' => now()]);
                request()->session()->flash('success', "Marked {$unreadCount} notifications as read");
            } else {
                request()->session()->flash('info', 'No unread notifications found');
            }
            
        } catch (Exception $e) {
            \Log::error('Error marking all notifications as read: ' . $e->getMessage(), [
                'user_id' => Auth::id()
            ]);
            request()->session()->flash('error', 'An error occurred while marking notifications as read');
        }

        return redirect()->back();
    }

    /**
     * Get unread notifications count for AJAX requests
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadCount(): \Illuminate\Http\JsonResponse
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                return response()->json(['count' => 0]);
            }

            $count = Auth::user()->unreadNotifications()->count();
            
            return response()->json(['count' => $count]);
            
        } catch (Exception $e) {
            \Log::error('Error getting unread notifications count: ' . $e->getMessage(), [
                'user_id' => Auth::id()
            ]);
            return response()->json(['count' => 0, 'error' => 'Failed to get count']);
        }
    }

    /**
     * Get recent notifications for AJAX requests
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecent(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                return response()->json(['notifications' => []]);
            }

            $limit = $request->input('limit', 5);
            $limit = min(max($limit, 1), 20); // Limit between 1 and 20

            $notifications = Auth::user()->notifications()
                ->orderBy('created_at', 'DESC')
                ->limit($limit)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'data' => $notification->data,
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at->diffForHumans(),
                    ];
                });
            
            return response()->json(['notifications' => $notifications]);
            
        } catch (Exception $e) {
            \Log::error('Error getting recent notifications: ' . $e->getMessage(), [
                'user_id' => Auth::id()
            ]);
            return response()->json(['notifications' => [], 'error' => 'Failed to get notifications']);
        }
    }
}
