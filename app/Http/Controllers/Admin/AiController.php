<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Settings;
use Illuminate\Support\Facades\Http;
use Exception;
use App\Http\Requests\Admin\AiGenerateRequest;

/**
 * AiController handles AI-powered content generation
 * 
 * This controller manages AI content generation using various providers
 * including OpenAI and Azure OpenAI with secure API key handling.
 */
class AiController extends Controller
{
    /**
     * Generate AI content based on request parameters
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function generate(AiGenerateRequest $request): JsonResponse
    {
        try {
            // Use validated data from FormRequest
            $validated = $request->validated();

            // Get AI settings
            $settings = $this->getAiSettings();
            if (!$settings) {
                return $this->errorResponse('AI is not configured or disabled', 400);
            }

            // Generate secure prompt
            $prompt = $this->generatePrompt($validated['type'], $validated['title'] ?? 'Untitled');

            // Process based on provider
            $result = $this->processAiRequest($settings, $prompt);
            
            if ($result['success']) {
                return $this->successResponse($result['data']);
            } else {
                return $this->errorResponse($result['message'], $result['code']);
            }

        } catch (Exception $e) {
            \Log::error('AI generation error: ' . $e->getMessage(), [
                'request_data' => $request->only(['field', 'type', 'title']),
                'user_id' => auth()->id()
            ]);
            return $this->errorResponse('An error occurred while generating content', 500);
        }
    }

    /**
     * Get AI settings and validate configuration
     * 
     * @return array|null
     */
    private function getAiSettings(): ?array
    {
        try {
            $settings = Settings::first();
            
            if (empty($settings) || 
                !$settings->ai_enabled || 
                empty($settings->ai_provider) || 
                empty($settings->ai_api_key)) {
                return null;
            }

            return [
                'provider' => $settings->ai_provider,
                'api_key' => $settings->ai_api_key,
                'model' => $settings->ai_model ?: 'gpt-3.5-turbo',
                'max_tokens' => $settings->ai_max_tokens ?: 200,
                'temperature' => is_null($settings->ai_temperature) ? 0.7 : (float) $settings->ai_temperature,
                'azure_endpoint' => $settings->azure_endpoint,
                'azure_deployment' => $settings->azure_deployment,
            ];
        } catch (Exception $e) {
            \Log::error('Error fetching AI settings: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate secure prompt for AI
     * 
     * @param string $type
     * @param string $title
     * @return string
     */
    private function generatePrompt(string $type, string $title): string
    {
        // Sanitize inputs to prevent prompt injection
        $sanitizedType = strip_tags($type);
        $sanitizedTitle = strip_tags($title);
        
        return "Generate a {$sanitizedType} for a product/article named: \"{$sanitizedTitle}\". " .
               "Keep it concise, SEO friendly, and professional. " .
               "Do not include any harmful, inappropriate, or misleading content.";
    }

    /**
     * Process AI request based on provider
     * 
     * @param array $settings
     * @param string $prompt
     * @return array
     */
    private function processAiRequest(array $settings, string $prompt): array
    {
        try {
            switch ($settings['provider']) {
                case 'openai':
                    return $this->processOpenAiRequest($settings, $prompt);
                case 'azure':
                    return $this->processAzureRequest($settings, $prompt);
                default:
                    return ['success' => false, 'message' => 'Unsupported AI provider', 'code' => 400];
            }
        } catch (Exception $e) {
            \Log::error('AI request processing error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'AI request failed', 'code' => 500];
        }
    }

    /**
     * Process OpenAI API request
     * 
     * @param array $settings
     * @param string $prompt
     * @return array
     */
    private function processOpenAiRequest(array $settings, string $prompt): array
    {
        try {
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $settings['api_key'],
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $settings['model'],
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful content generator. Generate professional, SEO-friendly content.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => (int) $settings['max_tokens'],
                'temperature' => (float) $settings['temperature'],
            ]);

            if ($response->failed()) {
                \Log::error('OpenAI API error: ' . $response->body());
                return ['success' => false, 'message' => 'OpenAI API error', 'code' => 500];
            }

            $json = $response->json();
            $generated = $this->extractGeneratedContent($json);

            return ['success' => true, 'data' => trim($generated)];

        } catch (Exception $e) {
            \Log::error('OpenAI request error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'OpenAI request failed', 'code' => 500];
        }
    }

    /**
     * Process Azure OpenAI API request
     * 
     * @param array $settings
     * @param string $prompt
     * @return array
     */
    private function processAzureRequest(array $settings, string $prompt): array
    {
        try {
            if (empty($settings['azure_endpoint']) || empty($settings['azure_deployment'])) {
                return ['success' => false, 'message' => 'Azure configuration missing', 'code' => 400];
            }

            $azureEndpoint = rtrim($settings['azure_endpoint'], "/");
            $url = $azureEndpoint . "/openai/deployments/" . $settings['azure_deployment'] . "/chat/completions?api-version=2023-05-15";

            $response = Http::timeout(30)->withHeaders([
                'api-key' => $settings['api_key'],
                'Content-Type' => 'application/json',
            ])->post($url, [
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful content generator. Generate professional, SEO-friendly content.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => (int) $settings['max_tokens'],
                'temperature' => (float) $settings['temperature'],
            ]);

            if ($response->failed()) {
                \Log::error('Azure OpenAI API error: ' . $response->body());
                return ['success' => false, 'message' => 'Azure OpenAI API error', 'code' => 500];
            }

            $json = $response->json();
            $generated = $json['choices'][0]['message']['content'] ?? '';

            return ['success' => true, 'data' => trim($generated)];

        } catch (Exception $e) {
            \Log::error('Azure OpenAI request error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Azure OpenAI request failed', 'code' => 500];
        }
    }

    /**
     * Extract generated content from API response
     * 
     * @param array $json
     * @return string
     */
    private function extractGeneratedContent(array $json): string
    {
        if (isset($json['choices'][0]['message']['content'])) {
            return $json['choices'][0]['message']['content'];
        } elseif (isset($json['choices'][0]['text'])) {
            return $json['choices'][0]['text'];
        }
        
        return '';
    }

    /**
     * Return success JSON response
     * 
     * @param string $data
     * @return JsonResponse
     */
    private function successResponse(string $data): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    /**
     * Return error JSON response
     * 
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    private function errorResponse(string $message, int $code = 400): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message
        ], $code);
    }
}
