<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;
use App\Models\PostCategory;
use App\Models\Language;
use Exception;
use App\Http\Requests\Admin\PostCategoryStoreRequest;
use App\Http\Requests\Admin\PostCategoryStoreRequest as PostCategoryUpdateRequest;

/**
 * PostCategoryController handles post category management operations
 * 
 * This controller manages post category creation, editing, deletion, and
 * multi-language translation support with secure validation and proper error handling.
 */
class PostCategoryController extends Controller
{
    /**
     * Display a listing of post categories
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            $postCategories = PostCategory::orderBy('id', 'DESC')->paginate(10);
            return view('backend.postcategory.index', compact('postCategories'));
            
        } catch (Exception $e) {
            \Log::error('Error loading post categories: ' . $e->getMessage());
            return view('backend.postcategory.index', ['postCategories' => collect()]);
        }
    }

    /**
     * Show the form for creating a new post category
     * 
     * @return View
     */
    public function create(): View
    {
        try {
            return view('backend.postcategory.create');
        } catch (Exception $e) {
            \Log::error('Error loading create post category form: ' . $e->getMessage());
            abort(404, 'Create form not found');
        }
    }

    /**
     * Store a newly created post category
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(PostCategoryStoreRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['title', 'status'];
            $data = $request->only($allowedFields);

            // Generate unique slug
            $data['slug'] = $this->generateUniqueSlug($request->title);

            // Create the post category
            $postCategory = PostCategory::create($data);

            if ($postCategory) {
                // Handle translations
                $this->processTranslations($postCategory, $request);

                request()->session()->flash('success', 'Post Category successfully added');
            } else {
                request()->session()->flash('error', 'Please try again!');
            }

        } catch (Exception $e) {
            \Log::error('Error storing post category: ' . $e->getMessage(), [
                'request_data' => $request->only(['title', 'status'])
            ]);
            request()->session()->flash('error', 'An error occurred while creating the post category');
        }

        return redirect()->route('post-category.index');
    }

    /**
     * Generate unique slug for post category
     * 
     * @param string $title
     * @return string
     */
    private function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (PostCategory::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . date('ymdis') . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Process translations for post category
     * 
     * @param PostCategory $postCategory
     * @param Request $request
     * @return void
     */
    private function processTranslations(PostCategory $postCategory, Request $request): void
    {
        try {
            $submitted = $this->filterTranslations($request->input('translations', []));
            
            // Ensure default language entry exists
            $default = Language::getDefault();
            $defaultCode = $default ? $default->code : null;
            
            if ($defaultCode) {
                $submitted[$defaultCode]['title'] = $submitted[$defaultCode]['title'] ?? $request->input('title');
                $submitted[$defaultCode]['summary'] = $submitted[$defaultCode]['summary'] ?? null;
            }
            
            if (!empty($submitted)) {
                $postCategory->translations = $submitted;
                $postCategory->save();
            }
        } catch (Exception $e) {
            \Log::error('Error processing translations: ' . $e->getMessage(), [
                'post_category_id' => $postCategory->id
            ]);
        }
    }

    /**
     * Display the specified post category
     * 
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        try {
            $postCategory = PostCategory::findOrFail($id);
            $translations = $postCategory->translations ?? [];
            
            return view('backend.postcategory.show', compact('postCategory', 'translations'));
            
        } catch (Exception $e) {
            \Log::error('Error loading post category details: ' . $e->getMessage(), [
                'post_category_id' => $id
            ]);
            abort(404, 'Post category not found');
        }
    }

    /**
     * Show the form for editing the specified post category
     * 
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        try {
            $postCategory = PostCategory::findOrFail($id);
            $translations = $postCategory->translations ?? [];
            
            return view('backend.postcategory.edit', compact('postCategory', 'translations'));
            
        } catch (Exception $e) {
            \Log::error('Error loading edit post category form: ' . $e->getMessage(), [
                'post_category_id' => $id
            ]);
            abort(404, 'Post category not found');
        }
    }

    /**
     * Update the specified post category
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(PostCategoryUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            $postCategory = PostCategory::findOrFail($id);
            $validatedData = $request->validated();

            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['title', 'status'];
            $data = $request->only($allowedFields);

            // Update slug if title changed compared to original DB value
            $originalTitle = $postCategory->getOriginal('title');
            if ($request->title !== $originalTitle) {
                $data['slug'] = $this->generateUniqueSlug($request->title);
            }

            $status = $postCategory->update($data);
            
            if ($status) {
                // Handle translations
                $this->mergeTranslations($postCategory, $request);
                
                request()->session()->flash('success', 'Post Category successfully updated');
            } else {
                request()->session()->flash('error', 'Please try again!');
            }
            
        } catch (Exception $e) {
            \Log::error('Error updating post category: ' . $e->getMessage(), [
                'post_category_id' => $id,
                'request_data' => $request->only(['title', 'status'])
            ]);
            request()->session()->flash('error', 'An error occurred while updating the post category');
        }

        return redirect()->route('post-category.index');
    }

    /**
     * Merge translations for post category update
     * 
     * @param PostCategory $postCategory
     * @param Request $request
     * @return void
     */
    private function mergeTranslations(PostCategory $postCategory, Request $request): void
    {
        try {
            $submitted = $this->filterTranslations($request->input('translations', []));

            // Merge into existing preserving other languages
            $existing = $postCategory->translations ?? [];
            $merged = array_replace_recursive($existing, $submitted);
            
            // Ensure default language has title
            $default = Language::getDefault();
            $defaultCode = $default ? $default->code : null;
            
            if ($defaultCode) {
                $merged[$defaultCode]['title'] = $merged[$defaultCode]['title'] ?? $request->input('title', $postCategory->title);
                $merged[$defaultCode]['summary'] = $merged[$defaultCode]['summary'] ?? ($postCategory->summary ?? null);
            }
            
            $postCategory->translations = $merged;
            $postCategory->save();
            
        } catch (Exception $e) {
            \Log::error('Error merging translations: ' . $e->getMessage(), [
                'post_category_id' => $postCategory->id
            ]);
        }
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

    /**
     * Remove the specified post category from storage
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $postCategory = PostCategory::findOrFail($id);
            
            $status = $postCategory->delete();
            
            if ($status) {
                request()->session()->flash('success', 'Post Category successfully deleted');
            } else {
                request()->session()->flash('error', 'Error while deleting post category');
            }
            
        } catch (Exception $e) {
            \Log::error('Error deleting post category: ' . $e->getMessage(), [
                'post_category_id' => $id
            ]);
            request()->session()->flash('error', 'An error occurred while deleting the post category');
        }

        return redirect()->route('post-category.index');
    }
}
