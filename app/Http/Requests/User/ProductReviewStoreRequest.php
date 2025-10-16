<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ProductReviewStoreRequest handles validation for product review creation
 * 
 * This request validates product review creation data including rating,
 * review content, and product association with security measures.
 */
class ProductReviewStoreRequest extends FormRequest
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
            'product_id' => 'required|integer|exists:products,id',
            'rate' => 'required|integer|min:1|max:5',
            'review' => 'required|string|min:10|max:1000',
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
            'product_id.required' => 'The product field is required.',
            'product_id.exists' => 'The selected product does not exist.',
            'rate.required' => 'The rating field is required.',
            'rate.min' => 'The rating must be at least 1 star.',
            'rate.max' => 'The rating may not be greater than 5 stars.',
            'review.required' => 'The review field is required.',
            'review.min' => 'The review must be at least 10 characters.',
            'review.max' => 'The review may not be greater than 1000 characters.',
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
            'product_id' => 'product',
            'rate' => 'rating',
            'review' => 'review content',
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
            'review' => trim($this->review),
        ]);
    }
}
