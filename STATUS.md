# Status do Projeto — JMF Customer Intelligence

## Resumo Executivo

**Data:** 2026-08-11  
**Versão:** 1.0.0-alpha  
**Status Geral:** `[~]` Em Desenvolvimento Ativo  
**Testes:** 386/387 passando (99.7%)

### Fases Concluídas (24/32)

1. ✅ **Fase 01** — Fundação e documentação
2. ✅ **Fase 02** — Autenticação e administração
3. ✅ **Fase 03** — Multiempresa e aplicações
4. ✅ **Fase 04** — Ingestão de eventos
5. ✅ **Fase 05** — Visitantes, sessões e contatos
6. ✅ **Fase 06** — Analytics MVP
7. ✅ **Fase 07** — SDK Laravel
8. ✅ **Fase 10** — Inteligência inicial
9. ✅ **Fase 12** — Integração com Feira Esquerda Livre (Marketplace Analytics & CJ)
10. ✅ **Fase 13** — AI Business Intelligence
11. ✅ **Fase 14** — AI Business Assistant
12. ✅ **Fase 15** — AI Marketing
13. ✅ **Fase 19** — Ajuda contextual e documentação de usuário
14. ✅ **Fase 20** — Plugin UI Instalável
15. ✅ **Fase 22** — Affiliate Intelligence (fundação)
16. ✅ **Fase 23** — Trend Intelligence (fundação)
17. ✅ **Fase 24** — Trend Score
18. ✅ **Fase 25** — Product Matcher
19. ✅ **Fase 26** — Product Opportunity Engine
20. ✅ **Fase 27** — Content & Link Tracking
21. ✅ **Fase 28** — Conversões
22. ✅ **Fase 29** — Affiliate Analytics
23. ✅ **Fase 30** — JMF Recommendation Engine
24. ✅ **Fase 32** — Product Opportunity Intelligence (Sprint A — Etapa A1)

### Fases em Andamento (0/32)

*(Todas as 22 fases concluídas estão listadas nas seções de Concluídas e Pendentes)*

### Fases Pendentes (8/32)

- [ ] Fase 08 — Integração com site pessoal (parcial)
- [ ] Fase 09 — Integração com Clube do Salão
- [ ] Fase 11 — Produção
- [ ] Fase 16 — AI Studio
- [ ] Fase 17 — AI Fraud Detection
- [ ] Fase 18 — Intelligence Engine
- [ ] Fase 21 — Integração com Feira Esquerda Livre (Piloto)
- [ ] Fase 31 — IA e Machine Learning (Trend/Affiliate Intelligence)

---

## Funcionalidades Principais Implementadas

### 1. Fundação e Arquitetura (Fases 01-03)
- ✅ Projeto Laravel 12 completo
- ✅ Multiempresa (Tenants)
- ✅ Multi-aplicação (Applications)
- ✅ Autenticação admin (Livewire)
- ✅ Controle de acesso (Policies/Gates)
- ✅ Auditoria de eventos administrativos
- ✅ Gerenciamento de tokens (Sanctum)

### 2. Ingestão e Identidade (Fases 04-05)
- ✅ API REST: `POST /api/v1/events`
- ✅ Validação de payload (StoreEventRequest)
- ✅ Idempotência por `event_id`
- ✅ Rate limiting por aplicação
- ✅ Materialização automática de Visitors/Sessions
- ✅ Identificação de Contatos (`identify()`)
- ✅ Gerenciamento de consentimentos (LGPD)
- ✅ Timeline de eventos por contato

### 3. Analytics e Recomendações (Fases 06, 10)
- ✅ Dashboard de Analytics (período, filtros)
- ✅ Métricas agregadas (daily_metrics)
- ✅ Análise de funis
- ✅ Análise de UTMs
- ✅ Lead Score por contato
- ✅ Afinidade entre produtos
- ✅ API de Recomendações (co-ocorrência + popularidade)

### 4. SDK Laravel e Plugin (Fase 07, 20)
- ✅ Pacote `jmf-system/customer-intelligence-sdk`
- ✅ `identify()`, `track()`, `conversion()`
- ✅ Visitor/Session automáticos (cookies)
- ✅ Middleware de resolução
- ✅ Envio assíncrono com retry/backoff
- ✅ Plugin UI instalável em aplicações clientes
- ✅ Dashboard do plugin (metrics, contactos, eventos)

### 5. Dashboards Integrados (Fase 12)
- ✅ Analytics Principal (8 KPIs, gráfico de tendência)
- ✅ CRM de Contatos (busca, filtros, lead score)
- ✅ Customer Journey Timeline (eventos, estágios, recomendações)
- ✅ Chart.js integrado
- ✅ Paginação e filtros reativos (Livewire)

