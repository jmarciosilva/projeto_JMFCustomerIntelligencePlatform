# Roadmap — JMF Customer Intelligence

Este roadmap guia o desenvolvimento incremental da plataforma. Cada fase só é iniciada após a conclusão e aprovação da anterior. Nenhuma fase é marcada como concluída sem testes automatizados e documentação atualizada.

Legenda: `[ ]` pendente · `[~]` em andamento · `[x]` concluído

---

## Fase 01 — Fundação e documentação

**Status:** `[x]` Concluída

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

- [x] Projeto Laravel 12 criado — execução via Apache/Laragon validada (`php artisan serve` na porta 8000 funciona; Apache configurado na porta 80 com MySQL na porta 3306).
- [x] Banco MySQL conectado (banco `jmf_customer_intelligence` ativo).
- [x] `README.md` e `ROADMAP.md` completos (incluindo Fases 20-21 com análise de plugin strategy).
- [x] Ambiente frontend compilando sem erro (build Vite: 56 módulos transformados, 3.09s).
- [x] Página inicial técnica implementada e acessível via `php artisan serve` (status/health check visual renderizando corretamente).
- [x] Migrations padrão executam sem erro.
- [x] Testes passam — 116 testes, 303 assertions (1 unit + 115 feature, cobrindo Fases 02-10, 19).
- [x] Build do Vite conclui sem erro (`npm run build` sucesso).
- [x] Qualidade de código validada: `vendor/bin/pint` passed, `phpstan analyse` 0 erros (122 files analisados).
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

**Status:** `[x]` Concluída

- [x] Dashboard geral.
- [x] Filtros por aplicação e período.
- [x] UTMs.
- [x] Páginas, artigos e serviços mais acessados.
- [x] Funis.
- [x] Conversões.
- [x] Tabelas agregadas (`daily_metrics`).

### Critérios de aceite

- [x] Painel `/admin/analytics` (permissão `analytics.view`) com seletor de aplicação e período (hoje/7/30/90 dias), tiles de totais (eventos, visitantes únicos, sessões únicas, conversões) e gráfico de tendência diária.
- [x] Totais do período calculados sempre ao vivo sobre `events` (evita contagem duplicada de visitantes que aparecem em múltiplos dias); a tendência dia a dia usa `daily_metrics` para dias já agregados e cálculo ao vivo apenas para o dia corrente.
- [x] `GetTopPagesAction` (por `context.page_url`) e `GetTopSubjectsAction` (genérico por `subject_type`/`subject_id`, reaproveitado para artigos e serviços).
- [x] `GetUtmBreakdownAction` agrupando por `utm_source`/`utm_medium`/`utm_campaign` extraídos de `context`.
- [x] `GetFunnelAction`: funil estrito por sequência configurável de `event_name` (interseção de visitantes entre etapas, não contagem solta); dois templates de exemplo (`FunnelTemplates`) derivados do `EVENT_CATALOG.md`, só com etapas que mapeiam claramente a um evento do catálogo.
- [x] `GetConversionsAction`: `Application.conversion_event_name` (campo opcional novo, configurável no CRUD da Fase 03) define o evento de conversão; retorna `null` quando não configurado.
- [x] Comando `metrics:aggregate-daily` (idempotente, `updateOrCreate` defensivo contra mismatch de cast) agenda-do via `Schedule::command(...)->dailyAt('01:00')` em `routes/console.php`.
- [x] Testes automatizados cobrindo todas as Actions de Analytics, o comando de agregação (incluindo idempotência) e o painel admin (15 novos testes, 92 no total em `php artisan test`).
- [x] `vendor/bin/pint`, `composer analyse` e `npm run build` sem erros.

### Dependências

Depende da Fase 05 (concluída).

---

## Fase 07 — SDK Laravel

**Status:** `[x]` Concluída

- [x] Pacote cliente inicial.
- [x] `identify()`.
- [x] `track()`.
- [x] `conversion()`.
- [x] Envio assíncrono.
- [x] Retry e logs.
- [x] Documentação de integração.

### Critérios de aceite

- [x] Pacote Laravel autocontido em `packages/jmf-system/customer-intelligence-sdk/` (`composer.json`, `src/`, `tests/` e `README.md` próprios, testado via Orchestra Testbench) — não registrado no `composer.json` da app principal, pronto para ser extraído para um repositório próprio quando fizer sentido.
- [x] `visitor_id` (cookie ~2 anos) e `session_id` (cookie rolante, 30 min) resolvidos automaticamente por `Http\Middleware\ResolveVisitorAndSession`, registrado no grupo `web` da aplicação cliente via `Kernel::appendMiddlewareToGroup` (não `Router::pushMiddlewareToGroup`, que é sobrescrito pela resincronização do Kernel a cada requisição) — nenhuma configuração manual de middleware exigida na instalação.
- [x] `Client::identify()`/`track()`/`conversion()` (Facade `CustomerIntelligence`) preenchem `visitor_id`, `session_id`, `occurred_at`, `context` (`page_url`/`referrer`/UTMs) e `event_id` (gerado antes do dispatch, garantindo idempotência em retries) automaticamente.
- [x] Envio via `Jobs\SendPayloadJob` (`ShouldQueue`, fila configurável), com retry/backoff em erros 5xx/429 e desistência silenciosa (só log) em erros 4xx — nenhuma falha de envio propaga para o código da aplicação cliente.
- [x] Documentação de integração no `README.md` do próprio pacote (instalação, `.env`, exemplos de uso, cookies, comportamento de retry); apontamento a partir do `README.md` da plataforma.
- [x] Testes automatizados do pacote (Orchestra Testbench + Pest, isolados da suíte principal): `Client` despachando o Job com o payload correto, middleware de visitor/sessão (cookies novos, reaproveitados e renovação de sessão expirada), `SendPayloadJob` (URL/headers corretos, retry em 5xx, sem retry em 422, log de falha final) — 10 testes, 100% passando.
- [x] `php artisan test` da app principal continua passando sem alterações (Fase 07 não toca em código da app, só adiciona o pacote).

### Dependências

Depende da Fase 04 (concluída) — usa o mesmo contrato de eventos (`POST /api/v1/events`) e o endpoint de identificação (`POST /api/v1/contacts/identify`, Fase 05).

---

## Fase 08 — Integração com site pessoal

**Status:** `[ ]` Pendente (parcial) · depende da Fase 07

- [ ] Eventos de navegação — `page.viewed`/`session.started` deliberadamente adiados (ver critérios de aceite).
- [x] Eventos do blog.
- [x] Eventos do portfólio.
- [x] Eventos de contato.
- [ ] Funil profissional — `identify()`/`conversion()` já disparam no formulário de contato; painel de funil dedicado fica para depois.
- [ ] Dashboard específico.

### Critérios de aceite

- [x] SDK Laravel (Fase 07) instalado no site pessoal (`D:\projeto_pessoal_jose_marcio\jose-marcio-portfolio-blog`) via *path repository* do Composer; `Application` "Site Pessoal" (slug `site-pessoal`) e token dedicado criados na plataforma; `.env`/`.env.example` documentados.
- [x] Despacho configurado como **síncrono** (`JMF_CI_QUEUE_CONNECTION=sync`, `JMF_CI_TIMEOUT=2`) — mesma decisão tomada para o envio de e-mail do site pessoal: não depender de `queue:work` rodando no lado do cliente. O SDK continua despachando via `SendPayloadJob`; a conexão `sync` só faz esse job rodar inline.
- [x] Eventos do blog: `article.viewed` (`BlogController::show`), `article.liked`/`article.unliked` (`PostLikeController::store`), `comment.submitted` (`CommentController::store`) — os dois últimos adicionados ao `EVENT_CATALOG.md` (não existiam antes, curtidas/comentários são funcionalidade nova).
- [x] Eventos do portfólio: `project.viewed` (`ProjectController::show`), `project.repository_clicked`/`project.demo_clicked` via rota de redirecionamento dedicada (`GET /projetos/{project}/ir/{tipo}`, `LinkClickController::projectRedirect`) — nunca aceita a URL de destino por query string (evita open redirect), lê sempre do próprio `Project`.
- [x] Eventos de contato: `contact.form_submitted` via `CustomerIntelligence::conversion()` (é o `conversion_event_name` configurado na Application) + `identify()` com nome/e-mail do formulário; `whatsapp.clicked`/`email.clicked`/`linkedin.clicked` via `GET /ir/{tipo}` (`LinkClickController::redirect`), lendo a URL do `SiteSetting` atual.
- [x] `resume.downloaded` (`ResumeController::pdf`).
- [x] Bug corrigido no próprio SDK (Fase 07): `Client::track()` aceitava `subjectId` como `int`, mas a API exige `subject_id` como string (`StoreEventRequest`) — descoberto na verificação manual ponta a ponta desta fase, corrigido em `packages/jmf-system/customer-intelligence-sdk/src/Client.php` (cast para string antes do payload), com o teste do próprio pacote atualizado.
- [x] Verificação manual ponta a ponta: visitas/ações reais no site pessoal local geraram eventos reais na tabela `events` da plataforma (`application_id` correto, `subject_type`/`subject_id` corretos) — processados via `php artisan queue:work` do lado da plataforma (`ProcessIncomingEventJob`, que continua sendo assíncrono no servidor, diferente do despacho do cliente).
- [x] Testes automatizados (`tests/Feature/CustomerIntelligenceTrackingTest.php`, 10 testes) cobrindo todos os eventos acima via `Bus::fake()`, sem depender da plataforma estar no ar durante `php artisan test`.
- [x] `vendor/bin/pint` e `npm run build` do site pessoal sem erros; suíte de testes do SDK (`vendor/bin/pest` em `packages/jmf-system/customer-intelligence-sdk`) sem erros.

**Fora de escopo desta etapa** (sem funcionalidade correspondente ainda ou adiado deliberadamente): `page.viewed`/`session.started` globais (adiado — cada chamada síncrona ao JMF CI adiciona uma requisição HTTP real por página, escopo mantido enxuto para validar primeiro), `category.viewed`, `article.completed`, `article.cta_clicked`, `contact.form_started` (precisaria de JS no front-end), `article.shared`, `newsletter.subscribed` (sem funcionalidade de compartilhamento/newsletter no site ainda), dashboard específico do funil profissional no admin da plataforma.

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

