<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PostCategoryUpdateRequest handles validation for post category updates
 * 
 * This request validates post category update data including title,
 * status, and translations with security measures.
 */
class PostCategoryUpdateRequest extends FormRequest
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
        $postCategoryId = $this->route('id');
        
        return [
            'title' => 'required|string|max:255|min:2|unique:post_categories,title,' . $postCategoryId,
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
            'title.required' => 'The post category title is required.',
            'title.min' => 'The post category title must be at least 2 characters.',
            'title.max' => 'The post category title may not be greater than 255 characters.',
            'title.unique' => 'A post category with this title already exists.',
            'status.required' => 'The post category status is required.',
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
            'title' => 'post category title',
            'status' => 'post category status',
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