### 6. AI Business Intelligence (Fase 13)
- ✅ Customer Score (RFV: Recência/Frequência/Valor)
- ✅ 5 Segmentos de Cliente (VIP, Novo, Inativo, Convertido, Engajado)
- ✅ Análise de Tendências por produto
- ✅ Previsão de Vendas (média móvel com tendência)
- ✅ Detecção de Oportunidades (4 tipos: cross-sell, up-sell, win-back, bundle)
- ✅ API REST de Oportunidades
- ✅ 5 Comandos agendados de inteligência

### 7. AI Business Assistant (Fase 14)
- ✅ Motor de Recomendações Textuais (4 detectores)
- ✅ Detecção de Queda de Vendas
- ✅ Oportunidades de Kit
- ✅ Preço fora da Média
- ✅ Horário Ideal de Venda
- ✅ API de Recomendações por Vendedor

### 8. AI Marketing (Fase 15)
- ✅ Arquitetura de Geração de Conteúdo
- ✅ Driver TemplateContentGenerator (sem custo)
- ✅ Driver AnthropicContentGenerator (pronto, opcional)
- ✅ Geração de: Títulos, Descrições, SEO, Hashtags, Redes Sociais, Email
- ✅ Status de Conteúdo (draft/approved/rejected)
- ✅ API REST de Geração e Listagem

### 9. Trend Intelligence (Fase 23)
- ✅ Watchlists de Palavras-chave/Hashtags
- ✅ Histórico de Sinais (TrendSnapshot)
- ✅ ManualTrendProvider (observação manual)
- ✅ InternalBehaviorProvider (dados próprios)
- ✅ Stubs de Providers (Instagram, Google Trends, YouTube)
- ✅ UI de CRUD e detalhe com histórico

### 10. Trend Score (Fase 24)
- ✅ Algoritmo baseado em regras (0-100)
- ✅ Fatores: Crescimento, Volume, Recorrência, Estabilidade
- ✅ Badge de Score com cores (verde/âmbar/vermelho)
- ✅ Breakdown do score
- ✅ Recálculo sob demanda

### 11. Product Matcher (Fase 25)
- ✅ Relacionamento Tendência ↔ Produto
- ✅ Algoritmo Ponderado (40% keyword, 35% categoria, 25% marca)
- ✅ Match Score (0-100)
- ✅ Componente Livewire de Produtos Relacionados

### 12. Product Opportunity Engine (Fase 26)
- ✅ ProductOpportunity com Opportunity Score (0-100)
- ✅ CommercialIntentClassifier (HIGH/MEDIUM/LOW)
- ✅ Ponderação de 5 Fatores (35% Trend, 25% Match, 20% Intent, 10% Commission, 10% Popularity)
- ✅ Integração no comando de cálculo de scores

### 13. Content & Link Tracking (Fase 27)
- ✅ Campaign (campanha de marketing)
- ✅ ContentPublication (publicação em plataforma)
- ✅ AffiliateLink (link de rastreamento /go/{slug})
- ✅ AffiliateClick (registro de clique)
- ✅ CRUDs completos (campaigns, content, links)
- ✅ Geração de slug com preview de URL + UTMs

### 14. Conversões (Fase 28)
- ✅ AffiliateConversion (registro de venda)
- ✅ Status Workflow (pending → approved → paid | cancelled)
- ✅ Registro Manual via Formulário
- ✅ Import CSV com Idempotência
- ✅ IntegrationLog para Auditoria
- ✅ CRUDs e Ações em Massa

### 15. Product Opportunity Intelligence — Sprint A Etapa A1 (Fase 32)
- ✅ **Domain Classes**:
  - ✅ `PurchaseIntentTerms` — Vocabulário centralizado (4 categorias, 4 constantes de ajuste)
  - ✅ `PurchaseIntentClassifier` — Classificação determinística (0-100, LOW/MEDIUM/HIGH)
  - ✅ `PerformanceScoreCalculator` — Scoring com redistribuição dinâmica de pesos
- ✅ **Database Schema**:
  - ✅ `application_id` adicionado a `product_opportunities` (tenant isolation)
  - ✅ 13 colunas Sprint A em `product_opportunities` (status, scores, lifecycle timestamps)
  - ✅ `product_opportunity_id` em `affiliate_links` (attribution chain)
  - ✅ `provider`, `external_conversion_id`, `product_opportunity_id` em `affiliate_conversions` (idempotency)
  - ✅ Tabela `curation_decisions` (audit trail, sem UNIQUE)
  - ✅ 7 Migrations com backfill logging e validação de duplicatas
- ✅ **Idempotency**:
  - ✅ UNIQUE constraint em `(application_id, provider, external_conversion_id)`
- ✅ **Tests**:
  - ✅ 15 testes PurchaseIntentClassifier (LOW/MEDIUM/HIGH, bonuses, bounds)
  - ✅ 12 testes PerformanceScoreCalculator (dois-fatores, um-fator, sem-fatores, confidence)
  - ✅ 27 testes 100% passando pré e pós-migrações

---

