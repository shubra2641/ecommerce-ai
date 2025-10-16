<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * WishlistDeleteRequest handles validation for wishlist item deletion
 * 
 * This request validates wishlist item deletion with proper authorization
 * and security measures.
 */
class WishlistDeleteRequest extends FormRequest
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
            'id' => 'required|integer|min:1|exists:wishlists,id',
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
            'id.required' => 'The wishlist item ID is required.',
            'id.integer' => 'The wishlist item ID must be a valid number.',
            'id.min' => 'The wishlist item ID must be at least 1.',
            'id.exists' => 'The selected wishlist item does not exist.',
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
            'id' => 'wishlist item ID',
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
            'id' => (int) $this->id,
        ]);
    }
}
