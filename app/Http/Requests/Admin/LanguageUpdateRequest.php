<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * LanguageUpdateRequest handles validation for language updates
 * 
 * This request validates language update data including code, name,
 * direction, and status with security measures.
 */
class LanguageUpdateRequest extends FormRequest
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
        $languageId = $this->route('id');
        
        return [
            'code' => 'required|string|max:10|min:2|unique:languages,code,' . $languageId . '|regex:/^[a-z]{2}(-[A-Z]{2})?$/',
            'name' => 'required|string|max:100|min:2',
            'direction' => 'required|string|in:ltr,rtl',
            'status' => 'required|in:active,inactive',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0|max:999',
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
            'code.required' => 'The language code is required.',
            'code.min' => 'The language code must be at least 2 characters.',
            'code.max' => 'The language code may not be greater than 10 characters.',
            'code.unique' => 'A language with this code already exists.',
            'code.regex' => 'The language code must be in format like "en" or "en-US".',
            'name.required' => 'The language name is required.',
            'name.min' => 'The language name must be at least 2 characters.',
            'name.max' => 'The language name may not be greater than 100 characters.',
            'direction.required' => 'The text direction is required.',
            'direction.in' => 'The text direction must be either ltr or rtl.',
            'status.required' => 'The language status is required.',
            'status.in' => 'The status must be either active or inactive.',
            'sort_order.min' => 'The sort order must be at least 0.',
            'sort_order.max' => 'The sort order may not be greater than 999.',
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
            'code' => 'language code',
            'name' => 'language name',
            'direction' => 'text direction',
            'status' => 'language status',
            'is_default' => 'default language',
            'is_active' => 'active status',
            'sort_order' => 'sort order',
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
            'code' => strtolower(trim($this->code)),
            'name' => trim($this->name),
        ]);
    }
}
