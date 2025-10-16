<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Admin Middleware
 * 
 * This middleware ensures that only authenticated users with admin role
 * can access protected admin routes. It provides comprehensive error handling,
 * logging, and security validation.
 * 
 * @package App\Http\Middleware
 * @author Laravel Application
 * @version 1.0.0
 * @since 1.0.0
 */
class Admin
{
    /**
     * Handle an incoming request.
     * 
     * Checks if the user is authenticated and has admin role.
     * Redirects unauthorized users with appropriate error messages.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                Log::warning('Unauthenticated user attempted to access admin area', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->fullUrl()
                ]);
                
                $request->session()->flash('error', 'Please login to access this page');
                return redirect()->route('login');
            }

            $user = Auth::user();
            
            // Validate user object
            if (!$user) {
                Log::error('Authenticated user object is null', [
                    'user_id' => Auth::id(),
                    'ip' => $request->ip()
                ]);
                
                $request->session()->flash('error', 'Authentication error. Please login again.');
                return redirect()->route('login');
            }

            // Check if user has admin role
            if ($this->isAdmin($user)) {
                Log::info('Admin user accessed protected area', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl()
                ]);
                
                return $next($request);
            }

            // User is authenticated but not admin
            Log::warning('Non-admin user attempted to access admin area', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);

            $request->session()->flash('error', 'You do not have permission to access this page');
            
            // Redirect based on user role
            return $this->redirectBasedOnRole($user);

        } catch (Exception $e) {
            Log::error('Error in Admin middleware: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'trace' => $e->getTraceAsString()
            ]);

            $request->session()->flash('error', 'An error occurred. Please try again.');
            return redirect()->route('login');
        }
    }

    /**
     * Check if user has admin role
     * 
     * @param mixed $user The user object to check
     * @return bool True if user is admin, false otherwise
     * @throws Exception If role checking fails
     */
    private function isAdmin($user): bool
    {
        try {
            // Validate user object
            if (!$user || !is_object($user)) {
                Log::warning('Invalid user object provided to isAdmin', [
                    'user_type' => gettype($user)
                ]);
                return false;
            }

            // Validate user object has role property
            if (!isset($user->role) || !is_string($user->role)) {
                Log::warning('User role is not set or invalid', [
                    'user_id' => $user->id ?? 'unknown',
                    'role' => $user->role ?? 'null',
                    'role_type' => gettype($user->role ?? null)
                ]);
                return false;
            }

            // Sanitize role value
            $role = trim($user->role);
            
            // Additional security validation
            if (empty($role) || strlen($role) > 50) {
                Log::warning('Invalid role length or empty role', [
                    'user_id' => $user->id ?? 'unknown',
                    'role_length' => strlen($role)
                ]);
                return false;
            }

            // Check for suspicious patterns in role
            if (preg_match('/[<>"\']/', $role)) {
                Log::warning('Suspicious characters in user role', [
                    'user_id' => $user->id ?? 'unknown',
                    'role' => $role
                ]);
                return false;
            }

            // Check for admin role (case-insensitive)
            $isAdmin = strtolower($role) === 'admin';
            
            if ($isAdmin) {
                Log::info('Admin role confirmed', [
                    'user_id' => $user->id ?? 'unknown',
                    'user_email' => $user->email ?? 'unknown'
                ]);
            }
            
            return $isAdmin;
        } catch (Exception $e) {
            Log::error('Error checking admin role: ' . $e->getMessage(), [
                'user_id' => $user->id ?? 'unknown',
                'user_type' => gettype($user)
            ]);
            return false;
        }
    }

    /**
     * Redirect user based on their role
     * 
     * @param mixed $user
     * @return RedirectResponse
     */
    private function redirectBasedOnRole($user): RedirectResponse
    {
        try {
            $role = strtolower(trim($user->role ?? 'user'));
            
            // Define role-based redirects
            $roleRoutes = [
                'user' => 'user.dashboard',
                'customer' => 'user.dashboard',
                'member' => 'user.dashboard',
                'guest' => 'login'
            ];

            $route = $roleRoutes[$role] ?? 'user.dashboard';
            
            // Check if route exists
            if (\Route::has($route)) {
                return redirect()->route($route);
            }
            
            // Fallback to home
            return redirect()->route('home');
        } catch (Exception $e) {
            Log::error('Error redirecting based on role: ' . $e->getMessage(), [
                'user_id' => $user->id ?? 'unknown',
                'role' => $user->role ?? 'null'
            ]);
            
            return redirect()->route('home');
        }
    }
}
