<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CouponApplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([ 'code' => $this->code ? trim($this->code) : null ]);
    }
}
