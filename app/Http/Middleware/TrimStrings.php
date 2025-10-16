<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * TrimStrings Middleware
 * 
 * This middleware extends Laravel's default string trimming middleware
 * to provide enhanced security, logging, and configuration options
 * for input data sanitization and validation.
 */
class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * These attributes will be preserved with their original whitespace
     * and should only contain data that requires exact formatting.
     *
     * @var array<string>
     */
    protected $except = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'api_token',
        'secret_key',
        'private_key',
        'certificate',
        'signature',
        'hash',
        'token',
        'code',
        'verification_code',
        'otp',
        'pin',
        'ssn',
        'credit_card',
        'cvv',
        'expiry_date',
        'bank_account',
        'routing_number',
        'swift_code',
        'iban',
        'bic',
        'sort_code',
        'account_number',
        'card_number',
        'cvc',
        'security_code'
    ];

    /**
     * The names of attributes that should be trimmed with special handling.
     * 
     * These attributes will be trimmed but with additional validation
     * and security checks.
     *
     * @var array<string>
     */
    protected array $specialTrim = [
        'email',
        'username',
        'name',
        'title',
        'description',
        'content',
        'message',
        'comment',
        'review',
        'feedback',
        'address',
        'phone',
        'mobile',
        'website',
        'url',
        'slug',
        'meta_title',
        'meta_description',
        'meta_keywords'
    ];

    /**
     * Handle an incoming request.
     * 
     * Enhanced with comprehensive logging and security checks for
     * string trimming operations.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        try {
            // Log trimming operations if in debug mode
            if (config('app.debug')) {
                $this->logTrimmingOperations($request);
            }
            
            // Validate input data before trimming
            $this->validateInputData($request);
            
            return parent::handle($request, $next);
        } catch (Exception $e) {
            Log::error('Error in TrimStrings middleware: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Continue with request even if trimming fails
            return $next($request);
        }
    }

    /**
     * Log trimming operations for debugging
     * 
     * @param Request $request
     * @return void
     */
    private function logTrimmingOperations(Request $request): void
    {
        try {
            $inputData = $request->all();
            $trimmedCount = 0;
            $exceptedCount = 0;
            $specialTrimCount = 0;
            
            foreach ($inputData as $key => $value) {
                if (is_string($value)) {
                    if (in_array($key, $this->except, true)) {
                        $exceptedCount++;
                    } elseif (in_array($key, $this->specialTrim, true)) {
                        $specialTrimCount++;
                    } else {
                        $trimmedCount++;
                    }
                }
            }
            
            if ($trimmedCount > 0 || $exceptedCount > 0 || $specialTrimCount > 0) {
                $logData = [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'trimmed_fields' => $trimmedCount,
                    'excepted_fields' => $exceptedCount,
                    'special_trim_fields' => $specialTrimCount,
                    'total_string_fields' => $trimmedCount + $exceptedCount + $specialTrimCount
                ];

                Log::info('String trimming operations logged', $logData);
            }
        } catch (Exception $e) {
            Log::error('Error logging trimming operations: ' . $e->getMessage());
        }
    }

    /**
     * Validate input data before trimming
     * 
     * @param Request $request
     * @return void
     */
    private function validateInputData(Request $request): void
    {
        try {
            $inputData = $request->all();
            
            foreach ($inputData as $key => $value) {
                if (is_string($value)) {
                    // Check for suspicious content
                    if ($this->isSuspiciousContent($key, $value)) {
                        Log::warning('Suspicious content detected in input', [
                            'field' => $key,
                            'content_length' => strlen($value),
                            'ip' => $request->ip(),
                            'user_agent' => $request->userAgent()
                        ]);
                    }
                    
                    // Check for oversized content
                    if (strlen($value) > 10000) { // 10KB limit
                        Log::warning('Oversized content detected', [
                            'field' => $key,
                            'content_size' => strlen($value),
                            'ip' => $request->ip()
                        ]);
                    }
                    
                    // Check for special trim fields
                    if (in_array($key, $this->specialTrim, true)) {
                        $this->validateSpecialTrimField($key, $value, $request);
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('Error validating input data: ' . $e->getMessage());
        }
    }

    /**
     * Check if content is suspicious
     * 
     * @param string $field
     * @param string $value
     * @return bool
     */
    private function isSuspiciousContent(string $field, string $value): bool
    {
        try {
            $suspiciousPatterns = [
                '/<script/i',
                '/javascript:/i',
                '/vbscript:/i',
                '/onload=/i',
                '/onerror=/i',
                '/onclick=/i',
                '/onmouseover=/i',
                '/eval\(/i',
                '/expression\(/i',
                '/document\.cookie/i',
                '/document\.write/i',
                '/window\.location/i',
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
            Log::error('Error checking suspicious content: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate special trim fields
     * 
     * @param string $field
     * @param string $value
     * @param Request $request
     * @return void
     */
    private function validateSpecialTrimField(string $field, string $value, Request $request): void
    {
        try {
            switch ($field) {
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        Log::warning('Invalid email format detected', [
                            'field' => $field,
                            'value' => $value,
                            'ip' => $request->ip()
                        ]);
                    }
                    break;
                    
                case 'url':
                case 'website':
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        Log::warning('Invalid URL format detected', [
                            'field' => $field,
                            'value' => $value,
                            'ip' => $request->ip()
                        ]);
                    }
                    break;
                    
                case 'phone':
                case 'mobile':
                    if (!preg_match('/^[\+]?[0-9\s\-\(\)]+$/', $value)) {
                        Log::warning('Invalid phone format detected', [
                            'field' => $field,
                            'value' => $value,
                            'ip' => $request->ip()
                        ]);
                    }
                    break;
            }
        } catch (Exception $e) {
            Log::error('Error validating special trim field: ' . $e->getMessage(), [
                'field' => $field
            ]);
        }
    }

    /**
     * Get the list of fields that should not be trimmed
     * 
     * @return array<string>
     */
    public function getExceptedFields(): array
    {
        return $this->except;
    }

    /**
     * Get the list of fields that should be specially trimmed
     * 
     * @return array<string>
     */
    public function getSpecialTrimFields(): array
    {
        return $this->specialTrim;
    }

    /**
     * Add a field to the except list
     * 
     * @param string $field
     * @return void
     */
    public function addExceptedField(string $field): void
    {
        if (!in_array($field, $this->except, true)) {
            $this->except[] = $field;
        }
    }

    /**
     * Add a field to the special trim list
     * 
     * @param string $field
     * @return void
     */
    public function addSpecialTrimField(string $field): void
    {
        if (!in_array($field, $this->specialTrim, true)) {
            $this->specialTrim[] = $field;
        }
    }
}
