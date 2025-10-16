<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * User Middleware
 * 
 * This middleware ensures that only authenticated users can access
 * protected user routes. It provides enhanced security, logging,
 * and error handling for user authentication checks.
 */
class User
{
    /**
     * Handle an incoming request.
     * 
     * Checks if the user is authenticated and redirects them to login
     * if they are not authenticated. Enhanced with comprehensive
     * error handling and logging.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Check if user is authenticated using Laravel Auth
            if (!Auth::check()) {
                // Log unauthenticated access attempt
                $this->logUnauthenticatedAccess($request);
                
                // Store intended URL for redirect after login
                $this->storeIntendedUrl($request);
                
                return redirect()->route('login.form');
            }

            $user = Auth::user();
            
            // Validate user object
            if (!$user) {
                Log::error('Authenticated user object is null', [
                    'user_id' => Auth::id(),
                    'ip' => $request->ip()
                ]);
                
                return redirect()->route('login.form');
            }

            // Check if user is active
            if (!$this->isUserActive($user)) {
                Log::warning('Inactive user attempted to access protected area', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl()
                ]);
                
                Auth::logout();
                $request->session()->flash('error', 'Your account is inactive. Please contact support.');
                return redirect()->route('login.form');
            }

            // Log successful authentication
            $this->logAuthenticatedAccess($request, $user);
            
            return $next($request);
        } catch (Exception $e) {
            Log::error('Error in User middleware: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // In case of error, redirect to login for security
            return redirect()->route('login.form');
        }
    }

    /**
     * Log unauthenticated access attempt
     * 
     * @param Request $request
     * @return void
     */
    private function logUnauthenticatedAccess(Request $request): void
    {
        try {
            $logData = [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'timestamp' => now()->toISOString(),
                'referer' => $request->header('referer')
            ];

            // Add session data if available
            if ($request->hasSession()) {
                $logData['session_id'] = $request->session()->getId();
            }

            Log::warning('Unauthenticated user attempted to access protected area', $logData);
        } catch (Exception $e) {
            Log::error('Error logging unauthenticated access: ' . $e->getMessage());
        }
    }

    /**
     * Log authenticated access
     * 
     * @param Request $request
     * @param mixed $user
     * @return void
     */
    private function logAuthenticatedAccess(Request $request, $user): void
    {
        try {
            $logData = [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'timestamp' => now()->toISOString(),
                'user_id' => $user->id ?? 'unknown',
                'user_email' => $user->email ?? 'unknown',
                'user_role' => $user->role ?? 'unknown'
            ];

            // Add session data if available
            if ($request->hasSession()) {
                $logData['session_id'] = $request->session()->getId();
            }

            Log::info('Authenticated user accessed protected area', $logData);
        } catch (Exception $e) {
            Log::error('Error logging authenticated access: ' . $e->getMessage());
        }
    }

    /**
     * Store intended URL for redirect after login
     * 
     * @param Request $request
     * @return void
     */
    private function storeIntendedUrl(Request $request): void
    {
        try {
            if ($request->hasSession()) {
                $request->session()->put('url.intended', $request->fullUrl());
            }
        } catch (Exception $e) {
            Log::error('Error storing intended URL: ' . $e->getMessage());
        }
    }

    /**
     * Check if user is active
     * 
     * @param mixed $user
     * @return bool
     */
    private function isUserActive($user): bool
    {
        try {
            // Check if user has status property
            if (isset($user->status)) {
                return $user->status === 'active' || $user->status === 1;
            }
            
            // Check if user has is_active property
            if (isset($user->is_active)) {
                return $user->is_active === true || $user->is_active === 1;
            }
            
            // Default to active if no status field
            return true;
        } catch (Exception $e) {
            Log::error('Error checking user status: ' . $e->getMessage(), [
                'user_id' => $user->id ?? 'unknown'
            ]);
            return false;
        }
    }

    /**
     * Get the list of routes that should be accessible without authentication
     * 
     * @return array<string>
     */
    public function getExemptRoutes(): array
    {
        return [
            'login.form',
            'login',
            'register',
            'password.request',
            'password.email',
            'password.reset',
            'password.update',
            'verification.notice',
            'verification.verify',
            'verification.resend'
        ];
    }

    /**
     * Check if current route is exempt from authentication
     * 
     * @param Request $request
     * @return bool
     */
    public function isExemptRoute(Request $request): bool
    {
        try {
            $routeName = $request->route()?->getName();
            if (!$routeName) {
                return false;
            }
            
            return in_array($routeName, $this->getExemptRoutes(), true);
        } catch (Exception $e) {
            Log::error('Error checking exempt route: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user information for logging
     * 
     * @param mixed $user
     * @return array<string, mixed>
     */
    private function getUserInfo($user): array
    {
        try {
            return [
                'id' => $user->id ?? 'unknown',
                'email' => $user->email ?? 'unknown',
                'name' => $user->name ?? 'unknown',
                'role' => $user->role ?? 'unknown',
                'status' => $user->status ?? 'unknown',
                'last_login' => $user->last_login_at ?? 'unknown'
            ];
        } catch (Exception $e) {
            Log::error('Error getting user info: ' . $e->getMessage());
            return ['error' => 'Unable to get user info'];
        }
    }
}
