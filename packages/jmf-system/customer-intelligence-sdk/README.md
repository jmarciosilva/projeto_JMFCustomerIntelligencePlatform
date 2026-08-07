# JMF Customer Intelligence SDK (Laravel)

SDK Laravel para enviar eventos, identificações e conversões à plataforma [JMF Customer Intelligence](../../../README.md), sem reimplementar a integração HTTP em cada aplicação cliente (Site Pessoal, Clube do Salão, e futuros projetos da JMF System).

Cuida sozinho de:

- Identificar o visitante (`visitor_id`, cookie de longa duração) e a sessão de navegação (`session_id`, cookie rolante de 30 min) — nada para o código da aplicação gerenciar.
- Montar o `context` do evento (`page_url`, `referrer`, UTMs) a partir da requisição atual.
- Enviar tudo de forma assíncrona, numa fila da própria aplicação cliente, com retry/backoff e sem nunca quebrar o fluxo do usuário se a plataforma estiver indisponível.

## Instalação

### Via Packagist (Recomendado)

```bash
composer require jmf-system/customer-intelligence-sdk
php artisan vendor:publish --tag=customer-intelligence-config
```

### Via Repositório Privado

Se o pacote ainda não está no Packagist, instale via *path repository* ou Git:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/jmf-system/customer-intelligence-sdk.git"
        }
    ],
    "require": {
        "jmf-system/customer-intelligence-sdk": "dev-main"
    }
}
```

```bash
composer require jmf-system/customer-intelligence-sdk:dev-main
php artisan vendor:publish --tag=customer-intelligence-config
```

## Configuração (`.env`)

Mínimo obrigatório:

```env
JMF_CI_BASE_URL=https://ci.jmfsystem.com/api/v1
JMF_CI_TOKEN=seu-token-api-aqui
```

Opcional (usa defaults sensatos):

```env
JMF_CI_TIMEOUT=5                           # Timeout de requisição (segundos)
JMF_CI_TRIES=3                             # Máximo de tentativas (com retry)
JMF_CI_BACKOFF=5,30,120                    # Delays de retry em segundos
JMF_CI_QUEUE_CONNECTION=sync                # Qual fila usar ('sync', 'redis', etc)
JMF_CI_QUEUE=default                       # Nome da fila
JMF_CI_ENABLED=true                        # Ativar/desativar o SDK (útil em testes)
JMF_CI_VALIDATE_ON_BOOT=true               # Validar config ao iniciar a app
```

**O token** é gerado no painel administrativo da plataforma JMF Customer Intelligence (**Aplicações → Tokens**).

**Requisito importante**: a aplicação cliente precisa ter uma fila configurada e um worker rodando (`php artisan queue:work`) — o SDK despacha o envio como `Job`, nunca é enviado de forma síncrona/bloqueante.

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

**Validar conexão:**

```php
if (CustomerIntelligence::healthCheck()) {
    echo "API está online ✓";
} else {
    echo "API está offline ✗";
}
```

Útil em painéis de configuração (UI) para validar a conexão antes de usar a plataforma.

**Nomes de eventos** devem seguir o padrão `entidade.acao` (ex.: `article.viewed`) — consulte o [`EVENT_CATALOG.md`](../../../EVENT_CATALOG.md) da plataforma para o catálogo de eventos e significado de cada campo.

## Cookies utilizados

| Cookie | Duração | Propósito |
|---|---|---|
| `jmf_ci_visitor_id` | ~2 anos | Identificador anônimo do visitante. |
| `jmf_ci_session_id` | 30 min, renovado a cada request | Sessão de navegação (independente da sessão de autenticação do Laravel). |

Nomes e durações são configuráveis em `config/customer-intelligence.php`.

## Retry e falhas

**Estratégia de retry automático:**

| Erro | Comportamento | Logs |
|------|---------------|------|
| **Rede** (timeout, conexão recusada) | Retry com backoff [5s, 30s, 120s] | `warning` com attempt nº e próximo retry |
| **5xx** (erro de servidor) | Retry com backoff [5s, 30s, 120s] | `warning` com status e reason |
| **429** (rate limit) | Retry com backoff [5s, 30s, 120s] | `warning` indicando limite atingido |
| **4xx** (ex.: 401, 422) | Sem retry, apenas log | `warning` com detalhes da falha |
| **Esgotadas tentativas** | Log final de erro | `error` com stack trace completo |

**Logs estruturados:**

Todos os logs incluem contexto padronizado:
- `trace_id` (ULID) — identificador único para rastrear requisição ponta-a-ponta
- `endpoint` — qual endpoint foi chamado (events, contacts/identify)
- `event_id` / `visitor_id` — contexto do evento
- `status` — código HTTP da resposta
- `duration_ms` — tempo de execução
- `attempt` / `max_attempts` — informação de retry

Exemplo de log em `storage/logs/laravel.log`:

```
[2026-08-07T10:30:15+00:00] local.WARNING: JMF Customer Intelligence: erro transitório, agendando retry {
  "trace_id":"01ARZ3NDEKTSV4RRFFQ69G5FAV",
  "endpoint":"events",
  "event_id":"evt-123",
  "status":500,
  "attempt":1,
  "max_attempts":3,
  "next_retry_in_seconds":5
}
```

**Nenhuma falha interrompe o fluxo da aplicação cliente** — tudo é processado de forma assíncrona.

## Segurança e privacidade

**Dados sensíveis:**

O SDK valida todos os payloads e **nunca permite o envio de dados sensíveis**:
- Senhas, tokens, API keys
- Números de cartão de crédito
- CPF, SSN, PII sensível

Se detectar algum desses dados, a requisição é rejeitada com um log de erro.

**Tamanho máximo de payload:** 10 KB (para evitar abuso)

**Exemplo:** Tentando enviar um cartão de crédito:

```php
// ❌ Isso gera um erro, não é enviado
CustomerIntelligence::track('purchase.completed', [
    'credit_card' => '1234-5678-9012-3456',  // REJEITADO
]);
```

## Troubleshooting

**P: Os eventos não estão sendo enviados**

A. Verifique:
1. A fila está rodando: `php artisan queue:work`
2. O `.env` tem `JMF_CI_BASE_URL` e `JMF_CI_TOKEN` corretos
3. Verifique `storage/logs/laravel.log` para erros
4. Teste a conexão: `php artisan tinker` → `CustomerIntelligence::healthCheck()`

**P: Erro "Payload vazio" ou "campo obrigatório faltando"**

A. Cada endpoint tem campos obrigatórios:
- **events**: `event_id`, `event_name`, `visitor_id`, `occurred_at`
- **contacts/identify**: `visitor_id` + (`email` OU `external_id`)

O SDK gera `event_id`, `visitor_id`, `session_id` automaticamente. Você só precisa fornecer `event_name` (track) ou identificadores (identify).

**P: Erro "dados sensíveis detectados"**

A. Você tentou enviar dados privados. Remova campos como:
- `password`, `token`, `secret`, `api_key`
- `credit_card`, `ssn`, `cpf`
- Ou qualquer PII (Personally Identifiable Information) sensível

**P: Fila não está funcionando**

A. Configure uma fila real em production:

```env
# .env
QUEUE_CONNECTION=redis  # ou 'database', 'beanstalk', etc
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

