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

## Fase 12 — Integração com Feira Esquerda Livre

**Status:** `[ ]` Pendente · depende da Fase 07 e da Fase 06

### Objetivo

Usar a Feira Esquerda Livre (marketplace) como laboratório principal de validação dos motores de inteligência da plataforma, com usuários reais — Analytics do Marketplace, CRM por expositor e Customer Journey completa do comprador.

### Tarefas

- [ ] Catálogo de eventos do Marketplace: visualização de produtos, pesquisa, filtros, favoritos, carrinho, checkout, compra, abandono de carrinho, origem dos acessos, eventos de rede social, interação entre compradores e expositores.
- [ ] CRM do Expositor: painel de inteligência individual por expositor (visitantes, produtos mais vistos, produtos com maior conversão, clientes recorrentes, horários de maior venda, canais de aquisição, origem do tráfego, comportamento dos compradores).
- [ ] Customer Journey completa do comprador (ex.: Instagram → Landing Page → Produto → Carrinho → Compra → Nova Compra).
- [ ] Dashboard específico por expositor.

### Critérios de aceite

- [ ] Feira Esquerda Livre integrada via SDK Laravel, sem acoplamento direto entre as aplicações.
- [ ] Cada expositor acessa, de forma isolada, seu próprio painel de inteligência.
- [ ] Jornada do comprador reconstruída ponta a ponta a partir dos eventos capturados.
- [ ] Testes automatizados cobrindo os novos eventos e o isolamento por expositor.

### Dependências

Depende da Fase 07 (SDK Laravel) e da Fase 06 (Analytics MVP).

---

## Fase 13 — AI Business Intelligence

**Status:** `[ ]` Pendente · depende da Fase 06 e da Fase 12

### Objetivo

Motor de inteligência artificial responsável por interpretar os dados já coletados pela plataforma e gerar indicadores preditivos e de afinidade.

### Tarefas

- [ ] Lead Score.
- [ ] Customer Score.
- [ ] Afinidade entre produtos.
- [ ] Recomendações e produtos relacionados.
- [ ] Previsão de vendas.
- [ ] Sazonalidade e tendências.
- [ ] Produtos em alta / produtos em queda.
- [ ] Segmentação automática.
- [ ] Identificação de oportunidades comerciais.

### Critérios de aceite

- [ ] Lead Score e Customer Score calculados a partir dos dados já existentes (eventos, visitantes, contatos) e expostos via API.
- [ ] Recomendações e afinidade entre produtos disponíveis para consumo pelas aplicações clientes.
- [ ] Segmentação automática de contatos com base em comportamento.
- [ ] Testes automatizados cobrindo os cálculos e a API de consumo.

### Dependências

Depende da Fase 06 (Analytics MVP) e da Fase 12 (dados reais da Feira Esquerda Livre para validação).

---

## Fase 14 — AI Business Assistant

**Status:** `[ ]` Pendente · depende da Fase 13

### Objetivo

Atuar como consultor inteligente automatizado para pequenos empreendedores, democratizando análises de negócio normalmente restritas a consultorias caras.

### Tarefas

- [ ] Motor de recomendações textuais a partir dos dados do expositor/vendedor (ex.: quedas de venda, qualidade de fotos, tempo de resposta, oportunidades de kit, preço fora da média da categoria, horário ideal de publicação).
- [ ] Painel de recomendações no dashboard do expositor.
- [ ] Priorização de recomendações por impacto esperado.

### Critérios de aceite

- [ ] Recomendações geradas automaticamente a partir dos dados já existentes na plataforma (Analytics, CRM, AI Business Intelligence).
- [ ] Recomendações acionáveis, específicas e atualizadas periodicamente.
- [ ] Testes automatizados cobrindo a geração das recomendações.

### Dependências

Depende da Fase 13 (AI Business Intelligence).

---

## Fase 15 — AI Marketing

**Status:** `[ ]` Pendente · depende da Fase 13

### Objetivo

Motor especializado em geração automática de conteúdo de marketing a partir dos dados do produto, reduzindo a dificuldade que pequenos empreendedores têm em divulgar o que vendem.

### Tarefas

- [ ] Geração de título, descrição, SEO e palavras-chave.
- [ ] Geração de hashtags e textos para Instagram, Facebook e WhatsApp.
- [ ] Geração de campanhas e e-mail marketing.
- [ ] Geração de banners.

### Critérios de aceite

- [ ] Conteúdo gerado automaticamente a partir dos dados do produto, disponível via API para as aplicações clientes.
- [ ] Conteúdo revisável/editável pelo usuário antes da publicação.
- [ ] Testes automatizados cobrindo a geração de conteúdo.

### Dependências

Depende da Fase 13 (AI Business Intelligence).

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
