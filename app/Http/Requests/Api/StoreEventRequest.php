<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    /**
     * Tamanho máximo (em bytes) permitido para os campos `properties` e
     * `context`, prevenindo payloads abusivos (SECURITY.md).
     */
    private const MAX_JSON_FIELD_BYTES = 10240;

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
            'properties' => ['nullable', 'array', $this->maxJsonSize()],
            'context' => ['nullable', 'array', $this->maxJsonSize()],
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

    private function maxJsonSize(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (strlen(json_encode($value)) > self::MAX_JSON_FIELD_BYTES) {
                $fail("O campo {$attribute} excede o tamanho máximo permitido (10 KB).");
            }
        };
    }
}
