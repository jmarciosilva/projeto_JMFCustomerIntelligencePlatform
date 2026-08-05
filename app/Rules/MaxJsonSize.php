<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Limita o tamanho (em bytes) de um campo array serializado como JSON,
 * prevenindo payloads abusivos (SECURITY.md). Usada em campos livres do
 * cliente como `properties`/`context` (eventos) e `properties` (identify).
 */
class MaxJsonSize implements ValidationRule
{
    public function __construct(private readonly int $maxBytes = 10240) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strlen(json_encode($value)) > $this->maxBytes) {
            $kilobytes = intdiv($this->maxBytes, 1024);
            $fail("O campo {$attribute} excede o tamanho máximo permitido ({$kilobytes} KB).");
        }
    }
}
