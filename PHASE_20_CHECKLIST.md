# Fase 20 — Checklist de Implementação

**Status:** 🚀 Iniciada em 2026-08-07

---

## Sprint 1: Refatoração do SDK (Semana 1 — até 2026-08-13)

### 1.1 Auditar dependências do SDK
- [x] Revisar `packages/jmf-system/customer-intelligence-sdk/composer.json`
- [x] Confirmar suporte a Laravel 11+ e PHP 8.2+
- [x] Verificar se há dependências específicas da app central
- **Status:** ✅ **CONCLUÍDO** — SDK está 100% desacoplado (ver TASK_1_1_AUDIT_REPORT.md)

### 1.2 Refatorar configuração
- [x] Criar `packages/jmf-system/customer-intelligence-sdk/config/customer-intelligence.php`
- [x] Definir variáveis: `api_url`, `api_token`, `queue_connection`, `timeout`
- [x] Adicionar sensible defaults
- [x] Atualizar `CustomerIntelligenceServiceProvider` para publicar config
- [x] Adicionar validação de variáveis obrigatórias
- [x] Teste: Integração com Orchestra Testbench

### 1.3 Melhorar validação e retry
- [x] `SendPayloadJob`: validação de payload antes de enviar
- [x] Adicionar logs estruturados (PayloadLogger + PayloadValidator)
- [x] Implementar retry com exponential backoff:
  - [x] 5xx: retry 3x com backoff [5s, 30s, 120s]
  - [x] 429: retry com backoff
  - [x] 4xx: sem retry, log de erro
- [x] Teste: Mock de respostas HTTP (45+ testes)

### 1.4 Adicionar health check
- [x] Método `Client::healthCheck()` 
- [x] Fazer `GET /api/v1/ping`
- [x] Retornar `true`/`false` (sem exceção)
- [x] Teste: Mock de servidor online/offline (4 testes)

### 1.5 Documentação do SDK
- [x] Atualizar `packages/jmf-system/customer-intelligence-sdk/README.md` (228 linhas)
  - [x] Instalação
  - [x] Configuração
  - [x] Exemplos completos
  - [x] Cookies
  - [x] Retry logic detalhado
  - [x] Segurança
  - [x] Troubleshooting
- [x] Criar `.env.example` no SDK (67 linhas)

### 1.6 Testes do SDK
- [x] Adicionar testes para validação de payload (PayloadValidatorTest: 12 testes)
- [x] Adicionar testes para retry behavior (SendPayloadJobTest: 4 testes)
- [x] Adicionar testes para health check (ClientTest: 4 testes)
- [x] Rodar `vendor/bin/pest` em `packages/jmf-system/customer-intelligence-sdk`
- [x] **Meta:** 45+ testes (meta era 15+), coverage 100%

### 1.7 Versão e tag
- [x] Atualizar `composer.json` para `1.0.0`
- [x] Tag Git: `sdk-v1.0.0`
- [x] CHANGELOG.md criado
- [x] Pronto para Packagist

### Sprint 1 — Critério de Aceite
- [x] SDK sem dependências hardcoded
- [x] Configuração 100% via `.env`
- [x] Health check funcionando
- [x] Retry inteligente implementado
- [x] Documentação completa
- [x] 45+ novos testes passando (meta: 15+)
- [x] `vendor/bin/pest` sem erros
- [x] `vendor/bin/pint` passed
- [x] Versão `1.0.0` tagged

---

## Sprint 2: UI Componentizada (Semana 2-2.5 — até 2026-08-26)

### 2.1 Estrutura de arquivos
- [x] Criar pasta: `resources/views/plugins/jmf-ci/`
- [x] Criar subpastas: `contacts/`, `events/`, `components/`
- [x] Criar arquivos base de views

### 2.2 Componentes Livewire

#### Dashboard
- [x] Criar `src/Livewire/Plugins/JmfCi/Dashboard.php`
- [x] Exibir métricas (eventos, visitantes, sessões, conversões)
- [x] Seletor de período (hoje/7/30/90 dias)
- [x] Gráfico de tendência diária
- [x] Tabelas de top contatos e eventos
- [x] Renderizar em `resources/views/plugins/jmf-ci/dashboard.blade.php`

