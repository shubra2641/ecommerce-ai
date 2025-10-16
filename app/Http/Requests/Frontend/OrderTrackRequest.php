<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * OrderTrackRequest handles validation for order tracking
 * 
 * This request validates order tracking with proper authorization
 * and security measures.
 */
class OrderTrackRequest extends FormRequest
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
            'order_number' => 'required|string|max:255|min:5|regex:/^[A-Z0-9\-_]+$/',
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
            'order_number.required' => 'The order number field is required.',
            'order_number.string' => 'The order number must be a string.',
            'order_number.min' => 'The order number must be at least 5 characters.',
            'order_number.max' => 'The order number may not be greater than 255 characters.',
            'order_number.regex' => 'The order number may only contain uppercase letters, numbers, hyphens, and underscores.',
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
            'order_number' => 'order number',
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
            'order_number' => strtoupper(trim($this->order_number)),
        ]);
    }
}