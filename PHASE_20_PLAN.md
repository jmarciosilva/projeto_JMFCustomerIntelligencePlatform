# Fase 20 — Plugin UI Instalável — Plano de Implementação

**Data de início:** 2026-08-07  
**Duração estimada:** 3-4 semanas (3 sprints de 1-1.5 semanas cada)  
**Responsável:** Time de Desenvolvimento  
**Objetivo:** Tornar o SDK e plataforma instaláveis como plugin em qualquer app Laravel  

---

## Visão Geral

A Fase 20 divide-se em **3 sprints**:

| Sprint | Nome | Duração | Foco |
|--------|------|---------|------|
| **1** | Refatoração do SDK | 1 semana | Desacoplamento, configuração, documentação |
| **2** | UI Componentizada | 1.5 semanas | Componentes Livewire reutilizáveis |
| **3** | Integração + Testes | 1.5 semanas | E2E, documentação final, publicação |

---

## Sprint 1: Refatoração do SDK (Semana 1)

**Objetivo:** Remover dependências hardcoded, garantir que SDK funcione em qualquer Laravel 11+

### Tarefas

#### 1.1 Auditar dependências do SDK
- [ ] Revisar `packages/jmf-system/customer-intelligence-sdk/composer.json`
  - Verificar se há dependências específicas da app central
  - Confirmar suporte a Laravel 11+ e PHP 8.2+
  - **Status:** Esperado estar OK (SDK já é independente)

#### 1.2 Refatorar configuração
- [ ] Criar arquivo de config padrão: `packages/jmf-system/customer-intelligence-sdk/config/customer-intelligence.php`
  - Variáveis: `JMF_CI_API_URL`, `JMF_CI_API_TOKEN`, `JMF_CI_QUEUE_CONNECTION`, `JMF_CI_TIMEOUT`
  - Providenciar sensibles defaults
- [ ] Atualizar `CustomerIntelligenceServiceProvider` para publicar config
- [ ] Adicionar validação de variáveis obrigatórias (error early se config inválida)
- [ ] **Teste:** Integração com Orchestra Testbench

#### 1.3 Melhorar validação de payload
- [ ] `SendPayloadJob` deve validar payload antes de enviar
- [ ] Adicionar logs estruturados (não apenas erros)
- [ ] Implementar retry inteligente com exponential backoff
  - 5xx: retry até 3 vezes (1s, 2s, 4s backoff)
  - 429: retry até 5 vezes
  - 4xx: não retry, log de erro e desistência silenciosa
- [ ] **Teste:** Mock de respostas HTTP, validar retry behavior

#### 1.4 Adicionar health check
- [ ] Criar método `Client::healthCheck()` que faz `GET /api/v1/ping`
  - Retorna `true` se servidor está online
  - Retorna `false` se offline (sem exceção)
- [ ] Usar na UI de configuração (Fase 20 Sprint 2)
- [ ] **Teste:** Mock de servidor online/offline

#### 1.5 Documentação do SDK
- [ ] Atualizar `packages/jmf-system/customer-intelligence-sdk/README.md`
  - Instalação via Composer
  - Configuração via `.env`
  - Exemplos de `track()`, `identify()`, `conversion()`
  - Cookies e comportamento
  - Retry logic e error handling
  - Troubleshooting
- [ ] Criar `.env.example` no SDK
- [ ] **Status:** Documentação simples e prática (max 500 linhas)

#### 1.6 Testes do SDK
- [ ] Adicionar testes para validação de payload
- [ ] Adicionar testes para retry behavior
- [ ] Adicionar testes para health check
- [ ] Rodar `vendor/bin/pest` em `packages/jmf-system/customer-intelligence-sdk`
- [ ] **Meta:** 15+ testes, 100% coverage das novas funcionalidades

#### 1.7 Versão e release
- [ ] Atualizar versão em `composer.json`: `1.0.0`
- [ ] Criar tag Git: `sdk-v1.0.0`
- [ ] Pronto para publicação no Packagist (próxima fase)

### Critério de Aceite (Sprint 1)
- ✅ SDK sem dependências hardcoded
- ✅ Configuração 100% via `.env`
- ✅ Health check funcionando
- ✅ Retry inteligente implementado
- ✅ Documentação completa
- ✅ 15+ novos testes passando
- ✅ `vendor/bin/pest` no SDK sem erros
- ✅ `vendor/bin/pint` no SDK passed
- ✅ Versão `1.0.0` tagged

---

## Sprint 2: UI Componentizada (Semana 2-2.5)

**Objetivo:** Criar componentes Livewire reutilizáveis para qualquer app Laravel

### Tarefas

