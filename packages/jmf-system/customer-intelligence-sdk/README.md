# JMF Customer Intelligence SDK (Laravel)

SDK Laravel para enviar eventos, identificações e conversões à plataforma [JMF Customer Intelligence](../../../README.md), sem reimplementar a integração HTTP em cada aplicação cliente (Site Pessoal, Clube do Salão, e futuros projetos da JMF System).

Cuida sozinho de:

- Identificar o visitante (`visitor_id`, cookie de longa duração) e a sessão de navegação (`session_id`, cookie rolante de 30 min) — nada para o código da aplicação gerenciar.
- Montar o `context` do evento (`page_url`, `referrer`, UTMs) a partir da requisição atual.
- Enviar tudo de forma assíncrona, numa fila da própria aplicação cliente, com retry/backoff e sem nunca quebrar o fluxo do usuário se a plataforma estiver indisponível.

## Instalação

Enquanto o pacote não está publicado no Packagist, instale via *path repository* apontando para este diretório:

```json
{
    "repositories": [
        { "type": "path", "url": "../caminho/para/customer-intelligence-sdk" }
    ],
    "require": {
        "jmf-system/customer-intelligence-sdk": "*"
    }
}
```

```bash
composer require jmf-system/customer-intelligence-sdk
php artisan vendor:publish --tag=customer-intelligence-config
```

## Configuração (`.env`)

```env
JMF_CI_BASE_URL=https://ci.jmfsystem.com/api/v1
JMF_CI_TOKEN=coloque-aqui-o-token-gerado-no-painel-admin
JMF_CI_TIMEOUT=5
JMF_CI_TRIES=3
```

O token é gerado no painel administrativo da plataforma JMF Customer Intelligence (**Aplicações → Tokens**).

**Requisito importante**: a aplicação cliente precisa ter uma fila configurada e um worker rodando (`php artisan queue:work`) — o SDK despacha o envio como `Job`, ele nunca é enviado de forma síncrona/bloqueante.

## Uso

```php
use JmfSystem\CustomerIntelligence\Facades\CustomerIntelligence;

// Identificar um contato (email e/ou external_id — pelo menos um é obrigatório)
CustomerIntelligence::identify([
    'email' => 'jose@example.com',
    'name' => 'José',
], consents: [
    ['purpose' => 'marketing', 'granted' => true],
]);

// Rastrear um evento — visitor_id/session_id/context são preenchidos automaticamente
CustomerIntelligence::track('article.viewed', [
    'article_id' => 15,
    'category' => 'Laravel',
], subjectType: 'Article', subjectId: 15);

// Marcar uma conversão (semanticamente igual a track(), só mais explícito no código)
CustomerIntelligence::conversion('contact.form_submitted', ['form' => 'contato']);
```

Ou injetando `JmfSystem\CustomerIntelligence\Client` via container/construtor, se preferir não usar a Facade.

Nomes de eventos devem seguir o padrão `entidade.acao` (ex.: `article.viewed`) — consulte o [`EVENT_CATALOG.md`](../../../EVENT_CATALOG.md) da plataforma para o catálogo de eventos e o significado de cada campo.

## Cookies utilizados

| Cookie | Duração | Propósito |
|---|---|---|
| `jmf_ci_visitor_id` | ~2 anos | Identificador anônimo do visitante. |
| `jmf_ci_session_id` | 30 min, renovado a cada request | Sessão de navegação (independente da sessão de autenticação do Laravel). |

Nomes e durações são configuráveis em `config/customer-intelligence.php`.

## Retry e falhas

- Erros de rede ou 5xx/429 da API: a fila tenta novamente (backoff crescente: 5s, 30s, 2min).
- Erros 4xx (ex.: payload inválido): não tenta de novo, só registra um log de aviso — nunca teria sucesso numa nova tentativa.
- Depois de esgotar as tentativas: log de erro. Em nenhum momento uma falha de envio interrompe o fluxo da aplicação cliente.

## Desenvolvimento do pacote

```bash
composer install
vendor/bin/pest
```
