<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class GetRecommendationsRequest extends FormRequest
{
    /**
     * A autorização da requisição já é feita pela cadeia de middlewares da
     * rota (auth:sanctum + ensure.application.active); nada a checar aqui.
     */
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
            'subject_id' => ['required', 'string', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ];
    }
}
