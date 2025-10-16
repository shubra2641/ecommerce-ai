<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CartUpdateRequest handles validation for cart quantity updates
 * 
 * This request validates cart quantity updates with proper authorization
 * and security measures.
 */
class CartUpdateRequest extends FormRequest
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
            'quant' => 'required|array|min:1',
            'quant.*' => 'required|integer|min:0|max:100',
            'qty_id' => 'required|array|min:1',
            'qty_id.*' => 'required|integer|min:1|exists:carts,id',
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
            'quant.required' => 'The quantity field is required.',
            'quant.array' => 'The quantity must be an array.',
            'quant.min' => 'At least one quantity must be provided.',
            'quant.*.required' => 'Each quantity is required.',
            'quant.*.integer' => 'Each quantity must be a valid number.',
            'quant.*.min' => 'Each quantity must be at least 0.',
            'quant.*.max' => 'Each quantity may not be greater than 100.',
            'qty_id.required' => 'The quantity ID field is required.',
            'qty_id.array' => 'The quantity ID must be an array.',
            'qty_id.min' => 'At least one quantity ID must be provided.',
            'qty_id.*.required' => 'Each quantity ID is required.',
            'qty_id.*.integer' => 'Each quantity ID must be a valid number.',
            'qty_id.*.min' => 'Each quantity ID must be at least 1.',
            'qty_id.*.exists' => 'The selected cart item does not exist.',
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
            'quant' => 'quantity',
            'qty_id' => 'quantity ID',
        ];
    }

    /**
     * Prepare the data for validation.
     * 
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Convert string values to integers
        if ($this->has('quant')) {
            $this->merge([
                'quant' => array_map('intval', $this->quant),
            ]);
        }
        
        if ($this->has('qty_id')) {
            $this->merge([
                'qty_id' => array_map('intval', $this->qty_id),
            ]);
        }
    }
}
