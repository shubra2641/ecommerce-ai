<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Brand;
use App\Models\Language;
use Illuminate\Support\Str;
use App\Helpers\helpers;
use Exception;
use App\Http\Requests\Admin\BrandStoreRequest;
use App\Http\Requests\Admin\BrandStoreRequest as BrandUpdateRequest;

/**
 * BrandController handles brand management operations
 * 
 * This controller manages brand creation, editing, deletion, and display
 * with multi-language support and secure data handling.
 */
class BrandController extends Controller
{
    /**
     * Constructor to share common view data
     */
    public function __construct()
    {
        $this->shareCommonViewData();
    }

    /**
     * Display a listing of brands
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            $brands = Brand::latest('id')->paginate(10);
            return view('backend.brand.index', compact('brands'));
        } catch (Exception $e) {
            \Log::error('Error fetching brands: ' . $e->getMessage());
            request()->session()->flash('error', 'Unable to load brands');
            return view('backend.brand.index', ['brands' => collect()]);
        }
    }

    /**
     * Show the form for creating a new brand
     * 
     * @return View
     */
    public function create(): View
    {
        try {
            return view('backend.brand.create');
        } catch (Exception $e) {
            \Log::error('Error loading create brand form: ' . $e->getMessage());
            request()->session()->flash('error', 'Unable to load create form');
            return redirect()->route('brand.index');
        }
    }

    /**
     * Store a newly created brand in storage
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(BrandStoreRequest $request): RedirectResponse
    {
        try {
            // Use validated data from FormRequest
            $validatedData = $request->validated();

            // Generate unique slug using helper function
            $slug = generateUniqueSlug($validatedData['title'], Brand::class);
            $validatedData['slug'] = $slug;

            // Handle translations securely
            $validatedData['translations'] = $this->processTranslations(
                $request->input('translations', []),
                $validatedData['title']
            );

            $brand = Brand::create($validatedData);

            if ($brand) {
                request()->session()->flash('success', 'Brand successfully created');
            } else {
                request()->session()->flash('error', 'Error occurred while creating brand');
            }

        } catch (Exception $e) {
            \Log::error('Error creating brand: ' . $e->getMessage(), [
                'request_data' => $request->only(['title', 'status']),
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'An error occurred while creating the brand');
        }

        return redirect()->route('brand.index');
    }

    /**
     * Display the specified brand
     * 
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        try {
            $brand = Brand::findOrFail($id);
            return view('backend.brand.show', compact('brand'));
        } catch (Exception $e) {
            \Log::error('Error fetching brand: ' . $e->getMessage());
            request()->session()->flash('error', 'Brand not found');
            return redirect()->route('brand.index');
        }
    }

    /**
     * Show the form for editing the specified brand
     * 
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        try {
            $brand = Brand::findOrFail($id);
            $translations = $brand->translations ?? [];
            return view('backend.brand.edit', compact('brand', 'translations'));
        } catch (Exception $e) {
            \Log::error('Error loading edit brand form: ' . $e->getMessage());
            request()->session()->flash('error', 'Brand not found');
            return redirect()->route('brand.index');
        }
    }



    /**
     * Update the specified brand in storage
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(BrandUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            $brand = Brand::findOrFail($id);

            // Use validated data from FormRequest
            $validatedData = $request->validated();

            // Handle translations securely
            $existingTranslations = $brand->translations ?? [];
            if (is_string($existingTranslations)) {
                $existingTranslations = json_decode($existingTranslations, true) ?? [];
            }
            
            $validatedData['translations'] = $this->mergeTranslations(
                $existingTranslations,
                $request->input('translations', []),
                $validatedData['title']
            );

            $status = $brand->update($validatedData);

            if ($status) {
                request()->session()->flash('success', 'Brand successfully updated');
            } else {
                request()->session()->flash('error', 'Error occurred while updating brand');
            }

        } catch (Exception $e) {
            \Log::error('Error updating brand: ' . $e->getMessage(), [
                'brand_id' => $id,
                'request_data' => $request->only(['title', 'status']),
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'An error occurred while updating the brand');
        }

        return redirect()->route('brand.index');
    }

    /**
     * Remove the specified brand from storage
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $brand = Brand::findOrFail($id);
            $status = $brand->delete();

            if ($status) {
                request()->session()->flash('success', 'Brand successfully deleted');
            } else {
                request()->session()->flash('error', 'Error occurred while deleting brand');
            }

        } catch (Exception $e) {
            \Log::error('Error deleting brand: ' . $e->getMessage(), [
                'brand_id' => $id,
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'An error occurred while deleting the brand');
        }

        return redirect()->route('brand.index');
    }

    /**
     * Process translations for new brand
     * 
     * @param array $translations
     * @param string $title
     * @return array
     */
    private function processTranslations(array $translations, string $title): array
    {
        try {
            // filter incoming translations to remove empty values so we don't overwrite existing translations
            $translations = $this->filterTranslations($translations);

            $default = Language::getDefault();
            $defaultCode = $default ? $default->code : app()->getLocale();
            
            if (!isset($translations[$defaultCode])) {
                $translations[$defaultCode] = [];
            }
            
            $translations[$defaultCode]['title'] = $title;
            
            return $translations;
        } catch (Exception $e) {
            \Log::error('Error processing translations: ' . $e->getMessage());
            return [$defaultCode => ['title' => $title]];
        }
    }

    /**
     * Merge translations for existing brand
     * 
     * @param array $existing
     * @param array $incoming
     * @param string $title
     * @return array
     */
    private function mergeTranslations(array $existing, array $incoming, string $title): array
    {
        try {
            // filter incoming to avoid replacing existing translations with empty values
            $incoming = $this->filterTranslations($incoming);
            $merged = array_replace_recursive($existing, $incoming);
            $default = Language::getDefault();
            $defaultCode = $default ? $default->code : app()->getLocale();
            
            if (!isset($merged[$defaultCode])) {
                $merged[$defaultCode] = [];
            }
            
            $merged[$defaultCode]['title'] = $title;
            
            return $merged;
        } catch (Exception $e) {
            \Log::error('Error merging translations: ' . $e->getMessage());
            return [$defaultCode => ['title' => $title]];
        }
    }

    /**
     * Remove empty translation values recursively so we don't overwrite existing data
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
            foreach ($fields as $key => $value) {
                if (is_array($value)) {
                    // nested arrays (unlikely) - filter recursively
                    $filtered = array_filter($value, function ($v) {
                        return $v !== null && $v !== '';
                    });
                    if (!empty($filtered)) {
                        $clean[$key] = $filtered;
                    }
                } else {
                    if ($value !== null && $value !== '') {
                        $clean[$key] = $value;
                    }
                }
            }

            if (!empty($clean)) {
                $result[$locale] = $clean;
            }
        }

        return $result;
    }
}
