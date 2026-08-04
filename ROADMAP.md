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

**Status:** `[ ]` Pendente · depende da Fase 02

- [ ] Tenants.
- [ ] Applications.
- [ ] Tokens por aplicação.
- [ ] Rotação e revogação de tokens.
- [ ] Isolamento por tenant.
- [ ] Testes de segurança.

---

## Fase 04 — Ingestão de eventos

**Status:** `[ ]` Pendente · depende da Fase 03

- [ ] `POST /api/v1/events`.
- [ ] Validação do payload.
- [ ] Idempotência via `event_id`.
- [ ] Rate limiting.
- [ ] Database Queue.
- [ ] Logs e tratamento de falhas.
- [ ] Testes de API.

---

## Fase 05 — Visitantes, sessões e contatos

**Status:** `[ ]` Pendente · depende da Fase 04

- [ ] Visitors.
- [ ] Sessions.
- [ ] Contacts.
- [ ] Identify.
- [ ] Associação anônimo-conhecido.
- [ ] Timeline.
- [ ] Consentimentos.

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
