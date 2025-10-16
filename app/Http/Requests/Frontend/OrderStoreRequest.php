<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\PaymentGateway;

/**
 * OrderStoreRequest handles validation for order creation
 * 
 * This request validates order creation data including customer information,
 * shipping details, and payment method with comprehensive validation.
 */
class OrderStoreRequest extends FormRequest
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
            'first_name' => 'required|string|max:255|min:2|regex:/^[a-zA-Z\s\x{0600}-\x{06FF}]+$/u',
            'last_name' => 'required|string|max:255|min:2|regex:/^[a-zA-Z\s\x{0600}-\x{06FF}]+$/u',
            'address1' => 'required|string|max:500|min:5',
            'address2' => 'nullable|string|max:500',
            'coupon' => 'nullable|numeric|min:0',
            'phone' => 'required|string|min:10|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'post_code' => 'nullable|string|max:20|regex:/^[a-zA-Z0-9\s\-]+$/',
            'country' => 'required|string|max:100|min:2',
            'email' => 'required|email|max:255',
            'shipping' => 'nullable|integer|exists:shippings,id',
            'payment_method' => 'required|string|max:50',
            'payment_proof' => 'nullable|image|max:2048|mimes:jpeg,png,jpg,gif',
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
            'first_name.required' => 'The first name field is required.',
            'first_name.min' => 'The first name must be at least 2 characters.',
            'first_name.regex' => 'The first name may only contain letters and spaces.',
            'last_name.required' => 'The last name field is required.',
            'last_name.min' => 'The last name must be at least 2 characters.',
            'last_name.regex' => 'The last name may only contain letters and spaces.',
            'address1.required' => 'The address field is required.',
            'address1.min' => 'The address must be at least 10 characters.',
            'phone.required' => 'The phone number field is required.',
            'phone.min' => 'The phone number must be at least 10 characters.',
            'phone.regex' => 'The phone number format is invalid.',
            'post_code.regex' => 'The postal code format is invalid.',
            'country.required' => 'The country field is required.',
            'country.min' => 'The country must be at least 2 characters.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'shipping.exists' => 'The selected shipping method is invalid.',
            'payment_method.required' => 'The payment method field is required.',
            'payment_proof.image' => 'The payment proof must be an image.',
            'payment_proof.max' => 'The payment proof may not be greater than 2MB.',
            'payment_proof.mimes' => 'The payment proof must be a file of type: jpeg, png, jpg, gif.',
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
            'first_name' => 'first name',
            'last_name' => 'last name',
            'address1' => 'address',
            'address2' => 'address line 2',
            'coupon' => 'coupon discount',
            'phone' => 'phone number',
            'post_code' => 'postal code',
            'country' => 'country',
            'email' => 'email address',
            'shipping' => 'shipping method',
            'payment_method' => 'payment method',
            'payment_proof' => 'payment proof',
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
            'first_name' => trim($this->first_name),
            'last_name' => trim($this->last_name),
            'address1' => trim($this->address1),
            'address2' => $this->address2 ? trim($this->address2) : null,
            'phone' => trim($this->phone),
            'post_code' => $this->post_code ? trim($this->post_code) : null,
            'country' => trim($this->country),
            'email' => strtolower(trim($this->email)),
            'payment_method' => trim($this->payment_method),
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
            // Validate payment method
            $this->validatePaymentMethod($validator);
        });
    }

    /**
     * Validate payment method and related requirements
     * 
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    private function validatePaymentMethod($validator): void
    {
        $paymentMethod = $this->input('payment_method');
        
        // Check if it's a valid payment gateway
        if (!in_array($paymentMethod, ['paypal', 'cod'])) {
            $gateway = PaymentGateway::where('slug', $paymentMethod)->first();
            if (!$gateway) {
                $validator->errors()->add('payment_method', 'The selected payment method is invalid.');
                return;
            }
            
            // Check if payment proof is required
            if ($gateway->require_proof && !$this->hasFile('payment_proof')) {
                $validator->errors()->add('payment_proof', 'Payment proof is required for this payment method.');
            }
        }
    }
}
