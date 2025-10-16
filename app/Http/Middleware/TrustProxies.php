<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * TrustProxies Middleware
 * 
 * This middleware extends Laravel's default trusted proxies middleware
 * to provide enhanced security, logging, and configuration options
 * for proxy detection and trust management.
 */
class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     * 
     * These proxies will be trusted to provide accurate client information
     * including IP addresses, host headers, and protocol information.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     * 
     * These headers will be used to extract client information
     * from proxy requests with enhanced security validation.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Handle an incoming request.
     * 
     * Enhanced with comprehensive logging and security checks for
     * proxy detection and trust management.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        try {
            // Log proxy detection attempt
            $this->logProxyDetection($request);
            
            // Validate proxy headers
            $this->validateProxyHeaders($request);
            
            return parent::handle($request, $next);
        } catch (Exception $e) {
            Log::error('Error in TrustProxies middleware: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Continue with request even if proxy detection fails
            return $next($request);
        }
    }

    /**
     * Log proxy detection attempt
     * 
     * @param Request $request
     * @return void
     */
    private function logProxyDetection(Request $request): void
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

            // Add proxy headers if present
            $proxyHeaders = $this->extractProxyHeaders($request);
            if (!empty($proxyHeaders)) {
                $logData['proxy_headers'] = $proxyHeaders;
                $logData['has_proxy'] = true;
            } else {
                $logData['has_proxy'] = false;
            }

            // Add session data if available
            if ($request->hasSession()) {
                $logData['session_id'] = $request->session()->getId();
            }

            Log::info('Proxy detection attempt', $logData);
        } catch (Exception $e) {
            Log::error('Error logging proxy detection: ' . $e->getMessage());
        }
    }

    /**
     * Get proxy headers from request
     * 
     * @param Request $request
     * @return array<string, string>
     */
    private function extractProxyHeaders(Request $request): array
    {
        $proxyHeaders = [];
        
        $headers = [
            'X-Forwarded-For' => 'HTTP_X_FORWARDED_FOR',
            'X-Forwarded-Host' => 'HTTP_X_FORWARDED_HOST',
            'X-Forwarded-Port' => 'HTTP_X_FORWARDED_PORT',
            'X-Forwarded-Proto' => 'HTTP_X_FORWARDED_PROTO',
            'X-Forwarded-Aws-Elb' => 'HTTP_X_FORWARDED_AWS_ELB',
            'X-Real-IP' => 'HTTP_X_REAL_IP',
            'X-Client-IP' => 'HTTP_X_CLIENT_IP',
            'CF-Connecting-IP' => 'HTTP_CF_CONNECTING_IP'
        ];
        
        foreach ($headers as $header => $serverVar) {
            $value = $request->header($header) ?: $request->server($serverVar);
            if ($value) {
                $proxyHeaders[$header] = $value;
            }
        }
        
        return $proxyHeaders;
    }

    /**
     * Validate proxy headers for security
     * 
     * @param Request $request
     * @return void
     */
    private function validateProxyHeaders(Request $request): void
    {
        try {
            $proxyHeaders = $this->extractProxyHeaders($request);
            
            foreach ($proxyHeaders as $header => $value) {
                // Check for suspicious content in proxy headers
                if ($this->isSuspiciousProxyHeader($header, $value)) {
                    Log::warning('Suspicious proxy header detected', [
                        'header' => $header,
                        'value' => $value,
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]);
                }
                
                // Validate IP addresses in X-Forwarded-For
                if ($header === 'X-Forwarded-For') {
                    $this->validateForwardedFor($value, $request);
                }
                
                // Validate protocol in X-Forwarded-Proto
                if ($header === 'X-Forwarded-Proto') {
                    $this->validateForwardedProto($value, $request);
                }
            }
        } catch (Exception $e) {
            Log::error('Error validating proxy headers: ' . $e->getMessage());
        }
    }

    /**
     * Check if proxy header contains suspicious content
     * 
     * @param string $header
     * @param string $value
     * @return bool
     */
    private function isSuspiciousProxyHeader(string $header, string $value): bool
    {
        try {
            $suspiciousPatterns = [
                '/<script/i',
                '/javascript:/i',
                '/vbscript:/i',
                '/onload=/i',
                '/onerror=/i',
                '/eval\(/i',
                '/expression\(/i',
                '/document\./i',
                '/window\./i',
                '/alert\(/i',
                '/confirm\(/i',
                '/prompt\(/i'
            ];
            
            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    return true;
                }
            }
            
            return false;
        } catch (Exception $e) {
            Log::error('Error checking suspicious proxy header: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate X-Forwarded-For header
     * 
     * @param string $value
     * @param Request $request
     * @return void
     */
    private function validateForwardedFor(string $value, Request $request): void
    {
        try {
            $ips = explode(',', $value);
            $ips = array_map('trim', $ips);
            
            foreach ($ips as $ip) {
                if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                    Log::warning('Invalid IP in X-Forwarded-For header', [
                        'ip' => $ip,
                        'full_value' => $value,
                        'client_ip' => $request->ip()
                    ]);
                }
            }
            
            // Check for too many IPs (potential abuse)
            if (count($ips) > 10) {
                Log::warning('Too many IPs in X-Forwarded-For header', [
                    'ip_count' => count($ips),
                    'client_ip' => $request->ip()
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error validating X-Forwarded-For: ' . $e->getMessage());
        }
    }

    /**
     * Validate X-Forwarded-Proto header
     * 
     * @param string $value
     * @param Request $request
     * @return void
     */
    private function validateForwardedProto(string $value, Request $request): void
    {
        try {
            $allowedProtocols = ['http', 'https'];
            $protocol = strtolower(trim($value));
            
            if (!in_array($protocol, $allowedProtocols, true)) {
                Log::warning('Invalid protocol in X-Forwarded-Proto header', [
                    'protocol' => $protocol,
                    'client_ip' => $request->ip()
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error validating X-Forwarded-Proto: ' . $e->getMessage());
        }
    }

    /**
     * Get the list of trusted proxies
     * 
     * @return array<int, string>|string|null
     */
    public function getTrustedProxies()
    {
        return $this->proxies;
    }

    /**
     * Get the proxy headers configuration
     * 
     * @return int
     */
    public function getProxyHeaders(): int
    {
        return $this->headers;
    }

    /**
     * Set trusted proxies
     * 
     * @param array<int, string>|string|null $proxies
     * @return void
     */
    public function setTrustedProxies($proxies): void
    {
        try {
            $this->proxies = $proxies;
            
            Log::info('Trusted proxies updated', [
                'proxies' => $proxies
            ]);
        } catch (Exception $e) {
            Log::error('Error setting trusted proxies: ' . $e->getMessage());
        }
    }

    /**
     * Add a trusted proxy
     * 
     * @param string $proxy
     * @return void
     */
    public function addTrustedProxy(string $proxy): void
    {
        try {
            if (is_array($this->proxies)) {
                if (!in_array($proxy, $this->proxies, true)) {
                    $this->proxies[] = $proxy;
                }
            } else {
                $this->proxies = [$proxy];
            }
            
            Log::info('Trusted proxy added', [
                'proxy' => $proxy
            ]);
        } catch (Exception $e) {
            Log::error('Error adding trusted proxy: ' . $e->getMessage());
        }
    }

    /**
     * Check if a proxy is trusted
     * 
     * @param string $proxy
     * @return bool
     */
    public function isProxyTrusted(string $proxy): bool
    {
        try {
            if (is_array($this->proxies)) {
                return in_array($proxy, $this->proxies, true);
            } elseif (is_string($this->proxies)) {
                return $proxy === $this->proxies;
            }
            
            return false;
        } catch (Exception $e) {
            Log::error('Error checking if proxy is trusted: ' . $e->getMessage());
            return false;
        }
    }
}
