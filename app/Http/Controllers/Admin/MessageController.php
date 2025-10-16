<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Http\Requests\Frontend\MessageStoreRequest;
use App\Http\Requests\Admin\MessageUpdateRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Events\MessageSent;
use Carbon\Carbon;
use Exception;

/**
 * MessageController handles message management operations
 * 
 * This controller manages message creation, viewing, deletion, and real-time
 * messaging functionality with secure validation and proper error handling.
 */
class MessageController extends Controller
{
    /**
     * Display a listing of messages
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            $messages = Message::orderBy('created_at', 'DESC')->paginate(20);
            return view('backend.message.index', compact('messages'));
            
        } catch (Exception $e) {
            \Log::error('Error loading messages: ' . $e->getMessage());
            return view('backend.message.index', ['messages' => collect()]);
        }
    }
    /**
     * Get the latest 5 unread messages for AJAX requests
     * 
     * @return JsonResponse
     */
    public function messageFive(): JsonResponse
    {
        try {
            $messages = Message::whereNull('read_at')
                ->orderBy('created_at', 'DESC')
                ->limit(5)
                ->get();
            
            return response()->json($messages);
            
        } catch (Exception $e) {
            \Log::error('Error loading unread messages: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load messages'], 500);
        }
    }

    /**
     * Display unread messages widget
     * 
     * @return View
     */
    public function message(): View
    {
        try {
            $messages = Message::whereNull('read_at')
                ->orderBy('created_at', 'DESC')
                ->limit(5)
                ->get();
            
            return view('backend.message.message', compact('messages'));
            
        } catch (Exception $e) {
            \Log::error('Error loading message widget: ' . $e->getMessage());
            return view('backend.message.message', ['messages' => collect()]);
        }
    }

    /**
     * Show the form for creating a new message
     * 
     * @return View
     */
    public function create(): View
    {
        try {
            return view('backend.message.create');
        } catch (Exception $e) {
            \Log::error('Error loading create message form: ' . $e->getMessage());
            abort(404, 'Create form not found');
        }
    }

    /**
     * Store a newly created message
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(MessageStoreRequest $request): JsonResponse
    {
        try {
            // Use validated data from the FormRequest
            $validatedData = $request->validated();

            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['name', 'email', 'message', 'subject', 'phone'];
            $data = array_intersect_key($validatedData, array_flip($allowedFields));

            // Create the message
            $message = Message::create($data);

            // Prepare event data
            $eventData = [
                'url' => route('message.show', $message->id),
                'date' => $message->created_at->format('F d, Y h:i A'),
                'name' => $message->name,
                'email' => $message->email,
                'phone' => $message->phone,
                'message' => $message->message,
                'subject' => $message->subject,
                'photo' => Auth::check() ? Auth::user()->photo : null,
            ];

            // Dispatch event for real-time notifications
            event(new MessageSent($eventData));

            return response()->json([
                'status' => true, 
                'message' => 'Message sent successfully.'
            ]);

        } catch (Exception $e) {
            \Log::error('Error storing message: ' . $e->getMessage(), [
                'request_data' => $request->only(['name', 'email', 'subject'])
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while sending the message.'
            ], 500);
        }
    }

    /**
     * Display the specified message
     * 
     * @param Request $request
     * @param int $id
     * @return View|RedirectResponse
     */
    public function show(Request $request, int $id)
    {
        try {
            $message = Message::findOrFail($id);
            
            // Mark message as read if not already read
            if (is_null($message->read_at)) {
                $message->update(['read_at' => Carbon::now()]);
            }
            
            return view('backend.message.show', compact('message'));
            
        } catch (Exception $e) {
            \Log::error('Error loading message: ' . $e->getMessage(), [
                'message_id' => $id
            ]);
            request()->session()->flash('error', 'Message not found');
            return redirect()->back();
        }
    }

    /**
     * Show the form for editing the specified message
     * 
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        try {
            $message = Message::findOrFail($id);
            return view('backend.message.edit', compact('message'));
            
        } catch (Exception $e) {
            \Log::error('Error loading edit message form: ' . $e->getMessage(), [
                'message_id' => $id
            ]);
            abort(404, 'Message not found');
        }
    }

    /**
     * Update the specified message
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(MessageUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            $message = Message::findOrFail($id);
            // Use validated data from the FormRequest
            $validatedData = $request->validated();

            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['name', 'email', 'message', 'subject', 'phone', 'status'];
            $data = array_intersect_key($validatedData, array_flip($allowedFields));
            
            // Update the message
            $message->update($data);
            
            request()->session()->flash('success', 'Message updated successfully');
            
        } catch (Exception $e) {
            \Log::error('Error updating message: ' . $e->getMessage(), [
                'message_id' => $id,
                'request_data' => $request->only(['name', 'email', 'subject'])
            ]);
            request()->session()->flash('error', 'An error occurred while updating the message');
        }

        return redirect()->route('message.index');
    }

    /**
     * Remove the specified message from storage
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $message = Message::findOrFail($id);
            
            $status = $message->delete();
            
            if ($status) {
                request()->session()->flash('success', 'Successfully deleted message');
            } else {
                request()->session()->flash('error', 'Error occurred please try again');
            }
            
        } catch (Exception $e) {
            \Log::error('Error deleting message: ' . $e->getMessage(), [
                'message_id' => $id
            ]);
            request()->session()->flash('error', 'Message not found or could not be deleted');
        }

        return redirect()->back();
    }
}