#### 2.1 Estrutura de arquivos
- [ ] Criar pasta: `resources/views/plugins/jmf-ci/`
  ```
  resources/views/plugins/jmf-ci/
  ├── dashboard.blade.php
  ├── configuration.blade.php
  ├── contacts/
  │   ├── index.blade.php
  │   └── show.blade.php
  ├── events/
  │   ├── index.blade.php
  │   └── filters.blade.php
  └── components/
      ├── metrics-card.blade.php
      ├── metrics-row.blade.php
      ├── event-chart.blade.php
      ├── event-table.blade.php
      └── connection-status.blade.php
  ```

#### 2.2 Componentes Livewire
- [ ] **`Dashboard`** (`app/Livewire/Plugins/JmfCi/Dashboard.php`)
  - Exibir métricas (eventos, visitantes, sessões, conversões)
  - Seletor de período (hoje/7/30/90 dias)
  - Gráfico de tendência diária (Chart.js)
  - Tabelas de top contatos e eventos recentes
  - **Renderização:** `resources/views/plugins/jmf-ci/dashboard.blade.php`

- [ ] **`Configuration`** (`app/Livewire/Plugins/JmfCi/Configuration.php`)
  - Campo input: API URL (com default de `.env`)
  - Campo input: API Token (com eye toggle)
  - Botão: "Validar Conexão" (chama `Client::healthCheck()`)
  - Status de conexão (online/offline com cor)
  - Exibir versão da API
  - Últimos eventos recebidos (timestamp, nome, status)
  - **Renderização:** `resources/views/plugins/jmf-ci/configuration.blade.php`

- [ ] **`ContactIndex`** (`app/Livewire/Plugins/JmfCi/Contacts/ContactIndex.php`)
  - Tabela de contatos (email, nome, lead_score, último evento, ação)
  - Filtro: período
  - Filtro: busca por email/nome
  - Paginação (25 por página)
  - Link para detalhe
  - **Renderização:** `resources/views/plugins/jmf-ci/contacts/index.blade.php`

- [ ] **`ContactShow`** (`app/Livewire/Plugins/JmfCi/Contacts/ContactShow.php`)
  - Detalhe do contato (email, nome, lead_score, data de criação)
  - Timeline de eventos (tabela com evento, data, propriedades)
  - Paginação de timeline
  - Link voltar para lista
  - **Renderização:** `resources/views/plugins/jmf-ci/contacts/show.blade.php`

- [ ] **`EventIndex`** (`app/Livewire/Plugins/JmfCi/Events/EventIndex.php`)
  - Tabela de eventos (evento, visitante, contato, data, propriedades)
  - Filtro: tipo de evento
  - Filtro: período
  - Filtro: busca por event_name
  - Paginação (50 por página)
  - **Renderização:** `resources/views/plugins/jmf-ci/events/index.blade.php`

#### 2.3 Componentes Blade (auxiliares)
- [ ] **`<x-jmf-ci-metrics-card>`** — Card com número + label + cor
  ```blade
  <x-jmf-ci-metrics-card 
    label="Eventos" 
    value="1234" 
    color="blue"
  />
  ```

- [ ] **`<x-jmf-ci-event-chart>`** — Gráfico de tendência (Chart.js)
  - Recebe array de dados: `['2026-08-01' => 100, '2026-08-02' => 150, ...]`
  - Renderiza linha de tendência

- [ ] **`<x-jmf-ci-connection-status>`** — Indicador de status
  - Online: badge verde "Conectado"
  - Offline: badge vermelho "Desconectado"

#### 2.4 Services de integração
- [ ] **`JmfCiApiClient`** (nova classe helper)
  - Wrapper de `CustomerIntelligence::Client`
  - Métodos:
    - `getMetrics(Application $app, DateRange $range): Metrics`
    - `getContacts(Application $app, int $page = 1): Collection`
    - `getContact(Application $app, int $id): Contact`
    - `getEvents(Application $app, int $page = 1, array $filters): Collection`
    - `healthCheck(): bool`
  - Tratamento de erros (retorna null ou array vazio se API indisponível)

#### 2.5 Migrations (se necessário)
- [ ] Se o plugin precisar armazenar configurações locais (ex.: mapeamento de eventos customizado), criar migration
- [ ] **Esperado:** Não ser necessário; tudo via `.env` + API

#### 2.6 Routes do plugin (opcional)
- [ ] Se plugin tiver suas próprias rotas, registrá-las via ServiceProvider
- [ ] **Esperado:** Usar routes da app cliente (plugin só fornece componentes)

#### 2.7 Assets (CSS/JS)
- [ ] Validar que Chart.js está disponível (via npm ou CDN)
- [ ] Estilos Tailwind (já inclusos no app cliente)
- [ ] **Nota:** Plugin não publica seus próprios assets; reutiliza do app cliente

