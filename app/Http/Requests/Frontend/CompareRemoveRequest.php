<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CompareRemoveRequest handles validation for removing products from compare list
 * 
 * This request validates product removal from compare list with proper
 * authorization and security measures.
 */
class CompareRemoveRequest extends FormRequest
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
            'id' => 'required|integer|min:1|exists:compares,id',
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
            'id.required' => 'The compare item ID is required.',
            'id.integer' => 'The compare item ID must be a valid number.',
            'id.min' => 'The compare item ID must be at least 1.',
            'id.exists' => 'The selected compare item does not exist.',
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
            'id' => 'compare item ID',
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
