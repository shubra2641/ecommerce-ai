<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CouponUpdateRequest handles validation for coupon updates
 * 
 * This request validates coupon update data including code, type,
 * value, and usage limits with comprehensive validation.
 */
class CouponUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $couponId = $this->route('id');
        
        return [
            'code' => 'required|string|max:50|min:3|unique:coupons,code,' . $couponId . '|regex:/^[A-Z0-9\-_]+$/',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0.01|max:999999.99',
            'status' => 'required|in:active,inactive',
            'expiry_date' => 'nullable|date|after:today',
            'usage_limit' => 'nullable|integer|min:1|max:999999',
            'minimum_amount' => 'nullable|numeric|min:0|max:999999.99',
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
            'code.required' => 'The coupon code is required.',
            'code.min' => 'The coupon code must be at least 3 characters.',
            'code.max' => 'The coupon code may not be greater than 50 characters.',
            'code.unique' => 'A coupon with this code already exists.',
            'code.regex' => 'The coupon code may only contain uppercase letters, numbers, hyphens, and underscores.',
            'type.required' => 'The coupon type is required.',
            'type.in' => 'The coupon type must be either fixed or percent.',
            'value.required' => 'The coupon value is required.',
            'value.min' => 'The coupon value must be at least 0.01.',
            'value.max' => 'The coupon value may not be greater than 999999.99.',
            'status.required' => 'The coupon status is required.',
            'status.in' => 'The status must be either active or inactive.',
            'expiry_date.after' => 'The expiry date must be a date after today.',
            'usage_limit.min' => 'The usage limit must be at least 1.',
            'usage_limit.max' => 'The usage limit may not be greater than 999999.',
            'minimum_amount.min' => 'The minimum amount must be at least 0.',
            'minimum_amount.max' => 'The minimum amount may not be greater than 999999.99.',
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
            'type' => 'coupon type',
            'value' => 'coupon value',
            'status' => 'coupon status',
            'expiry_date' => 'expiry date',
            'usage_limit' => 'usage limit',
            'minimum_amount' => 'minimum amount',
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

    /**
     * Configure the validator instance.
     * 
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate percent type value
            if ($this->input('type') === 'percent' && $this->input('value') > 100) {
                $validator->errors()->add('value', 'Percent discount cannot be greater than 100%.');
            }
        });
    }
}
