<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;

/**
 * Base Controller class for all application controllers
 * 
 * This controller provides common functionality including photo normalization,
 * view data sharing, and base traits for authorization, job dispatching, and validation.
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Normalize products collection or single product with photo array and first_photo
     * 
     * Accepts Eloquent collection, Paginator collection or single model.
     * Processes photo strings by splitting them into arrays and extracting the first photo.
     * 
     * @param mixed $data The data to normalize (single model or collection)
     * @return void
     */
    protected function normalizeProductPhotos(&$data): void
    {
        try {
            if (!$data) {
                return;
            }

            // Single model
            if (is_object($data) && method_exists($data, 'toArray') && !($data instanceof Collection)) {
                $this->normalizeSingleProduct($data);
                return;
            }

            // Collections / paginator
            if (is_iterable($data)) {
                $this->normalizeProductCollection($data);
            }
        } catch (Exception $e) {
            \Log::error('Error normalizing product photos: ' . $e->getMessage());
            // Continue execution without throwing exception to avoid breaking the application
        }
    }

    /**
     * Normalize a single product's photos
     * 
     * @param object $product The product model to normalize
     * @return void
     */
    private function normalizeSingleProduct(object $product): void
    {
        if (isset($product->photo) && $product->photo !== null && is_string($product->photo)) {
            $product->photo_array = explode(',', $product->photo);
            $product->first_photo = $product->photo_array[0] ?? '';
        } else {
            $product->photo_array = [];
            $product->first_photo = '';
        }
    }

    /**
     * Normalize a collection of products' photos
     * 
     * @param iterable $collection The collection to normalize
     * @return void
     */
    private function normalizeProductCollection(iterable $collection): void
    {
        foreach ($collection as $item) {
            if (is_object($item)) {
                $this->normalizeSingleProduct($item);
            }
        }
    }

    /**
     * Share common view data across all controllers
     * 
     * Shares language data and text direction that are commonly used
     * by backend views to avoid repetitive code in controllers.
     * 
     * @return void
     */
    protected function shareCommonViewData(): void
    {
        try {
            // Languages and direction used by many backend views
            // Model provides getActive() and getDefault() — use those to avoid calling undefined methods
            view()->share('activeLangs', \App\Models\Language::getActive());
            view()->share('defaultLang', \App\Models\Language::getDefault());
            
            // Share text direction with fallback to LTR
            $textDirection = session('text_direction', 'ltr');
            if (!in_array($textDirection, ['ltr', 'rtl'])) {
                $textDirection = 'ltr';
            }
            view()->share('dir', $textDirection);
        } catch (Exception $e) {
            \Log::error('Error sharing common view data: ' . $e->getMessage());
            // Set default values to prevent view errors
            view()->share('activeLangs', collect());
            view()->share('defaultLang', null);
            view()->share('dir', 'ltr');
        }
    }

    /**
     * Base controller constructor
     * 
     * Ensures common view data is shared for all controllers by default.
     * This includes language data and text direction settings.
     */
    public function __construct()
    {
        try {
            $this->shareCommonViewData();
        } catch (Exception $e) {
            \Log::error('Error in base controller constructor: ' . $e->getMessage());
            // Continue execution to prevent application from breaking
        }
    }
}