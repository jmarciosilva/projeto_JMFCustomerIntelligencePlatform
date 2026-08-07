# Rotas do Plugin JMF Customer Intelligence

O SDK fornece rotas prontas para o painel de administração do Customer Intelligence. Você pode usá-las de duas formas:

## Opção 1: Registrar Manualmente (Recomendado)

Adicione as rotas ao arquivo `routes/web.php` da sua aplicação:

```php
// routes/web.php

Route::middleware(['auth', 'admin'])->group(function () {
    require base_path('vendor/jmf-system/customer-intelligence-sdk/src/Routes/plugin.php');
});
```

## Opção 2: Usar ServiceProvider (Automático)

Adicione o ServiceProvider ao seu `config/app.php`:

```php
// config/app.php

'providers' => [
    // ... outros providers
    JmfSystem\CustomerIntelligence\Providers\JmfCiPluginRouteServiceProvider::class,
],
```

**Nota:** Esta opção registra as rotas com middleware `web` e `auth`. Se precisar de middlewares adicionais (como `admin` ou `verified`), use a Opção 1.

## Rotas Disponíveis

| Método | Rota | Componente | Descrição |
|--------|------|-----------|-----------|
| GET | `/admin/plugin/jmf-ci` | Dashboard | Dashboard com métricas |
| GET | `/admin/plugin/jmf-ci/configuration` | Configuration | Configuração e validação |
| GET | `/admin/plugin/jmf-ci/contacts` | ContactIndex | Lista de contatos |
| GET | `/admin/plugin/jmf-ci/contacts/{id}` | ContactShow | Detalhe do contato |
| GET | `/admin/plugin/jmf-ci/events` | EventIndex | Lista de eventos |

## Nomes de Rotas

Todas as rotas são nomeadas com prefixo `jmf-ci.`:

```php
// Em suas views/controllers:
route('jmf-ci.dashboard')      // /admin/plugin/jmf-ci
route('jmf-ci.configuration')  // /admin/plugin/jmf-ci/configuration
route('jmf-ci.contacts.index') // /admin/plugin/jmf-ci/contacts
route('jmf-ci.contacts.show', $id) // /admin/plugin/jmf-ci/contacts/{id}
route('jmf-ci.events.index')   // /admin/plugin/jmf-ci/events
```

## Customização de Middlewares

Se precisar de middlewares diferentes, registre as rotas customizadas:

```php
// routes/web.php

Route::middleware(['auth', 'admin', 'verified'])->group(function () {
    require base_path('vendor/jmf-system/customer-intelligence-sdk/src/Routes/plugin.php');
});
```

## Customização de Prefixo

Para usar um prefixo diferente de `/admin/plugin/jmf-ci`:

```php
// routes/web.php

Route::middleware(['auth', 'admin'])->prefix('dashboard/intelligence')->group(function () {
    require base_path('vendor/jmf-system/customer-intelligence-sdk/src/Routes/plugin.php');
});
```

Neste caso, as rotas seriam:
- `/dashboard/intelligence/` (Dashboard)
- `/dashboard/intelligence/configuration`
- etc.

## Verificar Rotas Registradas

Para verificar se as rotas foram registradas corretamente:

```bash
php artisan route:list | grep jmf-ci
```

Deve exibir algo como:

```
GET|HEAD  /admin/plugin/jmf-ci ............................ jmf-ci.dashboard
GET|HEAD  /admin/plugin/jmf-ci/configuration ........... jmf-ci.configuration
GET|HEAD  /admin/plugin/jmf-ci/contacts ............... jmf-ci.contacts.index
GET|HEAD  /admin/plugin/jmf-ci/contacts/{contact} ..... jmf-ci.contacts.show
GET|HEAD  /admin/plugin/jmf-ci/events ................. jmf-ci.events.index
```

## Troubleshooting

**Erro: "Class not found" para Dashboard, etc.**
→ Certifique-se que o Livewire está instalado: `composer require livewire/livewire`

**Erro: "Route not found" ao acessar as rotas**
→ Verifique se as rotas foram registradas com `php artisan route:list`

**Rotas não aparecem após instalar o SDK**
→ Se não usou o ServiceProvider, precisa registrá-las manualmente em `routes/web.php`