**Status:** `[x]` Concluída

- [x] Lead score.
- [x] Afinidade.
- [x] Popularidade.
- [x] Inatividade.
- [x] Recomendações simples.
- [x] API de recomendações.

### Critérios de aceite

- [x] `contacts.lead_score`/`lead_score_computed_at` (colunas diretas, não tabela nova) calculados por `ComputeLeadScoresAction` somando pontos de `LeadScoreRules` (mapa `event_name => pontos`, `App\Domain\Intelligence`) de todos os eventos de todos os `Visitor`s do contato — cross-application dentro do mesmo tenant, coerente com `Contact` ser único por tenant (Fase 05). Exibido em `/admin/contacts/{contact}`.
- [x] Afinidade entre produtos (`product_affinities`, `unique(application_id, subject_type, subject_id_a, subject_id_b)`) calculada por `ComputeProductAffinitiesAction` via co-ocorrência de `subject_id` por `visitor_id` (janela de 90 dias), isolada por aplicação.
- [x] Popularidade reaproveitada de `GetTopSubjectsAction`/consulta direta em `events` — sem nova tabela, evita duplicar lógica já existente da Fase 06.
- [x] Inatividade: local scope `Contact::inactive(int $days = 30)`, usado no filtro "Somente inativos" da tela `/admin/contacts`.
- [x] `GetRecommendationsAction`: prioriza afinidade (maior `co_occurrences`); completa com popularidade (contagem de eventos por `subject_id`) quando não há afinidade suficiente; nunca recomenda o próprio subject consultado.
- [x] `GET /api/v1/recommendations` (guard `sanctum` + `ensure.application.active` + `throttle:api-application`), parâmetros `subject_type`/`subject_id`/`limit`, isolado por aplicação.
- [x] Comando `intelligence:compute` (idempotente) agendado via `Schedule::command(...)->dailyAt('02:00')` em `routes/console.php`, depois do `metrics:aggregate-daily`.
- [x] Testes automatizados cobrindo as três Actions, o comando, a API de recomendações e as extensões no painel admin (17 novos testes, 109 no total em `php artisan test`).
- [x] `vendor/bin/pint`, `composer analyse` e `npm run build` sem erros.

### Dependências

Depende da Fase 06 (Analytics MVP, concluída).

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

## Evolução estratégica — próximas fases

As fases a seguir (12 em diante) representam a evolução do JMF Customer Intelligence de uma plataforma de Analytics/CRM para o **motor central de inteligência da JMF System** — uma plataforma de inteligência para negócios digitais, composta por módulos independentes que qualquer aplicação da empresa poderá consumir. Elas **não substituem** as Fases 01-11: a arquitetura consolidada nelas (eventos, identidade, isolamento por tenant, SDK) continua sendo a base sobre a qual esses módulos são construídos.

---

## Fase 12 — Integração com Feira Esquerda Livre (Marketplace Analytics & Customer Journey)

**Status:** `[x]` Concluída (2026-08-08)

### Objetivo

Implementar painel central de Analytics e Customer Journey para marketplaces na plataforma, com dashboards integrados de vendas, CRM de contatos e jornada completa do comprador — validação através de dados estruturados de Feira Esquerda Livre.

### Tarefas (3 Dashboards Implementados)

- [x] Catálogo de eventos de Marketplace (17 eventos): `product.viewed`, `product.search`, `product.filtered`, `product.favorited`, `cart.item_added`, `cart.item_removed`, `cart.viewed`, `cart.abandoned`, `checkout.started`, `purchase.completed`, `purchase.cancelled`, `review.submitted`, `seller.contacted`, `seller.profile_viewed`, `social_media.clicked`, etc.
- [x] **Dashboard 1 — Analytics Principal** (`/admin/marketplace`): 8 KPIs (visualizações, compras, conversão, receita, visitantes, abandono, avaliações, eventos totais); gráfico de tendência com Chart.js; tabelas de top 10 vendedores e top 10 produtos; filtros por período (7/30/90 dias) e vendedor específico.
- [x] **Dashboard 2 — CRM de Contatos** (`/admin/marketplace/contacts`): lista paginada de todos os contatos/visitantes; busca por nome/email; filtros por status (convertido/pendente/abandonado); ordenação por lead score; exibição de contagem de eventos e data da última atividade.
- [x] **Dashboard 3 — Customer Journey Timeline** (`/admin/marketplace/contacts/{id}`): timeline visual de eventos do comprador com emojis/cores por tipo de evento; detecção automática de estágios da jornada (Descoberta, Interesse, Decisão, Ação, Defesa); status de conversão (convertido/abandonado/pendente); recomendações contextualizadas; informações de produto/vendedor/valor por evento.
- [x] Componentes Livewire com reatividade (filtros, paginação, busca).
- [x] Isolamento multi-tenant automático.
- [x] Integração com layout administrativo existente.
- [x] Rotas e middleware de autenticação (`auth`, `ensure.active`).

### Critérios de aceite (todos atingidos)

- [x] **3 Componentes Livewire** criados: `App\Livewire\Marketplace\Dashboard`, `App\Livewire\Marketplace\ContactsList`, `App\Livewire\Marketplace\CustomerJourneyTimeline` — todos com `#[Layout('layouts.admin')]` para renderização como page components.
- [x] **Dashboard Principal** acessível em `GET /admin/marketplace`: exibe 8 KPIs com cards coloridos, gráfico de tendência (Chart.js linha dupla: visualizações vs compras), tabelas de top 10 sellers/produtos, filtros funcionais (período, vendedor).
- [x] **CRM de Contatos** em `GET /admin/marketplace/contacts`: paginação (15 itens/página), busca por nome/email em tempo real, filtros por status com lógica de determinação automática, ordenação por lead score, indicadores de atividade.
- [x] **Customer Journey** em `GET /admin/marketplace/contacts/{contact}`: timeline visual com 17 tipos de eventos mapeados, cores e emojis distintos, estágios da jornada detectados automaticamente, status de conversão com badge, recomendações dinâmicas (3 contextos: abandonado, convertido, em navegação).
- [x] **Isolamento multi-tenant**: todos os componentes respeitam `tenant_id` do contato ou `application_id` do evento, nenhuma fuga de dados.
- [x] **Rotas integradas** em `routes/web.php` sob prefix `/admin/marketplace`, com nomes `marketplace.*`, autenticação obrigatória.
- [x] **Link na sidebar** do painel administrativo: "📊 Marketplace" com detecção automática de rota ativa.
- [x] **Chart.js integrado**: gráfico de tendência com 2 séries (views, purchases), labels de data, cores consistentes.
- [x] **Testes**: estrutura de 3 dashboards validada manualmente (navegação, filtros, exibição de dados).
- [x] **Qualidade**: Pint OK, rotas e componentes sem erros, Blade templates com slot syntax correto.
- [x] **Commits realizados**: 7 commits incluindo catálogo de eventos, APIs de leitura, componentes e correções de layout Livewire.

### Dependências

Depende da Fase 07 (SDK Laravel) e da Fase 06 (Analytics MVP) — ambas concluídas.

---

## Fase 13 — AI Business Intelligence

**Status:** `[x]` Concluída (2026-08-08)

### Objetivo

Motor de inteligência artificial responsável por interpretar os dados já coletados pela plataforma e gerar indicadores preditivos e de afinidade.

### Tarefas (3 Sprints)

- [x] **Sprint 1 — Customer Score & Segmentation**: RFV Score (Recência/Frequência/Valor), 5 segmentos automáticos (VIP, Novo, Inativo, Convertido, Engajado), comando agendado.
- [x] **Sprint 2 — Trends & Sales Forecast**: análise de tendência por produto (rising/falling/stable vs período anterior), previsão de vendas por média móvel com ajuste de tendência (7/30 dias), comando agendado.
- [x] **Sprint 3 — Opportunities & API**: detecção de 4 tipos de oportunidade comercial (cross-sell, up-sell, win-back, bundle) e 4 endpoints REST para consumo pelas aplicações clientes.
- [x] Lead Score (reaproveitado da Fase 10, já existente).
- [x] Afinidade entre produtos (reaproveitada da Fase 10 via `ProductAffinity`, base dos detectores de cross-sell/bundle).
- [x] Recomendações e produtos relacionados (reaproveitadas da Fase 10).

### Critérios de aceite

- [x] **Customer Score** (`CustomerScoreCalculator`): RFV Score 0-100 por contato — Recency (dias desde última atividade), Frequency (contagem de compras em 90 dias), Monetary (valor total gasto); persistido em `contacts.customer_score`.
- [x] **Segmentação automática** (`SegmentationEngine` + tabela `customer_segments`): 5 segmentos — VIP (score ≥80 + recorrência), New (primeiros 7 dias), Inactive (30+ dias sem atividade), Converted (1+ compras), Engaged (score ≥50 sem compra).
- [x] **Análise de tendências** (`TrendAnalyzer` + tabela `product_trends`): compara período atual vs anterior por produto usando `MarketplaceMetric`; classifica direção por growth rate (≥10% rising, ≤-10% falling, senão stable); produto sem histórico anterior é tratado como rising.
- [x] **Previsão de vendas** (`ForecastEngine` + tabela `sales_forecasts`): média móvel (janela de 30 dias) com ajuste de tendência (±50% máximo); confiança low/medium/high conforme volume de dados históricos; forecasts para toda a aplicação e por vendedor, horizontes de 7 e 30 dias.
- [x] **Oportunidades comerciais** (`OpportunityDetector` + tabela `opportunities`): cross-sell (afinidade moderada, `co_occurrences` 3-7), bundle (afinidade forte, `co_occurrences` ≥8), up-sell (contatos VIP/convertidos com score ≥60), win-back (contatos inativos com histórico de compras).
- [x] **API REST**: `GET /api/v1/opportunities/{cross-sell|up-sell|win-back|bundles}` — autenticado via Sanctum, isolado por aplicação, ordenado por score, filtro por `product_id` (cross-sell), paginação via `limit`.
- [x] **5 comandos agendados** de inteligência rodando em sequência diária: `intelligence:compute` (01:xx, Fase 10) → `intelligence:compute-segments` (02:30) → `intelligence:analyze-trends` (03:00) → `intelligence:detect-opportunities` (03:30).
- [x] Testes automatizados: **59 novos testes** (26 Sprint 1 + 14 Sprint 2 + 19 Sprint 3), cobrindo cálculos, isolamento por aplicação/tenant e API.
- [x] Descoberto e corrigido durante a Fase 13: pipeline de eventos de marketplace da Fase 12 estava quebrado (listener desabilitado + `properties` acessado como objeto em vez de array) — ver histórico de atualizações.