E rode: `php artisan queue:work redis`

## Componentes UI (Plugin)

O SDK também inclui um painel administrativo completo com componentes Livewire 3:

### Componentes Livewire

| Componente | Rota | Funcionalidade |
|-----------|------|----------------|
| **Dashboard** | `/admin/plugin/jmf-ci` | Métricas gerais, gráficos, tabelas recentes |
| **Configuration** | `/admin/plugin/jmf-ci/configuration` | Validar conexão com API |
| **ContactIndex** | `/admin/plugin/jmf-ci/contacts` | Lista de contatos com busca e filtros |
| **ContactShow** | `/admin/plugin/jmf-ci/contacts/{id}` | Detalhe do contato com timeline de eventos |
| **EventIndex** | `/admin/plugin/jmf-ci/events` | Lista de eventos com filtros |

### Componentes Blade Auxiliares

- `<x-jmf-ci-metrics-card>` — Card com número + label + cor
- `<x-jmf-ci-event-chart>` — Gráfico de tendência (Chart.js)
- `<x-jmf-ci-connection-status>` — Indicador online/offline
- `<x-jmf-ci-metrics-row>` — Container grid para cards
- `<x-jmf-ci-event-table>` — Tabela genérica com paginação

**📖 Leia:** [PLUGIN_INSTALLATION.md](./PLUGIN_INSTALLATION.md) para instruções completas de instalação e customização do painel.

## Desenvolvimento do pacote

```bash
composer install
vendor/bin/pest                    # Rodar todos os testes
vendor/bin/pint                    # Formatar código (PSR-12)
composer analyse                   # Verificar tipos (PHPStan)
```

**Estrutura:**

```
packages/jmf-system/customer-intelligence-sdk/
├── config/
│   └── customer-intelligence.php
├── src/
│   ├── Client.php
│   ├── ConfigValidator.php
│   ├── PayloadValidator.php
│   ├── PayloadLogger.php
│   ├── Services/JmfCiApiClient.php           # Cliente para integração
│   ├── Facades/CustomerIntelligence.php
│   ├── Jobs/SendPayloadJob.php
│   ├── Middleware/
│   ├── Http/Middleware/ResolveVisitorAndSession.php
│   ├── Livewire/Plugins/JmfCi/               # Componentes da UI
│   │   ├── Dashboard.php
│   │   ├── Configuration.php
│   │   ├── Contacts/ContactIndex.php
│   │   ├── Contacts/ContactShow.php
│   │   └── Events/EventIndex.php
│   ├── Routes/plugin.php                     # Rotas da UI
│   ├── Providers/JmfCiPluginRouteServiceProvider.php
│   └── Support/
├── resources/
│   └── views/plugins/jmf-ci/                 # Templates
│       ├── components/                       # Componentes Blade
│       ├── dashboard.blade.php
│       ├── configuration.blade.php
│       ├── contacts/
│       └── events/
├── tests/                                    # 55 testes
├── PLUGIN_INSTALLATION.md                    # Guia de instalação
└── PLUGIN_ROUTES.md                          # Guia de rotas
```
