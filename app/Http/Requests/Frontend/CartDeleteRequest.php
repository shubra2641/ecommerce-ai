<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CartDeleteRequest handles validation for cart item deletion
 * 
 * This request validates cart item deletion with proper authorization
 * and security measures.
 */
class CartDeleteRequest extends FormRequest
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
            'id' => 'required|integer|min:1|exists:carts,id',
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
            'id.required' => 'The cart item ID is required.',
            'id.integer' => 'The cart item ID must be a valid number.',
            'id.min' => 'The cart item ID must be at least 1.',
            'id.exists' => 'The selected cart item does not exist.',
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
            'id' => 'cart item ID',
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
