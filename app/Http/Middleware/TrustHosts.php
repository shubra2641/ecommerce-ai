<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * TrustHosts Middleware
 * 
 * This middleware extends Laravel's default trusted hosts middleware
 * to provide enhanced security, logging, and configuration options
 * for host validation and trust management.
 */
class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     * 
     * Enhanced with comprehensive security checks and logging for
     * trusted host validation and management.
     *
     * @return array<string>
     */
    public function hosts(): array
    {
        try {
            $trustedHosts = $this->getTrustedHosts();
            
            // Log trusted hosts configuration
            $this->logTrustedHosts($trustedHosts);
            
            return $trustedHosts;
        } catch (Exception $e) {
            Log::error('Error getting trusted hosts: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return minimal trusted hosts as fallback
            return $this->getFallbackTrustedHosts();
        }
    }

    /**
     * Get the list of trusted hosts with security validation
     * 
     * @return array<string>
     */
    private function getTrustedHosts(): array
    {
        $trustedHosts = [];
        
        // Add application subdomains
        $appSubdomains = $this->allSubdomainsOfApplicationUrl();
        if ($appSubdomains) {
            $trustedHosts[] = $appSubdomains;
        }
        
        // Add additional trusted hosts from configuration
        $additionalHosts = $this->getAdditionalTrustedHosts();
        $trustedHosts = array_merge($trustedHosts, $additionalHosts);
        
        // Add development hosts if in debug mode
        if (config('app.debug')) {
            $devHosts = $this->getDevelopmentHosts();
            $trustedHosts = array_merge($trustedHosts, $devHosts);
        }
        
        // Validate and sanitize hosts
        $trustedHosts = $this->validateTrustedHosts($trustedHosts);
        
        return array_unique($trustedHosts);
    }

    /**
     * Get additional trusted hosts from configuration
     * 
     * @return array<string>
     */
    private function getAdditionalTrustedHosts(): array
    {
        try {
            $configHosts = config('app.trusted_hosts', []);
            
            if (!is_array($configHosts)) {
                Log::warning('Invalid trusted_hosts configuration, expected array');
                return [];
            }
            
            return $configHosts;
        } catch (Exception $e) {
            Log::error('Error getting additional trusted hosts: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get development hosts for debug mode
     * 
     * @return array<string>
     */
    private function getDevelopmentHosts(): array
    {
        return [
            'localhost',
            '127.0.0.1',
            '::1',
            '*.localhost',
            '*.test',
            '*.dev',
            '*.local'
        ];
    }

    /**
     * Validate and sanitize trusted hosts
     * 
     * @param array<string> $hosts
     * @return array<string>
     */
    private function validateTrustedHosts(array $hosts): array
    {
        $validatedHosts = [];
        
        foreach ($hosts as $host) {
            if ($this->isValidHost($host)) {
                $validatedHosts[] = $host;
            } else {
                Log::warning('Invalid trusted host removed', [
                    'host' => $host
                ]);
            }
        }
        
        return $validatedHosts;
    }

    /**
     * Check if a host is valid and secure
     * 
     * @param string $host
     * @return bool
     */
    private function isValidHost(string $host): bool
    {
        try {
            // Check if host is empty
            if (empty($host) || !is_string($host)) {
                return false;
            }
            
            // Check for suspicious patterns
            if ($this->isSuspiciousHost($host)) {
                Log::warning('Suspicious host pattern detected', [
                    'host' => $host
                ]);
                return false;
            }
            
            // Check for valid host pattern
            if (!preg_match('/^[\w\-\.\*]+$/', $host)) {
                return false;
            }
            
            // Check for wildcard usage (only allow at the beginning)
            if (strpos($host, '*') !== false && strpos($host, '*') !== 0) {
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            Log::error('Error validating host: ' . $e->getMessage(), [
                'host' => $host
            ]);
            return false;
        }
    }

    /**
     * Check if host contains suspicious patterns
     * 
     * @param string $host
     * @return bool
     */
    private function isSuspiciousHost(string $host): bool
    {
        try {
            $suspiciousPatterns = [
                '/script/i',
                '/javascript/i',
                '/vbscript/i',
                '/<script/i',
                '/eval/i',
                '/expression/i',
                '/\.\./',
                '/\/\//',
                '/\0/',
                '/\x00/'
            ];
            
            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $host)) {
                    return true;
                }
            }
            
            return false;
        } catch (Exception $e) {
            Log::error('Error checking suspicious host: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get fallback trusted hosts for error scenarios
     * 
     * @return array<string>
     */
    private function getFallbackTrustedHosts(): array
    {
        return [
            'localhost',
            '127.0.0.1',
            '::1'
        ];
    }

    /**
     * Log trusted hosts configuration
     * 
     * @param array<string> $trustedHosts
     * @return void
     */
    private function logTrustedHosts(array $trustedHosts): void
    {
        try {
            $logData = [
                'trusted_hosts_count' => count($trustedHosts),
                'trusted_hosts' => $trustedHosts,
                'app_url' => config('app.url'),
                'app_env' => config('app.env'),
                'app_debug' => config('app.debug')
            ];

            Log::info('Trusted hosts configuration loaded', $logData);
        } catch (Exception $e) {
            Log::error('Error logging trusted hosts: ' . $e->getMessage());
        }
    }

    /**
     * Handle an incoming request with enhanced security
     * 
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        try {
            // Log host validation attempt
            $this->logHostValidation($request);
            
            return parent::handle($request, $next);
        } catch (Exception $e) {
            Log::error('Error in TrustHosts middleware: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'host' => $request->getHost(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Continue with request even if host validation fails
            return $next($request);
        }
    }

    /**
     * Log host validation attempt
     * 
     * @param Request $request
     * @return void
     */
    private function logHostValidation(Request $request): void
    {
        try {
            $logData = [
                'ip' => $request->ip(),
                'host' => $request->getHost(),
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

            Log::info('Host validation attempt', $logData);
        } catch (Exception $e) {
            Log::error('Error logging host validation: ' . $e->getMessage());
        }
    }

    /**
     * Get the list of currently trusted hosts
     * 
     * @return array<string>
     */
    public function getCurrentTrustedHosts(): array
    {
        return $this->hosts();
    }

    /**
     * Check if a host is trusted
     * 
     * @param string $host
     * @return bool
     */
    public function isHostTrusted(string $host): bool
    {
        try {
            $trustedHosts = $this->hosts();
            
            foreach ($trustedHosts as $trustedHost) {
                if ($this->matchesHost($host, $trustedHost)) {
                    return true;
                }
            }
            
            return false;
        } catch (Exception $e) {
            Log::error('Error checking if host is trusted: ' . $e->getMessage(), [
                'host' => $host
            ]);
            return false;
        }
    }

    /**
     * Check if a host matches a trusted host pattern
     * 
     * @param string $host
     * @param string $trustedHost
     * @return bool
     */
    private function matchesHost(string $host, string $trustedHost): bool
    {
        try {
            // Exact match
            if ($host === $trustedHost) {
                return true;
            }
            
            // Wildcard match
            if (strpos($trustedHost, '*') === 0) {
                $pattern = str_replace('*', '.*', $trustedHost);
                return preg_match('/^' . $pattern . '$/', $host);
            }
            
            return false;
        } catch (Exception $e) {
            Log::error('Error matching host: ' . $e->getMessage(), [
                'host' => $host,
                'trusted_host' => $trustedHost
            ]);
            return false;
        }
    }
}
