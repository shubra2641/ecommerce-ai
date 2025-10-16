<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Models\Settings;
use Exception;

/**
 * FontController handles dynamic CSS generation for frontend and backend fonts
 * 
 * This controller generates CSS files dynamically based on font settings
 * stored in the database, with proper caching and security measures.
 */
class FontController extends Controller
{
    /**
     * Generate dynamic CSS for frontend fonts
     * 
     * @return Response
     */
    public function frontendCss(): Response
    {
        try {
            $settings = $this->getFontSettings();
            $css = $this->generateCss($settings, 'frontend');
            
            return response($css)
                ->header('Content-Type', 'text/css')
                ->header('Cache-Control', 'public, max-age=3600');
                
        } catch (Exception $e) {
            \Log::error('Error generating frontend CSS: ' . $e->getMessage());
            
            // Return default CSS on error
            $defaultCss = $this->getDefaultCss('frontend');
            return response($defaultCss)
                ->header('Content-Type', 'text/css')
                ->header('Cache-Control', 'public, max-age=300'); // Shorter cache on error
        }
    }
    
    /**
     * Generate dynamic CSS for backend fonts
     * 
     * @return Response
     */
    public function backendCss(): Response
    {
        try {
            $settings = $this->getFontSettings();
            $css = $this->generateCss($settings, 'backend');
            
            return response($css)
                ->header('Content-Type', 'text/css')
                ->header('Cache-Control', 'public, max-age=3600');
                
        } catch (Exception $e) {
            \Log::error('Error generating backend CSS: ' . $e->getMessage());
            
            // Return default CSS on error
            $defaultCss = $this->getDefaultCss('backend');
            return response($defaultCss)
                ->header('Content-Type', 'text/css')
                ->header('Cache-Control', 'public, max-age=300'); // Shorter cache on error
        }
    }

    /**
     * Get font settings from database with validation
     * 
     * @return object
     * @throws Exception
     */
    private function getFontSettings(): object
    {
        $settings = Settings::first();
        
        if (!$settings) {
            throw new Exception('Settings not found');
        }

        return (object) [
            'frontend_font_family' => $this->sanitizeFontFamily($settings->frontend_font_family ?? 'Arial, sans-serif'),
            'frontend_font_size' => $this->sanitizeFontSize($settings->frontend_font_size ?? '14px'),
            'frontend_font_weight' => $this->sanitizeFontWeight($settings->frontend_font_weight ?? 'normal'),
            'backend_font_family' => $this->sanitizeFontFamily($settings->backend_font_family ?? 'Arial, sans-serif'),
            'backend_font_size' => $this->sanitizeFontSize($settings->backend_font_size ?? '14px'),
            'backend_font_weight' => $this->sanitizeFontWeight($settings->backend_font_weight ?? 'normal'),
            'use_google_fonts' => (bool) ($settings->use_google_fonts ?? false),
            'google_fonts_url' => $this->sanitizeUrl($settings->google_fonts_url ?? ''),
        ];
    }

    /**
     * Generate CSS based on settings and type
     * 
     * @param object $settings
     * @param string $type
     * @return string
     */
    private function generateCss(object $settings, string $type): string
    {
        $fontFamily = $type === 'frontend' ? $settings->frontend_font_family : $settings->backend_font_family;
        $fontSize = $type === 'frontend' ? $settings->frontend_font_size : $settings->backend_font_size;
        $fontWeight = $type === 'frontend' ? $settings->frontend_font_weight : $settings->backend_font_weight;
        
        $css = '';
        
        // Add Google Fonts import if enabled and URL is valid
        if ($settings->use_google_fonts && !empty($settings->google_fonts_url)) {
            $css .= "@import url('{$settings->google_fonts_url}');\n";
        }
        
        // Add Cairo font if selected
        if (strpos($fontFamily, 'Cairo') !== false) {
            $css .= "@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap');\n";
        }
        
        if ($type === 'frontend') {
            $css .= $this->generateFrontendCss($fontFamily, $fontSize, $fontWeight);
        } else {
            $css .= $this->generateBackendCss($fontFamily, $fontSize, $fontWeight);
        }
        
        return $css;
    }

