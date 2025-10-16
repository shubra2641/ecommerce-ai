<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ProductReviewUpdateRequest handles validation for product review updates
 * 
 * This request validates product review update data including rating,
 * review content, and status with proper security measures.
 */
class ProductReviewUpdateRequest extends FormRequest
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
            'rate' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|min:10|max:1000',
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
            'rate.required' => 'The rating field is required.',
            'rate.integer' => 'The rating must be a valid number.',
            'rate.min' => 'The rating must be at least 1 star.',
            'rate.max' => 'The rating may not be greater than 5 stars.',
            'review.min' => 'The review must be at least 10 characters.',
            'review.max' => 'The review may not be greater than 1000 characters.',
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
            'rate' => 'rating',
            'review' => 'review content',
            'name' => 'reviewer name',
            'email' => 'email address',
            'status' => 'review status',
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
            'review' => $this->review ? trim($this->review) : null,
            'name' => $this->name ? trim($this->name) : null,
            'email' => $this->email ? strtolower(trim($this->email)) : null,
        ]);
    }
}
