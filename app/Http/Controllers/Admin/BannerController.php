<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Banner;
use App\Models\Language;
use App\Http\Requests\Admin\BannerStoreRequest;
use App\Http\Requests\Admin\BannerUpdateRequest;
use Illuminate\Support\Str;
use Exception;

/**
 * BannerController handles banner management operations
 * 
 * This controller manages banner creation, editing, deletion, and display
 * with multi-language support and secure data handling.
 */
class BannerController extends Controller
{
    /**
     * Constructor to share common view data
     */
    public function __construct()
    {
        $this->shareCommonViewData();
    }

    /**
     * Display a listing of banners
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            $banners = Banner::latest('id')->paginate(10);
            return view('backend.banner.index', compact('banners'));
        } catch (Exception $e) {
            \Log::error('Error fetching banners: ' . $e->getMessage());
            request()->session()->flash('error', 'Unable to load banners');
            return view('backend.banner.index', ['banners' => collect()]);
        }
    }

    /**
     * Show the form for creating a new banner
     * 
     * @return View
     */
    public function create(): View
    {
        try {
            return view('backend.banner.create');
        } catch (Exception $e) {
            \Log::error('Error loading create banner form: ' . $e->getMessage());
            request()->session()->flash('error', 'Unable to load create form');
            return redirect()->route('banner.index');
        }
    }

    /**
     * Store a newly created banner in storage
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(BannerStoreRequest $request): RedirectResponse
    {
        try {
            // Use validated data from the FormRequest
            $validatedData = $request->validated();

            // Generate unique slug using the validated title
            $slug = $this->generateUniqueSlug($validatedData['title']);
            $validatedData['slug'] = $slug;

            // Ensure translations include the default locale values
            $validatedData['translations'] = $this->processTranslations(
                $request->input('translations', []),
                $validatedData['title'],
                $validatedData['description'] ?? ''
            );

            $banner = Banner::create($validatedData);

            if ($banner) {
                request()->session()->flash('success', 'Banner successfully added');
            } else {
                request()->session()->flash('error', 'Error occurred while adding banner');
            }

        } catch (Exception $e) {
            \Log::error('Error creating banner: ' . $e->getMessage(), [
                'request_data' => $request->only(['title', 'description', 'photo', 'status']),
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'An error occurred while creating the banner');
        }

        return redirect()->route('banner.index');
    }

    /**
     * Display the specified banner
     * 
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        try {
            $banner = Banner::findOrFail($id);
            return view('backend.banner.show', compact('banner'));
        } catch (Exception $e) {
            \Log::error('Error fetching banner: ' . $e->getMessage());
            request()->session()->flash('error', 'Banner not found');
            return redirect()->route('banner.index');
        }
    }

    /**
     * Show the form for editing the specified banner
     * 
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        try {
            $banner = Banner::findOrFail($id);
            return view('backend.banner.edit', compact('banner'));
        } catch (Exception $e) {
            \Log::error('Error loading edit banner form: ' . $e->getMessage());
            request()->session()->flash('error', 'Banner not found');
            return redirect()->route('banner.index');
        }
    }

    /**
     * Update the specified banner in storage
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(BannerUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            $banner = Banner::findOrFail($id);
            // Use validated data from the FormRequest
            $validatedData = $request->validated();

            // Merge translations preserving existing ones and ensuring default locale
            $validatedData['translations'] = $this->mergeTranslations(
                is_array($banner->translations) ? $banner->translations : [],
                $request->input('translations', []),
                $validatedData['title'],
                $validatedData['description'] ?? ''
            );

            $status = $banner->update($validatedData);

            if ($status) {
                request()->session()->flash('success', 'Banner successfully updated');
            } else {
                request()->session()->flash('error', 'Error occurred while updating banner');
            }

        } catch (Exception $e) {
            \Log::error('Error updating banner: ' . $e->getMessage(), [
                'banner_id' => $id,
                'request_data' => $request->only(['title', 'description', 'photo', 'status']),
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'An error occurred while updating the banner');
        }

        return redirect()->route('banner.index');
    }

    /**
     * Remove the specified banner from storage
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $banner = Banner::findOrFail($id);
            $status = $banner->delete();

            if ($status) {
                request()->session()->flash('success', 'Banner successfully deleted');
            } else {
                request()->session()->flash('error', 'Error occurred while deleting banner');
            }

        } catch (Exception $e) {
            \Log::error('Error deleting banner: ' . $e->getMessage(), [
                'banner_id' => $id,
                'user_id' => auth()->id()
            ]);
            request()->session()->flash('error', 'An error occurred while deleting the banner');
        }

        return redirect()->route('banner.index');
    }

    /**
     * Generate a unique slug for the banner
     * 
     * @param string $title
     * @return string
     */
    private function generateUniqueSlug(string $title): string
    {
        try {
            $slug = Str::slug($title);
            $count = Banner::where('slug', $slug)->count();

            if ($count > 0) {
                $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
            }

            return $slug;
        } catch (Exception $e) {
            \Log::error('Error generating slug: ' . $e->getMessage());
            return Str::slug($title) . '-' . time();
        }
    }

    /**
     * Process translations for new banner
     * 
     * @param array $translations
     * @param string $title
     * @param string $description
     * @return array
     */
    private function processTranslations(array $translations, string $title, string $description): array
    {
        $defaultCode = app()->getLocale();

        try {
            // filter incoming translations to avoid empty overwrites
            $translations = $this->filterTranslations($translations);

            $default = Language::getDefault();
            $defaultCode = $default ? $default->code : $defaultCode;

            if (!isset($translations[$defaultCode]) || !is_array($translations[$defaultCode])) {
                $translations[$defaultCode] = [];
            }

            $translations[$defaultCode]['title'] = $title;
            $translations[$defaultCode]['description'] = $description;

            return $translations;
        } catch (Exception $e) {
            \Log::error('Error processing translations: ' . $e->getMessage());
            return [$defaultCode => ['title' => $title, 'description' => $description]];
        }
    }

    /**
     * Merge translations for existing banner
     * 
     * @param array $existing
     * @param array $incoming
     * @param string $title
     * @param string $description
     * @return array
     */
    private function mergeTranslations(array $existing, array $incoming, string $title, string $description): array
    {
        $defaultCode = app()->getLocale();

        try {
            $incoming = $this->filterTranslations($incoming);
            $merged = array_replace_recursive($existing, $incoming);
            $default = Language::getDefault();
            $defaultCode = $default ? $default->code : $defaultCode;

            if (!isset($merged[$defaultCode]) || !is_array($merged[$defaultCode])) {
                $merged[$defaultCode] = [];
            }

            $merged[$defaultCode]['title'] = $title;
            $merged[$defaultCode]['description'] = $description;

            return $merged;
        } catch (Exception $e) {
            \Log::error('Error merging translations: ' . $e->getMessage());
            return [$defaultCode => ['title' => $title, 'description' => $description]];
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
}