    /**
     * Generate frontend-specific CSS rules
     * 
     * @param string $fontFamily
     * @param string $fontSize
     * @param string $fontWeight
     * @return string
     */
    private function generateFrontendCss(string $fontFamily, string $fontSize, string $fontWeight): string
    {
        $css = '';
        
        $css .= "body, html {\n";
        // Do not globally override icon font-family (e.g. FontAwesome). Apply font-family to textual containers only.
        $css .= "    font-size: {$fontSize} !important;\n";
        $css .= "    font-weight: {$fontWeight} !important;\n";
        $css .= "}\n\n";
        // Avoid using the universal selector which can override icon fonts (FontAwesome 4/5).
        // Apply font-family to common textual elements only.
        
        $css .= "h1, h2, h3, h4, h5, h6 {\n";
        $css .= "    font-family: {$fontFamily} !important;\n";
        $css .= "}\n\n";
        
        $css .= "p, span, div, input, textarea, select, .form-control {\n";
        $css .= "    font-family: {$fontFamily} !important;\n";
        $css .= "}\n\n";
        
        // Navigation and UI elements
        $css .= "nav, .nav, .navbar, .nav-tabs, .nav-pills {\n";
        $css .= "    font-family: {$fontFamily} !important;\n";
        $css .= "}\n\n";
        
        // Links and buttons (excluding icon-only elements)
        $css .= "a:not([class*='fa-']):not([class*='icon']), .btn, button {\n";
        $css .= "    font-family: {$fontFamily} !important;\n";
        $css .= "}\n\n";
        
        // Product area tabs specifically
        $css .= ".product-area .nav-tabs li a, .nav-tabs .nav-link {\n";
        $css .= "    font-family: {$fontFamily} !important;\n";
        $css .= "}\n\n";
        
        // Labels and text elements
        $css .= "label, .label, .badge, .tag, .card-text, .text {\n";
        $css .= "    font-family: {$fontFamily} !important;\n";
        $css .= "}\n\n";
        
        // Dropdown and menu items
        $css .= ".dropdown-item, .menu-item, .list-group-item {\n";
        $css .= "    font-family: {$fontFamily} !important;\n";
        $css .= "}\n\n";
        
        // Table elements
        $css .= "td, th, .table {\n";
        $css .= "    font-family: {$fontFamily} !important;\n";
        $css .= "}\n";
        
        return $css;
    }

    /**
     * Generate backend-specific CSS rules
     * 
     * @param string $fontFamily
     * @param string $fontSize
     * @param string $fontWeight
     * @return string
     */
    private function generateBackendCss(string $fontFamily, string $fontSize, string $fontWeight): string
    {
        $css = '';
        
        // Backend-specific CSS with Font Awesome compatibility
        $css .= "body, html {\n";
        $css .= "    font-size: {$fontSize} !important;\n";
        $css .= "    font-weight: {$fontWeight} !important;\n";
        $css .= "}\n\n";

        // Apply font-family to headings
        $css .= "h1, h2, h3, h4, h5, h6 {\n";
        $css .= "    font-family: {$fontFamily} !important;\n";
        $css .= "}\n\n";
        
        // Apply font rules to common text containers but avoid anchors/buttons/icons
        $css .= "p, span, div, input, textarea, select, .form-control {\n";
        $css .= "    font-family: {$fontFamily} !important;\n";
        $css .= "}\n\n";
        
        // Apply to main content area
        $css .= ".main-content {\n";
        $css .= "    font-family: {$fontFamily} !important;\n";
        $css .= "}\n\n";
        
        // Apply to user dashboard specific elements
        $css .= "#content-wrapper, #content, .container-fluid {\n";
        $css .= "    font-family: {$fontFamily} !important;\n";
        $css .= "}\n\n";
        
        // Apply to user dashboard cards and tables
        $css .= ".card, .card-header, .card-body, .table, .table th, .table td {\n";
        $css .= "    font-family: {$fontFamily} !important;\n";
        $css .= "}\n\n";
        
        // Apply to user dashboard forms
        $css .= ".form-group, .form-control, .btn {\n";
        $css .= "    font-family: {$fontFamily} !important;\n";
        $css .= "}\n\n";
        
        // Apply to user dashboard navigation
        $css .= ".navbar, .navbar-nav, .nav-link, .dropdown-menu {\n";
        $css .= "    font-family: {$fontFamily} !important;\n";
        $css .= "}\n";
        
        return $css;
    }

    /**
     * Get default CSS when settings are unavailable
     * 
     * @param string $type
     * @return string
     */
    private function getDefaultCss(string $type): string
    {
        if ($type === 'frontend') {
            return "body, html { font-family: Arial, sans-serif !important; font-size: 14px !important; }\n";
        }
        
        return "body, html { font-size: 14px !important; }\n";
    }

    /**
     * Sanitize font family string
     * 
     * @param string $fontFamily
     * @return string
     */
    private function sanitizeFontFamily(string $fontFamily): string
    {
        // Remove potentially dangerous characters and limit length
        $sanitized = preg_replace('/[^a-zA-Z0-9\s,\-]/', '', $fontFamily);
        return substr(trim($sanitized), 0, 100);
    }

    /**
     * Sanitize font size string
     * 
     * @param string $fontSize
     * @return string
     */
    private function sanitizeFontSize(string $fontSize): string
    {
        // Allow only valid CSS font-size values
        if (preg_match('/^\d+(\.\d+)?(px|em|rem|%)$/', $fontSize)) {
            return $fontSize;
        }
        
        return '14px'; // Default fallback
    }

    /**
     * Sanitize font weight string
     * 
     * @param string $fontWeight
     * @return string
     */
    private function sanitizeFontWeight(string $fontWeight): string
    {
        $validWeights = ['normal', 'bold', 'bolder', 'lighter', '100', '200', '300', '400', '500', '600', '700', '800', '900'];
        
        if (in_array($fontWeight, $validWeights)) {
            return $fontWeight;
        }
        
        return 'normal'; // Default fallback
    }

    /**
     * Sanitize URL string
     * 
     * @param string $url
     * @return string
     */
    private function sanitizeUrl(string $url): string
    {
        // Only allow Google Fonts URLs
        if (filter_var($url, FILTER_VALIDATE_URL) && strpos($url, 'fonts.googleapis.com') !== false) {
            return $url;
        }
        
        return ''; // Return empty string for invalid URLs
    }
}