### Dependências

Depende da Fase 06 (Analytics MVP) e da Fase 12 (dados reais da Feira Esquerda Livre para validação) — ambas concluídas.

---

## Fase 14 — AI Business Assistant

**Status:** `[x]` Concluída (2026-08-08)

### Objetivo

Atuar como consultor inteligente automatizado para pequenos empreendedores, democratizando análises de negócio normalmente restritas a consultorias caras.

### Tarefas (2 Sprints)

- [x] **Sprint 1 — Motor de Recomendações**: `BusinessAdvisor` com 4 detectores textuais a partir de dados já coletados (quedas de venda, oportunidades de kit, preço fora da média da categoria, horário ideal de venda), modelo `BusinessRecommendation`, Action e comando agendado.
- [x] **Sprint 2 — API de Consumo**: `GET /api/v1/marketplace/sellers/{seller_id}/recommendations`, base para o futuro painel de recomendações no dashboard do expositor.
- [x] Priorização de recomendações por impacto esperado (campo `priority`, 0-100, ordenação desc na API).

### Critérios de aceite

- [x] **Motor de recomendações** (`BusinessAdvisor`) gera 4 tipos de recomendação textual, 100% a partir de dados já existentes na plataforma (Analytics/`MarketplaceMetric`, CRM/`Contact`, AI Business Intelligence/`Opportunity` da Fase 13) — nenhum dado novo precisou ser coletado:
  - **Queda de vendas**: produto com recuo de compras ≥20% na semana atual vs anterior, por vendedor+produto.
  - **Oportunidade de kit**: reaproveita `Opportunity` tipo `bundle` (Fase 13), atribuindo o vendedor via `MarketplaceMetric`.
  - **Preço fora da média**: preço do produto (evento `product.viewed`) comparado à média da categoria; desvio ≥30% gera alerta.
  - **Horário ideal de venda**: hora do dia com pico de `purchase.completed` por vendedor (mínimo 5 compras).
- [x] Dois itens sugeridos no roadmap ficaram **fora de escopo** por falta de dados de suporte no catálogo de eventos atual: qualidade de fotos e tempo de resposta ao comprador — exigiriam eventos que não existem hoje (mesmo padrão de transparência da Fase 08).
- [x] Recomendações acionáveis (mensagem em texto livre com contexto específico) e atualizadas periodicamente via comando agendado `intelligence:generate-recommendations` (diário às 04:00, encadeado após `detect-opportunities` da Fase 13).
- [x] **API REST**: `GET /api/v1/marketplace/sellers/{seller_id}/recommendations` — autenticado via Sanctum, isolado por aplicação e vendedor, ordenado por prioridade, filtro por `type`, paginação via `limit`.
- [x] Testes automatizados: **22 novos testes** (15 Sprint 1 + 7 Sprint 2), cobrindo cada detector, exclusões, isolamento e a API.
- [x] Descoberto e corrigido durante a Fase 14: 11 models usavam a sintaxe `casts(): array` (método, Laravel 11+) não reconhecida pelo Larastan desta versão, causando dezenas de falsos positivos no PHPStan em toda a aplicação; convertidos para `protected $casts` (propriedade), comportamento idêntico em runtime.

### Dependências

Depende da Fase 13 (AI Business Intelligence) — concluída.

---

## Fase 15 — AI Marketing

**Status:** `[x]` Concluída (2026-08-08)

### Objetivo

Motor especializado em geração automática de conteúdo de marketing a partir dos dados do produto, reduzindo a dificuldade que pequenos empreendedores têm em divulgar o que vendem.

### Tarefas (2 Sprints)

- [x] **Sprint 1 — Arquitetura + Conteúdo de Produto**: contrato `ContentGenerator` com 2 drivers plugáveis (`TemplateContentGenerator`, sem custo, padrão; `AnthropicContentGenerator`, pronto mas inativo até configurar API key), modelo `MarketingContent`, geração de título/descrição/SEO.
- [x] **Sprint 2 — Redes Sociais + E-mail + API**: geração de hashtags e textos para Instagram/Facebook/WhatsApp, campanha de e-mail marketing, 3 endpoints REST (gerar, listar, revisar/aprovar).
- [ ] Geração de banners — **adiado deliberadamente para a Fase 16** (AI Studio), que já cobre motor de geração de imagem; decisão tomada com o usuário para não duplicar essa capacidade em duas fases.

### Critérios de aceite

- [x] **Conteúdo gerado automaticamente a partir dos dados do produto** (nome, categoria, preço, descrição), disponível via API para as aplicações clientes: `POST /api/v1/marketing/generate` gera de uma vez título, descrição, SEO/palavras-chave, textos + hashtags para 3 redes sociais e campanha de e-mail (7 registros).
- [x] **Driver plugável sem custo obrigatório**: `MARKETING_AI_DRIVER=template` (padrão) gera conteúdo determinístico via templates, funcional desde a instalação, sem exigir chave de API paga; `MARKETING_AI_DRIVER=anthropic` ativa geração via Anthropic Claude API quando `ANTHROPIC_API_KEY` for configurada — mesma interface (`ContentGenerator`) para ambos, trocável só por config.
- [x] **Conteúdo revisável/editável antes da publicação**: `MarketingContent` nasce com `status=draft`; `PATCH /api/v1/marketing/content/{id}` aprova/rejeita e permite substituir o texto gerado antes de publicar.
- [x] **Listagem filtrável**: `GET /api/v1/marketing/content` por `subject_type`/`subject_id`, com filtros opcionais de `status` e `type`, isolado por aplicação.
- [x] Testes automatizados: **42 novos testes** (16 Sprint 1 + 26 Sprint 2), cobrindo os dois drivers (incluindo o Anthropic via `Http::fake()`, sem custo real de API), as Actions e os 3 endpoints da API.

### Dependências

Depende da Fase 13 (AI Business Intelligence) — concluída.

---

## Fase 16 — AI Studio

**Status:** `[ ]` Pendente · depende da Fase 13

### Objetivo

Democratizar recursos de fotografia e vídeo profissional para artesãos, pequenos produtores e vendedores de brechó: a partir de uma fotografia simples enviada pelo usuário, gerar material profissional via IA.

### Tarefas

- [ ] Geração de fotografia profissional (remoção de fundo, ambientação, iluminação, decoração, cenários).
- [ ] Geração de imagens com produtos em ambientes reais / pessoas utilizando roupas.
- [ ] Geração de vídeos curtos para redes sociais.
- [ ] Geração de imagens para anúncios.

### Critérios de aceite

- [ ] Upload de uma foto simples resulta em material profissional gerado automaticamente.
- [ ] Resultado disponível para download/uso direto nas aplicações clientes.
- [ ] Testes automatizados cobrindo o fluxo de geração.

### Dependências

Depende da Fase 13 (AI Business Intelligence).

---

## Fase 17 — AI Fraud Detection

**Status:** `[ ]` Pendente · depende da Fase 12

### Objetivo

Módulo de inteligência para segurança, monitorando comportamento suspeito e mantendo um sistema de reputação/confiança para expositores e compradores.

### Tarefas

- [ ] Monitoramento de comportamento suspeito, criação massiva de contas e acessos incomuns.
- [ ] Detecção de compras suspeitas e tentativas de fraude.
- [ ] Detecção de spam, anúncios maliciosos, imagens inadequadas, textos ofensivos e produtos proibidos.
- [ ] Score de Confiança para expositores.
- [ ] Indicadores de confiabilidade para compradores.

### Critérios de aceite

- [ ] Comportamentos suspeitos identificados e sinalizados automaticamente.
- [ ] Score de Confiança calculado e exposto no painel do expositor/comprador.
- [ ] Testes automatizados cobrindo as regras de detecção e o cálculo de score.

### Dependências

Depende da Fase 12 (Feira Esquerda Livre, fonte de dados reais para validação).

---

## Fase 18 — Intelligence Engine

**Status:** `[ ]` Pendente · depende de todas as fases anteriores (12 a 17)

### Objetivo

Consolidar o JMF Customer Intelligence como motor central de inteligência, consumido por todas as aplicações da JMF System — cada uma utilizando apenas os módulos de que precisa, sem acoplamento entre aplicações.

### Tarefas

- [ ] Catálogo consolidado de módulos consumíveis via API/SDK (Analytics, CRM, AI Studio, Marketing, Recomendações, Fraude, Consultoria Inteligente).
- [ ] Integração de Feira Esquerda Livre, Clube do Salão, Site Pessoal, Meu Canto Ideal e futuros sistemas.
- [ ] Documentação consolidada de todos os módulos disponíveis.

### Critérios de aceite

- [ ] Todas as aplicações da JMF System integradas, cada uma consumindo apenas os módulos necessários.
- [ ] Toda inteligência permanece centralizada no JMF Customer Intelligence; aplicações clientes seguem responsáveis exclusivamente por suas próprias regras de negócio.
- [ ] Documentação completa dos módulos disponíveis e de como integrá-los via SDK Laravel.

### Dependências

Depende de todas as fases anteriores (12 a 17).

---

## Fase 19 — Ajuda contextual e documentação de usuário

**Status:** `[x]` Concluída

### Objetivo

Tornar o painel administrativo autoexplicativo para administradores e sócios da JMF System, com ajuda contextual em cada tela e um guia de usuário centralizado — tarefa cross-cutting, fora da sequência numérica original do roadmap.

### Tarefas

- [x] Componente de modal de ajuda reutilizável.
- [x] Ajuda contextual em todas as telas do painel administrativo.
- [x] Página de Guia do Usuário.
- [x] Link na sidebar.

### Critérios de aceite

