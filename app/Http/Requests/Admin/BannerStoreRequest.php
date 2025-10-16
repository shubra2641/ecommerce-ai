<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * BannerStoreRequest handles validation for banner creation
 * 
 * This request validates banner creation data including title, description,
 * photo, status, and translations with security measures.
 */
class BannerStoreRequest extends FormRequest
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
            'title' => 'required|string|max:255|min:2',
            'description' => 'nullable|string|max:1000',
            'photo' => 'required|string|max:500',
            'status' => 'required|in:active,inactive',
            'translations' => 'nullable|array',
            'translations.*' => 'nullable|array',
            'translations.*.title' => 'nullable|string|max:255|min:2',
            'translations.*.description' => 'nullable|string|max:1000',
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
            'title.required' => 'The banner title is required.',
            'title.min' => 'The banner title must be at least 2 characters.',
            'title.max' => 'The banner title may not be greater than 255 characters.',
            'description.max' => 'The banner description may not be greater than 1000 characters.',
            'photo.required' => 'The banner photo is required.',
            'photo.max' => 'The photo path may not be greater than 500 characters.',
            'status.required' => 'The banner status is required.',
            'status.in' => 'The status must be either active or inactive.',
            'translations.*.title.min' => 'Translation title must be at least 2 characters.',
            'translations.*.title.max' => 'Translation title may not be greater than 255 characters.',
            'translations.*.description.max' => 'Translation description may not be greater than 1000 characters.',
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
            'title' => 'banner title',
            'description' => 'banner description',
            'photo' => 'banner photo',
            'status' => 'banner status',
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
            'description' => $this->description ? trim($this->description) : null,
            'photo' => trim($this->photo),
        ]);
    }
}
