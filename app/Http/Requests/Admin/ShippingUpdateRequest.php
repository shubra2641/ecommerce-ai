<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ShippingUpdateRequest handles validation for shipping method updates
 * 
 * This request validates shipping method update data including type,
 * price, status, and description with security measures.
 */
class ShippingUpdateRequest extends FormRequest
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
        $shippingId = $this->route('id');
        
        return [
            'type' => 'required|string|max:100|min:2|unique:shippings,type,' . $shippingId,
            'price' => 'required|numeric|min:0|max:999999.99',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string|max:500',
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
            'type.required' => 'The shipping type is required.',
            'type.min' => 'The shipping type must be at least 2 characters.',
            'type.max' => 'The shipping type may not be greater than 100 characters.',
            'type.unique' => 'A shipping method with this type already exists.',
            'price.required' => 'The shipping price is required.',
            'price.min' => 'The shipping price must be at least 0.',
            'price.max' => 'The shipping price may not be greater than 999999.99.',
            'status.required' => 'The shipping status is required.',
            'status.in' => 'The status must be either active or inactive.',
            'description.max' => 'The description may not be greater than 500 characters.',
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
            'type' => 'shipping type',
            'price' => 'shipping price',
            'status' => 'shipping status',
            'description' => 'shipping description',
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
            'type' => trim($this->type),
            'description' => $this->description ? trim($this->description) : null,
        ]);
    }
}
