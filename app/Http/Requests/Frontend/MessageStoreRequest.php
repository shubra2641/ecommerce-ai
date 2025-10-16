<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * MessageStoreRequest handles validation for message creation
 * 
 * This request validates message creation data including name, email,
 * subject, message content, and phone with security measures.
 */
class MessageStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
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
            'name.regex' => 'The name may only contain letters and spaces.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'message.required' => 'The message field is required.',
            'message.min' => 'The message must be at least 20 characters.',
            'message.max' => 'The message may not be greater than 1000 characters.',
            'subject.required' => 'The subject field is required.',
            'subject.min' => 'The subject must be at least 5 characters.',
            'subject.max' => 'The subject may not be greater than 255 characters.',
            'phone.required' => 'The phone number field is required.',
            'phone.min' => 'The phone number must be at least 10 characters.',
            'phone.max' => 'The phone number may not be greater than 20 characters.',
            'phone.regex' => 'The phone number format is invalid.',
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
            'name' => trim($this->name),
            'email' => strtolower(trim($this->email)),
            'subject' => trim($this->subject),
            'message' => trim($this->message),
            'phone' => trim($this->phone),
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
            $this->validateSpam($validator);
        });
    }

    /**
     * Validate for spam patterns
     * 
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    private function validateSpam($validator): void
    {
        $message = $this->input('message', '');
        $subject = $this->input('subject', '');
        
        // Check for excessive links
        if (substr_count($message, 'http') > 3) {
            $validator->errors()->add('message', 'Message contains too many links.');
        }
        
        // Check for repeated characters (potential spam)
        if (preg_match('/(.)\1{10,}/', $message)) {
            $validator->errors()->add('message', 'Message contains suspicious repeated characters.');
        }
        
        // Check for common spam words
        $spamWords = ['viagra', 'casino', 'lottery', 'winner', 'congratulations', 'click here', 'free money'];
        $messageLower = strtolower($message);
        foreach ($spamWords as $spamWord) {
            if (strpos($messageLower, $spamWord) !== false) {
                $validator->errors()->add('message', 'Message contains potentially spam content.');
                break;
            }
        }
    }
}
