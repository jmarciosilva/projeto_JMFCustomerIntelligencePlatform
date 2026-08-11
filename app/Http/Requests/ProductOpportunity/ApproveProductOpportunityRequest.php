<?php

namespace App\Http\Requests\ProductOpportunity;

use Illuminate\Foundation\Http\FormRequest;

class ApproveProductOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->application_id !== null;
    }

    public function rules(): array
    {
        return [
            'reason' => 'sometimes|string|max:500',
        ];
    }
}
