<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * MessageUpdateRequest handles validation for message updates via admin panel
 * 
 * This request validates message update data including name, email, message,
 * subject, phone, and status with comprehensive security measures.
 */
class MessageUpdateRequest extends FormRequest
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
            'name' => 'required|string|min:2|max:255|regex:/^[a-zA-Z\s\x{0600}-\x{06FF}]+$/u',
            'email' => 'required|email|max:255',
            'message' => 'required|string|min:20|max:1000',
            'subject' => 'required|string|min:5|max:255',
            'phone' => 'required|string|min:10|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'status' => 'nullable|in:read,unread,replied',
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
            'name.required' => 'The name field is required.',
            'name.min' => 'The name must be at least 2 characters.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'name.regex' => 'The name may only contain letters, spaces, and Arabic characters.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'message.required' => 'The message field is required.',
            'message.min' => 'The message must be at least 20 characters.',
            'message.max' => 'The message may not be greater than 1000 characters.',
            'subject.required' => 'The subject field is required.',
            'subject.min' => 'The subject must be at least 5 characters.',
            'subject.max' => 'The subject may not be greater than 255 characters.',
            'phone.required' => 'The phone field is required.',
            'phone.min' => 'The phone must be at least 10 characters.',
            'phone.max' => 'The phone may not be greater than 20 characters.',
            'phone.regex' => 'The phone format is invalid.',
            'status.in' => 'The status must be one of: read, unread, replied.',
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
            'name' => 'full name',
            'email' => 'email address',
            'message' => 'message content',
            'subject' => 'message subject',
            'phone' => 'phone number',
            'status' => 'message status',
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
            'name' => $this->name ? trim($this->name) : null,
            'email' => $this->email ? strtolower(trim($this->email)) : null,
            'subject' => $this->subject ? trim($this->subject) : null,
            'message' => $this->message ? trim($this->message) : null,
            'phone' => $this->phone ? trim($this->phone) : null,
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
            // Check for spam patterns
            $this->validateSpamPatterns($validator);
        });
    }

    /**
     * Validate spam patterns in message content
     * 
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    private function validateSpamPatterns($validator): void
    {
        $message = $this->input('message', '');
        $subject = $this->input('subject', '');
        
        // Check for excessive repetition
        if (preg_match('/(.)\1{10,}/', $message) || preg_match('/(.)\1{10,}/', $subject)) {
            $validator->errors()->add('message', 'The message contains suspicious repeated characters.');
        }
        
        // Check for excessive links
        $linkCount = preg_match_all('/https?:\/\/[^\s]+/', $message);
        if ($linkCount > 3) {
            $validator->errors()->add('message', 'The message contains too many links.');
        }
        
        // Check for excessive capitalization
        $capsCount = preg_match_all('/[A-Z]/', $message);
        $totalChars = strlen($message);
        if ($totalChars > 0 && ($capsCount / $totalChars) > 0.7) {
            $validator->errors()->add('message', 'The message contains excessive capitalization.');
        }
    }
}
