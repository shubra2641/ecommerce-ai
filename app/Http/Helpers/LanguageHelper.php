<?php

namespace App\Http\Helpers;

use App\Models\Language;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * LanguageHelper provides utility methods for language management
 * 
 * This helper class handles language switching, translation fallbacks,
 * and language-related operations with proper error handling and validation.
 * 
 * @package App\Http\Helpers
 * @author Laravel Application
 * @version 1.0.0
 * @since 1.0.0
 */
class LanguageHelper
{
    /**
     * Get current language
     * Returns the currently active language object
     * 
     * @return Language|null
     */
    public static function getCurrentLanguage(): ?Language
    {
        try {
            $languageCode = Session::get('locale', config('app.locale'));
            
            if (empty($languageCode)) {
                Log::warning('Language code is empty, using default locale');
                $languageCode = config('app.locale');
            }
            
            return Language::getByCode($languageCode);
        } catch (Exception $e) {
            Log::error('Error getting current language: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get current language code
     * Returns the currently active language code (e.g., 'en', 'ar')
     * 
     * @return string
     */
    public static function getCurrentLanguageCode(): string
    {
        try {
            $languageCode = Session::get('locale', config('app.locale'));
            
            if (empty($languageCode)) {
                Log::warning('Language code is empty, using default locale');
                return config('app.locale', 'en');
            }
            
            return $languageCode;
        } catch (Exception $e) {
            Log::error('Error getting current language code: ' . $e->getMessage());
            return config('app.locale', 'en');
        }
    }
    
    /**
     * Get current text direction
     * Returns 'ltr' or 'rtl' based on current language
     * 
     * @return string
     */
    public static function getCurrentDirection(): string
    {
        try {
            $direction = Session::get('text_direction', 'ltr');
            
            // Validate direction value
            if (!in_array($direction, ['ltr', 'rtl'])) {
                Log::warning('Invalid text direction: ' . $direction . ', using ltr');
                return 'ltr';
            }
            
            return $direction;
        } catch (Exception $e) {
            Log::error('Error getting current direction: ' . $e->getMessage());
            return 'ltr';
        }
    }
    
    /**
     * Get all active languages
     * Returns collection of active languages
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveLanguages()
    {
        try {
            return Language::getActive();
        } catch (Exception $e) {
            Log::error('Error getting active languages: ' . $e->getMessage());
            return collect();
        }
    }
    
    /**
     * Get default language
     * Returns the default language object
     * 
     * @return Language|null
     */
    public static function getDefaultLanguage(): ?Language
    {
        try {
            return Language::getDefault();
        } catch (Exception $e) {
            Log::error('Error getting default language: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Switch language
     * Changes the current language and updates session
     * 
     * @param string $languageCode The language code to switch to
     * @return bool True if successful, false otherwise
     * @throws Exception If language switching fails
     */
    public static function switchLanguage(string $languageCode): bool
    {
        try {
            // Validate language code
            if (empty($languageCode) || !is_string($languageCode)) {
                Log::warning('Invalid language code provided: ' . $languageCode);
                return false;
            }
            
            // Sanitize language code
            $languageCode = trim($languageCode);
            
            // Additional security validation
            if (strlen($languageCode) < 2 || strlen($languageCode) > 5) {
                Log::warning('Language code length invalid: ' . $languageCode);
                return false;
            }
            
            // Check for suspicious patterns
            if (preg_match('/[<>"\']/', $languageCode)) {
                Log::warning('Suspicious characters in language code: ' . $languageCode);
                return false;
            }
            
            $language = Language::getByCode($languageCode);
            
            if ($language && $language->is_active) {
                Session::put('locale', $languageCode);
                Session::put('text_direction', $language->direction);
                app()->setLocale($languageCode);
                
                Log::info('Language switched successfully to: ' . $languageCode, [
                    'user_id' => auth()->id(),
                    'ip' => request()->ip()
                ]);
                return true;
            }
            
            Log::warning('Language not found or inactive: ' . $languageCode);
            return false;
        } catch (Exception $e) {
            Log::error('Error switching language: ' . $e->getMessage(), [
                'language_code' => $languageCode,
                'user_id' => auth()->id(),
                'ip' => request()->ip()
            ]);
            return false;
        }
    }
    
    /**
     * Get language flag
     * Returns the flag code for current language
     * 
     * @return string
     */
    public static function getCurrentFlag(): string
    {
        try {
            $language = self::getCurrentLanguage();
            return $language ? $language->flag : 'us';
        } catch (Exception $e) {
            Log::error('Error getting current flag: ' . $e->getMessage());
            return 'us';
        }
    }
    
    /**
     * Check if current language is RTL
     * Returns true if current language is right-to-left
     * 
     * @return bool
     */
    public static function isRTL(): bool
    {
        try {
            return self::getCurrentDirection() === 'rtl';
        } catch (Exception $e) {
            Log::error('Error checking RTL status: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get language name
     * Returns the name of current language
     * 
     * @return string
     */
    public static function getCurrentLanguageName(): string
    {
        try {
            $language = self::getCurrentLanguage();
            return $language ? $language->name : 'English';
        } catch (Exception $e) {
            Log::error('Error getting current language name: ' . $e->getMessage());
            return 'English';
        }
    }
    
    /**
     * Generate language switcher data
     * Returns array of languages for language switcher component
     * 
     * @return \Illuminate\Support\Collection
     */
    public static function getLanguageSwitcherData()
    {
        try {
            $languages = self::getActiveLanguages();
            $currentCode = self::getCurrentLanguageCode();
            
            if ($languages->isEmpty()) {
                Log::warning('No active languages found for language switcher');
                return collect();
            }
            
            return $languages->map(function($language) use ($currentCode) {
                return [
                    'code' => $language->code,
                    'name' => $language->name,
                    'flag' => $language->flag,
                    'direction' => $language->direction,
                    'is_current' => $language->code === $currentCode,
                    'url' => request()->fullUrlWithQuery(['lang' => $language->code])
                ];
            });
        } catch (Exception $e) {
            Log::error('Error generating language switcher data: ' . $e->getMessage());
            return collect();
        }
    }
    
    /**
     * Get translation with fallback
     * Returns translation for given key with fallback to default language
     * 
     * @param string $key
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    public static function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        try {
            // Validate key
            if (empty($key) || !is_string($key)) {
                Log::warning('Invalid translation key provided: ' . $key);
                return $key;
            }
            
            $locale = $locale ?: self::getCurrentLanguageCode();
            
            // Try to get translation in current language
            $translation = trans($key, $replace, $locale);
            
            // If translation is same as key (not found), try default language
            if ($translation === $key) {
                $defaultLanguage = self::getDefaultLanguage();
                if ($defaultLanguage && $defaultLanguage->code !== $locale) {
                    $translation = trans($key, $replace, $defaultLanguage->code);
                }
            }
            
            return $translation;
        } catch (Exception $e) {
            Log::error('Error getting translation: ' . $e->getMessage(), [
                'key' => $key,
                'locale' => $locale
            ]);
            return $key;
        }
    }
    
    /**
     * Get translation choice with fallback
     * Returns translation choice for given key with fallback to default language
     * 
     * @param string $key
     * @param int $number
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    public static function transChoice(string $key, int $number, array $replace = [], ?string $locale = null): string
    {
        try {
            // Validate key
            if (empty($key) || !is_string($key)) {
                Log::warning('Invalid translation key provided: ' . $key);
                return $key;
            }
            
            // Validate number
            if (!is_numeric($number)) {
                Log::warning('Invalid number provided for transChoice: ' . $number);
                $number = 1;
            }
            
            $locale = $locale ?: self::getCurrentLanguageCode();
            
            // Try to get translation choice in current language
            $translation = trans_choice($key, $number, $replace, $locale);
            
            // If translation is same as key (not found), try default language
            if ($translation === $key) {
                $defaultLanguage = self::getDefaultLanguage();
                if ($defaultLanguage && $defaultLanguage->code !== $locale) {
                    $translation = trans_choice($key, $number, $replace, $defaultLanguage->code);
                }
            }
            
            return $translation;
        } catch (Exception $e) {
            Log::error('Error getting translation choice: ' . $e->getMessage(), [
                'key' => $key,
                'number' => $number,
                'locale' => $locale
            ]);
            return $key;
        }
    }
}
