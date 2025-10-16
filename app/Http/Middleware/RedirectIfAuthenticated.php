<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * RedirectIfAuthenticated Middleware
 * 
 * This middleware redirects authenticated users away from authentication
 * pages (like login, register) to prevent them from accessing these pages
 * when they are already logged in. It provides enhanced security, logging,
 * and error handling for authentication redirection.
 * 
 * @package App\Http\Middleware
 * @author Laravel Application
 * @version 1.0.0
 * @since 1.0.0
 */
class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     * 
     * Checks if the user is authenticated and redirects them to the
     * appropriate home page if they are already logged in.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $guard = null)
    {
        try {
            // Validate guard parameter
            $guard = $this->validateGuard($guard);
            
            // Check if user is authenticated
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                
                // Log the redirect action
                $this->logAuthenticatedRedirect($request, $user, $guard);
                
                // Get appropriate redirect URL based on user role
                $redirectUrl = $this->getRedirectUrl($user, $request);
                
                return redirect($redirectUrl);
            }

            return $next($request);
        } catch (Exception $e) {
            Log::error('Error in RedirectIfAuthenticated middleware: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'guard' => $guard,
                'trace' => $e->getTraceAsString()
            ]);
            
            // In case of error, allow request to proceed
            return $next($request);
        }
    }

    /**
     * Validate and sanitize guard parameter
     * 
     * @param string|null $guard
     * @return string
     */
    private function validateGuard(?string $guard): string
    {
        try {
            if (empty($guard) || !is_string($guard)) {
                return 'web'; // Default guard
            }
            
            // Sanitize guard name
            $guard = trim($guard);
            
            // Validate guard exists
            $availableGuards = array_keys(config('auth.guards', []));
            if (!in_array($guard, $availableGuards, true)) {
                Log::warning('Invalid guard specified in RedirectIfAuthenticated', [
                    'guard' => $guard,
                    'available_guards' => $availableGuards
                ]);
                return 'web'; // Fallback to default guard
            }
            
            return $guard;
        } catch (Exception $e) {
            Log::error('Error validating guard: ' . $e->getMessage(), [
                'guard' => $guard
            ]);
            return 'web';
        }
    }

    /**
     * Log authenticated user redirect
     * 
     * @param Request $request
     * @param mixed $user
     * @param string $guard
     * @return void
     */
    private function logAuthenticatedRedirect(Request $request, $user, string $guard): void
    {
        try {
            $logData = [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'guard' => $guard,
                'timestamp' => now()->toISOString(),
                'referer' => $request->header('referer')
            ];

            // Add user information if available
            if ($user) {
                $logData['user_id'] = $user->id ?? 'unknown';
                $logData['user_email'] = $user->email ?? 'unknown';
                $logData['user_role'] = $user->role ?? 'unknown';
            }

            // Add session data if available
            if ($request->hasSession()) {
                $logData['session_id'] = $request->session()->getId();
            }

            Log::info('Authenticated user redirected from auth page', $logData);
        } catch (Exception $e) {
            Log::error('Error logging authenticated redirect: ' . $e->getMessage());
        }
    }

    /**
     * Get appropriate redirect URL based on user role
     * 
     * @param mixed $user
     * @param Request $request
     * @return string
     */
    private function getRedirectUrl($user, Request $request): string
    {
        try {
            // Check if user has role property
            if ($user && isset($user->role)) {
                $role = strtolower(trim($user->role));
                
                // Define role-based redirects
                $roleRedirects = [
                    'admin' => '/admin/dashboard',
                    'user' => '/user/dashboard',
                    'customer' => '/user/dashboard',
                    'member' => '/user/dashboard'
                ];
                
                if (isset($roleRedirects[$role])) {
                    $redirectUrl = $roleRedirects[$role];
                    
                    // Check if route exists
                    if ($this->isValidRoute($redirectUrl)) {
                        return $redirectUrl;
                    }
                }
            }
            
            // Check if there's a specific redirect in the request
            if ($request->has('redirect')) {
                $redirectUrl = $request->get('redirect');
                if ($this->isValidRedirectUrl($redirectUrl)) {
                    return $redirectUrl;
                }
            }
            
            // Check if there's a redirect in session
            if ($request->hasSession() && $request->session()->has('url.intended')) {
                $intendedUrl = $request->session()->get('url.intended');
                if ($this->isValidRedirectUrl($intendedUrl)) {
                    $request->session()->forget('url.intended');
                    return $intendedUrl;
                }
            }
            
            // Default redirect to home
            return RouteServiceProvider::HOME;
        } catch (Exception $e) {
            Log::error('Error getting redirect URL: ' . $e->getMessage(), [
                'user_id' => $user->id ?? 'unknown',
                'user_role' => $user->role ?? 'unknown'
            ]);
            
            return RouteServiceProvider::HOME;
        }
    }

    /**
     * Check if a route is valid
     * 
     * @param string $route The route name to check
     * @return bool True if route exists, false otherwise
     * @throws Exception If route checking fails
     */
    private function isValidRoute(string $route): bool
    {
        try {
            // Validate route name
            if (empty($route) || !is_string($route)) {
                Log::warning('Invalid route name provided: ' . $route);
                return false;
            }

            // Sanitize route name
            $route = trim($route);
            
            // Additional security validation
            if (strlen($route) > 100) {
                Log::warning('Route name too long: ' . $route);
                return false;
            }

            // Check for suspicious patterns
            if (preg_match('/[<>"\']/', $route)) {
                Log::warning('Suspicious characters in route name: ' . $route);
                return false;
            }

            return \Route::has($route);
        } catch (Exception $e) {
            Log::error('Error checking route validity: ' . $e->getMessage(), [
                'route' => $route
            ]);
            return false;
        }
    }

    /**
     * Check if a redirect URL is valid and safe
     * 
     * @param string $url The URL to validate
     * @return bool True if URL is valid and safe, false otherwise
     * @throws Exception If URL validation fails
     */
    private function isValidRedirectUrl(string $url): bool
    {
        try {
            // Validate URL parameter
            if (empty($url) || !is_string($url)) {
                Log::warning('Invalid URL provided for validation: ' . $url);
                return false;
            }

            // Sanitize URL
            $url = trim($url);
            
            // Check URL length
            if (strlen($url) > 2000) {
                Log::warning('URL too long: ' . substr($url, 0, 100) . '...');
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
            
            // Check if URL is relative (starts with /)
            if (strpos($url, '/') === 0) {
                return true;
            }
            
            // Check if URL is from same domain
            $parsedUrl = parse_url($url);
            if (isset($parsedUrl['host'])) {
                $currentHost = request()->getHost();
                
                // Validate host format
                if (!$this->isValidHost($parsedUrl['host'])) {
                    Log::warning('Invalid host in redirect URL: ' . $parsedUrl['host']);
                    return false;
                }
                
                return $parsedUrl['host'] === $currentHost;
            }
            
            return false;
        } catch (Exception $e) {
            Log::error('Error validating redirect URL: ' . $e->getMessage(), [
                'url' => substr($url, 0, 100)
            ]);
            return false;
        }
    }

    /**
     * Validate host format
     * 
     * @param string $host The host to validate
     * @return bool True if host is valid, false otherwise
     */
    private function isValidHost(string $host): bool
    {
        try {
            if (empty($host) || !is_string($host)) {
                return false;
            }

            // Check host length
            if (strlen($host) > 253) {
                return false;
            }

            // Check for suspicious patterns
            if (preg_match('/[<>"\']/', $host)) {
                Log::warning('Suspicious characters in host: ' . $host);
                return false;
            }

            // Basic host validation
            return preg_match('/^[a-zA-Z0-9.-]+$/', $host);
        } catch (Exception $e) {
            Log::error('Error validating host: ' . $e->getMessage(), [
                'host' => $host
            ]);
            return false;
        }
    }
}
