<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class GenerateMarketingContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject_type' => ['required', 'string', 'max:255'],
            'subject_id' => ['required', 'integer'],
            'product' => ['required', 'array'],
            'product.name' => ['required', 'string', 'max:255'],
            'product.category' => ['required', 'string', 'max:255'],
            'product.price' => ['nullable', 'numeric', 'min:0'],
            'product.description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