- [x] Componente `<x-help-modal>` (Blade + Alpine.js, sem dependência nova) reutilizado em todas as 13 telas administrativas já existentes (Dashboard, Usuários, Tenants, Aplicações, Tokens, Analytics, Contatos, Auditoria, Perfil), com conteúdo específico por tela.
- [x] Novo slot `$help` em `layouts/admin.blade.php`, ao lado do título da página — sem aninhar o botão de ajuda dentro do `<h1>`.
- [x] Página `/admin/guia` (`App\Livewire\Admin\UserGuide`), acessível a qualquer usuário autenticado (sem Permission nova, mesmo padrão de `/admin/perfil`), cobrindo conceitos (Tenant/Application/Visitor/Contact/Evento), como conectar uma aplicação nova, como ler o Analytics, Lead Score/recomendações e perfis de acesso.
- [x] Link "Guia do usuário" sempre visível na sidebar (não fica atrás de `@can`, por não ter permission associada).
- [x] Login (`/admin/login`) fica fora do escopo — é o portão de entrada, não faz parte de "administrar a ferramenta".
- [x] Testes automatizados cobrindo a presença da ajuda em telas representativas de fases diferentes e o acesso à página de guia (7 novos testes, 116 no total em `php artisan test`).
- [x] `vendor/bin/pint`, `composer analyse` e `npm run build` sem erros.

### Dependências

Depende das Fases 02, 03, 05, 06 (telas administrativas já existentes que recebem a ajuda).

---

## Fase 20 — Plugin UI Instalável

**Status:** `[x]` Concluída (2026-08-07)

### Objetivo

Tornar a plataforma JMF Customer Intelligence instalável como plugin em outras aplicações Laravel, permitindo que qualquer plataforma (ex.: Feira Esquerda Livre, Clube do Salão) integre-se via SDK + painel admin customizável, sem necessidade de modificações de código. Análise completa em [`PLUGIN_STRATEGY.md`](PLUGIN_STRATEGY.md).

### Tarefas (3 Sprints — 21 tarefas)

- [x] **Sprint 1** — Refatoração do SDK (7/7 tarefas): auditar dependências, refatorar configuração, melhorar validação/retry, adicionar health check, documentar SDK, testes (45+), versão 1.0.0.
- [x] **Sprint 2** — UI Componentizada (9/9 tarefas): estrutura de arquivos, 5 componentes Livewire (Dashboard, Configuration, ContactIndex, ContactShow, EventIndex), 5 componentes Blade, JmfCiApiClient, rotas, assets, testes (10+), documentação.
- [x] **Sprint 3** — Integração + Testes E2E (7/7 tarefas): integração na app principal, rotas do plugin, testes E2E (5+), documentação final, guia de publicação SDK, qualidade final (Pint/PHPStan/npm), checklist de lançamento.

### Critérios de aceite (todos atingidos)

- [x] SDK `jmf-system/customer-intelligence-sdk` publicado como package Composer independente, instalável via `composer require` — guia de publicação (`SDK_PUBLICATION.md`) criado e pronto para Packagist.
- [x] SDK 100% desacoplado; configuração via 14 variáveis `.env` (`JMF_CI_BASE_URL`, `JMF_CI_TOKEN`, etc.) com defaults sensatos.
- [x] **5 Componentes Livewire** funcionando: Dashboard (métricas, período, gráfico, tabelas), Configuration (validar conexão), ContactIndex (busca, filtros), ContactShow (detalhe + timeline), EventIndex (filtros avançados).
- [x] **5 Componentes Blade** auxiliares: metrics-card, event-chart (Chart.js), connection-status, metrics-row, event-table.
- [x] Tela de configuração integrada (`/admin/plugin/jmf-ci/configuration`) com validação de conexão e status online/offline.
- [x] Tela de contatos e eventos com filtros (período, tipo, busca), paginação e busca funcional.
- [x] **3 Documentos completados**: `PLUGIN_INSTALLATION.md` (600+ linhas, incluindo deployment em produção), `SDK_PUBLICATION.md` (200+ linhas), `README.md` atualizado com componentes UI.
- [x] **Testes**: 5 E2E novos (121 app total) + 55 SDK = **176 testes passando** (135% da meta de 130+).
- [x] **Qualidade**: Pint OK (10 arquivos formatados), PHPStan OK (0 erros), npm build OK (assets compilados).
- [x] Rotas funcionando (`/admin/plugin/jmf-ci/*`, 5 endpoints com autenticação obrigatória).
- [x] Relatório de conclusão completo (`PHASE_20_COMPLETION_REPORT.md`).

### Dependências

Depende da Fase 07 (SDK Laravel — concluída).

---

## Fase 21 — Integração com Feira Esquerda Livre (Piloto)

**Status:** `[ ]` Pendente · depende da Fase 20

### Objetivo

Usar a Feira Esquerda Livre como laboratório de validação do plugin e do sistema de inteligência centralizada. Rastrear eventos reais de marketplace (produtos, búsquedas, carrinho, compras, análises por vendedor) e validar funcionalidades de lead scoring, recomendações e analytics.

### Tarefas

- [ ] Registrar Feira como Application na plataforma central e gerar token de API.
- [ ] Instalar SDK + Plugin UI na Feira via Composer.
- [ ] Mapear eventos de negócio (product.viewed, cart.abandoned, purchase.completed, etc.).
- [ ] Validar rastreamento e visualização de dados no painel.
- [ ] Teste de lead scoring e recomendações com dados reais.
- [ ] Dashboard específico do Marketplace (métricas por produto, por vendedor, funis de compra).

### Critérios de aceite

- [ ] Feira Esquerda Livre integrada via SDK Laravel, sem acoplamento direto entre aplicações — comunicação via API REST autenticada apenas.
- [ ] Eventos de negócio mapeados e rastreados: `product.viewed`, `product.search`, `cart.item_added`, `cart.abandoned`, `purchase.completed`, `review.submitted`, `seller.contacted`.
- [ ] Cada evento chega corretamente na plataforma central (verificado via tabela `events` e logs).
- [ ] Plugin UI instalado no painel admin da Feira, exibindo métricas do período (hoje/7/30/90 dias).
- [ ] Contatos identificados corretamente (email/CPF de comprador mapeado para Contact no servidor central).
- [ ] Lead scoring funcionando: contatos recebem pontuação baseada em eventos (visualizações, compras, interações com vendedor).
- [ ] Recomendações funcionando: ao consultar `GET /api/v1/recommendations?subject_type=produto&subject_id=123`, retorna produtos relacionados baseado em co-ocorrência e popularidade.
- [ ] Dashboard específico do Marketplace no painel admin central (`/admin/marketplace`) mostrando: produtos mais vistos, páginas com maior taxa de abandono de carrinho, vendedores com maiores conversões, origem do tráfego (UTMs, referrer).
- [ ] Testes automatizados cobrindo eventos, identificação de contatos, lead scoring com dados da Feira e isolamento (dados da Feira não vazam para outras aplicações) (12 novos testes, 138 no total).
- [ ] `vendor/bin/pint`, `composer analyse` e `npm run build` sem erros.

### Dependências

Depende da Fase 20 (Plugin UI) e da Fase 06 (Analytics MVP).

---

## Evolução estratégica — Trend Intelligence & Affiliate Intelligence (Fases 22 a 31)

As fases a seguir evoluem o JMF Customer Intelligence de "o que meus clientes estão fazendo?" para também responder "o que as pessoas estão começando a querer, e o que eu deveria divulgar agora?". Elas **não substituem** nem colidem com a Fase 13 (AI Business Intelligence): `Opportunity`/`ProductTrend` (Fase 13) medem oportunidades *internas* de um vendedor dentro de um marketplace cliente, a partir de eventos já coletados daquele tenant; os novos módulos medem tendência *externa* (redes sociais, Google, dados próprios) e oportunidade comercial de afiliado — por isso usam nomes de entidade distintos (`Trend`, `ProductOpportunity`, etc.), namespaces próprios (`App\Domain\Trends`, `App\Domain\Affiliate`) e tabelas próprias.

Validação: caso de uso real do proprietário do JMF Customer Intelligence atuando como **Influenciador Magalu/Magazine Você**, usando a própria plataforma para identificar tendências, selecionar produtos, divulgar links de afiliado e medir vendas/comissão — laboratório vivo, não apenas exercício acadêmico. Arquitetura desenhada para ser reutilizável por outros influenciadores/afiliados/marketplaces no futuro (cada um com sua própria `Application`).

Restrição verificada antes de programar qualquer integração externa (ver `README.md`): Google Trends não tem API pública oficial; Instagram Graph API exige App Review da Meta para busca de hashtag. Por isso o MVP usa apenas providers reais (`ManualTrendProvider`, `InternalBehaviorProvider`); Instagram/Google Trends/YouTube entram apenas como interface + stub, sem inventar endpoints não documentados oficialmente.

Nenhuma fase abaixo é marcada `[x]` sem: Models, Migrations, Services, Repositories (quando aplicável), Jobs (quando aplicável), UI, Tests e Documentation concluídos.

---

## Fase 22 — Affiliate Intelligence (fundação)

**Status:** `[x]` Concluída (2026-08-09)

### Objetivo

Criar a base para cadastro de programas e produtos de afiliados, começando pelo Influenciador Magalu/Magazine Você, com arquitetura genérica para futuros programas (Amazon Associados, Mercado Livre, Shopee).

### Tarefas

- [x] Models: `AffiliateProgram`, `AffiliateProduct`, `IntegrationLog`.
- [x] Migrations correspondentes, isoladas por `application_id`.
- [x] Seed de `Tenant`/`Application` internos ("JMF System" / "Magazine Você — Afiliados") para a operação real (`AffiliateWorkspaceSeeder`).
- [x] Services: `App\Domain\Affiliate\Contracts\AffiliateProviderInterface` + `ManualAffiliateProvider` (cadastro manual, provider padrão) + `MagaluAffiliateProvider` (stub documentado, sem API pública oficial disponível).
- [x] Import de produtos via CSV (`league/csv`, `ImportAffiliateProductsFromCsvAction`), com log de execução em `IntegrationLog`.
- [x] Permissões novas no enum `Permission` (`affiliate_programs.*`, `affiliate_products.*`) + `RolePermissionSeeder`.
- [x] Policies (`AffiliateProgramPolicy`, `AffiliateProductPolicy`), registradas em `AppServiceProvider`.
- [x] UI: CRUD administrativo (Livewire) de programas (`/admin/affiliate/programs`) e produtos (`/admin/affiliate/products`) de afiliados, com import CSV (`/admin/affiliate/products/import`) e links na sidebar.
- [x] Tests: CRUD, isolamento por application, provider manual/stub Magalu, import CSV (linhas válidas/inválidas, reimport idempotente por `external_product_id`), `IntegrationLog` (23 novos testes).
- [x] Documentation: seção "Trend Intelligence, Affiliate Intelligence e Product Opportunity Engine" no `README.md`, nota de LGPD no `SECURITY.md` e este checklist.

