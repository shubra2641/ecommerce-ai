<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * BrandUpdateRequest handles validation for brand updates
 * 
 * This request validates brand update data including title, status,
 * and translations with security measures.
 */
class BrandUpdateRequest extends FormRequest
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
        $brandId = $this->route('id');
        
        return [
            'title' => 'required|string|max:255|min:2|unique:brands,title,' . $brandId,
            'status' => 'required|in:active,inactive',
            'translations' => 'nullable|array',
            'translations.*' => 'nullable|array',
            'translations.*.title' => 'nullable|string|max:255|min:2',
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
            'title.required' => 'The brand title is required.',
            'title.min' => 'The brand title must be at least 2 characters.',
            'title.max' => 'The brand title may not be greater than 255 characters.',
            'title.unique' => 'A brand with this title already exists.',
            'status.required' => 'The brand status is required.',
            'status.in' => 'The status must be either active or inactive.',
            'translations.*.title.min' => 'Translation title must be at least 2 characters.',
            'translations.*.title.max' => 'Translation title may not be greater than 255 characters.',
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
            'title' => 'brand title',
            'status' => 'brand status',
            'translations' => 'translations',
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
            'title' => trim($this->title),
        ]);
    }
}
