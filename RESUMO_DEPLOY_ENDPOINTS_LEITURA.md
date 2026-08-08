# Resumo de Deploy — Endpoints de Leitura da API v1

**Data:** 8 de agosto de 2026
**Objetivo:** implementar os endpoints GET que o SDK cliente (`jmf-system/customer-intelligence-sdk`) precisa para popular os componentes Livewire (`Dashboard`, `EventIndex`, `ContactIndex`, `ContactShow`) embutidos nas aplicações consumidoras (ex.: Feira Esquerda Livre). Antes desta mudança, a API só tinha endpoints de escrita (`POST /events`, `POST /contacts/identify`) e o health-check (`GET /ping`).

---

## Arquivos novos (5 controllers + 4 arquivos de teste)

```
app/Http/Controllers/Api/MetricsController.php
app/Http/Controllers/Api/ListEventsController.php
app/Http/Controllers/Api/ListContactsController.php
app/Http/Controllers/Api/ShowContactController.php
app/Http/Controllers/Api/ListContactEventsController.php

tests/Feature/Api/MetricsTest.php
tests/Feature/Api/EventsIndexTest.php
tests/Feature/Api/ContactsIndexTest.php
tests/Feature/Api/ContactShowTest.php
```

## Arquivos modificados (3)

```
app/Models/Event.php     — adicionada relação contact(): BelongsTo
app/Models/Contact.php   — adicionada relação events(): HasMany
routes/api.php           — 5 novas rotas GET registradas
```

---

## Novas rotas registradas

| Método | Rota | Controller | Nome |
|---|---|---|---|
| GET | `/api/v1/metrics` | `MetricsController` | `api.metrics.index` |
| GET | `/api/v1/events` | `ListEventsController` | `api.events.index` |
| GET | `/api/v1/contacts` | `ListContactsController` | `api.contacts.index` |
| GET | `/api/v1/contacts/{contact}` | `ShowContactController` | `api.contacts.show` |
| GET | `/api/v1/contacts/{contact}/events` | `ListContactEventsController` | `api.contacts.events` |

Todas protegidas pelo mesmo middleware já usado nas rotas existentes: `auth:sanctum` + `ensure.application.active` + `throttle:api-application`.

---

## diff — routes/api.php

```diff
--- a/routes/api.php
+++ b/routes/api.php
@@ -2,8 +2,13 @@

 use App\Http\Controllers\Api\EventIngestController;
 use App\Http\Controllers\Api\IdentifyContactController;
+use App\Http\Controllers\Api\ListContactEventsController;
+use App\Http\Controllers\Api\ListContactsController;
+use App\Http\Controllers\Api\ListEventsController;
+use App\Http\Controllers\Api\MetricsController;
 use App\Http\Controllers\Api\PingController;
 use App\Http\Controllers\Api\RecommendationsController;
+use App\Http\Controllers\Api\ShowContactController;
 use Illuminate\Support\Facades\Route;

 Route::middleware(['auth:sanctum', 'ensure.application.active'])
@@ -13,6 +18,11 @@
             Route::get('/ping', PingController::class)->name('api.ping');
             Route::post('/contacts/identify', IdentifyContactController::class)->name('api.contacts.identify');
             Route::get('/recommendations', RecommendationsController::class)->name('api.recommendations.index');
+            Route::get('/metrics', MetricsController::class)->name('api.metrics.index');
+            Route::get('/events', ListEventsController::class)->name('api.events.index');
+            Route::get('/contacts', ListContactsController::class)->name('api.contacts.index');
+            Route::get('/contacts/{contact}', ShowContactController::class)->name('api.contacts.show');
+            Route::get('/contacts/{contact}/events', ListContactEventsController::class)->name('api.contacts.events');
         });

         Route::middleware('throttle:api-events')->group(function (): void {
```

---

## diff — app/Models/Event.php

```diff
     public function application(): BelongsTo
     {
         return $this->belongsTo(Application::class);
     }
+
+    /**
+     * @return BelongsTo<Contact, $this>
+     */
+    public function contact(): BelongsTo
+    {
+        return $this->belongsTo(Contact::class);
+    }
 }
```

