<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * WishlistStoreRequest handles validation for wishlist item creation
 * 
 * This request validates wishlist item creation with proper authorization
 * and security measures.
 */
class WishlistStoreRequest extends FormRequest
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
        ];
    }

    /**
     * Prepare the data for validation.
     * Ensure slug from route parameter /wishlist/{slug} is merged into request data
     * so validation succeeds even if slug not sent as query/body param.
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
