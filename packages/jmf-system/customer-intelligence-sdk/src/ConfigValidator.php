<?php

namespace JmfSystem\CustomerIntelligence;

class ConfigValidator
{
    /**
     * Valida as configurações obrigatórias do SDK.
     *
     * Lança exceção se configurações obrigatórias estão faltando.
     * Chama silenciosamente se SDK está desabilitado.
     *
     * @throws \RuntimeException se configuração inválida
     */
    public static function validate(): void
    {
        if (! config('customer-intelligence.enabled')) {
            return;
        }

        $errors = self::getValidationErrors();

        if (! empty($errors)) {
            throw new \RuntimeException(
                'Configuração inválida do SDK JMF Customer Intelligence:' . "\n" .
                implode("\n", array_map(fn ($e) => "  • $e", $errors))
            );
        }
    }

    /**
     * Retorna lista de erros de validação.
     *
     * Retorna array vazio se SDK está desabilitado.
     *
     * @return array<string>
     */
    public static function getValidationErrors(): array
    {
        if (! config('customer-intelligence.enabled', true)) {
            return [];
        }

        $errors = [];

        if (! config('customer-intelligence.base_url')) {
            $errors[] = 'JMF_CI_BASE_URL não está configurada';
        } elseif (! self::isValidUrl(config('customer-intelligence.base_url'))) {
            $errors[] = 'JMF_CI_BASE_URL não é uma URL válida';
        }

        if (! config('customer-intelligence.token')) {
            $errors[] = 'JMF_CI_TOKEN não está configurado';
        }

        if (config('customer-intelligence.timeout', 5) < 1) {
            $errors[] = 'JMF_CI_TIMEOUT deve ser >= 1 segundo';
        }

        if (config('customer-intelligence.tries', 3) < 1) {
            $errors[] = 'JMF_CI_TRIES deve ser >= 1';
        }

        return $errors;
    }

    /**
     * Valida se uma string é uma URL válida.
     */
    private static function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Retorna status de configuração como array.
     *
     * @return array<string, mixed>
     */
    public static function status(): array
    {
        return [
            'enabled' => config('customer-intelligence.enabled'),
            'base_url' => config('customer-intelligence.base_url') ? '✓' : '✗',
            'token' => config('customer-intelligence.token') ? '✓ (****)' : '✗',
            'timeout' => config('customer-intelligence.timeout') . 's',
            'tries' => config('customer-intelligence.tries'),
            'sync' => config('customer-intelligence.sync'),
            'errors' => self::getValidationErrors(),
        ];
    }
}