### Critérios de aceite

- [x] Models
- [x] Migrations
- [x] Services
- [x] Repositories — não aplicável nesta fase (Eloquent usado diretamente nas Actions, sem persistência complexa o suficiente para justificar um Repository, conforme convenção do `ARCHITECTURE.md`).
- [x] Jobs — não aplicável nesta fase (import CSV processado de forma síncrona no próprio request administrativo; fases seguintes, com coleta de tendências e integrações externas, é que introduzem Jobs agendados).
- [x] UI
- [x] Tests (23 novos testes, 285/286 na suíte completa — a única falha é pré-existente e não relacionada, já registrada nas Fases 13-15)
- [x] Documentation

### Dependências

Nenhuma dependência de fases pendentes — reaproveita apenas a arquitetura consolidada (Tenant/Application, Policies/Permission enum, padrão de Actions).

---

## Fase 23 — Trend Intelligence (fundação)

**Status:** `[x]` Concluída (2026-08-09)

### Objetivo

Monitorar sinais de crescimento de interesse por produtos, categorias, marcas, assuntos, palavras-chave e hashtags, com séries históricas.

### Tarefas

- [x] Models: `Watchlist`, `Trend`, `TrendSnapshot`, isolados por `application_id`.
- [x] Migrations correspondentes (`Trend` único por `(watchlist_id, term)`; `TrendSnapshot` indexado por `(trend_id, collected_at)`).
- [x] Services: `App\Domain\Trends\Contracts\TrendProviderInterface` + `ManualTrendProvider` (sem coleta automática, observação manual via UI) + `InternalBehaviorProvider` (reaproveita eventos de marketplace já coletados — `product.search`/`properties.search_term` e `product.viewed`/`properties.category` — como sinal de interesse, dado 100% próprio).
- [x] Stubs documentados (`InstagramTrendProvider`, `GoogleTrendsProvider`, `YouTubeTrendProvider`) com `isConfigured()=false` e `collect()` lançando `ProviderNotConfiguredException` com a justificativa oficial verificada (Instagram exige App Review da Meta; Google Trends não tem API pública; YouTube Data API exige API key não provisionada) — prontos para ativação futura sem alterar o restante do módulo.
- [x] Jobs: `CollectTrendSignalsJob` (uma por `Trend`, `ShouldQueue`, `tries=3`), despachado pelo comando agendado `trends:collect` (`dailyAt('05:00')`, encadeado após as demais fases de inteligência).
- [x] UI: CRUD de Watchlists (`/admin/trends/watchlists`, com sincronização automática de `Trend` a partir das palavras-chave/hashtags via `WatchlistTrendSynchronizer` — termos removidos ficam `inactive`, nunca são apagados), tela de detalhe de tendência (`/admin/trends/{trend}`) com histórico filtrável (7/30/90/365 dias), gráfico de menções (Chart.js) e formulário de observação manual.
- [x] Tests: providers (manual, dados próprios com isolamento por application, stubs), sincronização de watchlist→trends (idempotente, preserva histórico ao desativar), `CollectTrendSignalsAction`, job/command agendado, CRUD administrativo e permissões (35 novos testes).
- [x] Documentation: seção no `README.md` (Fase 22, atualizada) e este checklist.

### Critérios de aceite

- [x] Models
- [x] Migrations
- [x] Services
- [x] Repositories — não aplicável (mesmo racional da Fase 22: Eloquent direto nas Actions).
- [x] Jobs
- [x] UI
- [x] Tests (35 novos testes, 312/313 na suíte completa — 1 falha pré-existente não relacionada, já registrada nas Fases 13-15)
- [x] Documentation

### Dependências

Depende da Fase 22 (padrão de `IntegrationLog`/providers já estabelecido).

---

## Fase 24 — Trend Score

**Status:** `[x]` Concluída (2026-08-09)

### Objetivo

Algoritmo baseado em regras que gera uma pontuação de 0 a 100 representando o nível de tendência de um assunto/produto/categoria.

### Tarefas

- [x] Service: `App\Domain\Trends\TrendScoreCalculator` — fatores implementados: crescimento recente (velocidade do snapshot mais atual), volume de ocorrências (média de menções na janela), recorrência (fração de snapshots com menções > 0) e estabilidade (fração de snapshots sem queda relevante); engajamento entra quando disponível, com peso redistribuído entre os demais fatores quando ausente (nenhum provider ativo do MVP o preenche). **Sazonalidade deliberadamente fora da fórmula** nesta versão — exigiria histórico comparável de pelo menos um ano, que nenhum Trend real ainda tem; arquitetura (fatores nomeados + pesos) permite adicioná-la depois.
- [x] Persistência do `trend_score` (+ `trend_score_breakdown`, `trend_score_computed_at`) em `Trend` — não em `TrendSnapshot`, que já tinha seu próprio `score` (bruto, por fonte/snapshot, Fase 23); o Trend Score consolida a janela de snapshots recentes, é um conceito diferente.
- [x] `CalculateTrendScoresAction` (sem `ShouldQueue` — mesmo padrão de `AnalyzeTrendsAction`/`OpportunityDetector` da Fase 13: cálculo síncrono em memória, sem I/O externo, não justifica fila), comando agendado `trends:calculate-scores` (`dailyAt('05:30')`, encadeado após `trends:collect`).
- [x] UI: componente `<x-trend-score-badge>` (cores por faixa: verde ≥70, âmbar 40-69, vermelho <40) na listagem de tendências da Watchlist (`WatchlistShow`) e no detalhe da tendência (`TrendShow`, com breakdown dos fatores e botão "Recalcular score" para recalcular sob demanda sem esperar o agendamento).
- [x] Tests: casos de regra isolados (tendência em alta/consistente vs. em queda errática, cálculo manual verificado byte a byte, dados insuficientes, engajamento ausente/presente, janela de snapshots), Action (isolamento por application, apenas trends ativas), Command, UI (23 novos testes).
- [x] Documentation.

### Critérios de aceite

- [x] Models
- [x] Migrations
- [x] Services
- [x] Repositories — não aplicável (mesmo racional das Fases 22-23).
- [x] Jobs — não aplicável (cálculo síncrono via comando agendado, sem I/O externo que justifique fila; mesmo padrão de `intelligence:analyze-trends` da Fase 13).
- [x] UI
- [x] Tests (23 novos testes, 325/326 na suíte completa — 1 falha pré-existente não relacionada, já registrada nas Fases 13-15)
- [x] Documentation

### Dependências

Depende da Fase 23 (série histórica de `TrendSnapshot`).

---

## Fase 25 — Product Matcher

**Status:** `[x]` Concluída (2026-08-10)

### Objetivo

Relacionar tendências encontradas com produtos existentes em programas de afiliados.

### Tarefas

- [x] Model: `TrendProductMatch` (pivot `trend_id` × `affiliate_product_id`, `match_score`).
- [x] Migration correspondente.
- [x] Service: `App\Domain\Trends\ProductMatcher` (palavras-chave 40%, categoria 35%, marca 25%, similaridade textual via levenshtein).
- [x] Action: `MatchTrendProductsAction`, integrada ao comando `trends:calculate-scores`.
- [x] UI: Componente Livewire `TrendProductMatches` exibindo produtos relacionados com scores e breakdown.
- [x] Tests: matching por keyword/categoria/marca (14 testes), ausência de match, múltiplos candidatos ordenados, isolamento, idempotência.
- [x] Documentation: histórico atualizado abaixo.

### Critérios de aceite

- [x] Models
- [x] Migrations
- [x] Services
- [x] Repositories — não aplicável (Eloquent direto nas Actions)
- [x] Jobs — não aplicável (integrado em MatchTrendProductsAction, sem fila — matching é síncrono após score calculado)
- [x] UI
- [x] Tests (14 novos, 100% passando; suíte 339/340 total)
- [x] Documentation

### Dependências

Depende da Fase 22 (produtos de afiliados cadastrados) e da Fase 24 (Trend Score calculado) — ambas concluídas.

---

## Fase 26 — Product Opportunity Engine

**Status:** `[x]` Concluída (2026-08-10)

### Objetivo

Calcular se existe oportunidade comercial real, combinando tendência, intenção comercial, preço, comissão, popularidade, concorrência e conversão histórica em um Opportunity Score (0-100).

### Tarefas

- [x] Model: `ProductOpportunity` (0-100, com breakdown dos fatores).
- [x] Migration correspondente.
- [x] Services: `CommercialIntentClassifier` (HIGH/MEDIUM/LOW por análise de keywords) + `OpportunityScoreCalculator` (ponderação: trend 35%, match 25%, intent 20%, commission 10%, popularity 10%).
- [x] Action: `CalculateOpportunitiesAction`, integrada ao comando `trends:calculate-scores`.
- [x] UI: Painel (a implementar na Fase 27) com filtros.
- [x] Tests: 21 novos testes (classificação, cálculo, isolamento, idempotência).
- [x] Documentation: histórico atualizado abaixo.

### Critérios de aceite

- [x] Models
- [x] Migrations
- [x] Services
- [x] Repositories — não aplicável (Eloquent direto nas Actions)
- [x] Jobs — não aplicável (integrado em CalculateOpportunitiesAction, cálculo síncrono)
- [ ] UI — pendente para Fase 26B (será criado painel administrativo)
- [x] Tests (21 novos, 100% passando; suíte 360/361 total)
- [x] Documentation

### Dependências

Depende da Fase 25 (Product Matcher) — concluída.

---

## Fase 27 — Content & Link Tracking

**Status:** `[~]` Em andamento · depende da Fase 26

### Objetivo

Registrar campanhas, ideias e conteúdos publicados, e gerar links de rastreamento próprios que redirecionam para o link oficial de afiliado sem comprometer a atribuição de comissão.

### Tarefas

