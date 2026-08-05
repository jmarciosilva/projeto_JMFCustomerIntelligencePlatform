<?php

namespace App\Http\Requests\Api;

use App\Rules\MaxJsonSize;
use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
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
            'event_id' => ['required', 'string', 'max:255'],
            'event_name' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_]+(\.[a-z0-9_]+)+$/'],
            'visitor_id' => ['required', 'string', 'max:255'],
            'session_id' => ['nullable', 'string', 'max:255'],
            'contact_id' => ['nullable', 'string', 'max:255'],
            'subject_type' => ['nullable', 'string', 'max:255'],
            'subject_id' => ['nullable', 'string', 'max:255'],
            'properties' => ['nullable', 'array', new MaxJsonSize],
            'context' => ['nullable', 'array', new MaxJsonSize],
            'occurred_at' => ['required', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'event_name.regex' => 'O campo event_name deve seguir o padrão "entidade.acao" (ex.: article.viewed).',
        ];
    }
}
