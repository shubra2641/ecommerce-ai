<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ProductRequest handles validation for product creation and updates
 * 
 * This request validates product data including title, summary, description,
 * pricing, stock, and category information with comprehensive security measures.
 */
class ProductRequest extends FormRequest
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
        $rules = [
            'title' => 'required|string|max:255|min:5',
            'summary' => 'required|string|min:20|max:1000',
            'description' => 'nullable|string|max:5000',
            'photo' => 'nullable|string|max:500',
            'size' => 'nullable|array',
            'size.*' => 'string|max:50',
            'stock' => 'required|integer|min:0|max:999999',
            'cat_id' => 'required|integer|exists:categories,id',
            'child_cat_id' => 'nullable|integer|exists:categories,id',
            'price' => 'required|numeric|min:0.01|max:999999.99',
            'brand_id' => 'nullable|integer|exists:brands,id',
            'discount' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive',
            'condition' => 'required|in:default,new,hot',
            'is_featured' => 'nullable|boolean',
            'translations' => 'nullable|array',
            'translations.*' => 'nullable|array',
            'translations.*.title' => 'nullable|string|max:255|min:5',
            'translations.*.summary' => 'nullable|string|min:20|max:1000',
            'translations.*.description' => 'nullable|string|max:5000',
        ];

        // For update requests, make unique validation consider current product
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $productId = $this->route('product') ? $this->route('product')->id : null;
            $rules['title'] = 'required|string|max:255|min:5|unique:products,title,' . $productId;
        } else {
            $rules['title'] = 'required|string|max:255|min:5|unique:products,title';
        }

        return $rules;
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The product title is required.',
            'title.min' => 'The product title must be at least 5 characters.',
            'title.max' => 'The product title may not be greater than 255 characters.',
            'title.unique' => 'A product with this title already exists.',
            'summary.required' => 'The product summary is required.',
            'summary.min' => 'The product summary must be at least 20 characters.',
            'summary.max' => 'The product summary may not be greater than 1000 characters.',
            'description.max' => 'The product description may not be greater than 5000 characters.',
            'photo.max' => 'The photo path may not be greater than 500 characters.',
            'size.*.max' => 'Each size may not be greater than 50 characters.',
            'stock.required' => 'The stock quantity is required.',
            'stock.min' => 'The stock quantity must be at least 0.',
            'stock.max' => 'The stock quantity may not be greater than 999999.',
            'cat_id.required' => 'The category is required.',
            'cat_id.exists' => 'The selected category does not exist.',
            'child_cat_id.exists' => 'The selected subcategory does not exist.',
            'price.required' => 'The price is required.',
            'price.min' => 'The price must be at least 0.01.',
            'price.max' => 'The price may not be greater than 999999.99.',
            'brand_id.exists' => 'The selected brand does not exist.',
            'discount.min' => 'The discount must be at least 0.',
            'discount.max' => 'The discount may not be greater than 100%.',
            'status.required' => 'The product status is required.',
            'status.in' => 'The status must be either active or inactive.',
            'condition.required' => 'The product condition is required.',
            'condition.in' => 'The condition must be one of: default, new, hot.',
            'translations.*.title.min' => 'Translation title must be at least 5 characters.',
            'translations.*.title.max' => 'Translation title may not be greater than 255 characters.',
            'translations.*.summary.min' => 'Translation summary must be at least 20 characters.',
            'translations.*.summary.max' => 'Translation summary may not be greater than 1000 characters.',
            'translations.*.description.max' => 'Translation description may not be greater than 5000 characters.',
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
            'title' => 'product title',
            'summary' => 'product summary',
            'description' => 'product description',
            'photo' => 'product photo',
            'size' => 'product sizes',
            'stock' => 'stock quantity',
            'cat_id' => 'category',
            'child_cat_id' => 'subcategory',
            'price' => 'product price',
            'brand_id' => 'brand',
            'discount' => 'discount percentage',
            'status' => 'product status',
            'condition' => 'product condition',
            'is_featured' => 'featured product',
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
            'summary' => trim($this->summary),
            'description' => $this->description ? trim($this->description) : null,
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
            // Validate child category belongs to parent category
            $this->validateChildCategory($validator);
        });
    }

    /**
     * Validate child category belongs to parent category
     * 
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    private function validateChildCategory($validator): void
    {
        $catId = $this->input('cat_id');
        $childCatId = $this->input('child_cat_id');
        
        if ($childCatId && $catId) {
            $childCategory = \App\Models\Category::find($childCatId);
            if ($childCategory && $childCategory->parent_id != $catId) {
                $validator->errors()->add('child_cat_id', 'The selected subcategory does not belong to the selected category.');
            }
        }
    }
}