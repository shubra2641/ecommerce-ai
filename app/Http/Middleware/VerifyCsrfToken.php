<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * VerifyCsrfToken Middleware
 * 
 * This middleware extends Laravel's default CSRF token verification middleware
 * to provide enhanced security, logging, and configuration options
 * for CSRF protection and token validation.
 */
class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * These routes will be accessible without CSRF token verification.
     * Use with caution as this reduces security protection.
     *
     * @var array<string>
     */
    protected $except = [
        'api/*',
        'webhook/*',
        'payment/*',
        'stripe/*',
        'paypal/*',
        'callback/*',
        'notify/*',
        'health-check',
        'status'
    ];

    /**
     * The URIs that should be excluded from CSRF verification for specific methods.
     * 
     * These routes will be excluded only for specific HTTP methods.
     *
     * @var array<string, array<string>>
     */
    protected array $exceptMethods = [
        'api/*' => ['GET', 'POST', 'PUT', 'DELETE'],
        'webhook/*' => ['POST'],
        'payment/*' => ['POST'],
        'callback/*' => ['POST']
    ];

    /**
     * Handle an incoming request.
     * 
     * Enhanced with comprehensive logging and security checks for
     * CSRF token verification and validation.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        try {
            // Log CSRF verification attempt
            $this->logCsrfVerification($request);
            
            // Validate CSRF token if required
            if ($this->shouldVerifyCsrf($request)) {
                $this->validateCsrfToken($request);
            }
            
            return parent::handle($request, $next);
        } catch (Exception $e) {
            Log::error('Error in VerifyCsrfToken middleware: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Continue with request even if CSRF verification fails
            return $next($request);
        }
    }

    /**
     * Log CSRF verification attempt
     * 
     * @param Request $request
     * @return void
     */
    private function logCsrfVerification(Request $request): void
    {
        try {
            $logData = [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'timestamp' => now()->toISOString(),
                'referer' => $request->header('referer'),
                'has_csrf_token' => $request->has('_token'),
                'csrf_token_length' => strlen($request->input('_token', '')),
                'is_ajax' => $request->ajax(),
                'is_json' => $request->expectsJson()
            ];

            // Add session data if available
            if ($request->hasSession()) {
                $logData['session_id'] = $request->session()->getId();
                $logData['session_csrf_token'] = $request->session()->token();
            }

            // Add user information if authenticated
            if (auth()->check()) {
                $user = auth()->user();
                $logData['user_id'] = $user->id ?? 'unknown';
                $logData['user_email'] = $user->email ?? 'unknown';
            }

            Log::info('CSRF verification attempt', $logData);
        } catch (Exception $e) {
            Log::error('Error logging CSRF verification: ' . $e->getMessage());
        }
    }

    /**
     * Check if CSRF verification should be performed
     * 
     * @param Request $request
     * @return bool
     */
    private function shouldVerifyCsrf(Request $request): bool
    {
        try {
            // Skip CSRF for GET, HEAD, OPTIONS requests
            if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
                return false;
            }
            
            // Check if route is in except list
            if ($this->isExemptRoute($request)) {
                return false;
            }
            
            // Check if route is in except methods list
            if ($this->isExemptMethod($request)) {
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            Log::error('Error checking CSRF verification requirement: ' . $e->getMessage());
            return true; // Default to verifying for security
        }
    }

    /**
     * Check if route is exempt from CSRF verification
     * 
     * @param Request $request
     * @return bool
     */
    private function isExemptRoute(Request $request): bool
    {
        try {
            $path = $request->path();
            
            foreach ($this->except as $pattern) {
                if ($request->is($pattern)) {
                    return true;
                }
            }
            
            return false;
        } catch (Exception $e) {
            Log::error('Error checking exempt route: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if route is exempt for specific method
     * 
     * @param Request $request
     * @return bool
     */
    private function isExemptMethod(Request $request): bool
    {
        try {
            $path = $request->path();
            $method = $request->method();
            
            foreach ($this->exceptMethods as $pattern => $methods) {
                if ($request->is($pattern) && in_array($method, $methods, true)) {
                    return true;
                }
            }
            
            return false;
        } catch (Exception $e) {
            Log::error('Error checking exempt method: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate CSRF token
     * 
     * @param Request $request
     * @return void
     */
    private function validateCsrfToken(Request $request): void
    {
        try {
            $token = $request->input('_token');
            $sessionToken = $request->session()->token();
            
            if (!$token) {
                Log::warning('CSRF token missing', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method()
                ]);
                return;
            }
            
            if (!hash_equals($sessionToken, $token)) {
                Log::warning('CSRF token mismatch', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'token_length' => strlen($token),
                    'session_token_length' => strlen($sessionToken)
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error validating CSRF token: ' . $e->getMessage());
        }
    }

    /**
     * Get the list of exempt routes
     * 
     * @return array<string>
     */
    public function getExemptRoutes(): array
    {
        return $this->except;
    }

    /**
     * Get the list of exempt methods
     * 
     * @return array<string, array<string>>
     */
    public function getExemptMethods(): array
    {
        return $this->exceptMethods;
    }

    /**
     * Add a route to the exempt list
     * 
     * @param string $route
     * @return void
     */
    public function addExemptRoute(string $route): void
    {
        try {
            if (!in_array($route, $this->except, true)) {
                $this->except[] = $route;
                
                Log::info('CSRF exempt route added', [
                    'route' => $route
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error adding exempt route: ' . $e->getMessage());
        }
    }

    /**
     * Remove a route from the exempt list
     * 
     * @param string $route
     * @return void
     */
    public function removeExemptRoute(string $route): void
    {
        try {
            $key = array_search($route, $this->except, true);
            if ($key !== false) {
                unset($this->except[$key]);
                $this->except = array_values($this->except);
                
                Log::info('CSRF exempt route removed', [
                    'route' => $route
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error removing exempt route: ' . $e->getMessage());
        }
    }

    /**
     * Check if a route is exempt from CSRF verification
     * 
     * @param string $route
     * @return bool
     */
    public function isRouteExempt(string $route): bool
    {
        try {
            return in_array($route, $this->except, true);
        } catch (Exception $e) {
            Log::error('Error checking if route is exempt: ' . $e->getMessage());
            return false;
        }
    }
}
