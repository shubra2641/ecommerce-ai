<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * SingleAddToCartRequest handles validation for single product addition to cart
 * 
 * This request validates single product addition to cart including slug,
 * quantity, and variant selection with security measures.
 */
class SingleAddToCartRequest extends FormRequest
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
            'quant' => 'required|array|min:1',
            'quant.*' => 'required|integer|min:1|max:100',
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
            'quant.required' => 'The quantity field is required.',
            'quant.array' => 'The quantity must be an array.',
            'quant.min' => 'At least one quantity must be provided.',
            'quant.*.required' => 'Each quantity is required.',
            'quant.*.integer' => 'Each quantity must be a valid number.',
            'quant.*.min' => 'Each quantity must be at least 1.',
            'quant.*.max' => 'Each quantity may not be greater than 100.',
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
            'quant' => 'quantity',
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