## diff — app/Models/Contact.php

```diff
     public function consents(): HasMany
     {
         return $this->hasMany(ContactConsent::class);
     }
+
+    /**
+     * @return HasMany<Event, $this>
+     */
+    public function events(): HasMany
+    {
+        return $this->hasMany(Event::class);
+    }
```

---

## Decisões de design

1. **Reaproveitamento de lógica existente:** `MetricsController` usa a mesma `GetDashboardOverviewAction` já usada pelo painel `/admin/analytics` nativo, apenas remapeando as chaves de resposta (`events_total` → `events`, etc.) para o formato que o SDK cliente espera. `trend` é convertido de lista de objetos `{date, events_total, ...}` para um mapa `{"08/08": 15, ...}`, formato exigido pelo componente de gráfico do SDK (`array_keys`/`array_values`).

2. **Isolamento de dados:**
   - `Event` é escopado por `application_id` (uma aplicação só vê os próprios eventos).
   - `Contact` é escopado por `tenant_id` (contatos são compartilhados entre applications do mesmo tenant, seguindo o modelo de dados já existente).
   - `ShowContactController` e `ListContactEventsController` retornam **404** (não 403) quando o contato pertence a outro tenant, para não confirmar a existência do registro a quem não tem acesso.
   - Eventos de um contato (`/contacts/{id}/events`) são adicionalmente filtrados por `application_id`, para não misturar eventos de outras applications do mesmo tenant.

3. **Paginação:** todos os endpoints de listagem seguem o envelope `{data, total, per_page, current_page}`, já documentado e esperado pelo `JmfCiApiClient` do SDK cliente.

4. **`per_page` limitado a 100** em todos os endpoints, para evitar consultas excessivamente grandes.

---

## Validação realizada

- `php artisan route:list --path=api/v1` — 5 novas rotas listadas sem conflito com as existentes.
- **17 novos testes de feature** (`MetricsTest`, `EventsIndexTest`, `ContactsIndexTest`, `ContactShowTest`) cobrindo: formato de resposta, rejeição sem token, isolamento por aplicação/tenant, filtros (`search`, `event_name`, `start_date`/`end_date`), e o caso de contato de outro tenant retornando 404.
- Suíte completa do projeto: **138 testes passando** (121 pré-existentes + 17 novos), **zero regressões**.

---

## Checklist para deploy em produção

1. `git add app/Models/Event.php app/Models/Contact.php routes/api.php app/Http/Controllers/Api/MetricsController.php app/Http/Controllers/Api/ListEventsController.php app/Http/Controllers/Api/ListContactsController.php app/Http/Controllers/Api/ShowContactController.php app/Http/Controllers/Api/ListContactEventsController.php tests/Feature/Api/MetricsTest.php tests/Feature/Api/EventsIndexTest.php tests/Feature/Api/ContactsIndexTest.php tests/Feature/Api/ContactShowTest.php`
2. Revisar e commitar.
3. Deploy para a VPS (`179.198.115.221`, `/var/www/jmf-ci-dev`).
4. Rodar `php artisan route:cache` (se usado em produção) para garantir que as novas rotas entrem no cache.
5. Validar em produção: `GET /api/v1/metrics` com um token de Application real deve retornar `{events, visitors, sessions, conversions, trend}`.
6. Conferir no admin da Feira Esquerda Livre (`/admin/customer-intelligence`) que o dashboard, a tabela de contatos e a tabela de eventos passam a exibir dados.

---

## Nota — arquivos não relacionados presentes no repositório

O `git status` também apontou estes arquivos, que **não foram criados nesta sessão** e não fazem parte desta mudança — mencionados aqui só para não gerar confusão na hora do commit:

- `deploy-jmf-ci.sh`
- `phpmyadmin-config.inc.php`
- um arquivo de scratchpad de outra sessão (caminho começando com `C:\Users\...\debug-migrations.sh`, aparentemente criado por engano dentro deste repositório)

Nenhum deles foi tocado por este trabalho — revise separadamente antes de decidir se entram no commit.
