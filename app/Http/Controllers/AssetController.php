<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Asset Controller for serving static files
 * 
 * This controller handles serving CSS, JS, and image files
 * from different directories with fallback support.
 */
class AssetController extends Controller
{
    /**
     * Serve CSS files
     * 
     * @param string $file
     * @return Response|BinaryFileResponse
     */
    public function css($file)
    {
        $paths = [
            public_path("frontend/css/{$file}"),
            public_path("css/{$file}"),
            public_path("backend/css/{$file}")
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return response()->file($path, [
                    'Content-Type' => 'text/css'
                ]);
            }
        }
        
        abort(404);
    }

    /**
     * Serve JavaScript files
     * 
     * @param string $file
     * @return Response|BinaryFileResponse
     */
    public function js($file)
    {
        // Special handling for main.js - redirect to app.js
        if ($file === 'main.js') {
            $appJsPath = public_path("js/app.js");
            if (file_exists($appJsPath)) {
                return response()->file($appJsPath, [
                    'Content-Type' => 'application/javascript'
                ]);
            }
        }
        
        $paths = [
            public_path("frontend/js/{$file}"),
            public_path("js/{$file}"),
            public_path("backend/js/{$file}")
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return response()->file($path, [
                    'Content-Type' => 'application/javascript'
                ]);
            }
        }
        
        abort(404);
    }

    /**
     * Serve image files
     * 
     * @param string $file
     * @return Response|BinaryFileResponse
     */
    public function images($file)
    {
        $paths = [
            public_path("frontend/img/{$file}"),
            public_path("images/{$file}"),
            public_path("backend/img/{$file}")
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $mimeType = $this->getMimeType($path);
                return response()->file($path, [
                    'Content-Type' => $mimeType
                ]);
            }
        }
        
        abort(404);
    }

    /**
     * Serve favicon
     * 
     * @return Response|BinaryFileResponse
     */
    public function favicon()
    {
        $paths = [
            public_path("frontend/img/favicon.png"),
            public_path("images/favicon.png"),
            public_path("backend/img/favicon.png"),
            public_path("favicon.ico")
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $mimeType = $this->getMimeType($path);
                return response()->file($path, [
                    'Content-Type' => $mimeType
                ]);
            }
        }
        
        abort(404);
    }

    /**
     * Get MIME type for file
     * 
     * @param string $path
     * @return string
     */
    private function getMimeType($path)
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'webp' => 'image/webp'
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
