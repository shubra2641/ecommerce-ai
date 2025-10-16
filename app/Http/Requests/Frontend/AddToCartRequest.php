<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * AddToCartRequest handles validation for adding products to cart
 * 
 * This request validates product addition to cart including slug
 * and variant selection with security measures.
 */
class AddToCartRequest extends FormRequest
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
            'slug.exists' => 'The selected product does not exist.',
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
        $this->merge([
            'slug' => trim($this->slug),
        ]);
    }
}
