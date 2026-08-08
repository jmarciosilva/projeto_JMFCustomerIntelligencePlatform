<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Driver de Geração de Conteúdo (Fase 15 — AI Marketing)
    |--------------------------------------------------------------------------
    |
    | 'template': gera conteúdo determinístico a partir de templates, sem custo
    |             e sem dependência externa — driver padrão.
    | 'anthropic': gera conteúdo via Anthropic Claude API (requer ANTHROPIC_API_KEY).
    |
    */
    'driver' => env('MARKETING_AI_DRIVER', 'template'),

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-3-5-haiku-20241022'),
        'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1/messages'),
        'max_tokens' => env('ANTHROPIC_MAX_TOKENS', 1024),
    ],
];
