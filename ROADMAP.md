# Roadmap — JMF Customer Intelligence

Este roadmap guia o desenvolvimento incremental da plataforma. Cada fase só é iniciada após a conclusão e aprovação da anterior. Nenhuma fase é marcada como concluída sem testes automatizados e documentação atualizada.

Legenda: `[ ]` pendente · `[~]` em andamento · `[x]` concluído

---

## Fase 01 — Fundação e documentação

**Status:** `[~]` Em andamento (aguardando validação final via Apache/Laragon)

### Tarefas

- [x] Inspecionar pasta do projeto e validar ambiente (PHP, Composer, Node, npm, MySQL).
- [x] Criar `README.md`.
- [x] Criar `ROADMAP.md`.
- [x] Criar documentação complementar (`INSTALL.md`, `CONTRIBUTING.md`, `ARCHITECTURE.md`, `EVENT_CATALOG.md`, `SECURITY.md`).
- [x] Criar projeto Laravel 12.
- [x] Configurar MySQL local (`.env`, banco de dados `jmf_customer_intelligence`).
- [x] Configurar Blade, Livewire 3, Tailwind CSS 4, Alpine.js (via Livewire) e Vite.
- [x] Configurar Laravel Pint e análise estática (PHPStan/Larastan).
- [x] Decidir e documentar framework de testes (Pest).
- [x] Criar página inicial técnica do projeto (status/health check visual).
- [x] Executar migrations padrão sem erro.
- [x] Executar testes (`php artisan test`).
- [x] Executar build do Vite (`npm run build`).

### Critérios de aceite

- [~] Projeto Laravel 12 criado — execução via Apache/Laragon pendente de configuração do Virtual Host pelo usuário (passo manual na interface do Laragon, documentado em `INSTALL.md`).
- [x] Banco MySQL conectado.
- [x] `README.md` e `ROADMAP.md` completos.
- [x] Ambiente frontend compilando sem erro.
- [~] Página inicial técnica implementada e acessível via `php artisan serve`; validação final via Apache pendente do Virtual Host.
- [x] Migrations padrão executam sem erro.
- [x] Testes padrão passam (2 testes, 2 assertions).
- [x] Build do Vite conclui sem erro.
- [x] Comandos de qualidade documentados (`pint`, `composer analyse`, `composer refactor`, `php artisan test`).
- [x] Nenhuma funcionalidade das fases seguintes foi iniciada sem aprovação.

### Dependências

Nenhuma (fase inicial).

---

## Fase 02 — Autenticação e administração

**Status:** `[x]` Concluída

- [x] Login administrativo.
- [x] Usuários, perfis e permissões.
- [x] Layout administrativo.
- [x] Policies e Gates.
- [x] Auditoria inicial.
- [x] Testes de acesso.
- [x] Criação do primeiro administrador via seeder (`ADMIN_EMAIL`/`ADMIN_PASSWORD` no `.env`) e via comando interativo (`php artisan admin:create`).
- [x] Alternância de visibilidade de senha (mostrar/ocultar) nos campos de senha.
- [x] Edição de perfil próprio (nome e senha, com confirmação da senha atual) em `/admin/perfil`.

### Critérios de aceite

- [x] Login administrativo via Livewire (`/admin/login`), sessão autenticada, rate limiting básico.
- [x] Usuários internos da JMF System com perfis (`Super Admin`, `Administrador`) via `spatie/laravel-permission`.
- [x] CRUD de usuários (criação, edição, ativação/desativação, exclusão) protegido por `UserPolicy`.
- [x] Layout administrativo (sidebar, topbar) consistente com a identidade visual do projeto.
- [x] Auditoria (`audit_logs`) registrando login, logout, ações de gestão de usuários e atualização de perfil próprio, com tela de consulta.
- [x] Primeiro administrador provisionável sem credenciais commitadas (seeder lê `ADMIN_EMAIL`/`ADMIN_PASSWORD` do `.env`; alternativa via `php artisan admin:create`).
- [x] Usuário autenticado pode alterar o próprio nome e senha (exigindo senha atual) em `/admin/perfil`.
- [x] Testes automatizados cobrindo login, logout, autorização, auditoria e edição de perfil (27 testes, `php artisan test`).
- [x] `vendor/bin/pint`, `composer analyse` e `npm run build` sem erros.

### Dependências

Depende da Fase 01 (fundação do projeto — validação final do Virtual Host no Laragon segue como passo manual pendente do usuário, documentado em `INSTALL.md`, mas não bloqueia o desenvolvimento).

---

## Fase 03 — Multiempresa e aplicações

**Status:** `[x]` Concluída

- [x] Tenants.
- [x] Applications.
- [x] Tokens por aplicação.
- [x] Rotação e revogação de tokens.
- [x] Isolamento por tenant.
- [x] Testes de segurança.

### Critérios de aceite

