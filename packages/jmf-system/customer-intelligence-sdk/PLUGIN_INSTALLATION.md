# Plugin Installation Guide — JMF Customer Intelligence

Guia completo para instalar e usar o plugin JMF Customer Intelligence em sua aplicação Laravel.

---

## Índice

1. [Pré-requisitos](#pré-requisitos)
2. [Instalação Rápida](#instalação-rápida)
3. [Configuração](#configuração)
4. [Usando os Componentes](#usando-os-componentes)
5. [Rotas do Plugin](#rotas-do-plugin)
6. [Customização](#customização)
7. [Troubleshooting](#troubleshooting)
8. [FAQ](#faq)

---

## Pré-requisitos

- **Laravel:** 11.0 ou superior (12.0 recomendado)
- **PHP:** 8.2 ou superior
- **Livewire:** 3.0 ou superior
- **Tailwind CSS:** 4.0 ou superior (já deve estar na sua app)
- **Composer:** Última versão

Verifique sua versão:

```bash
php --version
laravel --version
composer --version
```

---

## Instalação Rápida

### 1. Instalar o pacote

```bash
composer require jmf-system/customer-intelligence-sdk
```

Ou, se estiver usando um repositório privado:

```bash
# Adicione ao composer.json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/jmf-system/customer-intelligence-sdk.git"
    }
]

# Depois execute:
composer require jmf-system/customer-intelligence-sdk:dev-main
```

### 2. Publicar assets e config

```bash
php artisan vendor:publish --provider="JmfSystem\CustomerIntelligence\CustomerIntelligenceServiceProvider" --tag=customer-intelligence-config
```

### 3. Configurar variáveis de ambiente

Adicione ao seu `.env`:

```env
JMF_CI_ENABLED=true
JMF_CI_BASE_URL=https://ci.jmfsystem.com/api/v1
JMF_CI_TOKEN=seu_token_api_aqui
JMF_CI_TIMEOUT=5
JMF_CI_TRIES=3
JMF_CI_QUEUE_CONNECTION=redis
JMF_CI_QUEUE=default
```

**Obtendo o token:** Acesse o painel administrativo do Customer Intelligence → **Aplicações** → **Tokens** → Gere um novo token para sua aplicação.

### 4. Registrar as rotas do plugin (Opção A - Recomendada)

No arquivo `routes/web.php`:

```php
// routes/web.php

Route::middleware(['auth', 'admin'])->group(function () {
    require base_path('vendor/jmf-system/customer-intelligence-sdk/src/Routes/plugin.php');
});
```

**Opcional:** Se preferir registrar o ServiceProvider automaticamente:

```php
// config/app.php - em 'providers'

JmfSystem\CustomerIntelligence\Providers\JmfCiPluginRouteServiceProvider::class,
```

### 5. Validar instalação

```bash
# Verificar se as rotas foram registradas
php artisan route:list | grep jmf-ci

# Testar a conexão com a API
php artisan tinker
>>> app(\JmfSystem\CustomerIntelligence\Services\JmfCiApiClient::class)->healthCheck()
=> true // sucesso!
```

---

## Configuração

### Variáveis de Ambiente

| Variável | Obrigatória | Padrão | Descrição |
|----------|------------|--------|-----------|
| `JMF_CI_ENABLED` | Não | `true` | Ativar/desativar o SDK |
| `JMF_CI_BASE_URL` | **Sim** | — | URL base da API (ex: `https://ci.jmfsystem.com/api/v1`) |
| `JMF_CI_TOKEN` | **Sim** | — | Token de autenticação gerado no painel |
| `JMF_CI_TIMEOUT` | Não | `5` | Timeout de requisição (segundos) |
| `JMF_CI_TRIES` | Não | `3` | Número de tentativas com retry |
| `JMF_CI_BACKOFF` | Não | `5,30,120` | Delays de retry (segundos, separados por vírgula) |
| `JMF_CI_QUEUE_CONNECTION` | Não | `default` | Conexão da fila (`redis`, `database`, `sync`) |
| `JMF_CI_QUEUE` | Não | `default` | Nome da fila |
| `JMF_CI_VALIDATE_ON_BOOT` | Não | `true` | Validar config ao iniciar a app |

### Arquivo de Configuração

O arquivo `config/customer-intelligence.php` contém todas as opções e pode ser customizado:

```php
// config/customer-intelligence.php

return [
    'enabled' => env('JMF_CI_ENABLED', true),
    'base_url' => env('JMF_CI_BASE_URL'),
    'token' => env('JMF_CI_TOKEN'),
    'timeout' => (int) env('JMF_CI_TIMEOUT', 5),
    'tries' => (int) env('JMF_CI_TRIES', 3),
    'backoff' => array_map('intval', explode(',', env('JMF_CI_BACKOFF', '5,30,120'))),
    'queue_connection' => env('JMF_CI_QUEUE_CONNECTION', 'default'),
    'queue' => env('JMF_CI_QUEUE', 'default'),
    'validate_on_boot' => env('JMF_CI_VALIDATE_ON_BOOT', true),
    // ... mais configurações
];
```

---

## Usando os Componentes

### Dashboard

Exibe métricas gerais e tabelas de contatos/eventos recentes.

**Rota:** `GET /admin/plugin/jmf-ci`

**Componente:** `JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Dashboard`

**Uso em blade:**

```blade
@livewire('jmf-ci.dashboard')
```

**Funcionalidades:**
- 📊 Métricas em cards (Eventos, Visitantes, Sessões, Conversões)
- 📅 Seletor de período (Hoje, 7, 30, 90 dias)
- 📈 Gráfico de tendência diária
- 👥 Tabela de contatos recentes
- 📝 Tabela de eventos recentes

---

### Configuration

Valida a conexão com a API e exibe status.

**Rota:** `GET /admin/plugin/jmf-ci/configuration`

**Componente:** `JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Configuration`

**Uso em blade:**

```blade
@livewire('jmf-ci.configuration')
```

**Funcionalidades:**
- 🔧 Campos para Base URL e Token
- 👁️ Toggle para mostrar/ocultar token
- ✅ Botão "Validar Conexão" (chama `healthCheck()`)
- 🟢 Indicador de status (Online/Offline)
- 🕐 Último horário verificado

---

### ContactIndex

Lista paginada de todos os contatos com filtros.

**Rota:** `GET /admin/plugin/jmf-ci/contacts`

**Componente:** `JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Contacts\ContactIndex`

**Uso em blade:**

```blade
@livewire('jmf-ci.contacts.index')
```

**Funcionalidades:**
- 👥 Tabela de contatos (email, nome, lead score, último evento)
- 🔍 Busca em tempo real (email/nome)
- 📅 Filtro de período
- 📄 Paginação (25 contatos por página)
- 🔗 Link para detalhe do contato

---

### ContactShow

Detalhe completo do contato com timeline de eventos.

**Rota:** `GET /admin/plugin/jmf-ci/contacts/{id}`

**Componente:** `JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Contacts\ContactShow`

**Uso em blade:**

```blade
@livewire('jmf-ci.contacts.show', ['contactId' => $contact->id])
```

**Funcionalidades:**
- 👤 Informações do contato (email, nome, lead score)
- 📅 Data de criação
- 📋 Timeline de eventos (paginada)
- 🏷️ Propriedades de cada evento
- ⬅️ Link para voltar à lista

---

### EventIndex

Lista paginada de todos os eventos com filtros avançados.

**Rota:** `GET /admin/plugin/jmf-ci/events`

**Componente:** `JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Events\EventIndex`

**Uso em blade:**

```blade
@livewire('jmf-ci.events.index')
```

**Funcionalidades:**
- 📊 Tabela de eventos (tipo, visitante, contato, data)
- 🔍 Busca por visitante
- 🏷️ Filtro por tipo de evento
- 📅 Filtro de período
- 📄 Paginação (50 eventos por página)
- 🔍 Visualização de propriedades

---

## Rotas do Plugin

Todas as rotas estão prefixadas com `/admin/plugin/jmf-ci` e possuem o nome `jmf-ci.`:

```php
// Dentro de rotas protegidas pela sua aplicação:

Route::middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    route('jmf-ci.dashboard')      // GET /admin/plugin/jmf-ci

    // Configuration
    route('jmf-ci.configuration')  // GET /admin/plugin/jmf-ci/configuration

    // Contacts
    route('jmf-ci.contacts.index') // GET /admin/plugin/jmf-ci/contacts
    route('jmf-ci.contacts.show', $id) // GET /admin/plugin/jmf-ci/contacts/{id}

    // Events
    route('jmf-ci.events.index')   // GET /admin/plugin/jmf-ci/events
});
```

### Linking em Templates

```blade
<a href="{{ route('jmf-ci.dashboard') }}">Dashboard</a>
<a href="{{ route('jmf-ci.configuration') }}">Configuração</a>
<a href="{{ route('jmf-ci.contacts.index') }}">Contatos</a>
<a href="{{ route('jmf-ci.events.index') }}">Eventos</a>
```

---

## Customização

### Tema (Cores Tailwind)

Os componentes usam cores Tailwind. Para customizar:

```blade
{{-- Componente de Card --}}
<x-jmf-ci-metrics-card 
    label="Eventos" 
    value="1234" 
    color="blue"  {{-- blue, green, purple, orange, red --}}
/>
```

### Prefixo das Rotas

Se não quiser usar `/admin/plugin/jmf-ci`, customize em `routes/web.php`:

```php
Route::middleware(['auth', 'admin'])
    ->prefix('dashboard/intelligence')  {{-- customize aqui --}}
    ->group(function () {
        require base_path('vendor/jmf-system/customer-intelligence-sdk/src/Routes/plugin.php');
    });

// Resultado: /dashboard/intelligence, /dashboard/intelligence/configuration, etc.
```

### Layout

Os componentes usam Tailwind CSS puro e não dependem de um layout específico. Você pode envolvê-los no seu próprio layout:

```blade
@extends('layouts.app')

@section('content')
    <div class="p-6">
        <h1 class="text-3xl font-bold mb-6">Customer Intelligence</h1>
        
        @livewire('jmf-ci.dashboard')
    </div>
@endsection
```

---

## Deployment em Produção

### 1. Preparar Servidor

```bash
# Clonar repositório (ou fazer pull se já existe)
git clone https://seu-repo.com/seu-app.git
cd seu-app

# Instalar dependências
composer install --no-dev --optimize-autoloader

# Gerar chave da aplicação
php artisan key:generate

# Executar migrações (se houver)
php artisan migrate --force

# Construir assets
npm install --omit=dev
npm run build

# Cache de configuração (IMPORTANTE para performance)
php artisan config:cache
php artisan route:cache
```

### 2. Variáveis de Ambiente em Produção

Criar arquivo `.env.production`:

```env
APP_ENV=production
APP_DEBUG=false
JMF_CI_ENABLED=true
JMF_CI_BASE_URL=https://ci.jmfsystem.com/api/v1
JMF_CI_TOKEN=seu_token_seguro_aqui
JMF_CI_TIMEOUT=10
JMF_CI_TRIES=3
JMF_CI_BACKOFF=5,30,120
JMF_CI_QUEUE_CONNECTION=redis
JMF_CI_QUEUE=default
JMF_CI_VALIDATE_ON_BOOT=false
QUEUE_DRIVER=redis
```

### 3. Fila em Produção

```bash
# Usar Supervisor ou similar para garantir que a fila está sempre rodando
# Exemplo de configuração Supervisor:

[program:laravel-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/app/artisan queue:work redis --sleep=3 --tries=3 --timeout=300
autostart=true
autorestart=true
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/laravel-queue.log
```

### 4. Verificações Pré-Deployment

```bash
# Testar saúde da aplicação
php artisan health

# Testar conexão com API JMF
php artisan tinker
>>> app(\JmfSystem\CustomerIntelligence\Services\JmfCiApiClient::class)->healthCheck()
=> true // sucesso!

# Rodar testes
php artisan test
```

### 5. Após Deployment

```bash
# Limpar cache
php artisan cache:clear
php artisan view:clear

# Re-compilar configurações
php artisan config:cache
php artisan route:cache

# Monitorar logs
tail -f storage/logs/laravel.log
```

---

## Troubleshooting

### "Class not found" para Dashboard, Configuration, etc.

**Causa:** Livewire não encontra os componentes

**Solução:**

```bash
# 1. Verifique se Livewire 3+ está instalado
composer show livewire/livewire

# 2. Se não estiver:
composer require livewire/livewire

# 3. Registre os componentes no AppServiceProvider:
use Livewire\Livewire;

Livewire::component('jmf-ci.dashboard', \JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Dashboard::class);
Livewire::component('jmf-ci.configuration', \JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Configuration::class);
// ... etc
```

### "Connection refused" ao acessar a API

**Causa:** Base URL ou Token incorretos

**Solução:**

```bash
# 1. Verifique as variáveis de ambiente
env | grep JMF_CI

# 2. Teste a conexão
php artisan tinker
>>> app(\JmfSystem\CustomerIntelligence\Services\JmfCiApiClient::class)->healthCheck()

# 3. Se retornar false, verifique:
#    - Base URL está correta?
#    - Token é válido?
#    - API está online?
```

### Tabelas vazias / Sem dados

**Causa:** API não retorna dados (ainda não há eventos/contatos) ou fila não está rodando

**Solução:**

```bash
# 1. Verifique se há dados na API
php artisan tinker
>>> app(\JmfSystem\CustomerIntelligence\Services\JmfCiApiClient::class)->getMetrics()

# 2. Se retornar zeros, não há dados ainda. Para testar:
>>> use JmfSystem\CustomerIntelligence\Facades\CustomerIntelligence;
>>> CustomerIntelligence::track('product.viewed', ['id' => 1])

# 3. Processe a fila
php artisan queue:work

# 4. Verifique novamente
>>> app(\JmfSystem\CustomerIntelligence\Services\JmfCiApiClient::class)->getMetrics()
```

### Timeout ao fazer requisições

**Causa:** API está lenta ou timeout é muito curto

**Solução:**

```bash
# Aumentar timeout no .env
JMF_CI_TIMEOUT=15

# Ou se a API está realmente offline:
# - Verificar status da API
# - Aumentar número de retries
JMF_CI_TRIES=5
```

### "Livewire component not found"

**Causa:** Componentes não estão registrados

**Solução:**

```php
// app/Providers/AppServiceProvider.php

use Livewire\Livewire;
use JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Dashboard;
use JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Configuration;
// ... etc

public function boot(): void
{
    Livewire::component('jmf-ci.dashboard', Dashboard::class);
    Livewire::component('jmf-ci.configuration', Configuration::class);
    Livewire::component('jmf-ci.contacts.index', ContactIndex::class);
    Livewire::component('jmf-ci.contacts.show', ContactShow::class);
    Livewire::component('jmf-ci.events.index', EventIndex::class);
}
```

---

## FAQ

**P: Posso usar o plugin em várias aplicações ao mesmo tempo?**

R: Sim! Cada aplicação pode ter sua própria instalação do SDK. Basta usar o mesmo token de API, e todos os eventos/identificações convergirão para a mesma conta na plataforma.

**P: Como integro os eventos do meu app com o plugin?**

R: Use a Facade do SDK:

```php
use JmfSystem\CustomerIntelligence\Facades\CustomerIntelligence;

// Rastrear um evento
CustomerIntelligence::track('product.viewed', [
    'product_id' => $product->id,
    'price' => $product->price,
]);

// Identificar um usuário
CustomerIntelligence::identify([
    'email' => auth()->user()->email,
    'name' => auth()->user()->name,
]);
```

**P: Os dados são sincronizados em tempo real?**

R: Não exatamente. Os dados são enfileirados e enviados de forma assíncrona. A fila processa os dados em segundos/minutos dependendo da sua configuração. Para desenvolvimento, use `JMF_CI_QUEUE_CONNECTION=sync`.

**P: Posso customizar as cores dos componentes?**

R: Sim! Os componentes usam Tailwind CSS. Você pode customizar as cores passando propriedades aos componentes ou sobrescrevendo os arquivos de view publicados.

**P: Como faço para desabilitar o plugin?**

R: Simplesmente defina `JMF_CI_ENABLED=false` no `.env`. O SDK não enviará eventos e as rotas continuarão acessíveis (mas sem dados).

**P: Preciso backup dos dados que envio?**

R: Os dados são armazenados na plataforma JMF Customer Intelligence. Verifique a documentação de backup e retenção de dados da plataforma.

---

## Próximos Passos

1. ✅ Instalar o pacote
2. ✅ Configurar `.env`
3. ✅ Registrar rotas
4. ✅ Usar os componentes
5. 👉 **Integrar eventos da sua app** — veja [README.md](./README.md#uso)
6. 📊 **Monitorar no Dashboard**

---

**Precisa de ajuda?** Verifique:
- [README.md](./README.md) — Documentação técnica do SDK
- [PLUGIN_ROUTES.md](./PLUGIN_ROUTES.md) — Guia detalhado de rotas
- [PLUGIN_STRATEGY.md](../../../PLUGIN_STRATEGY.md) — Arquitetura e decisões

**Última atualização:** 2026-08-07  
**Versão:** 1.0.0
