<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use App\Models\Language;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\User;
use Exception;
use App\Http\Requests\Admin\PostStoreRequest;
use App\Http\Requests\Admin\PostStoreRequest as PostUpdateRequest;

/**
 * PostController handles post management operations
 * 
 * This controller manages post creation, editing, deletion, and
 * multi-language translation support with secure validation and proper error handling.
 */
class PostController extends Controller
{
    public function __construct()
    {
        $this->shareCommonViewData();
    }
    /**
     * Display a listing of posts
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            $posts = Post::with(['cat_info', 'tag_info', 'author_info'])->orderBy('id', 'DESC')->paginate(10);
            $authorInfo = User::select('id', 'name')->get();
            
            return view('backend.post.index', compact('posts', 'authorInfo'))->with('author_info', $authorInfo);
            
        } catch (Exception $e) {
            \Log::error('Error loading posts: ' . $e->getMessage());
            return view('backend.post.index', [
                'posts' => collect(),
                'authorInfo' => collect()
            ])->with('author_info', collect());
        }
    }

    /**
     * Show the form for creating a new post
     * 
     * @return View
     */
    public function create(): View
    {
        try {
            $categories = PostCategory::get();
            $tags = PostTag::get();
            $users = User::get();
            
            return view('backend.post.create', compact('categories', 'tags', 'users'));
            
        } catch (Exception $e) {
            \Log::error('Error loading create post form: ' . $e->getMessage());
            abort(404, 'Create form not found');
        }
    }