- [x] `Tenant` (empresa/cliente) 1:N `Application` (produto/projeto), gerenciáveis via painel admin com CRUD completo protegido por `TenantPolicy`/`ApplicationPolicy`.
- [x] Autenticação de aplicações via token (Laravel Sanctum), com criação, rotação e revogação — o token completo só é exibido no momento da criação/rotação.
- [x] Rota `GET /api/v1/ping` (guard `sanctum` + `ensure.application.active` + rate limit por aplicação) valida a cadeia de autenticação/isolamento ponta a ponta; a rota real de ingestão de eventos fica para a Fase 04.
- [x] Isolamento: token de uma aplicação nunca retorna dados de outro tenant/aplicação; aplicação/tenant inativos são rejeitados (403); token inválido/ausente/revogado é rejeitado (401).
- [x] Exclusão de tenant bloqueada quando há applications vinculadas; exclusão de application revoga os tokens junto.
- [x] Testes automatizados cobrindo CRUD administrativo, autorização, gestão de tokens e segurança da API (29 novos testes, 51 no total em `php artisan test`).
- [x] `vendor/bin/pint`, `composer analyse` e `npm run build` sem erros.

### Dependências

Depende da Fase 02 (concluída).

---

## Fase 04 — Ingestão de eventos

**Status:** `[x]` Concluída

- [x] `POST /api/v1/events`.
- [x] Validação do payload.
- [x] Idempotência via `event_id`.
- [x] Rate limiting.
- [x] Database Queue.
- [x] Logs e tratamento de falhas.
- [x] Testes de API.

### Critérios de aceite

- [x] Rota `POST /api/v1/events` (guard `sanctum` + `ensure.application.active` + `throttle:api-events`) recebe o payload descrito em `EVENT_CATALOG.md`; `tenant_id`/`application_id` são sempre derivados do token autenticado, nunca do corpo da requisição.
- [x] `StoreEventRequest` valida campos obrigatórios (`event_id`, `event_name`, `visitor_id`, `occurred_at`), o padrão `entidade.acao` de `event_name` e o tamanho máximo (10 KB) de `properties`/`context`.
- [x] Idempotência garantida por `unique(application_id, event_id)` na tabela `events`: reenvio do mesmo `event_id` pela mesma aplicação retorna `200 duplicate` sem criar novo registro; o mesmo `event_id` em aplicações diferentes não é considerado duplicado.
- [x] Ingestão é assíncrona: `IngestEventAction` despacha `ProcessIncomingEventJob` (fila `database`, `tries=3`); o Job trata corridas de concorrência na constraint única e loga falhas finais via `failed()` (`Log::error`), além do registro padrão do Laravel em `failed_jobs`.
- [x] Rate limiting dedicado (`api-events`, 300 req/min por aplicação) separado do limite de `api-application` usado por `/ping`.
- [x] Exclusão de application com eventos vinculados é bloqueada (`DeleteApplicationAction`), mesmo padrão já usado para tenants com applications vinculadas.
- [x] Testes automatizados cobrindo sucesso, validação, limite de tamanho, idempotência, isolamento entre aplicações, autenticação/autorização e o bloqueio de exclusão (10 novos testes, 61 no total em `php artisan test`).
- [x] `vendor/bin/pint`, `composer analyse` e `npm run build` sem erros.

### Dependências

Depende da Fase 03 (concluída).

---

## Fase 05 — Visitantes, sessões e contatos

**Status:** `[x]` Concluída

- [x] Visitors.
- [x] Sessions.
- [x] Contacts.
- [x] Identify.
- [x] Associação anônimo-conhecido.
- [x] Timeline.
- [x] Consentimentos.

### Critérios de aceite

- [x] `Visitor` (por aplicação, `unique(application_id, visitor_id)`) e `VisitorSession` (tabela `visitor_sessions` — nome escolhido para não colidir com a tabela `sessions` do próprio Laravel/`SESSION_DRIVER=database`) são materializados automaticamente a partir dos eventos ingeridos, via `App\Events\EventWasIngested` disparado por `ProcessIncomingEventJob` (Fase 04) e tratado por `ResolveVisitorAndSessionListener` (fila `database`, `tries=3`), sem alterar a tabela `events`.
- [x] `Contact` é único por **tenant** (`unique(tenant_id, external_id)`), não por aplicação — permite unificar a mesma pessoa entre diferentes produtos da JMF System.
- [x] `POST /api/v1/contacts/identify` (guard `sanctum` + `ensure.application.active` + `throttle:api-application`) cria/atualiza um `Contact` (por `external_id` ou `email`) sem apagar dados já conhecidos, e vincula o `visitor_id` informado a esse contato de forma síncrona.
- [x] Associação anônimo→conhecido: uma vez linkado, todo evento passado e futuro do `visitor_id` resolve para o `Contact` via *join* em tempo de consulta (`Visitor.contact_id`), sem necessidade de backfill.
- [x] Timeline: `GetContactTimelineAction` retorna os eventos de um contato (via todos os seus `Visitor`s) ordenados por `occurred_at desc`, paginados; exposta em `/admin/contacts/{contact}` (somente leitura, permissão `contacts.view`).
- [x] Consentimentos LGPD: tabela `contact_consents` (`unique(contact_id, purpose)`, enum `ConsentPurpose`), capturados via `consents` no payload do `identify()`.
- [x] Exclusão de application com visitantes vinculados é bloqueada (`DeleteApplicationAction`), mesmo padrão usado para eventos (Fase 04).
- [x] Testes automatizados cobrindo resolução de visitante/sessão, identify (validação, idempotência de merge, consentimentos, associação anônimo-conhecido), Timeline (isolamento entre contatos) e o painel admin somente leitura (16 novos testes, 77 no total em `php artisan test`).
- [x] `vendor/bin/pint`, `composer analyse` e `npm run build` sem erros.

