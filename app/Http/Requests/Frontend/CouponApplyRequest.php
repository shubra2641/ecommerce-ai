<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CouponApplyRequest handles validation for coupon application
 * 
 * This request validates coupon code application with security measures
 * and proper authorization.
 */
class CouponApplyRequest extends FormRequest
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
            'code' => 'required|string|max:255|min:3|regex:/^[A-Z0-9\-_]+$/',
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
            'code.required' => 'The coupon code field is required.',
            'code.string' => 'The coupon code must be a string.',
            'code.min' => 'The coupon code must be at least 3 characters.',
            'code.max' => 'The coupon code may not be greater than 255 characters.',
            'code.regex' => 'The coupon code may only contain uppercase letters, numbers, hyphens, and underscores.',
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
            'code' => 'coupon code',
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
            'code' => strtoupper(trim($this->code)),
        ]);
    }
}
