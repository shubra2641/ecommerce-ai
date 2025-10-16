<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PostStoreRequest handles validation for post creation
 * 
 * This request validates post creation data including title, content,
 * category, and translations with security measures.
 */
class PostStoreRequest extends FormRequest
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
            'title' => 'required|string|max:255|min:5',
            'quote' => 'nullable|string|max:500',
            'summary' => 'required|string|max:1000|min:20',
            'description' => 'nullable|string',
            'photo' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'added_by' => 'nullable|integer|exists:users,id',
            'post_cat_id' => 'required|integer|exists:post_categories,id',
            'status' => 'required|in:active,inactive',
            'translations' => 'nullable|array',
            'translations.*' => 'nullable|array',
            'translations.*.title' => 'nullable|string|max:255|min:5',
            'translations.*.summary' => 'nullable|string|max:1000|min:20',
            'translations.*.description' => 'nullable|string',
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
            'title.required' => 'The post title is required.',
            'title.min' => 'The post title must be at least 5 characters.',
            'title.max' => 'The post title may not be greater than 255 characters.',
            'quote.max' => 'The quote may not be greater than 500 characters.',
            'summary.required' => 'The post summary is required.',
            'summary.min' => 'The post summary must be at least 20 characters.',
            'summary.max' => 'The post summary may not be greater than 1000 characters.',
            'photo.max' => 'The photo path may not be greater than 255 characters.',
            'tags.*.max' => 'Each tag may not be greater than 50 characters.',
            'added_by.exists' => 'The selected author does not exist.',
            'post_cat_id.required' => 'The post category is required.',
            'post_cat_id.exists' => 'The selected post category does not exist.',
            'status.required' => 'The post status is required.',
            'status.in' => 'The status must be either active or inactive.',
            'translations.*.title.min' => 'Translation title must be at least 5 characters.',
            'translations.*.title.max' => 'Translation title may not be greater than 255 characters.',
            'translations.*.summary.min' => 'Translation summary must be at least 20 characters.',
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
            'title' => 'post title',
            'quote' => 'post quote',
            'summary' => 'post summary',
            'description' => 'post description',
            'photo' => 'post photo',
            'tags' => 'post tags',
            'added_by' => 'post author',
            'post_cat_id' => 'post category',
            'status' => 'post status',
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
            'quote' => $this->quote ? trim($this->quote) : null,
            'summary' => trim($this->summary),
            'description' => $this->description ? trim($this->description) : null,
            'photo' => $this->photo ? trim($this->photo) : null,
        ]);
    }
}