### Dependências

Depende da Fase 04 (concluída).

---

## Fase 06 — Analytics MVP

**Status:** `[ ]` Pendente · depende da Fase 05

- [ ] Dashboard geral.
- [ ] Filtros por aplicação e período.
- [ ] UTMs.
- [ ] Páginas, artigos e serviços mais acessados.
- [ ] Funis.
- [ ] Conversões.
- [ ] Tabelas agregadas (`daily_metrics`).

---

## Fase 07 — SDK Laravel

**Status:** `[ ]` Pendente · depende da Fase 04

- [ ] Pacote cliente inicial.
- [ ] `identify()`.
- [ ] `track()`.
- [ ] `conversion()`.
- [ ] Envio assíncrono.
- [ ] Retry e logs.
- [ ] Documentação de integração.

---

## Fase 08 — Integração com site pessoal

**Status:** `[ ]` Pendente · depende da Fase 07

- [ ] Eventos de navegação.
- [ ] Eventos do blog.
- [ ] Eventos do portfólio.
- [ ] Eventos de contato.
- [ ] Funil profissional.
- [ ] Dashboard específico.

---

## Fase 09 — Integração com Clube do Salão

**Status:** `[ ]` Pendente · depende da Fase 07

- [ ] Eventos de serviços.
- [ ] Eventos de profissionais.
- [ ] Eventos de agendamento.
- [ ] Conversões e cancelamentos.
- [ ] Recorrência.
- [ ] Dashboard específico.

---

## Fase 10 — Inteligência inicial

**Status:** `[ ]` Pendente · depende da Fase 06

- [ ] Lead score.
- [ ] Afinidade.
- [ ] Popularidade.
- [ ] Inatividade.
- [ ] Recomendações simples.
- [ ] API de recomendações.

---

## Fase 11 — Produção

**Status:** `[ ]` Pendente · depende de todas as fases anteriores

- [ ] Segurança avançada.
- [ ] Observabilidade.
- [ ] Políticas de retenção.
- [ ] Backups.
- [ ] Otimização de consultas.
- [ ] Preparação para VPS e Redis.

---

## Histórico de atualizações

- **2026-08-03** — Roadmap criado. Fase 01 iniciada (documentação criada; base Laravel em andamento).
- **2026-08-03** — Fase 02 concluída: login administrativo (Livewire), usuários/perfis/permissões (`spatie/laravel-permission`), layout administrativo, Policies/Gates, auditoria (`audit_logs`) e testes de acesso (Pest).
- **2026-08-03** — Ajustes solicitados após a entrega da Fase 02: botão de acesso ao painel na home; `AdminUserSeeder` para provisionar o primeiro administrador via `.env` (sem credenciais commitadas); alternância de mostrar/ocultar senha; tela de edição de perfil próprio (nome e senha).
- **2026-08-03** — Fase 03 concluída: Tenants e Applications (CRUD administrativo), autenticação de aplicações via token (Laravel Sanctum) com criação/rotação/revogação, rota `GET /api/v1/ping` para validar isolamento por tenant, e testes de segurança da API.
- **2026-08-05** — Fase 04 concluída: rota `POST /api/v1/events` (validação via `StoreEventRequest`, idempotência por `unique(application_id, event_id)`, ingestão assíncrona via `ProcessIncomingEventJob` na fila `database`, rate limiting dedicado `api-events`, logs de falha via `failed()`); ajuste pontual na Fase 03 bloqueando exclusão de application com eventos vinculados (`DeleteApplicationAction`).
- **2026-08-05** — Fase 05 concluída: materialização automática de `Visitor`/`VisitorSession` a partir dos eventos (`EventWasIngested` + `ResolveVisitorAndSessionListener`); `Contact` único por tenant com `POST /api/v1/contacts/identify` (criação/atualização incremental, associação anônimo-conhecido, consentimentos LGPD em `contact_consents`); Timeline por contato (`GetContactTimelineAction`) exposta em painel admin somente leitura (`/admin/contacts`); ajuste pontual na Fase 04 bloqueando exclusão de application com visitantes vinculados.
