<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * AiGenerateRequest handles validation for AI content generation
 * 
 * This request validates AI content generation parameters including field,
 * type, and title with security measures to prevent prompt injection.
 */
class AiGenerateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'field' => 'required|string|max:100|min:2|regex:/^[a-zA-Z0-9_\-\s]+$/',
            'type' => 'required|string|max:50|in:summary,description,title,content',
            'title' => 'nullable|string|max:255|min:2',
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
            'field.required' => 'The field parameter is required.',
            'field.min' => 'The field must be at least 2 characters.',
            'field.max' => 'The field may not be greater than 100 characters.',
            'field.regex' => 'The field contains invalid characters. Only letters, numbers, underscores, hyphens, and spaces are allowed.',
            'type.required' => 'The type parameter is required.',
            'type.in' => 'The type must be one of: summary, description, title, content.',
            'title.min' => 'The title must be at least 2 characters.',
            'title.max' => 'The title may not be greater than 255 characters.',
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
            'field' => 'field name',
            'type' => 'content type',
            'title' => 'content title',
        ];
    }

    /**
     * Prepare the data for validation.
     * 
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'field' => trim($this->field),
            'type' => trim($this->type),
            'title' => $this->title ? trim($this->title) : null,
        ]);
    }

    /**
     * Configure the validator instance.
     * 
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional security checks
            $this->validateSecurity($validator);
        });
    }

    /**
     * Validate security aspects of the request
     * 
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    private function validateSecurity($validator): void
    {
        $title = $this->input('title', '');
        
        // Check for potential prompt injection attempts
        $suspiciousPatterns = [
            '/ignore\s+previous\s+instructions/i',
            '/forget\s+everything/i',
            '/you\s+are\s+now/i',
            '/pretend\s+to\s+be/i',
            '/act\s+as\s+if/i',
            '/system\s*:/i',
            '/assistant\s*:/i',
            '/user\s*:/i',
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $title)) {
                $validator->errors()->add('title', 'The title contains potentially harmful content.');
                break;
            }
        }

        // Check for excessive length that might indicate abuse
        if (strlen($title) > 1000) {
            $validator->errors()->add('title', 'The title is too long and may indicate abuse.');
        }

        // Check for repeated characters (potential spam)
        if (preg_match('/(.)\1{10,}/', $title)) {
            $validator->errors()->add('title', 'The title contains suspicious repeated characters.');
        }
    }
}