    /**
     * Store a newly created post
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(PostStoreRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['title', 'quote', 'summary', 'description', 'photo', 'added_by', 'post_cat_id', 'status'];
            $data = $request->only($allowedFields);

            // Generate unique slug
            $data['slug'] = $this->generateUniqueSlug($request->title);

            // Handle tags
            $data['tags'] = $this->processTags($request->input('tags'));

            // Process translations
            $data['translations'] = $this->processTranslations($request, $data);

            // Create the post
            $post = Post::create($data);

            if ($post) {
                request()->session()->flash('success', 'Post successfully added');
            } else {
                request()->session()->flash('error', 'Please try again!');
            }

        } catch (Exception $e) {
            \Log::error('Error storing post: ' . $e->getMessage(), [
                'request_data' => $request->only(['title', 'post_cat_id', 'status'])
            ]);
            request()->session()->flash('error', 'An error occurred while creating the post');
        }

        return redirect()->route('post.index');
    }

    /**
     * Generate unique slug for post
     * 
     * @param string $title
     * @return string
     */
    private function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (Post::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . date('ymdis') . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Process tags array
     * 
     * @param array|null $tags
     * @return string
     */
    private function processTags(?array $tags): string
    {
        if ($tags && is_array($tags)) {
            return implode(',', $tags);
        }
        return '';
    }

    /**
     * Process translations for post
     * 
     * @param Request $request
     * @param array $data
     * @return array
     */
    private function processTranslations(Request $request, array $data): array
    {
        $translations = $this->filterTranslations($request->input('translations', []));
        $default = Language::getDefault();
        $defaultCode = $default ? $default->code : app()->getLocale();

        if (!isset($translations[$defaultCode])) {
            $translations[$defaultCode] = [];
        }

        // Ensure default language has required fields
        if (isset($data['title'])) {
            $translations[$defaultCode]['title'] = $data['title'];
        }
        if (isset($data['summary'])) {
            $translations[$defaultCode]['summary'] = $data['summary'];
        }
        if (isset($data['description'])) {
            $translations[$defaultCode]['description'] = $data['description'];
        }

        return $translations;
    }

    /**
     * Display the specified post
     * 
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        try {
            $post = Post::findOrFail($id);
            $translations = $post->translations ?? [];
            $postTags = explode(',', $post->tags);
            
            return view('backend.post.show', compact('post', 'translations', 'postTags'));
            
        } catch (Exception $e) {
            \Log::error('Error loading post details: ' . $e->getMessage(), [
                'post_id' => $id
            ]);
            abort(404, 'Post not found');
        }
    }

    /**
     * Show the form for editing the specified post
     * 
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        try {
            $post = Post::findOrFail($id);
            $categories = PostCategory::get();
            $tags = PostTag::get();
            $users = User::get();
            $postTags = explode(',', $post->tags);
            $translations = $post->translations ?? [];
            
            return view('backend.post.edit', compact('categories', 'users', 'tags', 'post', 'postTags', 'translations'))->with('post_tags', $postTags);
            
        } catch (Exception $e) {
            \Log::error('Error loading edit post form: ' . $e->getMessage(), [
                'post_id' => $id
            ]);
            abort(404, 'Post not found');
        }
    }

    /**
     * Update the specified post
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(PostUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            $post = Post::findOrFail($id);
            $validatedData = $request->validated();

            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['title', 'quote', 'summary', 'description', 'photo', 'added_by', 'post_cat_id', 'status'];
            $data = $request->only($allowedFields);

            // Update slug if title changed compared to original DB value
            $originalTitle = $post->getOriginal('title');
            if ($request->title !== $originalTitle) {
                $data['slug'] = $this->generateUniqueSlug($request->title);
            }

            // Handle tags
            $data['tags'] = $this->processTags($request->input('tags'));

            // Merge translations
            $data['translations'] = $this->mergeTranslations($post, $request, $data);

            $status = $post->update($data);
            
            if ($status) {
                request()->session()->flash('success', 'Post successfully updated');
            } else {
                request()->session()->flash('error', 'Please try again!');
            }
            
        } catch (Exception $e) {
            \Log::error('Error updating post: ' . $e->getMessage(), [
                'post_id' => $id,
                'request_data' => $request->only(['title', 'post_cat_id', 'status'])
            ]);
            request()->session()->flash('error', 'An error occurred while updating the post');
        }

        return redirect()->route('post.index');
    }

    /**
     * Merge translations for post update
     * 
     * @param Post $post
     * @param Request $request
     * @param array $data
     * @return array
     */
    private function mergeTranslations(Post $post, Request $request, array $data): array
    {
        $existing = $post->translations ?? [];
        $incoming = $this->filterTranslations($request->input('translations', []));
        $merged = array_replace_recursive($existing, $incoming);
        
        $default = Language::getDefault();
        $defaultCode = $default ? $default->code : app()->getLocale();
        
        if (!isset($merged[$defaultCode])) {
            $merged[$defaultCode] = [];
        }
        
        // Ensure default language has required fields
        if (isset($data['title'])) {
            $merged[$defaultCode]['title'] = $data['title'];
        }
        if (isset($data['summary'])) {
            $merged[$defaultCode]['summary'] = $data['summary'];
        }
        if (isset($data['description'])) {
            $merged[$defaultCode]['description'] = $data['description'];
        }

        return $merged;
    }

    /**
     * Filter out empty translation entries to avoid wiping existing translations
     *
     * @param array|null $translations
     * @return array
     */
    private function filterTranslations(?array $translations): array
    {
        $translations = $translations ?? [];
        $result = [];

        foreach ($translations as $locale => $fields) {
            if (!is_array($fields)) {
                continue;
            }
            $clean = [];
            foreach ($fields as $k => $v) {
                if (is_array($v)) {
                    $v = array_filter($v, function ($val) { return $val !== null && $val !== ''; });
                    if (!empty($v)) {
                        $clean[$k] = $v;
                    }
                } else {
                    if ($v !== null && $v !== '') {
                        $clean[$k] = $v;
                    }
                }
            }
            if (!empty($clean)) {
                $result[$locale] = $clean;
            }
        }

        return $result;
    }

    /**
     * Remove the specified post from storage
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $post = Post::findOrFail($id);
            
            $status = $post->delete();
            
            if ($status) {
                request()->session()->flash('success', 'Post successfully deleted');
            } else {
                request()->session()->flash('error', 'Error while deleting post');
            }
            
        } catch (Exception $e) {
            \Log::error('Error deleting post: ' . $e->getMessage(), [
                'post_id' => $id
            ]);
            request()->session()->flash('error', 'An error occurred while deleting the post');
        }

        return redirect()->route('post.index');
    }
}
