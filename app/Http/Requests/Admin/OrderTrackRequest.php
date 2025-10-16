<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OrderTrackRequest extends FormRequest
{
    public function authorize(): bool
    {
        // allow guests to track if needed - keep true
        return true;
    }

    public function rules(): array
    {
        return [
            'order_number' => 'required|string|max:255',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([ 'order_number' => $this->order_number ? trim($this->order_number) : null ]);
    }
}
