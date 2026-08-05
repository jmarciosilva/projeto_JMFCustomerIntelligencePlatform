<?php

return [

    /*
    |--------------------------------------------------------------------------
    | URL base da API
    |--------------------------------------------------------------------------
    |
    | Ex.: https://ci.jmfsystem.com/api/v1
    |
    */
    'base_url' => env('JMF_CI_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Token da aplicação
    |--------------------------------------------------------------------------
    |
    | Token Sanctum gerado no painel admin da plataforma JMF Customer
    | Intelligence (Tenants > Applications > Tokens).
    |
    */
    'token' => env('JMF_CI_TOKEN'),

    'timeout' => env('JMF_CI_TIMEOUT', 5),

    'tries' => env('JMF_CI_TRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Fila
    |--------------------------------------------------------------------------
    |
    | Deixe null para usar a conexão/fila padrão da aplicação cliente.
    |
    */
    'queue_connection' => env('JMF_CI_QUEUE_CONNECTION'),
    'queue' => env('JMF_CI_QUEUE'),

    /*
    |--------------------------------------------------------------------------
    | Cookies de identificação
    |--------------------------------------------------------------------------
    |
    | visitor_id: identificador anônimo de longa duração.
    | session_id: identificador de sessão de navegação, com expiração curta
    | e rolante (renovada a cada request), independente da sessão de
    | autenticação do Laravel.
    |
    */
    'visitor_cookie' => [
        'name' => 'jmf_ci_visitor_id',
        'minutes' => 60 * 24 * 365 * 2,
    ],

    'session_cookie' => [
        'name' => 'jmf_ci_session_id',
        'minutes' => 30,
    ],
];
