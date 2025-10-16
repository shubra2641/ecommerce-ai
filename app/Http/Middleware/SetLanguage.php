<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\Language;
use Exception;

/**
 * SetLanguage Middleware
 * 
 * This middleware sets the application locale based on user preference,
 * URL parameters, session data, or browser language. It provides enhanced
 * security, logging, and error handling for language switching operations.
 * 
 * @package App\Http\Middleware
 * @author Laravel Application
 * @version 1.0.0
 * @since 1.0.0
 */
class SetLanguage
{
    /**
     * Handle an incoming request.
     * 
     * This middleware sets the application locale based on user preference
     * or default language with comprehensive error handling and logging.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Get language code from session, request parameter, or default
            $languageCode = $this->getLanguageCode($request);
            
            // Validate and set the language
            if ($this->isValidLanguage($languageCode)) {
                // Set application locale
                App::setLocale($languageCode);
                
                // Store in session for future requests
                Session::put('locale', $languageCode);
                
                // Set text direction based on language
                $this->setTextDirection($languageCode);
                
                // Log successful language setting
                $this->logLanguageSet($request, $languageCode, 'success');
            } else {
                // Fallback to default language (from DB). If not configured, try the
                // first active language, otherwise fall back to config('app.locale').
                $defaultLanguage = Language::getDefault();
                if (!$defaultLanguage) {
                    // Try to pick the first active language as a sensible default
                    $defaultLanguage = Language::getActive()->first();
                }

                if ($defaultLanguage) {
                    $code = $defaultLanguage->code ?? config('app.locale');
                    App::setLocale($code);
                    Session::put('locale', $code);
                    $this->setTextDirection($code);

                    // Log fallback to default language
                    $this->logLanguageSet($request, $code, 'fallback');
                } else {
                    // As a last resort, use the app configuration locale
                    $fallback = config('app.locale');
                    App::setLocale($fallback);
                    Session::put('locale', $fallback);
                    $this->setTextDirection($fallback);

                    Log::warning('No languages configured in DB; falling back to config app.locale in SetLanguage middleware', [
                        'ip' => $request->ip(),
                        'url' => $request->fullUrl(),
                        'requested_language' => $languageCode,
                        'fallback' => $fallback
                    ]);
                }
            }
            
            return $next($request);
        } catch (Exception $e) {
            Log::error('Error in SetLanguage middleware: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Continue with request even if language setting fails
            return $next($request);
        }
    }
    
    /**
     * Get language code from various sources
     * Priority: URL parameter > Session > Default from DB > Browser language > 'en'
     * 
     * @param Request $request The HTTP request object
     * @return string|null The language code or null if not found
     * @throws Exception If language code retrieval fails
     */
    private function getLanguageCode(Request $request): ?string
    {
        try {
            // Validate request object
            if (!$request || !is_object($request)) {
                Log::error('Invalid request object provided to getLanguageCode');
                return null;
            }

            // Check URL parameter first (e.g., ?lang=en)
            if ($request->has('lang')) {
                $langParam = $request->get('lang');
                if ($this->isValidLanguageCode($langParam)) {
                    Log::info('Language code from URL parameter', [
                        'language_code' => $langParam,
                        'ip' => $request->ip()
                    ]);
                    return $langParam;
                } else {
                    Log::warning('Invalid language code in URL parameter', [
                        'language_code' => $langParam,
                        'ip' => $request->ip()
                    ]);
                }
            }
            
            // Check session
            if (Session::has('locale')) {
                $sessionLang = Session::get('locale');
                if ($this->isValidLanguageCode($sessionLang)) {
                    Log::info('Language code from session', [
                        'language_code' => $sessionLang,
                        'ip' => $request->ip()
                    ]);
                    return $sessionLang;
                } else {
                    Log::warning('Invalid language code in session', [
                        'language_code' => $sessionLang,
                        'ip' => $request->ip()
                    ]);
                }
            }
            
            // Get default language from database
            $defaultLanguage = Language::getDefault();
            if ($defaultLanguage && $this->isValidLanguageCode($defaultLanguage->code)) {
                Log::info('Using default language from database', [
                    'language_code' => $defaultLanguage->code,
                    'ip' => $request->ip()
                ]);
                return $defaultLanguage->code;
            }
            
            // Check browser language from Accept-Language header
            $acceptLanguage = $request->header('Accept-Language');
            if ($acceptLanguage) {
                // Validate Accept-Language header
                if (strlen($acceptLanguage) > 1000) {
                    Log::warning('Accept-Language header too long', [
                        'length' => strlen($acceptLanguage),
                        'ip' => $request->ip()
                    ]);
                    return null;
                }

                // Parse Accept-Language header (e.g., "ar-SA,ar;q=0.9,en;q=0.8")
                $languages = explode(',', $acceptLanguage);
                foreach ($languages as $lang) {
                    // Extract language code (e.g., 'ar-SA' -> 'ar', 'ar;q=0.9' -> 'ar')
                    $lang = trim(explode(';', $lang)[0]);
                    $languageCode = substr($lang, 0, 2);
                    
                    if ($this->isValidLanguageCode($languageCode)) {
                        Log::info('Language code from browser header', [
                            'language_code' => $languageCode,
                            'original_header' => $acceptLanguage,
                            'ip' => $request->ip()
                        ]);
                        return $languageCode;
                    }
                }
            }
            
            // Return 'en' as final fallback
            Log::info('No language found, using fallback en', [
                'ip' => $request->ip()
            ]);
            return 'en';
        } catch (Exception $e) {
            Log::error('Error getting language code: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'trace' => $e->getTraceAsString()
            ]);
            return 'en';
        }
    }
    
    /**
     * Validate if language code is supported
     * 
     * @param string|null $languageCode
     * @return bool
     */
    private function isValidLanguage(?string $languageCode): bool
    {
        try {
            if (!$languageCode) {
                return false;
            }
            
            // Check if language exists and is active
            $language = Language::getByCode($languageCode);
            return $language && $language->is_active;
        } catch (Exception $e) {
            Log::error('Error validating language: ' . $e->getMessage(), [
                'language_code' => $languageCode
            ]);
            return false;
        }
    }

    /**
     * Validate language code format and security
     * 
     * @param mixed $languageCode The language code to validate
     * @return bool True if language code is valid and safe, false otherwise
     * @throws Exception If validation fails
     */
    private function isValidLanguageCode($languageCode): bool
    {
        try {
            // Validate input type
            if (!$languageCode || !is_string($languageCode)) {
                Log::warning('Invalid language code type provided', [
                    'type' => gettype($languageCode),
                    'value' => $languageCode
                ]);
                return false;
            }
            
            // Sanitize language code
            $languageCode = trim($languageCode);
            
            // Check if empty after trimming
            if (empty($languageCode)) {
                Log::warning('Empty language code provided');
                return false;
            }
            
            // Check length (should be 2-5 characters)
            if (strlen($languageCode) < 2 || strlen($languageCode) > 5) {
                Log::warning('Invalid language code length', [
                    'language_code' => $languageCode,
                    'length' => strlen($languageCode)
                ]);
                return false;
            }
            
            // Check for valid language code pattern (letters, numbers, hyphens, underscores)
            if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $languageCode)) {
                Log::warning('Invalid language code pattern', [
                    'language_code' => $languageCode
                ]);
                return false;
            }
            
            // Check for suspicious patterns
            $suspiciousPatterns = [
                '/script/i',
                '/javascript/i',
                '/vbscript/i',
                '/<script/i',
                '/eval/i',
                '/expression/i',
                '/onload/i',
                '/onerror/i',
                '/onclick/i'
            ];
            
            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $languageCode)) {
                    Log::warning('Suspicious language code detected', [
                        'language_code' => $languageCode,
                        'pattern' => $pattern,
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent()
                    ]);
                    return false;
                }
            }
            
            // Additional security check for SQL injection patterns
            $sqlPatterns = [
                '/union/i',
                '/select/i',
                '/insert/i',
                '/update/i',
                '/delete/i',
                '/drop/i',
                '/create/i',
                '/alter/i'
            ];
            
            foreach ($sqlPatterns as $pattern) {
                if (preg_match($pattern, $languageCode)) {
                    Log::warning('Potential SQL injection pattern in language code', [
                        'language_code' => $languageCode,
                        'pattern' => $pattern,
                        'ip' => request()->ip()
                    ]);
                    return false;
                }
            }
            
            return true;
        } catch (Exception $e) {
            Log::error('Error validating language code format: ' . $e->getMessage(), [
                'language_code' => $languageCode,
                'type' => gettype($languageCode)
            ]);
            return false;
        }
    }
    
    /**
     * Set text direction based on language
     * 
     * @param string $languageCode
     * @return void
     */
    private function setTextDirection(string $languageCode): void
    {
        try {
            $language = Language::getByCode($languageCode);
            if ($language) {
                // Validate direction value
                $direction = $language->direction;
                if (in_array($direction, ['ltr', 'rtl'], true)) {
                    // Store direction in session for use in views
                    Session::put('text_direction', $direction);
                } else {
                    Log::warning('Invalid text direction for language', [
                        'language_code' => $languageCode,
                        'direction' => $direction
                    ]);
                    // Default to LTR
                    Session::put('text_direction', 'ltr');
                }
            }
        } catch (Exception $e) {
            Log::error('Error setting text direction: ' . $e->getMessage(), [
                'language_code' => $languageCode
            ]);
            // Default to LTR
            Session::put('text_direction', 'ltr');
        }
    }

    /**
     * Log language setting operations
     * 
     * @param Request $request
     * @param string $languageCode
     * @param string $status
     * @return void
     */
    private function logLanguageSet(Request $request, string $languageCode, string $status): void
    {
        try {
            $logData = [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'language_code' => $languageCode,
                'status' => $status,
                'timestamp' => now()->toISOString(),
                'referer' => $request->header('referer')
            ];

            // Add session data if available
            if ($request->hasSession()) {
                $logData['session_id'] = $request->session()->getId();
            }

            // Add user information if authenticated
            if (auth()->check()) {
                $user = auth()->user();
                $logData['user_id'] = $user->id ?? 'unknown';
                $logData['user_email'] = $user->email ?? 'unknown';
            }

            if ($status === 'success') {
                Log::info('Language set successfully', $logData);
            } elseif ($status === 'fallback') {
                Log::warning('Language fallback to default', $logData);
            }
        } catch (Exception $e) {
            Log::error('Error logging language set: ' . $e->getMessage());
        }
    }
}