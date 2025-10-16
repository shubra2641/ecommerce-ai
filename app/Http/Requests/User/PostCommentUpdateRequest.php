<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PostCommentUpdateRequest handles validation for post comment updates
 * 
 * This request validates post comment update data including comment content,
 * name, email, and status with proper security measures.
 */
class PostCommentUpdateRequest extends FormRequest
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
            'comment' => 'required|string|min:10|max:1000',
            'name' => 'nullable|string|max:255|regex:/^[a-zA-Z\s\x{0600}-\x{06FF}]+$/u',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
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
            'comment.required' => 'The comment field is required.',
            'comment.min' => 'The comment must be at least 10 characters.',
            'comment.max' => 'The comment may not be greater than 1000 characters.',
            'name.regex' => 'The name may only contain letters and spaces.',
            'email.email' => 'The email must be a valid email address.',
            'status.required' => 'The status field is required.',
            'status.in' => 'The status must be either active or inactive.',
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
            'comment' => 'comment content',
            'name' => 'commenter name',
            'email' => 'email address',
            'status' => 'comment status',
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
            'comment' => trim($this->comment),
            'name' => $this->name ? trim($this->name) : null,
            'email' => $this->email ? strtolower(trim($this->email)) : null,
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
        $comment = $this->input('comment', '');
        
        // Check for excessive links
        if (substr_count($comment, 'http') > 2) {
            $validator->errors()->add('comment', 'Comment contains too many links.');
        }
        
        // Check for repeated characters (potential spam)
        if (preg_match('/(.)\1{10,}/', $comment)) {
            $validator->errors()->add('comment', 'Comment contains suspicious repeated characters.');
        }
        
        // Check for common spam words
        $spamWords = ['viagra', 'casino', 'lottery', 'winner', 'congratulations', 'click here', 'free money'];
        $commentLower = strtolower($comment);
        foreach ($spamWords as $spamWord) {
            if (strpos($commentLower, $spamWord) !== false) {
                $validator->errors()->add('comment', 'Comment contains potentially spam content.');
                break;
            }
        }
    }
}
