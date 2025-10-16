<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * CheckForMaintenanceMode Middleware
 * 
 * This middleware extends Laravel's default maintenance mode middleware
 * to provide enhanced security, logging, and configuration options
 * for maintenance mode access control.
 * 
 * @package App\Http\Middleware
 * @author Laravel Application
 * @version 1.0.0
 * @since 1.0.0
 */
class CheckForMaintenanceMode extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     * 
     * These routes will be accessible even when the application is in
     * maintenance mode. Useful for admin access, API endpoints, or
     * critical system functions.
     *
     * @var array<string>
     */
    protected $except = [
        'admin/*',
        'api/*',
        'login',
        'logout',
        'password/reset',
        'password/email',
        'password/reset/*',
        'health-check',
        'status'
    ];

    /**
     * The URIs that should be accessible only to specific IP addresses
     * during maintenance mode.
     * 
     * @var array<string, array<string>>
     */
    protected array $allowedIps = [
        'admin/*' => ['127.0.0.1', '::1'],
        'api/*' => ['127.0.0.1', '::1']
    ];

    /**
     * Handle an incoming request.
     * 
     * Enhanced with comprehensive logging and security checks for
     * maintenance mode access attempts.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        try {
            // Check if maintenance mode is enabled
            if ($this->app->isDownForMaintenance()) {
                $this->logMaintenanceAccess($request);
                
                // Check if request is from allowed IP
                if ($this->isAllowedIp($request)) {
                    Log::info('Maintenance mode access granted for allowed IP', [
                        'ip' => $request->ip(),
                        'url' => $request->fullUrl(),
                        'user_agent' => $request->userAgent()
                    ]);
                    return $next($request);
                }
                
                // Check if request is for excepted route
                if ($this->shouldPassThrough($request)) {
                    Log::info('Maintenance mode access granted for excepted route', [
                        'ip' => $request->ip(),
                        'url' => $request->fullUrl(),
                        'route' => $request->path()
                    ]);
                    return $next($request);
                }
                
                // Block access and show maintenance page
                Log::warning('Maintenance mode access blocked', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                    'user_agent' => $request->userAgent(),
                    'referer' => $request->header('referer')
                ]);
                
                return $this->maintenanceResponse($request);
            }
            
            return $next($request);
        } catch (Exception $e) {
            Log::error('Error in CheckForMaintenanceMode middleware: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // In case of error, allow request to proceed
            return $next($request);
        }
    }

    /**
     * Log maintenance mode access attempt
     * 
     * @param Request $request
     * @return void
     */
    private function logMaintenanceAccess(Request $request): void
    {
        try {
            $logData = [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'timestamp' => now()->toISOString(),
                'referer' => $request->header('referer'),
                'is_ajax' => $request->ajax(),
                'is_json' => $request->expectsJson()
            ];

            // Add session data if available
            if ($request->hasSession()) {
                $logData['session_id'] = $request->session()->getId();
            }

            Log::info('Maintenance mode access attempt', $logData);
        } catch (Exception $e) {
            Log::error('Error logging maintenance access: ' . $e->getMessage());
        }
    }

    /**
     * Check if the request is from an allowed IP address
     * 
     * @param Request $request The HTTP request object
     * @return bool True if IP is allowed, false otherwise
     * @throws Exception If IP checking fails
     */
    private function isAllowedIp(Request $request): bool
    {
        try {
            // Validate request object
            if (!$request || !is_object($request)) {
                Log::error('Invalid request object provided to isAllowedIp');
                return false;
            }

            $clientIp = $request->ip();
            $requestPath = $request->path();
            
            // Validate IP address
            if (empty($clientIp) || !is_string($clientIp)) {
                Log::warning('Invalid client IP address: ' . $clientIp);
                return false;
            }

            // Sanitize IP address
            $clientIp = trim($clientIp);
            
            // Additional IP validation
            if (!$this->isValidIpAddress($clientIp)) {
                Log::warning('Invalid IP address format: ' . $clientIp);
                return false;
            }
            
            // Check against allowed IPs
            foreach ($this->allowedIps as $path => $allowedIps) {
                if ($request->is($path)) {
                    $isAllowed = in_array($clientIp, $allowedIps, true);
                    
                    if ($isAllowed) {
                        Log::info('IP access granted during maintenance mode', [
                            'ip' => $clientIp,
                            'path' => $path,
                            'request_path' => $requestPath
                        ]);
                    }
                    
                    return $isAllowed;
                }
            }
            
            return false;
        } catch (Exception $e) {
            Log::error('Error checking allowed IP: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'path' => $request->path()
            ]);
            return false;
        }
    }

    /**
     * Check if the request should pass through maintenance mode
     * 
     * @param Request $request
     * @return bool
     */
    protected function shouldPassThrough(Request $request): bool
    {
        try {
            return parent::shouldPassThrough($request);
        } catch (Exception $e) {
            Log::error('Error checking pass through: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'path' => $request->path()
            ]);
            return false;
        }
    }

    /**
     * Get the maintenance mode response
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    protected function maintenanceResponse(Request $request)
    {
        try {
            // Check if request expects JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Service temporarily unavailable',
                    'message' => 'The application is currently in maintenance mode. Please try again later.',
                    'retry_after' => 300 // 5 minutes
                ], 503);
            }
            
            // Return HTML maintenance page
            return parent::maintenanceResponse($request);
        } catch (Exception $e) {
            Log::error('Error generating maintenance response: ' . $e->getMessage());
            
            // Fallback response
            return response('Service temporarily unavailable. Please try again later.', 503);
        }
    }

    /**
     * Get the maintenance mode view
     * 
     * @return string The view name for maintenance mode
     * @throws Exception If view retrieval fails
     */
    protected function getMaintenanceView(): string
    {
        try {
            // Check if custom maintenance view exists
            if (view()->exists('errors.503')) {
                return 'errors.503';
            }
            
            // Fallback to default Laravel maintenance view
            return parent::getMaintenanceView();
        } catch (Exception $e) {
            Log::error('Error getting maintenance view: ' . $e->getMessage());
            return 'errors.503';
        }
    }

    /**
     * Validate IP address format
     * 
     * @param string $ip The IP address to validate
     * @return bool True if IP is valid, false otherwise
     */
    private function isValidIpAddress(string $ip): bool
    {
        try {
            if (empty($ip) || !is_string($ip)) {
                return false;
            }

            // Check IP length
            if (strlen($ip) > 45) { // IPv6 max length
                return false;
            }

            // Check for suspicious patterns
            if (preg_match('/[<>"\']/', $ip)) {
                Log::warning('Suspicious characters in IP address: ' . $ip);
                return false;
            }

            // Validate IP format (IPv4 or IPv6)
            return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
        } catch (Exception $e) {
            Log::error('Error validating IP address: ' . $e->getMessage(), [
                'ip' => $ip
            ]);
            return false;
        }
    }

    /**
     * Get maintenance mode configuration
     * 
     * @return array Configuration array for maintenance mode
     */
    public function getMaintenanceConfig(): array
    {
        try {
            return [
                'except' => $this->except,
                'allowed_ips' => $this->allowedIps,
                'is_enabled' => $this->app->isDownForMaintenance()
            ];
        } catch (Exception $e) {
            Log::error('Error getting maintenance config: ' . $e->getMessage());
            return [
                'except' => [],
                'allowed_ips' => [],
                'is_enabled' => false
            ];
        }
    }
}
