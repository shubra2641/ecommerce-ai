<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PaypalSuccessRequest handles validation for PayPal payment success
 * 
 * This request validates PayPal payment success callback with proper
 * authorization and security measures.
 */
class PaypalSuccessRequest extends FormRequest
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
            'token' => 'required|string|max:255|min:10',
            'PayerID' => 'required|string|max:255|min:5'
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
            'token.required' => 'The PayPal token is required.',
            'token.string' => 'The PayPal token must be a string.',
            'token.min' => 'The PayPal token must be at least 10 characters.',
            'token.max' => 'The PayPal token may not be greater than 255 characters.',
            'PayerID.required' => 'The PayPal Payer ID is required.',
            'PayerID.string' => 'The PayPal Payer ID must be a string.',
            'PayerID.min' => 'The PayPal Payer ID must be at least 5 characters.',
            'PayerID.max' => 'The PayPal Payer ID may not be greater than 255 characters.',
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
            'token' => 'PayPal token',
            'PayerID' => 'PayPal Payer ID',
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
            'token' => trim($this->token),
            'PayerID' => trim($this->PayerID),
        ]);
    }
}
