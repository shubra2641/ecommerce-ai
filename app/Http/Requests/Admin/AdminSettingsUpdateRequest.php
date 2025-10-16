<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * AdminSettingsUpdateRequest handles validation for admin settings updates
 * 
 * This request validates system settings including basic info, AI settings,
 * social login settings, and font configurations with comprehensive validation.
 */
class AdminSettingsUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Basic settings (supports multilingual arrays)
            'short_des' => 'nullable',
            'short_des.*' => 'nullable|string|max:255|min:3',
            'description' => 'nullable',
            'description.*' => 'nullable|string|max:2000|min:10',
            'photo' => 'required|string|max:500',
            'logo' => 'required|string|max:500',
            'address' => 'required|string|max:500|min:10',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',

            // AI settings
            'ai_enabled' => 'nullable|boolean',
            'ai_provider' => 'nullable|string|max:50|in:openai,azure',
            'ai_api_key' => 'nullable|string|max:255|required_if:ai_enabled,1',
            'ai_model' => 'nullable|string|max:100',
            'ai_max_tokens' => 'nullable|integer|min:1|max:4000',
            'ai_temperature' => 'nullable|numeric|min:0|max:2',
            'azure_endpoint' => 'nullable|string|max:255|url|required_if:ai_provider,azure',
            'azure_deployment' => 'nullable|string|max:100|required_if:ai_provider,azure',

            // Social login settings
            'google_login_enabled' => 'nullable|boolean',
            'google_client_id' => 'nullable|string|max:255|required_if:google_login_enabled,1',
            'google_client_secret' => 'nullable|string|max:255|required_if:google_login_enabled,1',
            'facebook_login_enabled' => 'nullable|boolean',
            'facebook_client_id' => 'nullable|string|max:255|required_if:facebook_login_enabled,1',
            'facebook_client_secret' => 'nullable|string|max:255|required_if:facebook_login_enabled,1',
            'github_login_enabled' => 'nullable|boolean',
            'github_client_id' => 'nullable|string|max:255|required_if:github_login_enabled,1',
            'github_client_secret' => 'nullable|string|max:255|required_if:github_login_enabled,1',

            // Font settings
            'frontend_font_family' => 'nullable|string|max:100',
            'frontend_font_size' => 'nullable|string|max:20',
            'frontend_font_weight' => 'nullable|string|max:20|in:normal,bold,bolder,lighter,100,200,300,400,500,600,700,800,900',
            'backend_font_family' => 'nullable|string|max:100',
            'backend_font_size' => 'nullable|string|max:20',
            'backend_font_weight' => 'nullable|string|max:20|in:normal,bold,bolder,lighter,100,200,300,400,500,600,700,800,900',
            'use_google_fonts' => 'nullable|boolean',
            'google_fonts_url' => 'nullable|url|max:500|required_if:use_google_fonts,1',
            // Multilingual site name
            'site_name' => 'nullable|array',
            'site_name.*' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     * 
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'short_des.required' => 'The short description field is required.',
            'short_des.min' => 'The short description must be at least 10 characters.',
            'description.required' => 'The description field is required.',
            'description.min' => 'The description must be at least 20 characters.',
            'address.required' => 'The address field is required.',
            'address.min' => 'The address must be at least 10 characters.',
            'phone.regex' => 'The phone number format is invalid.',
            'ai_api_key.required_if' => 'The AI API key is required when AI is enabled.',
            'azure_endpoint.required_if' => 'The Azure endpoint is required when using Azure provider.',
            'azure_deployment.required_if' => 'The Azure deployment is required when using Azure provider.',
            'google_client_id.required_if' => 'The Google client ID is required when Google login is enabled.',
            'google_client_secret.required_if' => 'The Google client secret is required when Google login is enabled.',
            'facebook_client_id.required_if' => 'The Facebook client ID is required when Facebook login is enabled.',
            'facebook_client_secret.required_if' => 'The Facebook client secret is required when Facebook login is enabled.',
            'github_client_id.required_if' => 'The GitHub client ID is required when GitHub login is enabled.',
            'github_client_secret.required_if' => 'The GitHub client secret is required when GitHub login is enabled.',
            'frontend_font_size.regex' => 'The frontend font size must be a valid CSS size value.',
            'backend_font_size.regex' => 'The backend font size must be a valid CSS size value.',
            'google_fonts_url.required_if' => 'The Google Fonts URL is required when using Google Fonts.',
        ];
    }

    

    /**
     * Get custom attributes for validator errors.
     * 
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'short_des' => 'short description',
            'photo' => 'photo URL',
            'logo' => 'logo URL',
            'ai_enabled' => 'AI enabled',
            'ai_provider' => 'AI provider',
            'ai_api_key' => 'AI API key',
            'ai_model' => 'AI model',
            'ai_max_tokens' => 'AI max tokens',
            'ai_temperature' => 'AI temperature',
            'azure_endpoint' => 'Azure endpoint',
            'azure_deployment' => 'Azure deployment',
            'google_login_enabled' => 'Google login enabled',
            'google_client_id' => 'Google client ID',
            'google_client_secret' => 'Google client secret',
            'facebook_login_enabled' => 'Facebook login enabled',
            'facebook_client_id' => 'Facebook client ID',
            'facebook_client_secret' => 'Facebook client secret',
            'github_login_enabled' => 'GitHub login enabled',
            'github_client_id' => 'GitHub client ID',
            'github_client_secret' => 'GitHub client secret',
            'frontend_font_family' => 'frontend font family',
            'frontend_font_size' => 'frontend font size',
            'frontend_font_weight' => 'frontend font weight',
            'backend_font_family' => 'backend font family',
            'backend_font_size' => 'backend font size',
            'backend_font_weight' => 'backend font weight',
            'use_google_fonts' => 'use Google Fonts',
            'google_fonts_url' => 'Google Fonts URL',
        ];
    }

    /**
     * Prepare the data for validation.
     * 
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Recursive trimmer: trims strings, maps over arrays
        $trimValue = function ($value) use (&$trimValue) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $value[$k] = $trimValue($v);
                }
                return $value;
            }
            if (is_string($value)) {
                return trim($value);
            }
            return $value;
        };

        $shortDes = $this->has('short_des') ? $trimValue($this->short_des) : null;
        $description = $this->has('description') ? $trimValue($this->description) : null;

        $this->merge([
            'short_des' => $shortDes,
            'description' => $description,
            'address' => $this->has('address') ? $trimValue($this->address) : null,
            'email' => $this->has('email') ? strtolower($trimValue($this->email)) : null,
            'phone' => $this->has('phone') ? $trimValue($this->phone) : null,
            'ai_api_key' => $this->has('ai_api_key') ? $trimValue($this->ai_api_key) : null,
            'azure_endpoint' => $this->has('azure_endpoint') ? rtrim($trimValue($this->azure_endpoint), '/') : null,
            'google_fonts_url' => $this->has('google_fonts_url') ? $trimValue($this->google_fonts_url) : null,
            // site_name may be an array of translations; keep as-is after trimming
            'site_name' => $this->has('site_name') ? $trimValue($this->site_name) : null,
        ]);
    }
}
