<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\PaymentGateway;

/**
 * PaymentGatewayUpdateRequest handles validation for payment gateway updates
 * 
 * This request validates payment gateway update data including name, credentials,
 * and configuration settings with security measures.
 */
class PaymentGatewayUpdateRequest extends FormRequest
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
        $gatewayId = $this->route('id') ?? $this->route('payment_gateway');
        
        return [
            'name' => 'required|string|max:255|min:2|unique:payment_gateways,name,' . $gatewayId,
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
            // Get the existing gateway to check its type
            $gatewayId = $this->route('id') ?? $this->route('payment_gateway');
            $gateway = PaymentGateway::find($gatewayId);
            
            if ($gateway) {
                // Validate credentials based on gateway type
                $this->validateCredentialsByType($validator, $gateway->slug);
            }
        });
    }

    /**
     * Validate credentials based on payment gateway type
     * 
     * @param \Illuminate\Validation\Validator $validator
     * @param string $gatewayType
     * @return void
     */
    private function validateCredentialsByType($validator, string $gatewayType): void
    {
        $credentials = $this->input('credentials', []);

        switch ($gatewayType) {
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