#### Configuration
- [x] Criar `src/Livewire/Plugins/JmfCi/Configuration.php`
- [x] API URL input
- [x] API Token input (com eye toggle)
- [x] Botão "Validar Conexão"
- [x] Status de conexão (online/offline)
- [x] Integrado com healthCheck()
- [x] Renderizar em `resources/views/plugins/jmf-ci/configuration.blade.php`

#### ContactIndex
- [x] Criar `src/Livewire/Plugins/JmfCi/Contacts/ContactIndex.php`
- [x] Tabela de contatos
- [x] Filtro: período
- [x] Filtro: busca
- [x] Paginação (25/página)
- [x] Renderizar em `resources/views/plugins/jmf-ci/contacts/index.blade.php`

#### ContactShow
- [x] Criar `src/Livewire/Plugins/JmfCi/Contacts/ContactShow.php`
- [x] Detalhe do contato
- [x] Timeline de eventos
- [x] Paginação de timeline
- [x] Renderizar em `resources/views/plugins/jmf-ci/contacts/show.blade.php`

#### EventIndex
- [x] Criar `src/Livewire/Plugins/JmfCi/Events/EventIndex.php`
- [x] Tabela de eventos
- [x] Filtro: tipo (event_name)
- [x] Filtro: período
- [x] Paginação (50/página)
- [x] Renderizar em `resources/views/plugins/jmf-ci/events/index.blade.php`

### 2.3 Componentes Blade auxiliares
- [x] `<x-jmf-ci-metrics-card>` — Card com métrica + label + cor
- [x] `<x-jmf-ci-event-chart>` — Gráfico com Chart.js
- [x] `<x-jmf-ci-connection-status>` — Indicador online/offline
- [x] `<x-jmf-ci-metrics-row>` — Row de grid
- [x] `<x-jmf-ci-event-table>` — Tabela genérica

### 2.4 Services
- [x] Criar `src/Services/JmfCiApiClient.php`
- [x] Métodos: `healthCheck()`, `getMetrics()`, `getContacts()`, `getContact()`, `getContactEvents()`, `getEvents()`
- [x] Tratamento de erros gracioso (sem exceções)

### 2.5 Migrations
- [x] Avaliar se necessário → Não necessária (tudo via .env + API)

### 2.6 Routes do plugin
- [x] Criar `src/Routes/plugin.php` com 5 rotas
- [x] Criar `src/Providers/JmfCiPluginRouteServiceProvider.php`
- [x] Criar `PLUGIN_ROUTES.md` com documentação

### 2.7 Assets
- [x] Validar Chart.js disponível (via CDN em component)
- [x] Estilos Tailwind OK

### 2.8 Testes da UI
- [x] Teste: JmfCiApiClient existe e funciona
- [x] Teste: healthCheck funciona
- [x] Teste: getMetrics retorna dados
- [x] Teste: getContacts com paginação
- [x] Teste: getContact individual
- [x] Teste: getContactEvents
- [x] Teste: getEvents paginados
- [x] Teste: Trata erros gracefully
- [x] Teste: Aceita filtros (Contacts)
- [x] Teste: Aceita filtros (Events)
- [x] **Meta:** 10+ novos testes ✅ (10 testes implementados)
- **Status:** ✅ CONCLUÍDO

### 2.9 Documentação
- [x] Criar `PLUGIN_INSTALLATION.md` (523 linhas)
- [x] Pré-requisitos documentados
- [x] Instalação Rápida (5 passos)
- [x] Configuração (14 variáveis)
- [x] Como usar (5 componentes documentados)
- [x] Rotas do plugin
- [x] Customização (tema, prefixo, layout)
- [x] Troubleshooting (5 cenários)
- [x] FAQ (6 perguntas)
- **Status:** ✅ CONCLUÍDO

### Sprint 2 — Critério de Aceite
- [ ] 5 componentes Livewire funcionando
- [ ] 5 componentes Blade auxiliares criados
- [ ] `JmfCiApiClient` implementado
- [ ] Tabelas exibindo dados
- [ ] Filtros funcionando
- [ ] Paginação funcionando
- [ ] 10+ testes novos
- [ ] `vendor/bin/pint` + `composer analyse` OK
- [ ] `PLUGIN_INSTALLATION.md` completo

---

## Sprint 3: Integração + Testes E2E (Semana 3-3.5 — até 2026-09-06)

### 3.1 Integração na app principal
- [ ] Registrar componentes Livewire no AppServiceProvider
- [ ] Usar auto-discovery (se Livewire 3 permitir)
- [ ] Publicar views via `vendor:publish`

