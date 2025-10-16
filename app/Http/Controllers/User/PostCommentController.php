<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\User\PostCommentStoreRequest;
use App\Models\Post;
use App\Models\User;
use App\Models\PostComment;
use App\Notifications\StatusNotification;
use Exception;

/**
 * PostCommentController handles post comment management operations
 * 
 * This controller manages post comment creation, editing, deletion, and
 * notification handling with secure validation and proper error handling.
 */
class PostCommentController extends Controller
{
    /**
     * Display a listing of post comments
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            $comments = PostComment::with(['user', 'user_info', 'post'])->orderBy('id', 'DESC')->paginate(10);
            return view('backend.comment.index', compact('comments'));
            
        } catch (Exception $e) {
            \Log::error('Error loading post comments: ' . $e->getMessage());
            return view('backend.comment.index', ['comments' => collect()]);
        }
    }

    /**
     * Show the form for creating a new post comment
     * 
     * @return View
     */
    public function create(): View
    {
        try {
            return view('backend.comment.create');
        } catch (Exception $e) {
            \Log::error('Error loading create comment form: ' . $e->getMessage());
            abort(404, 'Create form not found');
        }
    }

    /**
     * Store a newly created post comment
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(PostCommentStoreRequest $request): RedirectResponse
    {
        try {
            // Check if user is authenticated
            if (!auth()->check()) {
                request()->session()->flash('error', 'Please login to post a comment');
                return redirect()->route('login');
            }

            $validatedData = $request->validated();

            // Get post information
            $postInfo = Post::getPostBySlug($validatedData['slug']);
            if (!$postInfo) {
                request()->session()->flash('error', 'Post not found');
                return redirect()->back();
            }

            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['comment', 'name', 'email'];
            $data = $request->only($allowedFields);
            $data['user_id'] = auth()->id();
            $data['post_id'] = $postInfo->id;
            $data['status'] = 'active';

            // Create the comment
            $comment = PostComment::create($data);

            if ($comment) {
                // Send notification to admins
                $this->sendCommentNotification($postInfo);
                
                request()->session()->flash('success', 'Thank you for your comment');
            } else {
                request()->session()->flash('error', 'Something went wrong! Please try again!');
            }

        } catch (Exception $e) {
            \Log::error('Error storing post comment: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->only(['slug', 'comment'])
            ]);
            request()->session()->flash('error', 'An error occurred while posting your comment');
        }

        return redirect()->back();
    }

    /**
     * Send notification to admins about new comment
     * 
     * @param Post $post
     * @return void
     */
    private function sendCommentNotification(Post $post): void
    {
        try {
            $admins = User::where('role', 'admin')->get();
            
            if ($admins->isNotEmpty()) {
                $details = [
                    'title' => 'New Comment created',
                    'actionURL' => route('blog.detail', $post->slug),
                    'fas' => 'fas fa-comment'
                ];
                
                Notification::send($admins, new StatusNotification($details));
            }
        } catch (Exception $e) {
            \Log::error('Error sending comment notification: ' . $e->getMessage(), [
                'post_id' => $post->id
            ]);
        }
    }

    /**
     * Display the specified post comment
     * 
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        try {
            $comment = PostComment::findOrFail($id);
            return view('backend.comment.show', compact('comment'));
            
        } catch (Exception $e) {
            \Log::error('Error loading comment details: ' . $e->getMessage(), [
                'comment_id' => $id
            ]);
            abort(404, 'Comment not found');
        }
    }

    /**
     * Show the form for editing the specified post comment
     * 
     * @param int $id
     * @return View|RedirectResponse
     */
    public function edit(int $id)
    {
        try {
            $comment = PostComment::findOrFail($id);
            return view('backend.comment.edit', compact('comment'));
            
        } catch (Exception $e) {
            \Log::error('Error loading edit comment form: ' . $e->getMessage(), [
                'comment_id' => $id
            ]);
            request()->session()->flash('error', 'Comment not found');
            return redirect()->back();
        }
    }

    /**
     * Update the specified post comment
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(\App\Http\Requests\User\PostCommentUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            $comment = PostComment::findOrFail($id);
            
            $validatedData = $request->validated();
            $allowedFields = ['comment', 'name', 'email', 'status'];
            $data = array_intersect_key($validatedData, array_flip($allowedFields));

            $status = $comment->update($data);
            
            if ($status) {
                request()->session()->flash('success', 'Comment successfully updated');
            } else {
                request()->session()->flash('error', 'Something went wrong! Please try again!');
            }
            
        } catch (Exception $e) {
            \Log::error('Error updating comment: ' . $e->getMessage(), [
                'comment_id' => $id,
                'request_data' => $request->only(['comment', 'status'])
            ]);
            request()->session()->flash('error', 'An error occurred while updating the comment');
        }

        return redirect()->route('comment.index');
    }

    /**
     * Remove the specified post comment from storage
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $comment = PostComment::findOrFail($id);
            
            $status = $comment->delete();
            
            if ($status) {
                request()->session()->flash('success', 'Post Comment successfully deleted');
            } else {
                request()->session()->flash('error', 'Error occurred please try again');
            }
            
        } catch (Exception $e) {
            \Log::error('Error deleting comment: ' . $e->getMessage(), [
                'comment_id' => $id
            ]);
            request()->session()->flash('error', 'An error occurred while deleting the comment');
        }

        return redirect()->back();
    }
}