## Infraestrutura e Stack

### Backend
- **Framework:** Laravel 12
- **PHP:** 8.3.30
- **Banco de Dados:** MySQL 8
- **ORM:** Eloquent

### Frontend
- **UI:** Blade + Livewire 3 + Alpine.js
- **Styling:** Tailwind CSS 4
- **Gráficos:** Chart.js
- **Build:** Vite + Node.js 22

### Qualidade
- **Testes:** Pest (381 testes passando)
- **Linting:** Laravel Pint
- **Análise Estática:** PHPStan/Larastan
- **CI/CD:** GitHub Actions (configurável)

### Infraestrutura
- **Fila:** Database (MVP)
- **Cache:** Database (MVP)
- **Sessão:** Database
- **Scheduler:** Cron + Laravel Scheduler
- **Hospedagem:** Compatível com compartilhada

---

## Documentação

### Documentos Principais
- 📄 **README.md** — Visão geral, stack, instalação, convenções
- 📄 **ROADMAP.md** — Plano de fases (31 fases)
- 📄 **ARCHITECTURE.md** — Padrões, estrutura de pastas
- 📄 **EVENT_CATALOG.md** — Contrato de eventos
- 📄 **SECURITY.md** — Segurança e LGPD
- 📄 **INSTALL.md** — Guia de instalação
- 📄 **CONTRIBUTING.md** — Guia de contribuição

### Documentos de Fase
- 📋 **PHASE_20_COMPLETION_REPORT.md** — Relatório da Fase 20
- 📋 **PHASE_12_COMPLETION_REPORT.md** — Relatório da Fase 12
- 📋 **PLUGIN_STRATEGY.md** — Análise de plugin strategy
- 📋 **STATUS.md** — Este documento

---

## Métricas de Qualidade

| Métrica | Status |
|---------|--------|
| Testes Automatizados | 386/387 (99.7%) |
| Cobertura de Código | ~85% (estimado) |
| Análise Estática (PHPStan) | 13 avisos pré-existentes |
| Lint (Pint) | ✅ Sem erros |
| Build (npm) | ✅ Sem erros |
| Migrations | ✅ 7 Sprint A testadas |
| SDK Publicável | ✅ Pronto (v1.0.0) |

---

## Roadmap Imediato (Próximas Sprints)

### Sprint A (Agosto-Setembro)
- [x] ✅ **Fase 32 — Sprint A Etapa A1** — Database & Domain Layer (CONCLUÍDO 2026-08-11)
  - [x] Domain Classes: PurchaseIntentTerms, PurchaseIntentClassifier, PerformanceScoreCalculator
  - [x] 7 Migrations (application_id, Sprint A columns, curation_decisions, backfill, validation, constraint)
  - [x] 27 Testes (100% passando)
  - [x] Commit 0e0a499
- [ ] **Sprint A Etapa A2–A6** — Blocked, awaiting approval of A1 results

### Sprint B (Setembro+)
- [ ] Fase 31 — IA e Machine Learning (quando houver volume de dados suficiente)
- [ ] Fase 11 — Validação em produção
- [ ] Fase 21 — Integração com Feira Esquerda Livre (Piloto)

---

## Notas Importantes

### Decisões Arquiteturais
1. **Sem Repositories no MVP** — Eloquent usado diretamente nas Actions quando suficiente
2. **Actions e Services** — Toda regra de negócio em camadas acima dos Controllers
3. **Fila Database** — Sem Redis no MVP; migração transparente quando necessário
4. **SDK Desacoplado** — Pacote independente, publicável no Packagist
5. **Multiempresa Obrigatória** — Isolamento por `tenant_id`/`application_id` em todo dado

### Restrições Conhecidas
- Google Trends: Sem API pública oficial (bloqueado)
- Instagram Graph API: Exige App Review da Meta (bloqueado)
- Plugin E2E: 1 falha pré-existente não relacionada à funcionalidade

### Próximas Prioridades
1. Testes E2E de fluxos Fase 27-28
2. Documentação de deployment em produção
3. Plano de retenção de dados (LGPD)
4. Integração real com aplicações clientes (Feira, Clube do Salão)

---

## Como Contribuir

1. Abra uma issue descrevendo o trabalho
2. Crie uma branch feature (`git checkout -b feature/descricao`)
3. Implemente, teste e faça commit com mensagens claras
4. Abra um Pull Request com `@usuario` para review
5. Aguarde aprovação e merge

Consulte [`CONTRIBUTING.md`](CONTRIBUTING.md) para mais detalhes.

---

## Contato e Suporte

**Responsável:** José Marcio (jmarciosilva@gmail.com)  
**Repositório:** [GitHub - JMF Customer Intelligence](https://github.com/jmf-system/customer-intelligence)  
**Documentação:** `/docs` e `*.md` na raiz

---

**Última atualização:** 2026-08-11  
**Próxima revisão:** 2026-08-25