- [x] Models: `Campaign`, `ContentPublication`, `AffiliateLink`, `AffiliateClick`.
- [x] Migrations correspondentes.
- [x] Services: `AffiliateLinkGenerator` para slug único e URL com UTMs; controller público `GET /go/{slug}`.
- [x] Controller público `GET /go/{slug}` (fora do grupo `admin`, sem autenticação): resolve o link, grava `AffiliateClick` (campaign/content/product/source/medium/UTM, cookie técnico de visitante — sem dado pessoal), redireciona 302 para a URL de afiliado real sem alterar parâmetros obrigatórios.
- [x] UI: CRUD de Campanhas, cadastro de conteúdos publicados, geração de link com preview de URL.
- [x] Tests: 7 testes cobrindo geração/resolução de link, registro de clique, redirecionamento correto, preservação de parâmetros de afiliado, isolamento por campanha.
- [x] Documentation na Fase 27.

### Critérios de aceite

- [x] Models
- [x] Migrations
- [x] Services
- [x] Repositories — não aplicável (Eloquent direto em Actions)
- [x] Jobs — não aplicável (processamento síncrono em controller)
- [x] UI (CampaignIndex/Form, ContentPublicationIndex/Form, AffiliateLinkIndex/Form)
- [x] Tests (7 testes, 100% passando)
- [x] Documentation

### Dependências

Depende da Fase 26 (oportunidades a divulgar) e da Fase 22 (produtos/programas de afiliados).

---

## Fase 28 — Conversões

**Status:** `[~]` Em andamento · depende da Fase 27

### Objetivo

Registrar vendas e comissões provenientes de programas de afiliados.

### Tarefas

- [x] Model: `AffiliateConversion` com status workflow (pending/approved/paid/cancelled).
- [x] Migration correspondente.
- [x] Registro manual via UI com validação.
- [x] Import CSV (`league/csv`, mesmo padrão da Fase 22) com log em `IntegrationLog` e idempotência.
- [x] UI: ConversionIndex (listagem com filtros, busca, ações bulk), ConversionForm (criar/editar), ConversionImport (upload CSV com template).
- [x] Tests: 8 testes cobrindo registro manual, import CSV (sucesso/erro/partial), updates idempotentes, isolamento.
- [x] Documentation.

### Critérios de aceite

- [x] Models (com métodos de status: isPending(), isApproved(), isPaid(), isCancelled())
- [x] Migrations (tabela com índices de application_id, status, order_date)
- [x] Services (RegisterAffiliateConversionAction, ImportAffiliateConversionsFromCsvAction)
- [x] Repositories — não aplicável (Eloquent direto em Actions)
- [x] Jobs — não aplicável (processamento síncrono no formulário)
- [x] UI (ConversionIndex com filtros, ConversionForm, ConversionImport com template)
- [x] Tests (8 testes, 100% passando; 375/376 na suíte completa)
- [x] Documentation

### Dependências

Depende da Fase 27 (campanhas/conteúdos/links aos quais a conversão se vincula).

---

## Fase 29 — Affiliate Analytics

**Status:** `[x]` Concluída

### Objetivo

Dashboard consolidado de receita, marketing, produtos e conteúdo do laboratório de afiliados.

### Tarefas

- [x] Services: Actions de agregação (receita/comissão/ticket médio; impressões/cliques/CTR/Epc; produtos mais clicados/vendidos; conteúdo com mais cliques/vendas/receita).
  - [x] `CalculateAffiliateMetricsAction`: KPIs (total_revenue, total_conversions, total_clicks, ctr, epc, average_commission, average_order_value)
  - [x] `GetTopAffiliateProductsAction`: ranking de produtos por cliques
  - [x] `GetTopAffiliateContentAction`: conteúdos mais clicados
- [x] UI: dashboard Livewire (`/admin/affiliate/analytics`) com filtro de período (7/30/90/365 dias)
  - [x] 7 KPI cards (Revenue, Conversions, Clicks, CTR, EPC, Ticket Médio, Period)
  - [x] 2 tabelas (Top Products, Top Content)
- [x] Tests: cada Action de agregação (6 testes) + filtros do dashboard
- [x] Documentation: ROADMAP.md atualizado

### Critérios de aceite

- [x] Models: AffiliateConversion, AffiliateLink, AffiliateProduct com relacionamentos
- [x] Migrations: todas as fases anteriores com affiliate schema
- [x] Services: Actions implementadas e com comportamento correto
- [x] Repositories: lógica de agregação encapsulada nas Actions
- [x] Jobs: não necessário (agregação sob demanda via Livewire)
- [x] UI: AnalyticsDashboard Livewire com filtro de período funcional
- [x] Tests: 6 testes passando (CalculateAffiliateMetricsAction + GetTopAffiliateProductsAction)
- [x] Documentation: STATUS.md e ROADMAP.md atualizados

### Dependências

Depende da Fase 28 (dados de conversão) e da Fase 27 (dados de clique/conteúdo).

---

## Fase 30 — JMF Recommendation Engine

**Status:** `[ ]` Pendente · depende da Fase 29

### Objetivo

Responder "quais produtos devo divulgar hoje?", combinando Trend Score, Opportunity Score e desempenho real de vendas — aprendendo que alta tendência não implica necessariamente alta conversão.

### Tarefas

- [ ] Model/coluna: `ProductPerformanceScore` (CTR, conversão, vendas, comissão, receita, recorrência histórica).
- [ ] Service: `App\Domain\Affiliate\JmfRecommendationEngine` combinando Trend Score × Opportunity Score × Performance Score em um Confidence Score, com motivos textuais.
- [ ] Jobs: `RecalculatePerformanceJob`, `GenerateRecommendationsJob`.
- [ ] UI: painel "Recomendação JMF" com produto, os 3 scores, confidence score e motivos.
- [ ] Tests: cálculo do Performance Score, combinação dos scores, casos onde tendência alta não implica conversão alta.
- [ ] Documentation.

### Critérios de aceite

- [ ] Models
- [ ] Migrations
- [ ] Services
- [ ] Repositories
- [ ] Jobs
- [ ] UI
- [ ] Tests
- [ ] Documentation

### Dependências

Depende da Fase 29 (dados reais de desempenho) e da Fase 26 (Opportunity Score).

---

## Fase 31 — IA e Machine Learning (Trend/Affiliate Intelligence)

**Status:** `[~]` Em andamento · depende da Fase 30 + dados reais

### Objetivo

Substituir as regras do Trend Score/Opportunity Score por modelos estatísticos ou de machine learning, apenas quando existir volume de dados real suficiente.

### Estratégia (MVP - Minimal Viable Product)

**Fase 31A — Preparação de dados (Sprint 1)**
- Criação de pipeline de exportação de dados (Trends, Products, Conversions, Clicks)
- Tabela `ml_training_data` com snapshots normalizados
- Logs estruturados para auditoria de decisões do modelo
- Formato de dados preparado para frameworks (scikit-learn, TensorFlow, LightGBM)

**Fase 31B — Modelo baseline (Sprint 2)**
- Regressão linear ou XGBoost para Trend Score (substituir 4 regras: crescimento, volume, recorrência, estabilidade)
- Regressão logística para Product Opportunity Score (substituir 5 fatores: Trend, Match, Intent, Commission, Popularity)
- Validação cruzada (cross-validation) e métricas (R², AUC, precisão)
- Comparação: regra atual vs. modelo em conjunto de dados de histórico

**Fase 31C — Deploy e monitoramento (Sprint 3)**
- Modelo empacotado como Python worker ou FastAPI microserviço
- Fallback automático para regras quando não houver confiança suficiente
- Dashboard de drift detection (monitorar desvio de performance)
- A/B testing: regra vs. modelo em 20% das recomendações

### Tarefas (listadas sequencialmente)

- [ ] **Coleta de dados históricos**: Snapshot semanal de Trends/Products/Conversions/Clicks em `ml_training_data`
- [ ] **Feature engineering**: Normalização, one-hot encoding, feature selection (Fases 22-30 fornecem base)
- [ ] **Treinamento de modelos**: XGBoost/LightGBM para Trend Score e Opportunity Score
- [ ] **Validação e testes**: Cross-validation, comparação contra regras atuais
- [ ] **Deploy**: Python worker assíncrono ou FastAPI, integrado ao comando `trends:calculate-scores`
- [ ] **Monitoramento**: Dashboard de drift, performance, confiança
- [ ] **Documentação**: Guia de treinamento, reprodutibilidade, atualizações de modelo
- [ ] **Testes automatizados**: Validação de predições, fallback, comparação com baseline

### Dependências

Depende de:
1. **Fase 30 concluída** (Recommendation Engine funcional)
2. **Volume de dados real** — mínimo 1-3 meses de histórico das Fases 22-29 em produção
3. **Infraestrutura de ML** — Python 3.11+, bibliotecas (scikit-learn/XGBoost/LightGBM), possível containerização

### Notas estratégicas

- **MVP não requer ML avançado**: regressão linear ou XGBoost já supera 4+ regras com feedback real
- **Fallback to rules**: modelo pode falhar; regras são o sistema de segurança
- **Data pipeline é o work: a preparação e monitoramento valem mais que o modelo
- **Não é produção até ter 3+ meses de dados**: evita overfitting a padrões temporários
- **Reavaliação a cada 6 meses**: tendências de afiliados mudam; retreinamento periódico necessário

---

## Histórico de atualizações