#### 2.8 Testes da UI
- [ ] Testes de renderização dos componentes Livewire
  - Dashboard renderiza e exibe métricas
  - Configuration renderiza e permite validar conexão
  - ContactIndex renderiza tabela de contatos
  - EventIndex renderiza tabela de eventos
- [ ] Testes de interação
  - Filtros funcionam (período, busca)
  - Paginação funciona
  - Links navegam corretamente
- [ ] Testes de integração
  - Componentes conseguem chamar `JmfCiApiClient` com sucesso
  - Erros de API são tratados gracefully
- [ ] **Meta:** 10+ novos testes

#### 2.9 Documentação
- [ ] Criar `PLUGIN_INSTALLATION.md` (novo arquivo)
  - Screenshots dos componentes
  - Como instalar o plugin em uma app
  - Como usar os componentes
  - Customização (cores, temas)
  - Troubleshooting

### Critério de Aceite (Sprint 2)
- ✅ 5 componentes Livewire funcionando
- ✅ 5 componentes Blade auxiliares criados
- ✅ `JmfCiApiClient` implementado
- ✅ Tabelas exibindo dados corretamente
- ✅ Filtros funcionando
- ✅ Paginação funcionando
- ✅ Testes: 10+ testes feature novos
- ✅ `vendor/bin/pint` + `composer analyse` sem erros
- ✅ `PLUGIN_INSTALLATION.md` completo

---

## Sprint 3: Integração + Testes E2E (Semana 3-3.5)

**Objetivo:** Integrar plugin na app principal, testes ponta-a-ponta, documentação final

### Tarefas

#### 3.1 Integração na app principal
- [ ] Registrar componentes Livewire no `app/Providers/AppServiceProvider.php`
  - `Livewire::component('jmf-ci-dashboard', ...)`
  - `Livewire::component('jmf-ci-configuration', ...)`
  - etc.
- [ ] Ou usar auto-discovery se Livewire 3 permitir
- [ ] Publicar views do plugin via `php artisan vendor:publish`

#### 3.2 Rota + Layout para plugin UI
- [ ] Criar rota: `GET /admin/plugin/jmf-ci` → Dashboard
  - Ou integrar na sidebar do admin existente como novo menu item
- [ ] Rota: `GET /admin/plugin/jmf-ci/configuration` → Configuration
- [ ] Rota: `GET /admin/plugin/jmf-ci/contacts` → ContactIndex
- [ ] Rota: `GET /admin/plugin/jmf-ci/contacts/{contact}` → ContactShow
- [ ] Rota: `GET /admin/plugin/jmf-ci/events` → EventIndex
- [ ] Usar layout admin existente: `layouts/admin.blade.php`

#### 3.3 Testes E2E
- [ ] **Teste: Rastrear evento → Visualizar no dashboard**
  1. Disparar evento via SDK: `CustomerIntelligence::track('product.viewed', ...)`
  2. Processar fila: `php artisan queue:work`
  3. Acessar `/admin/plugin/jmf-ci`
  4. Validar que evento aparece nas métricas e tabela de eventos
  - **Arquivo:** `tests/Feature/Plugin/PluginDashboardE2eTest.php`

- [ ] **Teste: Identificar contato → Visualizar no painel**
  1. Disparar identify: `CustomerIntelligence::identify(['email' => '...'])`
  2. Processar fila
  3. Acessar `/admin/plugin/jmf-ci/contacts`
  4. Validar que contato aparece na tabela
  - **Arquivo:** `tests/Feature/Plugin/PluginContactsE2eTest.php`

- [ ] **Teste: Validar conexão da plataforma**
  1. Acessar `/admin/plugin/jmf-ci/configuration`
  2. Clicar "Validar Conexão"
  3. Validar que status muda para "Conectado"
  - **Arquivo:** `tests/Feature/Plugin/PluginConfigurationTest.php`

- [ ] **Meta:** 5+ testes E2E

#### 3.4 Documentação final
- [ ] Atualizar `PLUGIN_INSTALLATION.md`
  - Passo a passo: instalação via Composer em outra app
  - Publicação de assets
  - Configuração do `.env`
  - Mapeamento de eventos
  - Exemplos de código
  - FAQ

- [ ] Atualizar README.md do SDK
  - Listar componentes disponíveis
  - Link para documentação de instalação

- [ ] Criar guia de customização (opcional)
  - Como customizar cores/temas
  - Como estender componentes

#### 3.5 Publicação do SDK
- [ ] Publicar `jmf-system/customer-intelligence-sdk` no Packagist (ou repositório privado)
  - Se Packagist: criar conta e publicar
  - Se privado: registrar em `composer.json` como git repository
