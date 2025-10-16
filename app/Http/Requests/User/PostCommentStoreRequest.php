<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PostCommentStoreRequest handles validation for post comment creation
 * 
 * This request validates post comment creation data including post ID,
 * comment content, and user authentication with security measures.
 */
class PostCommentStoreRequest extends FormRequest
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
            'post_id' => 'required|integer|exists:posts,id',
            'comment' => 'required|string|min:10|max:1000',
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
            'post_id.required' => 'The post field is required.',
            'post_id.exists' => 'The selected post does not exist.',
            'comment.required' => 'The comment field is required.',
            'comment.min' => 'The comment must be at least 10 characters.',
            'comment.max' => 'The comment may not be greater than 1000 characters.',
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
            'post_id' => 'post',
            'comment' => 'comment content',
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