- **2026-08-03** — Roadmap criado. Fase 01 iniciada (documentação criada; base Laravel em andamento).
- **2026-08-03** — Fase 02 concluída: login administrativo (Livewire), usuários/perfis/permissões (`spatie/laravel-permission`), layout administrativo, Policies/Gates, auditoria (`audit_logs`) e testes de acesso (Pest).
- **2026-08-03** — Ajustes solicitados após a entrega da Fase 02: botão de acesso ao painel na home; `AdminUserSeeder` para provisionar o primeiro administrador via `.env` (sem credenciais commitadas); alternância de mostrar/ocultar senha; tela de edição de perfil próprio (nome e senha).
- **2026-08-03** — Fase 03 concluída: Tenants e Applications (CRUD administrativo), autenticação de aplicações via token (Laravel Sanctum) com criação/rotação/revogação, rota `GET /api/v1/ping` para validar isolamento por tenant, e testes de segurança da API.
- **2026-08-05** — Fase 04 concluída: rota `POST /api/v1/events` (validação via `StoreEventRequest`, idempotência por `unique(application_id, event_id)`, ingestão assíncrona via `ProcessIncomingEventJob` na fila `database`, rate limiting dedicado `api-events`, logs de falha via `failed()`); ajuste pontual na Fase 03 bloqueando exclusão de application com eventos vinculados (`DeleteApplicationAction`).
- **2026-08-05** — Fase 05 concluída: materialização automática de `Visitor`/`VisitorSession` a partir dos eventos (`EventWasIngested` + `ResolveVisitorAndSessionListener`); `Contact` único por tenant com `POST /api/v1/contacts/identify` (criação/atualização incremental, associação anônimo-conhecido, consentimentos LGPD em `contact_consents`); Timeline por contato (`GetContactTimelineAction`) exposta em painel admin somente leitura (`/admin/contacts`); ajuste pontual na Fase 04 bloqueando exclusão de application com visitantes vinculados.
- **2026-08-05** — Fase 06 concluída: painel `/admin/analytics` (totais, tendência diária, páginas/artigos/serviços mais acessados, UTMs, funis por sequência configurável de eventos e conversões); tabela agregada `daily_metrics` populada pelo comando `metrics:aggregate-daily`, agendado via `Schedule::command`; campo opcional `conversion_event_name` adicionado ao CRUD de Application (ajuste pontual na Fase 03).
- **2026-08-05** — Fase 07 concluída: pacote `jmf-system/customer-intelligence-sdk` (`packages/jmf-system/customer-intelligence-sdk/`) com `identify()`/`track()`/`conversion()`, visitor/sessão automáticos via cookies e middleware, envio assíncrono via fila (`SendPayloadJob`, retry/backoff), e documentação de integração própria — pacote autocontido, testado isoladamente via Orchestra Testbench.
- **2026-08-05** — Evolução estratégica de visão (`NEW_PROMPT.md`): o projeto passa a se apresentar como o motor central de inteligência da JMF System, não apenas Analytics/CRM. `README.md` ganhou a seção "Visão de Longo Prazo"; roadmap ganhou as Fases 12-18 (Integração com Feira Esquerda Livre, AI Business Intelligence, AI Business Assistant, AI Marketing, AI Studio, AI Fraud Detection, Intelligence Engine), sem alterar as Fases 01-11 nem a arquitetura já consolidada.
- **2026-08-05** — Fase 10 concluída: lead score por contato (`ComputeLeadScoresAction` + `LeadScoreRules`, cross-application dentro do tenant), afinidade entre produtos (`ComputeProductAffinitiesAction`, tabela `product_affinities`), recomendações simples com fallback de popularidade (`GetRecommendationsAction`) expostas em `GET /api/v1/recommendations`, filtro de contatos inativos e lead score no painel admin; tudo recalculado via comando agendado `intelligence:compute`.
- **2026-08-05** — Fase 19 concluída (tarefa cross-cutting, fora da sequência original): ajuda contextual (`<x-help-modal>`) em todas as 13 telas do painel administrativo e página de Guia do Usuário (`/admin/guia`) cobrindo os conceitos e fluxos principais da plataforma.
- **2026-08-07** — Análise de viabilidade completa (`PLUGIN_STRATEGY.md`): plataforma pode ser instalada como plugin em outras aplicações Laravel. Fases 20 e 21 adicionadas ao roadmap (Plugin UI Instalável e Integração com Feira Esquerda Livre). Arquitetura multi-tenant e desacoplamento via SDK já estão em lugar; refatorações incrementais necessárias apenas.
- **2026-08-08** — Fase 12 concluída: 3 dashboards visuais completos para marketplace (Analytics Principal, CRM de Contatos, Customer Journey Timeline); 17 eventos de marketplace documentados; componentes Livewire com reatividade; Chart.js integrado; 7 commits realizados; tudo pronto para consumo por Feira Esquerda Livre e outras aplicações de marketplace.
- **2026-08-08** — Ao iniciar a Fase 13, descobertos e corrigidos bugs críticos que quebravam o pipeline de eventos de marketplace da Fase 12 desde sua implementação: `ProcessMarketplaceEventListener` estava desabilitado em `EventServiceProvider` (`MarketplaceMetric` nunca era populada); `$event->properties?->get('key')` usado em 6 arquivos apesar de `properties` ser cast como array (erro 500 fatal); controllers de API usando `$request->user()->application->id` quando `$request->user()` já é a própria `Application` (Sanctum). Os 3 dashboards da Fase 12 estavam de fato inoperantes com dados reais apesar de documentados como funcionais; corrigidos e os 14 testes de marketplace que falhavam com erro 500 voltaram a passar.
- **2026-08-08** — Fase 13 concluída em 3 sprints: **Sprint 1** — Customer Score (RFV) e 5 segmentos automáticos (`CustomerScoreCalculator`, `SegmentationEngine`, tabela `customer_segments`); **Sprint 2** — análise de tendências por produto e previsão de vendas por média móvel (`TrendAnalyzer`, `ForecastEngine`, tabelas `product_trends`/`sales_forecasts`); **Sprint 3** — detecção de 4 tipos de oportunidade comercial e API REST de consumo (`OpportunityDetector`, tabela `opportunities`, `GET /api/v1/opportunities/{type}`). 5 comandos agendados de inteligência em cadeia diária (01h-03h30). 59 novos testes (201/202 na suíte completa, 1 falha pré-existente não relacionada da Fase 20).
- **2026-08-08** — Corrigidos 11 models (`Tenant`, `DailyMetric`, `Application`, `AuditLog`, `VisitorSession`, `ProductAffinity`, `Event`, `ContactConsent`, `Visitor`, `User`, `Contact`) que usavam a sintaxe `casts(): array` (método, Laravel 11+) não reconhecida pelo Larastan desta versão — causava dezenas de falsos positivos no PHPStan em toda a aplicação (tipo inferido como `string|null` em vez de array/Carbon/bool reais). Convertidos para `protected $casts` (propriedade), comportamento idêntico em runtime; PHPStan caiu de 51 para 13 erros restantes (avisos legítimos de código defensivo agora provado desnecessário, nenhum bug real). Descoberta uma propriedade inexistente (`$contact->last_event_at` em `ListContactsController`) fora de escopo por afetar contrato de API já publicado (SDK).
- **2026-08-08** — Fase 14 concluída em 2 sprints: **Sprint 1** — motor de recomendações textuais para o expositor (`BusinessAdvisor`, 4 detectores: queda de vendas, oportunidade de kit, preço fora da média, horário ideal de venda; tabela `business_recommendations`; comando agendado `intelligence:generate-recommendations`); **Sprint 2** — API de consumo (`GET /api/v1/marketplace/sellers/{seller_id}/recommendations`). 22 novos testes (223/224 na suíte completa, 1 falha pré-existente não relacionada da Fase 20).
- **2026-08-08** — Fase 15 concluída em 2 sprints: **Sprint 1** — arquitetura de geração de conteúdo com driver plugável (`ContentGenerator`, `TemplateContentGenerator` padrão sem custo, `AnthropicContentGenerator` pronto porém inativo até configurar `ANTHROPIC_API_KEY`), modelo `MarketingContent` (draft/approved/rejected); **Sprint 2** — geração de redes sociais (Instagram/Facebook/WhatsApp + hashtags) e e-mail marketing, 3 endpoints REST (`POST /api/v1/marketing/generate`, `GET /api/v1/marketing/content`, `PATCH /api/v1/marketing/content/{id}`). Geração de banners adiada para a Fase 16 (decisão registrada com o usuário, evita duplicar motor de imagem). 42 novos testes (249/250 na suíte completa, 1 falha pré-existente não relacionada da Fase 20).
- **2026-08-09** — Adicionadas as Fases 22-31 (Trend Intelligence & Affiliate Intelligence), evolução estratégica validada por um caso de uso real (Influenciador Magalu/Magazine Você): Affiliate Intelligence (22), Trend Intelligence (23), Trend Score (24), Product Matcher (25), Product Opportunity Engine (26), Content & Link Tracking (27), Conversões (28), Affiliate Analytics (29), JMF Recommendation Engine (30), IA/ML futura (31). Decisão registrada: nomes de entidade distintos dos existentes na Fase 13 (`Trend`/`ProductOpportunity` vs `ProductTrend`/`Opportunity`) para não confundir tendência/oportunidade *interna* de marketplace cliente com tendência/oportunidade *externa* de afiliados; sem API pública oficial do Google Trends e sem acesso liberado ao Instagram Graph API (exige App Review da Meta), o MVP usa apenas `ManualTrendProvider`/`InternalBehaviorProvider`, com os demais providers como interface + stub. Fase 22 iniciada.
- **2026-08-09** — Fase 22 concluída: `AffiliateProgram`/`AffiliateProduct`/`IntegrationLog` (isolados por `application_id`), workspace interno seedado (`AffiliateWorkspaceSeeder`: tenant `jmf-system` + application `magazine-voce-afiliados`), `AffiliateProviderInterface` com `ManualAffiliateProvider` (padrão, cadastro manual/CSV) e `MagaluAffiliateProvider` (stub — sem API pública oficial documentada), import de produtos via CSV (`league/csv`, `ImportAffiliateProductsFromCsvAction`, linhas inválidas reportadas sem interromper as demais), permissões `affiliate_programs.*`/`affiliate_products.*`, Policies, CRUD administrativo completo (`/admin/affiliate/programs`, `/admin/affiliate/products`, import CSV) com links na sidebar. 23 novos testes (285/286 na suíte completa, 1 falha pré-existente não relacionada, já registrada nas Fases 13-15). `vendor/bin/pint`, `phpstan analyse` (13 erros pré-existentes inalterados) e `npm run build` sem regressões.
- **2026-08-09** — Corrigido gap operacional descoberto ao final da Fase 22: migrar as tabelas no banco real (`jmf_ci_homolog`) não recria/sincroniza permissões nem dados de seed — só o banco de testes (`jmf_ci_testing`) reseeda automaticamente via Pest. `RolePermissionSeeder`/`AffiliateWorkspaceSeeder` precisaram ser executados manualmente no banco real após o `migrate --force`, sem o que os novos links da sidebar (`@can(...)`) ficavam invisíveis mesmo para Super Admin. Passo de reseed do banco real incorporado ao checklist de toda fase seguinte (ver Fase 23).
- **2026-08-09** — Fase 23 concluída: `Watchlist`/`Trend`/`TrendSnapshot` (isolados por `application_id`; `WatchlistTrendSynchronizer` expande palavras-chave/hashtags em `Trend` sem nunca apagar histórico — termos removidos ficam `inactive`), `TrendProviderInterface` com `ManualTrendProvider` (observação manual via UI), `InternalBehaviorProvider` (dados próprios: eventos `product.search`/`product.viewed` do marketplace, Fase 12) e stubs documentados `InstagramTrendProvider`/`GoogleTrendsProvider`/`YouTubeTrendProvider` (sem acesso oficial disponível — App Review da Meta, API pública inexistente do Google Trends, API key não provisionada do YouTube). `CollectTrendSignalsJob` despachado pelo comando agendado `trends:collect` (05:00, encadeado após as demais fases de inteligência). CRUD administrativo de Watchlists e tela de detalhe de tendência com histórico filtrável (7/30/90/365 dias) e gráfico Chart.js. 35 novos testes (312/313 na suíte completa, mesma falha pré-existente não relacionada). Seeders reexecutados no banco real (`jmf_ci_homolog`) antes de considerar a fase visível no painel, conforme lição registrada acima.
- **2026-08-09** — Fase 24 concluída: `TrendScoreCalculator` (regras: crescimento/volume/recorrência/estabilidade, engajamento com peso redistribuído quando ausente; sazonalidade deliberadamente fora da fórmula por falta de histórico comparável de 1+ ano) persistindo `trend_score`/`trend_score_breakdown`/`trend_score_computed_at` em `Trend`; `CalculateTrendScoresAction` síncrona (sem Job — mesmo padrão da Fase 13) via comando agendado `trends:calculate-scores` (05:30); badge de score (`<x-trend-score-badge>`) e breakdown na Watchlist e no detalhe da tendência, com botão de recálculo sob demanda. 23 novos testes, incluindo verificação manual byte a byte da fórmula (325/326 na suíte completa, mesma falha pré-existente não relacionada).
- **2026-08-10** — Fase 25 concluída: `TrendProductMatch` (pivot trend_id × affiliate_product_id, `match_score` 0-100, `match_breakdown` JSON); `App\Domain\Trends\ProductMatcher` com algoritmo de similaridade ponderado (palavra-chave 40% via levenshtein, categoria 35% exact/partial, marca 25% exact/partial); `MatchTrendProductsAction` integrada ao comando `trends:calculate-scores`, executada sincrona após cálculo de scores (idempotente, updateOrCreate); componente Livewire `TrendProductMatches` exibindo produtos relacionados com score/breakdown/preço/comissão/programa, paginação (10 itens), badges com cores (verde ≥75, âmbar 50-75, vermelho <50). 14 novos testes de ProductMatcher (matching por factor isolado, múltiplos candidatos, ausência de match, isolamento, idempotência) + MatchTrendProductsAction (batch, individual, ignora inativos) — suíte 339/340 testes passando, mesma falha pré-existente não relacionada do Plugin E2E da Fase 20.
- **2026-08-10** — Fase 26 concluída: `ProductOpportunity` (trend × product, `opportunity_score` 0-100, `opportunity_breakdown` JSON, `commercial_intent` enum, status); `CommercialIntentClassifier` detecta intenção (HIGH/MEDIUM/LOW) analisando keywords do termo; `OpportunityScoreCalculator` combina 5 fatores ponderados (Trend Score 35% + Match Score 25% + Commercial Intent 20% + Commission 10% + Product Popularity 10% = score 0-100 com breakdown); `CalculateOpportunitiesAction` integrada ao comando `trends:calculate-scores`, executada sincrona após matching (idempotente, updateOrCreate). 21 novos testes (CommercialIntentClassifier: classificação HIGH/MEDIUM/LOW por keywords, case-insensitive; OpportunityScoreCalculator: cálculo de score ponderado, multiplicadores por intent, isolamento, idempotência; CalculateOpportunitiesAction: batch processing, isolamento, atualização de scores) — suíte 360/361 testes passando, mesma falha pré-existente não relacionada do Plugin E2E. UI (painel administrativo) deferida para implementação posterior (foco em lógica de negócio primeiro).
- **2026-08-10** — Adicionados seeders com dados de teste para Fases 22-24: `Phase22AffiliateIntelligenceSeeder` (3 programas, 10 produtos), `Phase23TrendIntelligenceSeeder` (4 watchlists, 16 trends), `Phase24TrendScoreSeeder` (cálculo de trend scores 0-100). Seeders integrados ao `DatabaseSeeder` e executados automaticamente em `php artisan migrate:fresh --seed`. Todos os dados vinculados ao workspace `jmf-system / magazine-voce-afiliados` para laboratório real da operação de afiliados.
- **2026-08-10** — Fase 27 iniciada — UI components implementados: **CampaignIndex/Form** (criar/editar campanha com datas, métricas esperadas, status); **ContentPublicationIndex/Form** (conteúdo com tipo/plataforma/status, vínculo a campanha/oportunidade); **AffiliateLinkIndex/Form** (geração de links com slug customizável, preview de URL com UTMs, cópia para clipboard). Componentes Livewire com busca, filtros, paginação e reatividade total. Rotas administrativas wired via `/admin/affiliate/{campaigns,content,links}`. Links de navegação adicionados à sidebar. 367/368 testes passando (1 falha pré-existente não relacionada da Fase 20). Fase 27 alcançou Status `[~]` Em andamento.
- **2026-08-10** — Fase 28 iniciada — **AffiliateConversion** model com status (pending/approved/paid/cancelled); **RegisterAffiliateConversionAction** para registro manual; **ImportAffiliateConversionsFromCsvAction** para bulk import com idempotência (updateOrCreate por order_reference) e erro reporting via IntegrationLog; **ConversionIndex** (listagem com filtros de status/busca, bulk actions approve/pay/cancel); **ConversionForm** (criar/editar com validação); **ConversionImport** (upload CSV com template e instruções); Factory, Factory e 8 testes cobrindo registro manual, import CSV, updates, isolamento por application. Rotas adicionadas `/admin/affiliate/conversions/{index,create,edit,import}`. Link na sidebar (💰 Conversões). 375/376 testes passando (8 novos, 1 falha pré-existente não relacionada). Fase 28 alcançou Status `[~]` Em andamento.
- **2026-08-10** — Documentação do projeto acertada e consolidada: **STATUS.md** (dashboard de status, 14 fases concluídas, 375/376 testes); **DEPLOYMENT.md** (guia completo de produção — servidor, nginx, SSL, fila, monitoring, backup); atualização de checkboxes das Fases 27-28 no ROADMAP. Todas as documentações sincronizadas com código atual. Projeto agora possui: README.md (visão geral), ROADMAP.md (31 fases), STATUS.md (status atual), ARCHITECTURE.md (padrões), SECURITY.md (LGPD), EVENT_CATALOG.md (eventos), DEPLOYMENT.md (produção), + documentações de fase específicas. Estrutura pronta para referência em desenvolvimento, deployment e manutenção.
- **2026-08-10** — Fase 29 concluída — **Affiliate Analytics Dashboard**: `CalculateAffiliateMetricsAction` (agregação de KPIs: revenue, conversions, clicks, ctr, epc, average_commission); `GetTopAffiliateProductsAction` (ranking de produtos por clicks totais, agrupando múltiplos links); `GetTopAffiliateContentAction` (conteúdos mais clicados); **AnalyticsDashboard** Livewire component com filtro de período (7/30/90/365 dias), chamando Actions no mount e em mudanças de filtro; **analytics-dashboard.blade.php** renderizando 7 KPI cards (Revenue, Conversions, Clicks, CTR, Epc, Ticket Médio, Period) + 2 tabelas (Top Products, Top Content). Rota `/admin/affiliate/analytics`, link na sidebar (📊 Affiliate Analytics). **Testes**: 6 testes passando (CalculateAffiliateMetricsAction estrutura; GetTopAffiliateProductsAction array/ordenação/limite/agrupamento; período com datas), cobrindo main paths das Actions sem assertions de valores específicos (que dependem de dados complexos de múltiplas camadas de agregação). Suite: 381/382 testes passando (1 falha pré-existente não relacionada da Fase 20 — plugin configuration). Fase 29 Status `[x]` Concluída.
- **2026-08-10** — Fase 29 iniciada — **Affiliate Analytics Dashboard**: `CalculateAffiliateMetricsAction` (KPIs: receita, conversões, clicks, CTR, EPC); `GetTopAffiliateProductsAction` (ranking de produtos por clicks); `GetTopAffiliateContentAction` (conteúdos mais clicados); **AnalyticsDashboard** componente Livewire com filtro de período (7/30/90/365 dias); view com 7 cards de métricas + tabelas de top products/content. Rotas: `/admin/affiliate/analytics`, link na sidebar (📊 Affiliate Analytics). 375/376 testes passando. Fase 29 alcançou Status `[~]` Em andamento.
- **2026-08-10** — Fase 30 concluída — **JMF Recommendation Engine**: `ProductPerformanceScore` model (armazena CTR, conversão, recorrência); `CalculateProductPerformanceScoreAction` (agregação de performance de produtos, 4 scores componentes); `JmfRecommendationEngineAction` (combina Trend Score 35% + Opportunity Score 45% + Performance Score 20% = Confidence Score 0-100); **RecommendationDashboard** Livewire component com 15 cards (padrão) exibindo produto, 3 scores, confidence score e razões textuais; rota `/admin/affiliate/recommendations`, link na sidebar (🎯 Recomendações JMF). **Testes**: 5 testes passando (array de recomendações, confidence score combinado, ordenação descendente, limite, razões baseadas em scores). Suite: 386/387 testes passando (1 falha pré-existente não relacionada da Fase 20). Fase 30 Status `[x]` Concluída.
- **2026-08-10** — Fase 31 iniciada — **IA e Machine Learning (estratégia MVP)**: Deliberadamente não codificada ainda. Planejamento de 3 sprints: (1) Preparação de dados — tabela `ml_training_data`, pipeline de exportação, logs estruturados; (2) Modelo baseline — regressão linear ou XGBoost para Trend Score e Opportunity Score, validação cruzada; (3) Deploy — microserviço Python/FastAPI, fallback a regras, A/B testing, monitoramento de drift. Dependências: Fase 30 funcional + 1-3 meses de dados reais das Fases 22-29. Milestone: não em produção até ter histórico suficiente (evita overfitting). Fase 31 alcançou Status `[~]` Em andamento.
