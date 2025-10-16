<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Session;
use App\Models\Language;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * LanguageComposer handles language-related data for views
 * 
 * This composer provides language direction and other language-related
 * data to all admin views automatically.
 */
class LanguageComposer
{
    /**
     * Bind data to the view.
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view): void
    {
        try {
            // Get text direction from session (set by SetLanguage middleware)
            $direction = Session::get('text_direction', 'ltr');
            
            // Get current language
            $currentLocale = app()->getLocale();
            $currentLanguage = Language::getByCode($currentLocale);
            
            // If no language found, use default
            if (!$currentLanguage) {
                $currentLanguage = Language::getDefault();
                $direction = $currentLanguage ? $currentLanguage->direction : 'ltr';
            }
            
            // Get all active languages for language switcher
            $languages = Language::getActive();
            
            // Share data with the view
            $view->with([
                'dir' => $direction,
                'currentLanguage' => $currentLanguage,
                'languages' => $languages,
                'isRtl' => $direction === 'rtl',
                'textDirection' => $direction
            ]);
            
        } catch (Exception $e) {
            Log::error('Error in LanguageComposer: ' . $e->getMessage(), [
                'view_name' => $view->getName(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback to safe defaults
            $view->with([
                'dir' => 'ltr',
                'currentLanguage' => null,
                'languages' => collect(),
                'isRtl' => false,
                'textDirection' => 'ltr'
            ]);
        }
    }
}