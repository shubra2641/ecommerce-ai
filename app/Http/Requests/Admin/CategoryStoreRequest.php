<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CategoryStoreRequest handles validation for category creation
 * 
 * This request validates category creation data including title, summary,
 * photo, status, and hierarchical structure with security measures.
 */
class CategoryStoreRequest extends FormRequest
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
            'summary' => 'nullable|string|max:1000',
            'photo' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
            'is_parent' => 'sometimes|in:1',
            'parent_id' => 'nullable|exists:categories,id',
            'translations' => 'nullable|array',
            'translations.*' => 'nullable|array',
            'translations.*.title' => 'nullable|string|max:255|min:2',
            'translations.*.summary' => 'nullable|string|max:1000',
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
            'title.required' => 'The category title is required.',
            'title.min' => 'The category title must be at least 2 characters.',
            'title.max' => 'The category title may not be greater than 255 characters.',
            'summary.max' => 'The category summary may not be greater than 1000 characters.',
            'photo.max' => 'The photo path may not be greater than 500 characters.',
            'status.required' => 'The category status is required.',
            'status.in' => 'The status must be either active or inactive.',
            'is_parent.in' => 'The is_parent field must be 1 if set.',
            'parent_id.exists' => 'The selected parent category does not exist.',
            'translations.*.title.min' => 'Translation title must be at least 2 characters.',
            'translations.*.title.max' => 'Translation title may not be greater than 255 characters.',
            'translations.*.summary.max' => 'Translation summary may not be greater than 1000 characters.',
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
            'title' => 'category title',
            'summary' => 'category summary',
            'photo' => 'category photo',
            'status' => 'category status',
            'is_parent' => 'parent category',
            'parent_id' => 'parent category',
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
            'summary' => $this->summary ? trim($this->summary) : null,
            'photo' => $this->photo ? trim($this->photo) : null,
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
            // Validate parent-child relationship
            $this->validateParentChildRelationship($validator);
        });
    }

    /**
     * Validate parent-child relationship
     * 
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    private function validateParentChildRelationship($validator): void
    {
        $isParent = $this->input('is_parent');
        $parentId = $this->input('parent_id');
        
        // If it's a parent category, it shouldn't have a parent
        if ($isParent && $parentId) {
            $validator->errors()->add('parent_id', 'A parent category cannot have a parent category.');
        }
        
        // If it has a parent, it shouldn't be marked as parent
        if ($parentId && $isParent) {
            $validator->errors()->add('is_parent', 'A child category cannot be marked as parent.');
        }
    }
}