### 3.2 Rotas do plugin
- [ ] Rota: `GET /admin/plugin/jmf-ci` → Dashboard
- [ ] Rota: `GET /admin/plugin/jmf-ci/configuration` → Configuration
- [ ] Rota: `GET /admin/plugin/jmf-ci/contacts` → ContactIndex
- [ ] Rota: `GET /admin/plugin/jmf-ci/contacts/{id}` → ContactShow
- [ ] Rota: `GET /admin/plugin/jmf-ci/events` → EventIndex
- [ ] Usar layout admin existente

### 3.3 Testes E2E
- [ ] Teste: Rastrear evento → Visualizar no dashboard
- [ ] Teste: Identificar contato → Visualizar no painel
- [ ] Teste: Validar conexão da plataforma
- [ ] **Meta:** 5+ testes E2E

### 3.4 Documentação final
- [ ] Completar `PLUGIN_INSTALLATION.md` com passo a passo
- [ ] Atualizar README do SDK
- [ ] Criar guia de customização (opcional)

### 3.5 Publicação do SDK
- [ ] Publicar `jmf-system/customer-intelligence-sdk` no Packagist
- [ ] Ou registrar como git repository privado
- [ ] Validar `composer require` funciona

### 3.6 Qualidade Final
- [ ] `php artisan test` → 130+ testes
- [ ] `vendor/bin/pint` → passed
- [ ] `composer analyse` → 0 erros
- [ ] `npm run build` → sucesso

### 3.7 Checklist de Lançamento
- [ ] Todas as tarefas dos Sprints 1-3 completas
- [ ] 130+ testes passando
- [ ] Qualidade OK
- [ ] Documentação OK
- [ ] SDK publicado
- [ ] ROADMAP.md atualizado (Fase 20 → Concluída)
- [ ] README.md atualizado

### Sprint 3 — Critério de Aceite
- [ ] Plugin integrado
- [ ] Rotas do plugin funcionando
- [ ] 5+ testes E2E passando
- [ ] Documentação completa
- [ ] SDK publicado
- [ ] 130+ testes totais
- [ ] Qualidade: Pint, PHPStan, npm OK

---

## Métricas de Acompanhamento

| Sprint | Testes (Novo) | Testes (Total) | Qualidade | Status |
|--------|--------------|----------------|-----------|--------|
| 1 | 45 | 45 | ✅ Pint OK | ✅ **CONCLUÍDO** |
| 2 | 10 | 55 (pendente) | 🔄 | ⏳ 70% completo (2.8-2.9 faltam) |
| 3 | 5 | 60+ | 🔄 | ⏳ Aguardando Sprint 2 |

**Meta final:** 130+ testes, 0 erros de qualidade

**Sprint 1 Status:** ✅ 100% CONCLUÍDO
- 7 tarefas completadas
- 45 testes passando (3x meta de 15)
- Versão 1.0.0 taggeada
- Pint formatação OK

**Sprint 2 Status:** ⏳ 70% CONCLUÍDO
- Tarefas 2.1-2.4, 2.6 completadas (5 de 7)
- Faltam: 2.8 (Testes) + 2.9 (Documentação)

---

## Bloqueadores Conhecidos

- [ ] Chart.js indisponível → Usar tabelas simples
- [ ] SDK não publica no Packagist → Usar git repository privado
- [ ] Permissão de acesso → Documentar como manual

---

## Notas de Desenvolvimento

### Convenções de Código
- Componentes Livewire em: `app/Livewire/Plugins/JmfCi/`
- Views do plugin em: `resources/views/plugins/jmf-ci/`
- Nomes de classes: PascalCase (ex.: `JmfCiApiClient`)
- Nomes de componentes Livewire: PascalCase em classe, kebab-case em blade
- Nomes de componentes Blade: kebab-case com prefixo `jmf-ci-` (ex.: `<x-jmf-ci-metrics-card>`)

### Dependências
- Laravel 11+, Livewire 3, Tailwind CSS 4 (já existem)
- Chart.js (validar CDN)
- Orchestra Testbench (já existe)

### Testing
- Framework: Pest
- Driver: PHPUnit
- Database: SQLite (já configurado)
- Mock: Usando `Bus::fake()` e Http mocks

---

**Última atualização:** 2026-08-07  
**Próximo checkpoint:** Fim de Sprint 1 (2026-08-13)
