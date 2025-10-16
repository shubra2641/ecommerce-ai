<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\Category;
use Exception;
use App\Http\Requests\Admin\CategoryStoreRequest;
use App\Http\Requests\Admin\CategoryStoreRequest as CategoryUpdateRequest;

/**
 * CategoryController handles category management operations
 * 
 * This controller manages category creation, editing, deletion, and display
 * with hierarchical structure support and multi-language capabilities.
 */
class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            $categories = Category::with(['parent_info', 'child_cat'])->orderBy('id', 'DESC')->paginate(10);
            return view('backend.category.index', compact('categories'));
        } catch (Exception $e) {
            \Log::error('Error fetching categories: ' . $e->getMessage());
            request()->session()->flash('error', 'Unable to load categories');
            return view('backend.category.index', ['categories' => collect()]);
        }
    }

    /**
     * Show the form for creating a new category
     * 
     * @return View
     */
    public function create(): View
    {
        try {
            $parent_cats = Category::where('is_parent', 1)->orderBy('title', 'ASC')->get();
            return view('backend.category.create', compact('parent_cats'));
        } catch (Exception $e) {
            \Log::error('Error loading create category form: ' . $e->getMessage());
            request()->session()->flash('error', 'Unable to load create form');
            return redirect()->route('category.index');
        }
    }

    /**
     * Store a newly created category in storage
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(CategoryStoreRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            $slug = generateUniqueSlug($validatedData['title'], Category::class);
            $validatedData['slug'] = $slug;
            $validatedData['is_parent'] = $request->input('is_parent', 0);

            $category = Category::create($validatedData);

            // Handle translations securely
            if ($request->has('translations') && is_array($request->input('translations'))) {
                $category->translations = $request->input('translations');
                $category->save();
            }

            if ($category) {
                request()->session()->flash('success', 'Category successfully added');
            } else {
                request()->session()->flash('error', 'Error occurred while creating category');
            }

        } catch (Exception $e) {
            \Log::error('Error creating category: ' . $e->getMessage(), [
                'request_data' => $request->only(['title', 'summary', 'photo', 'status', 'is_parent', 'parent_id']),
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'An error occurred while creating the category');
        }

        return redirect()->route('category.index');
    }

    /**
     * Display the specified category
     * 
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        try {
            $category = Category::findOrFail($id);
            return view('backend.category.show', compact('category'));
        } catch (Exception $e) {
            \Log::error('Error fetching category: ' . $e->getMessage());
            request()->session()->flash('error', 'Category not found');
            return redirect()->route('category.index');
        }
    }

    /**
     * Show the form for editing the specified category
     * 
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        try {
            $category = Category::findOrFail($id);
            $parent_cats = Category::where('is_parent', 1)->get();
            $translations = $category->translations ?? [];
            return view('backend.category.edit', compact('category', 'parent_cats', 'translations'));
        } catch (Exception $e) {
            \Log::error('Error loading edit category form: ' . $e->getMessage());
            request()->session()->flash('error', 'Category not found');
            return redirect()->route('category.index');
        }
    }

    /**
     * Update the specified category in storage
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(CategoryUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            $category = Category::findOrFail($id);
            $validatedData = $request->validated();

            $validatedData['is_parent'] = $request->input('is_parent', 0);

            $status = $category->update($validatedData);

            // Handle translations securely
            if ($request->has('translations') && is_array($request->input('translations'))) {
                $existing = $category->translations ?? [];
                $merged = array_replace_recursive($existing, $request->input('translations'));
                $category->translations = $merged;
                $category->save();
            }

            if ($status) {
                request()->session()->flash('success', 'Category successfully updated');
            } else {
                request()->session()->flash('error', 'Error occurred while updating category');
            }

        } catch (Exception $e) {
            \Log::error('Error updating category: ' . $e->getMessage(), [
                'category_id' => $id,
                'request_data' => $request->only(['title', 'summary', 'photo', 'status', 'is_parent', 'parent_id']),
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'An error occurred while updating the category');
        }

        return redirect()->route('category.index');
    }

    /**
     * Remove the specified category from storage
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $category = Category::findOrFail($id);
            $child_cat_id = Category::where('parent_id', $id)->pluck('id');

            $status = $category->delete();

            if ($status && $child_cat_id->count() > 0) {
                Category::shiftChild($child_cat_id);
            }

            if ($status) {
                request()->session()->flash('success', 'Category successfully deleted');
            } else {
                request()->session()->flash('error', 'Error occurred while deleting category');
            }

        } catch (Exception $e) {
            \Log::error('Error deleting category: ' . $e->getMessage(), [
                'category_id' => $id,
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'An error occurred while deleting the category');
        }

        return redirect()->route('category.index');
    }

    /**
     * Get child categories by parent ID
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getChildByParent(\App\Http\Requests\Admin\CategoryChildRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $category = Category::findOrFail($validated['id']);
            $child_cat = Category::getChildByParentID($validated['id']);

            if ($child_cat->count() <= 0) {
                return response()->json([
                    'status' => false, 
                    'msg' => 'No child categories found', 
                    'data' => null
                ]);
            }

            return response()->json([
                'status' => true, 
                'msg' => 'Child categories retrieved successfully', 
                'data' => $child_cat
            ]);

        } catch (Exception $e) {
            \Log::error('Error fetching child categories: ' . $e->getMessage(), [
                'parent_id' => $request->id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'status' => false, 
                'msg' => 'An error occurred while fetching child categories', 
                'data' => null
            ], 500);
        }
    }
}
