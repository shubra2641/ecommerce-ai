<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;
use App\Models\PostTag;
use App\Models\Language;
use Exception;
use App\Http\Requests\Admin\PostTagStoreRequest;
use App\Http\Requests\Admin\PostTagStoreRequest as PostTagUpdateRequest;

/**
 * PostTagController handles post tag management operations
 * 
 * This controller manages post tag creation, editing, deletion, and
 * multi-language translation support with secure validation and proper error handling.
 */
class PostTagController extends Controller
{
    public function __construct()
    {
        $this->shareCommonViewData();
    }
    /**
     * Display a listing of post tags
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            $postTags = PostTag::orderBy('id', 'DESC')->paginate(10);
            return view('backend.posttag.index', compact('postTags'));
            
        } catch (Exception $e) {
            \Log::error('Error loading post tags: ' . $e->getMessage());
            return view('backend.posttag.index', ['postTags' => collect()]);
        }
    }

    /**
     * Show the form for creating a new post tag
     * 
     * @return View
     */
    public function create(): View
    {
        try {
            return view('backend.posttag.create');
        } catch (Exception $e) {
            \Log::error('Error loading create post tag form: ' . $e->getMessage());
            abort(404, 'Create form not found');
        }
    }

    /**
     * Store a newly created post tag
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(PostTagStoreRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['title', 'status'];
            $data = $request->only($allowedFields);

            // Generate unique slug
            $data['slug'] = $this->generateUniqueSlug($request->title);

            // Process translations
            $data['translations'] = $this->processTranslations($request, $data);

            // Create the post tag
            $postTag = PostTag::create($data);

            if ($postTag) {
                request()->session()->flash('success', 'Post Tag successfully added');
            } else {
                request()->session()->flash('error', 'Please try again!');
            }

        } catch (Exception $e) {
            \Log::error('Error storing post tag: ' . $e->getMessage(), [
                'request_data' => $request->only(['title', 'status'])
            ]);
            request()->session()->flash('error', 'An error occurred while creating the post tag');
        }

        return redirect()->route('post-tag.index');
    }

    /**
     * Display the specified post tag
     * 
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        try {
            $postTag = PostTag::findOrFail($id);
            $translations = $postTag->translations ?? [];
            
            return view('backend.posttag.show', compact('postTag', 'translations'));
            
        } catch (Exception $e) {
            \Log::error('Error loading post tag details: ' . $e->getMessage(), [
                'post_tag_id' => $id
            ]);
            abort(404, 'Post tag not found');
        }
    }

    /**
     * Show the form for editing the specified post tag
     * 
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        try {
            $postTag = PostTag::findOrFail($id);
            $translations = $postTag->translations ?? [];
            
            return view('backend.posttag.edit', compact('postTag', 'translations'));
            
        } catch (Exception $e) {
            \Log::error('Error loading edit post tag form: ' . $e->getMessage(), [
                'post_tag_id' => $id
            ]);
            abort(404, 'Post tag not found');
        }
    }

    /**
     * Update the specified post tag
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(PostTagUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            $postTag = PostTag::findOrFail($id);
            $validatedData = $request->validated();

            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['title', 'status'];
            $data = $request->only($allowedFields);

            // Update slug if title changed compared to original DB value
            $originalTitle = $postTag->getOriginal('title');
            if ($request->title !== $originalTitle) {
                $data['slug'] = $this->generateUniqueSlug($request->title);
            }

            // Merge translations
            $data['translations'] = $this->mergeTranslations($postTag, $request, $data);

            $status = $postTag->update($data);
            
            if ($status) {
                request()->session()->flash('success', 'Post Tag successfully updated');
            } else {
                request()->session()->flash('error', 'Please try again!');
            }
            
        } catch (Exception $e) {
            \Log::error('Error updating post tag: ' . $e->getMessage(), [
                'post_tag_id' => $id,
                'request_data' => $request->only(['title', 'status'])
            ]);
            request()->session()->flash('error', 'An error occurred while updating the post tag');
        }

        return redirect()->route('post-tag.index');
    }

    /**
     * Remove the specified post tag from storage
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $postTag = PostTag::findOrFail($id);
            
            $status = $postTag->delete();
            
            if ($status) {
                request()->session()->flash('success', 'Post Tag successfully deleted');
            } else {
                request()->session()->flash('error', 'Error while deleting post tag');
            }
            
        } catch (Exception $e) {
            \Log::error('Error deleting post tag: ' . $e->getMessage(), [
                'post_tag_id' => $id
            ]);
            request()->session()->flash('error', 'An error occurred while deleting the post tag');
        }

        return redirect()->route('post-tag.index');
    }

    /**
     * Generate unique slug for post tag
     * 
     * @param string $title
     * @return string
     */
    private function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (PostTag::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . date('ymdis') . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Process translations for post tag
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

        return $translations;
    }

    /**
     * Merge translations for post tag update
     * 
     * @param PostTag $postTag
     * @param Request $request
     * @param array $data
     * @return array
     */
    private function mergeTranslations(PostTag $postTag, Request $request, array $data): array
    {
        $existing = $postTag->translations ?? [];
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

        return $merged;
    }

    /**
     * Filter translations to remove empty values
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
                if ($v !== null && $v !== '') {
                    $clean[$k] = $v;
                }
            }
            if (!empty($clean)) {
                $result[$locale] = $clean;
            }
        }

        return $result;
    }
}
