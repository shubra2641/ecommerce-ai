<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Language;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Language API Controller
 * 
 * Handles language-related API operations for frontend applications
 * including retrieving active languages with caching support.
 */
class LanguageApiController extends Controller
{
    /**
     * Return active languages
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Cache languages for 1 hour to improve performance
            $data = Cache::remember('api_languages', 3600, function () {
                $langs = Language::active()->orderBy('sort_order')->get();
                
                return $langs->map(function ($l) {
                    return [
                        'id' => $l->id,
                        'name' => $l->name,
                        'code' => $l->code,
                        'flag' => $l->flag,
                        'direction' => $l->direction,
                        'is_default' => (bool) $l->is_default,
                    ];
                });
            });
            
            return response()->json(['languages' => $data], 200);
        } catch (\Exception $e) {
            Log::error('Language API error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve languages'], 500);
        }
    }
}
