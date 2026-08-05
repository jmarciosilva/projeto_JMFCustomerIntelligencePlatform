<?php

namespace App\Http\Requests\Api;

use App\Domain\Shared\Enums\ConsentPurpose;
use App\Rules\MaxJsonSize;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IdentifyContactRequest extends FormRequest
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
            'visitor_id' => ['required', 'string', 'max:255'],
            'external_id' => ['required_without:email', 'nullable', 'string', 'max:255'],
            'email' => ['required_without:external_id', 'nullable', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'properties' => ['nullable', 'array', new MaxJsonSize],
            'consents' => ['nullable', 'array'],
            'consents.*.purpose' => ['required', 'string', Rule::in(ConsentPurpose::values())],
            'consents.*.granted' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'external_id.required_without' => 'Informe ao menos um identificador estável: external_id ou email.',
            'email.required_without' => 'Informe ao menos um identificador estável: external_id ou email.',
        ];
    }
}