- [ ] Validar que `composer require jmf-system/customer-intelligence-sdk` funciona

#### 3.6 Qualidade Final
- [ ] Rodar `php artisan test` — Meta: 130+ testes
  - 116 existentes + 14 novos (Sprint 1-3)
- [ ] Rodar `vendor/bin/pint`
- [ ] Rodar `composer analyse`
- [ ] Rodar `npm run build`
- [ ] Verificar coverage de testes

#### 3.7 Checklist de Lançamento
- [ ] Todas as tarefas dos Sprints 1-3 completas
- [ ] Testes: 130+ passando
- [ ] Qualidade: Pint, PHPStan, npm build sem erros
- [ ] Documentação: `PLUGIN_STRATEGY.md`, `PLUGIN_INSTALLATION.md`, README atualizado
- [ ] SDK publicado (Packagist ou privado)
- [ ] ROADMAP.md atualizado (Fase 20 → Concluída)
- [ ] README.md atualizado (Fase 20 → Concluída)

### Critério de Aceite (Sprint 3)
- ✅ Plugin integrado na app principal
- ✅ Rotas do plugin funcionando
- ✅ 5+ testes E2E passando
- ✅ Documentação completa
- ✅ SDK publicado
- ✅ 130+ testes totais passando
- ✅ Qualidade: Pint, PHPStan, npm build sem erros

---

## Cronograma Resumido

```
Semana 1 (2026-08-07 a 2026-08-13)
└─ Sprint 1: Refatoração do SDK ✅ CONCLUÍDO
   ├─ Seg-Qua: Auditar e refatorar SDK ✅
   ├─ Qui-Sex: Testes e documentação ✅
   └─ Segunda seguinte: Review + aprovação ✅
   
   Resultado: 45 testes passando (3x meta)

Semana 2-2.5 (2026-08-14 a 2026-08-26)
└─ Sprint 2: UI Componentizada ✅ 100% CONCLUÍDO
   ├─ Seg-Ter: Estrutura + Dashboard + Configuration ✅
   ├─ Qua-Qui: ContactIndex + ContactShow + EventIndex ✅
   ├─ Sex: Testes + Documentação ✅
   └─ Completo: Todas as 7 tarefas (2.1-2.9) ✅
   
   Resultado: 5 Livewire + 5 Blade + JmfCiApiClient + 10 testes + Documentação completa

Semana 3-3.5 (2026-08-27 a 2026-09-06)
└─ Sprint 3: Integração + Testes E2E ⏳ Aguardando Sprint 2
   ├─ Seg-Ter: Integração + Rotas + E2E testes
   ├─ Qua: Documentação final
   ├─ Qui: Publicação SDK
   ├─ Sex: QA final
   └─ Segunda seguinte: Fechamento + Release
```

---

## Métricas de Sucesso

| Métrica | Target | Status |
|---------|--------|--------|
| Testes novos (Sprint 1) | 15+ | ✅ 45 testes |
| Testes novos (Sprint 2) | 10+ | ✅ 10 testes (total 55) |
| Testes totais | 130+ | ⏳ 55/130 (42%) |
| Cobertura de código | 90%+ | 🔄 Em progresso |
| Documentação (Sprint 1) | 100% | ✅ README + .env.example |
| Documentação (Sprint 2) | 100% | ✅ PLUGIN_INSTALLATION.md (523 linhas) |
| Qualidade (Pint/PHPStan) | 0 erros | ✅ Sprint 1 OK |
| SDK publicado | Sim | ⏳ Fase 20 Sprint 3 |
| Plugin instalável | Sim | ✅ Documentado em Sprint 2 |

---

## Dependências Externas

- ✅ Fase 07 (SDK já existe e funciona)
- ✅ Laravel 12 + Livewire 3 (já rodando)
- ✅ Chart.js (CDN ou npm — validar)

---

## Possíveis Bloqueadores

| Bloqueador | Plano B |
|-----------|---------|
| Chart.js indisponível | Usar tabelas simples + Tailwind |
| SDK não publica via Packagist | Usar git repository privado no composer.json |
| App cliente não tem permissão de acesso ao plugin | Documentar e deixar como manual para Fase 21 |

---

## Próximas Fases

Após Fase 20 concluída:
- **Fase 21** — Integração com Feira Esquerda Livre (Piloto) — 2-3 semanas

---

**Documento criado em:** 2026-08-07  
**Status:** ✅ Sprint 1 CONCLUÍDO (100%) | ✅ Sprint 2 CONCLUÍDO (100%) | ⏳ Sprint 3 Próximo
