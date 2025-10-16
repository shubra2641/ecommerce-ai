<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * WishlistAddRequest handles validation for adding products to wishlist
 * 
 * This request validates product addition to wishlist including slug
 * and variant selection with security measures.
 */
class WishlistAddRequest extends FormRequest
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
            'slug' => 'required|string|max:255|exists:products,slug',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
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
            'slug.required' => 'The product slug is required.',
            'slug.string' => 'The product slug must be a string.',
            'slug.max' => 'The product slug may not be greater than 255 characters.',
            'slug.exists' => 'The selected product does not exist.',
            'variant_id.integer' => 'The product variant ID must be a valid number.',
            'variant_id.exists' => 'The selected product variant does not exist.',
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
            'slug' => 'product',
            'variant_id' => 'product variant',
        ];
    }

    /**
     * Prepare the data for validation.
     * 
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $slug = $this->slug ?? $this->route('slug');
        
        if ($slug) {
            $this->merge([
                'slug' => trim($slug)
            ]);
        }
    }
}
