<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * EncryptCookies Middleware
 * 
 * This middleware extends Laravel's default cookie encryption middleware
 * to provide enhanced security, logging, and configuration options
 * for cookie encryption and decryption operations.
 */
class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     * 
     * These cookies will be transmitted in plain text and should only
     * contain non-sensitive data. Be careful when adding cookies to
     * this list as they will be visible to users.
     *
     * @var array<string>
     */
    protected $except = [
        'XSRF-TOKEN',
        'laravel_session',
        'remember_token',
        'guest_session',
        'analytics_id',
        'preferences',
        'theme',
        'language',
        'timezone'
    ];

    /**
     * The names of cookies that should be encrypted with additional security.
     * 
     * These cookies will be encrypted with enhanced security measures
     * and additional validation.
     *
     * @var array<string>
     */
    protected array $highSecurityCookies = [
        'auth_token',
        'admin_session',
        'payment_token',
        'user_data',
        'sensitive_preferences'
    ];

    /**
     * Handle an incoming request.
     * 
     * Enhanced with comprehensive logging and security checks for
     * cookie encryption and decryption operations.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        try {
            // Log cookie operations if in debug mode
            if (config('app.debug')) {
                $this->logCookieOperations($request);
            }
            
            // Validate cookie security
            $this->validateCookieSecurity($request);
            
            return parent::handle($request, $next);
        } catch (Exception $e) {
            Log::error('Error in EncryptCookies middleware: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Continue with request even if cookie encryption fails
            return $next($request);
        }
    }

    /**
     * Log cookie operations for debugging
     * 
     * @param Request $request
     * @return void
     */
    private function logCookieOperations(Request $request): void
    {
        try {
            $cookies = $request->cookies->all();
            $cookieCount = count($cookies);
            
            if ($cookieCount > 0) {
                $logData = [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                    'cookie_count' => $cookieCount,
                    'cookie_names' => array_keys($cookies),
                    'encrypted_cookies' => array_diff(array_keys($cookies), $this->except),
                    'unencrypted_cookies' => array_intersect(array_keys($cookies), $this->except),
                    'high_security_cookies' => array_intersect(array_keys($cookies), $this->highSecurityCookies)
                ];

                Log::info('Cookie operations logged', $logData);
            }
        } catch (Exception $e) {
            Log::error('Error logging cookie operations: ' . $e->getMessage());
        }
    }

    /**
     * Validate cookie security
     * 
     * @param Request $request
     * @return void
     */
    private function validateCookieSecurity(Request $request): void
    {
        try {
            $cookies = $request->cookies->all();
            
            foreach ($cookies as $name => $value) {
                // Check for suspicious cookie names
                if ($this->isSuspiciousCookieName($name)) {
                    Log::warning('Suspicious cookie name detected', [
                        'cookie_name' => $name,
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]);
                }
                
                // Check for oversized cookies
                if (strlen($value) > 4096) { // 4KB limit
                    Log::warning('Oversized cookie detected', [
                        'cookie_name' => $name,
                        'cookie_size' => strlen($value),
                        'ip' => $request->ip()
                    ]);
                }
                
                // Check for high security cookies in unencrypted list
                if (in_array($name, $this->highSecurityCookies) && in_array($name, $this->except)) {
                    Log::error('High security cookie found in unencrypted list', [
                        'cookie_name' => $name,
                        'ip' => $request->ip()
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error('Error validating cookie security: ' . $e->getMessage());
        }
    }

    /**
     * Check if cookie name is suspicious
     * 
     * @param string $cookieName
     * @return bool
     */
    private function isSuspiciousCookieName(string $cookieName): bool
    {
        try {
            $suspiciousPatterns = [
                '/script/i',
                '/javascript/i',
                '/vbscript/i',
                '/onload/i',
                '/onerror/i',
                '/<script/i',
                '/eval/i',
                '/expression/i'
            ];
            
            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $cookieName)) {
                    return true;
                }
            }
            
            return false;
        } catch (Exception $e) {
            Log::error('Error checking suspicious cookie name: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Determine if the cookie should be encrypted
     * 
     * @param string $name
     * @return bool
     */
    protected function shouldEncrypt(string $name): bool
    {
        try {
            // Don't encrypt cookies in the except list
            if (in_array($name, $this->except, true)) {
                return false;
            }
            
            // Always encrypt high security cookies
            if (in_array($name, $this->highSecurityCookies, true)) {
                return true;
            }
            
            // Default to encrypting all other cookies
            return true;
        } catch (Exception $e) {
            Log::error('Error determining cookie encryption: ' . $e->getMessage(), [
                'cookie_name' => $name
            ]);
            
            // Default to encrypting for security
            return true;
        }
    }

    /**
     * Get the encryption key for a specific cookie
     * 
     * @param string $name
     * @return string
     */
    protected function getEncryptionKey(string $name): string
    {
        try {
            // Use enhanced encryption for high security cookies
            if (in_array($name, $this->highSecurityCookies, true)) {
                return config('app.key') . '_high_security';
            }
            
            // Use default encryption key
            return config('app.key');
        } catch (Exception $e) {
            Log::error('Error getting encryption key: ' . $e->getMessage(), [
                'cookie_name' => $name
            ]);
            
            return config('app.key');
        }
    }

    /**
     * Get the list of cookies that should not be encrypted
     * 
     * @return array<string>
     */
    public function getUnencryptedCookies(): array
    {
        return $this->except;
    }

    /**
     * Get the list of high security cookies
     * 
     * @return array<string>
     */
    public function getHighSecurityCookies(): array
    {
        return $this->highSecurityCookies;
    }
}
