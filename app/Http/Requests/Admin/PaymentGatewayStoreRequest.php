<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\PaymentGateway;

/**
 * PaymentGatewayStoreRequest handles validation for payment gateway creation
 * 
 * This request validates payment gateway creation data including name, type,
 * credentials, and configuration settings with security measures.
 */
class PaymentGatewayStoreRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255|min:2|unique:payment_gateways,name',
            'type' => 'nullable|string|max:50|in:paypal,stripe,offline,cod',
            'prefill_slug' => 'nullable|string|max:50|in:paypal,stripe,offline,cod',
            'enabled' => 'nullable|boolean',
            'mode' => 'nullable|string|in:sandbox,live,test',
            'credentials' => 'nullable|array',
            'credentials.*' => 'nullable|array',
            'credentials.sandbox' => 'nullable|array',
            'credentials.sandbox.client_id' => 'nullable|string|max:255',
            'credentials.sandbox.client_secret' => 'nullable|string|max:255',
            'credentials.live' => 'nullable|array',
            'credentials.live.client_id' => 'nullable|string|max:255',
            'credentials.live.client_secret' => 'nullable|string|max:255',
            'credentials.test' => 'nullable|array',
            'credentials.test.publishable_key' => 'nullable|string|max:255',
            'credentials.test.secret_key' => 'nullable|string|max:255',
            'transfer_details' => 'nullable|string|max:1000',
            'require_proof' => 'nullable|boolean',
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
            'name.required' => 'The payment gateway name is required.',
            'name.min' => 'The payment gateway name must be at least 2 characters.',
            'name.unique' => 'A payment gateway with this name already exists.',
            'type.in' => 'The selected payment gateway type is invalid.',
            'prefill_slug.in' => 'The selected prefill slug is invalid.',
            'mode.in' => 'The selected mode is invalid. Must be sandbox, live, or test.',
            'transfer_details.max' => 'The transfer details may not be greater than 1000 characters.',
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
            'name' => 'payment gateway name',
            'type' => 'payment gateway type',
            'prefill_slug' => 'prefill slug',
            'enabled' => 'enabled status',
            'mode' => 'payment mode',
            'credentials' => 'payment credentials',
            'transfer_details' => 'transfer details',
            'require_proof' => 'require proof',
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
            'name' => trim($this->name),
            'type' => $this->type ? trim($this->type) : null,
            'prefill_slug' => $this->prefill_slug ? trim($this->prefill_slug) : null,
            'transfer_details' => $this->transfer_details ? trim($this->transfer_details) : null,
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
            // Check if slug already exists
            $type = $this->input('type') ?: $this->input('prefill_slug');
            $slug = $type ?: \Illuminate\Support\Str::slug($this->input('name'));
            
            if (PaymentGateway::where('slug', $slug)->exists()) {
                $validator->errors()->add('name', 'A payment gateway with this type already exists.');
            }

            // Validate credentials based on type
            $this->validateCredentialsByType($validator);
        });
    }

    /**
     * Validate credentials based on payment gateway type
     * 
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    private function validateCredentialsByType($validator): void
    {
        $type = $this->input('type') ?: $this->input('prefill_slug');
        $credentials = $this->input('credentials', []);

        switch ($type) {
            case 'paypal':
                if (empty($credentials['sandbox']['client_id']) && empty($credentials['live']['client_id'])) {
                    $validator->errors()->add('credentials', 'PayPal requires at least sandbox or live client ID.');
                }
                break;
                
            case 'stripe':
                if (empty($credentials['test']['publishable_key']) && empty($credentials['live']['publishable_key'])) {
                    $validator->errors()->add('credentials', 'Stripe requires at least test or live publishable key.');
                }
                break;
        }
    }
}
