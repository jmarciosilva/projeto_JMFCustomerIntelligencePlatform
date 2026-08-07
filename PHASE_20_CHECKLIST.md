# Fase 20 — Checklist de Implementação

**Status:** 🚀 Iniciada em 2026-08-07

---

## Sprint 1: Refatoração do SDK (Semana 1 — até 2026-08-13)

### 1.1 Auditar dependências do SDK
- [ ] Revisar `packages/jmf-system/customer-intelligence-sdk/composer.json`
- [ ] Confirmar suporte a Laravel 11+ e PHP 8.2+
- [ ] Verificar se há dependências específicas da app central
- **Status:** Esperado estar OK

### 1.2 Refatorar configuração
- [ ] Criar `packages/jmf-system/customer-intelligence-sdk/config/customer-intelligence.php`
- [ ] Definir variáveis: `api_url`, `api_token`, `queue_connection`, `timeout`
- [ ] Adicionar sensible defaults
- [ ] Atualizar `CustomerIntelligenceServiceProvider` para publicar config
- [ ] Adicionar validação de variáveis obrigatórias
- [ ] Teste: Integração com Orchestra Testbench

### 1.3 Melhorar validação e retry
- [ ] `SendPayloadJob`: validação de payload antes de enviar
- [ ] Adicionar logs estruturados
- [ ] Implementar retry com exponential backoff:
  - 5xx: retry 3x (1s, 2s, 4s)
  - 429: retry 5x
  - 4xx: sem retry, log de erro
- [ ] Teste: Mock de respostas HTTP

### 1.4 Adicionar health check
- [ ] Método `Client::healthCheck()` 
- [ ] Fazer `GET /api/v1/ping`
- [ ] Retornar `true`/`false` (sem exceção)
- [ ] Teste: Mock de servidor online/offline

### 1.5 Documentação do SDK
- [ ] Atualizar `packages/jmf-system/customer-intelligence-sdk/README.md`
  - Instalação
  - Configuração
  - Exemplos
  - Cookies
  - Retry logic
  - Troubleshooting
- [ ] Criar `.env.example` no SDK

### 1.6 Testes do SDK
- [ ] Adicionar testes para validação de payload
- [ ] Adicionar testes para retry behavior
- [ ] Adicionar testes para health check
- [ ] Rodar `vendor/bin/pest` em `packages/jmf-system/customer-intelligence-sdk`
- [ ] **Meta:** 15+ testes, coverage 100%

### 1.7 Versão e tag
- [ ] Atualizar `composer.json` para `1.0.0`
- [ ] Tag Git: `sdk-v1.0.0`
- [ ] Pronto para Packagist

### Sprint 1 — Critério de Aceite
- [ ] SDK sem dependências hardcoded
- [ ] Configuração 100% via `.env`
- [ ] Health check funcionando
- [ ] Retry inteligente implementado
- [ ] Documentação completa
- [ ] 15+ novos testes passando
- [ ] `vendor/bin/pest` sem erros
- [ ] `vendor/bin/pint` passed
- [ ] Versão `1.0.0` tagged

---

## Sprint 2: UI Componentizada (Semana 2-2.5 — até 2026-08-26)

### 2.1 Estrutura de arquivos
- [ ] Criar pasta: `resources/views/plugins/jmf-ci/`
- [ ] Criar subpastas: `contacts/`, `events/`, `components/`
- [ ] Criar arquivos base de views

### 2.2 Componentes Livewire

#### Dashboard
- [ ] Criar `app/Livewire/Plugins/JmfCi/Dashboard.php`
- [ ] Exibir métricas (eventos, visitantes, sessões, conversões)
- [ ] Seletor de período (hoje/7/30/90 dias)
- [ ] Gráfico de tendência diária
- [ ] Tabelas de top contatos e eventos
- [ ] Renderizar em `resources/views/plugins/jmf-ci/dashboard.blade.php`

#### Configuration
- [ ] Criar `app/Livewire/Plugins/JmfCi/Configuration.php`
- [ ] API URL input
- [ ] API Token input (com eye toggle)
- [ ] Botão "Validar Conexão"
- [ ] Status de conexão (online/offline)
- [ ] Versão da API
- [ ] Últimos eventos recebidos
- [ ] Renderizar em `resources/views/plugins/jmf-ci/configuration.blade.php`

#### ContactIndex
- [ ] Criar `app/Livewire/Plugins/JmfCi/Contacts/ContactIndex.php`
- [ ] Tabela de contatos
- [ ] Filtro: período
- [ ] Filtro: busca
- [ ] Paginação (25/página)
- [ ] Renderizar em `resources/views/plugins/jmf-ci/contacts/index.blade.php`

#### ContactShow
- [ ] Criar `app/Livewire/Plugins/JmfCi/Contacts/ContactShow.php`
- [ ] Detalhe do contato
- [ ] Timeline de eventos
- [ ] Paginação de timeline
- [ ] Renderizar em `resources/views/plugins/jmf-ci/contacts/show.blade.php`

#### EventIndex
- [ ] Criar `app/Livewire/Plugins/JmfCi/Events/EventIndex.php`
- [ ] Tabela de eventos
- [ ] Filtro: tipo
- [ ] Filtro: período
- [ ] Paginação (50/página)
- [ ] Renderizar em `resources/views/plugins/jmf-ci/events/index.blade.php`

### 2.3 Componentes Blade auxiliares
- [ ] `<x-jmf-ci-metrics-card>`
- [ ] `<x-jmf-ci-event-chart>`
- [ ] `<x-jmf-ci-connection-status>`

### 2.4 Services
- [ ] Criar `app/Services/JmfCiApiClient.php`
- [ ] Métodos: `getMetrics()`, `getContacts()`, `getContact()`, `getEvents()`, `healthCheck()`
- [ ] Tratamento de erros

### 2.5 Migrations
- [ ] Avaliar se necessário (esperado: não)

### 2.6 Routes do plugin
- [ ] Registrar routes no ServiceProvider (se necessário)

### 2.7 Assets
- [ ] Validar Chart.js disponível
- [ ] Estilos Tailwind OK

### 2.8 Testes da UI
- [ ] Teste: Dashboard renderiza
- [ ] Teste: Configuration renderiza
- [ ] Teste: ContactIndex renderiza
- [ ] Teste: EventIndex renderiza
- [ ] Teste: Filtros funcionam
- [ ] Teste: Paginação funciona
- [ ] Teste: Links navegam
- [ ] Teste: API indisponível é tratada
- [ ] **Meta:** 10+ novos testes

### 2.9 Documentação
- [ ] Criar `PLUGIN_INSTALLATION.md`
- [ ] Screenshots dos componentes
- [ ] Como instalar
- [ ] Como usar
- [ ] Customização
- [ ] Troubleshooting

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
| 1 | 15 | 131 | 🔄 | ⏳ Em progresso |
| 2 | 10 | 141 | 🔄 | ⏳ Aguardando Sprint 1 |
| 3 | 5 | 146 | 🔄 | ⏳ Aguardando Sprint 2 |

**Meta final:** 130+ testes, 0 erros de qualidade

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
