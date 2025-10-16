<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Authenticate Middleware
 * 
 * This middleware extends Laravel's default authentication middleware
 * to provide enhanced security, logging, and error handling for
 * unauthenticated access attempts.
 * 
 * @package App\Http\Middleware
 * @author Laravel Application
 * @version 1.0.0
 * @since 1.0.0
 */
class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     * 
     * Enhanced with comprehensive logging and security checks for
     * unauthenticated access attempts.
     *
     * @param Request $request
     * @return string|null
     */
    protected function redirectTo(Request $request): ?string
    {
        try {
            // Log unauthenticated access attempt
            $this->logUnauthenticatedAccess($request);
            
            // Check if request expects JSON response
            if (!$request->expectsJson()) {
                // Validate that login route exists
                if ($this->isValidRoute('login')) {
                    return route('login');
                }
                
                // Fallback to home route if login route doesn't exist
                Log::warning('Login route not found, redirecting to home', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl()
                ]);
                
                return $this->isValidRoute('home') ? route('home') : '/';
            }
            
            return null;
        } catch (Exception $e) {
            Log::error('Error in Authenticate middleware redirectTo: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback to login route or home
            return $this->isValidRoute('login') ? route('login') : '/';
        }
    }

    /**
     * Log unauthenticated access attempt
     * 
     * @param Request $request The HTTP request object
     * @return void
     * @throws Exception If logging fails
     */
    private function logUnauthenticatedAccess(Request $request): void
    {
        try {
            // Validate request object
            if (!$request || !is_object($request)) {
                Log::error('Invalid request object provided to logUnauthenticatedAccess');
                return;
            }

            $logData = [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'timestamp' => now()->toISOString(),
                'expects_json' => $request->expectsJson()
            ];

            // Add referer if available and valid
            if ($request->hasHeader('referer')) {
                $referer = $request->header('referer');
                // Validate referer URL for security
                if ($this->isValidUrl($referer)) {
                    $logData['referer'] = $referer;
                }
            }

            // Add session data if available
            if ($request->hasSession()) {
                $logData['session_id'] = $request->session()->getId();
            }

            // Add user information if available (for debugging)
            if (auth()->check()) {
                $user = auth()->user();
                $logData['user_id'] = $user->id ?? 'unknown';
                $logData['user_email'] = $user->email ?? 'unknown';
            }

            Log::warning('Unauthenticated access attempt', $logData);
        } catch (Exception $e) {
            Log::error('Error logging unauthenticated access: ' . $e->getMessage(), [
                'request_url' => $request->fullUrl() ?? 'unknown',
                'request_method' => $request->method() ?? 'unknown'
            ]);
        }
    }

    /**
     * Check if a route exists
     * 
     * @param string $routeName The route name to check
     * @return bool True if route exists, false otherwise
     * @throws Exception If route checking fails
     */
    private function isValidRoute(string $routeName): bool
    {
        try {
            // Validate route name
            if (empty($routeName) || !is_string($routeName)) {
                Log::warning('Invalid route name provided: ' . $routeName);
                return false;
            }

            // Sanitize route name
            $routeName = trim($routeName);
            
            // Additional security validation
            if (strlen($routeName) > 100) {
                Log::warning('Route name too long: ' . $routeName);
                return false;
            }

            // Check for suspicious patterns
            if (preg_match('/[<>"\']/', $routeName)) {
                Log::warning('Suspicious characters in route name: ' . $routeName);
                return false;
            }

            return \Route::has($routeName);
        } catch (Exception $e) {
            Log::error('Error checking route existence: ' . $e->getMessage(), [
                'route_name' => $routeName
            ]);
            return false;
        }
    }

    /**
     * Validate URL for security
     * 
     * @param string $url The URL to validate
     * @return bool True if URL is valid and safe, false otherwise
     */
    private function isValidUrl(string $url): bool
    {
        try {
            if (empty($url) || !is_string($url)) {
                return false;
            }

            // Check URL length
            if (strlen($url) > 2000) {
                Log::warning('URL too long: ' . substr($url, 0, 100) . '...');
                return false;
            }

            // Parse URL
            $parsedUrl = parse_url($url);
            if (!$parsedUrl) {
                return false;
            }

            // Check for suspicious schemes
            $allowedSchemes = ['http', 'https'];
            if (isset($parsedUrl['scheme']) && !in_array($parsedUrl['scheme'], $allowedSchemes)) {
                Log::warning('Suspicious URL scheme: ' . $parsedUrl['scheme']);
                return false;
            }

            // Check for suspicious patterns
            $suspiciousPatterns = [
                '/javascript:/i',
                '/data:/i',
                '/vbscript:/i',
                '/<script/i',
                '/eval\(/i'
            ];

            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $url)) {
                    Log::warning('Suspicious URL pattern detected: ' . substr($url, 0, 100));
                    return false;
                }
            }

            return true;
        } catch (Exception $e) {
            Log::error('Error validating URL: ' . $e->getMessage(), [
                'url' => substr($url, 0, 100)
            ]);
            return false;
        }
    }

    /**
     * Handle unauthenticated user
     * 
     * Override parent method to add enhanced logging and security checks
     *
     * @param Request $request
     * @param array $guards
     * @return void
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function unauthenticated($request, array $guards): void
    {
        try {
            // Log the unauthenticated attempt with guard information
            Log::warning('User unauthenticated for guards', [
                'guards' => $guards,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method()
            ]);

            // Call parent method to handle the exception
            parent::unauthenticated($request, $guards);
        } catch (Exception $e) {
            Log::error('Error in unauthenticated handler: ' . $e->getMessage(), [
                'guards' => $guards,
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            
            // Re-throw the exception
            throw $e;
        }
    }
}
