<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Facades\Log;

/**
 * Banner API Controller
 * 
 * Handles banner-related API operations for frontend applications
 * including retrieving active banners with proper formatting.
 */
class BannerApiController extends Controller
{
    /**
     * Return active banners for frontend use
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $banners = Banner::active()->orderBy('id', 'desc')->get();
            
            // Transform to a simple array
            $data = $banners->map(function ($b) {
                return [
                    'id' => $b->id,
                    'title' => $b->title,
                    'description' => $b->description,
                    'photo' => $b->photo,
                    'status' => $b->status,
                    'translations' => $b->translations ?? []
                ];
            });
            
            return response()->json(['banners' => $data], 200);
        } catch (\Exception $e) {
            Log::error('Banner API error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve banners'], 500);
        }
    }
}
