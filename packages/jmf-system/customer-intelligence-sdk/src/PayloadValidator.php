<?php

namespace JmfSystem\CustomerIntelligence;

class PayloadValidator
{
    /**
     * Validações de segurança para payload.
     */
    private const MAX_PAYLOAD_SIZE = 10_000; // 10KB

    private const SENSITIVE_KEYS = [
        'password',
        'token',
        'secret',
        'api_key',
        'credit_card',
        'ssn',
        'cpf',
        'cnpj',
    ];

    /**
     * Valida completamente um payload antes de enviar.
     *
     * @param  'events'|'contacts/identify'  $endpoint
     * @param  array<string, mixed>  $payload
     *
     * @throws \RuntimeException se payload inválido
     * @return void
     */
    public static function validate(string $endpoint, array $payload): void
    {
        self::validateNotEmpty($payload);
        self::validateRequiredFields($endpoint, $payload);
        self::validatePayloadSize($payload);
        self::validateNoSensitiveData($payload);
    }

    /**
     * Valida que payload não está vazio.
     */
    private static function validateNotEmpty(array $payload): void
    {
        if (empty($payload)) {
            throw new \RuntimeException('Payload vazio não pode ser enviado');
        }
    }

    /**
     * Valida campos obrigatórios por endpoint.
     */
    private static function validateRequiredFields(string $endpoint, array $payload): void
    {
        match ($endpoint) {
            'events' => self::validateEventPayload($payload),
            'contacts/identify' => self::validateIdentifyPayload($payload),
            default => throw new \RuntimeException("Endpoint desconhecido: $endpoint"),
        };
    }

    /**
     * Valida payload de evento.
     */
    private static function validateEventPayload(array $payload): void
    {
        $required = ['event_id', 'event_name', 'visitor_id', 'occurred_at'];

        foreach ($required as $field) {
            if (! isset($payload[$field]) || trim((string) $payload[$field]) === '') {
                throw new \RuntimeException("Campo obrigatório ausente ou vazio: $field");
            }
        }

        if (! self::isValidEventName($payload['event_name'])) {
            throw new \RuntimeException(
                "event_name deve estar no formato 'entidade.acao', recebeu: {$payload['event_name']}"
            );
        }
    }

    /**
     * Valida payload de identificação.
     */
    private static function validateIdentifyPayload(array $payload): void
    {
        if (! isset($payload['visitor_id'])) {
            throw new \RuntimeException('Campo obrigatório ausente: visitor_id');
        }

        $hasIdentifier = isset($payload['external_id']) || isset($payload['email']);

        if (! $hasIdentifier) {
            throw new \RuntimeException(
                'Payload de identify deve ter pelo menos external_id ou email'
            );
        }
    }

    /**
     * Valida tamanho máximo do payload.
     */
    private static function validatePayloadSize(array $payload): void
    {
        $size = strlen(json_encode($payload) ?: '{}');

        if ($size > self::MAX_PAYLOAD_SIZE) {
            throw new \RuntimeException(
                "Payload excede tamanho máximo: {$size}B > " . self::MAX_PAYLOAD_SIZE . 'B'
            );
        }
    }

    /**
     * Valida que não há dados sensíveis no payload.
     *
     * Procura por chaves suspeitas no payload (recursivamente).
     */
    private static function validateNoSensitiveData(array $payload, string $path = ''): void
    {
        foreach ($payload as $key => $value) {
            $currentPath = $path ? "$path.$key" : $key;

            // Verificar se a chave é sensível
            foreach (self::SENSITIVE_KEYS as $sensitive) {
                if (stripos($key, $sensitive) !== false) {
                    throw new \RuntimeException(
                        "Campo potencialmente sensível detectado: $currentPath (contém '$sensitive')"
                    );
                }
            }

            // Recursivamente verificar arrays e objetos
            if (is_array($value)) {
                self::validateNoSensitiveData($value, $currentPath);
            }
        }
    }

    /**
     * Valida formato de event_name (entidade.acao).
     */
    private static function isValidEventName(mixed $eventName): bool
    {
        if (! is_string($eventName)) {
            return false;
        }

        $parts = explode('.', $eventName);

        return count($parts) === 2
            && strlen($parts[0]) > 0
            && strlen($parts[1]) > 0
            && preg_match('/^[a-z_]+$/i', $parts[0]) !== false
            && preg_match('/^[a-z_]+$/i', $parts[1]) !== false;
    }

    /**
     * Retorna informações de debug do payload (sem dados sensíveis).
     *
     * @return array<string, mixed>
     */
    public static function debug(array $payload): array
    {
        return [
            'size_bytes' => strlen(json_encode($payload) ?: '{}'),
            'field_count' => count($payload),
            'has_event_id' => isset($payload['event_id']),
            'has_visitor_id' => isset($payload['visitor_id']),
            'has_email' => isset($payload['email']),
            'has_external_id' => isset($payload['external_id']),
            'keys' => array_keys($payload),
        ];
    }
}
