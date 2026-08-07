# Estratégia de Plugin Instalável — JMF Customer Intelligence

**Data da análise:** 2026-08-07  
**Status:** Análise completa - Pronto para implementação  
**Objetivo:** Tornar a plataforma JMF CI um plugin instalável para outras plataformas (ex.: Feira Esquerda Livre)

---

## Sumário Executivo

A arquitetura atual do JMF Customer Intelligence **já possui a base necessária** para funcionar como plugin instalável em outras plataformas. O SDK Laravel (`packages/jmf-system/customer-intelligence-sdk/`) está separado, desacoplado e pronto para ser distribuído via Composer. Com refatorações estratégicas e criação de uma UI de administração, é viável ter a plataforma funcionando em múltiplas aplicações coletando dados em um servidor central.

**Viabilidade:** ✅ 100% Sim  
**Complexidade:** Média (3-4 meses para implementação completa)  
**Investimento:** 2-3 sprints de desenvolvimento  

---

## Por que é possível?

### 1. Você já tem um SDK pronto e desacoplado

```
packages/jmf-system/customer-intelligence-sdk/
├── src/
│   ├── Client.php                    ← 3 métodos: identify(), track(), conversion()
│   ├── CustomerIntelligenceServiceProvider.php
│   ├── Http/Middleware/ResolveVisitorAndSession.php
│   ├── Jobs/SendPayloadJob.php
│   └── Support/
├── config/customer-intelligence.php
├── composer.json                      ← Package independente
└── tests/                             ← Testes isolados via Testbench
```

**Características-chave:**
- ✅ Package Composer completamente independente
- ✅ ServiceProvider Laravel (padrão de extensão)
- ✅ Middleware automático que resolve visitor/session
- ✅ Envio assíncrono via Jobs/Queues
- ✅ Sem dependências hardcoded da aplicação central

### 2. A arquitetura é multi-tenant por design

```
Aplicação Central (Plataforma JMF CI)
├── Tenants (empresas/clientes)
│   ├── Applications (produtos/plataformas)
│   │   ├── Tokens (acesso via API)
│   │   ├── Events (dados coletados)
│   │   └── Contacts (inteligência)
│   └── Analytics/Intelligence
└── APIs REST autenticadas (Sanctum)
```

**Cada plataforma é uma "Application"** dentro de um Tenant. Isolamento garantido via tokens e permissões.

### 3. Você já tem API REST estruturada

```
POST /api/v1/events                    ← Ingestão de eventos
POST /api/v1/contacts/identify         ← Identificação de contatos
GET  /api/v1/recommendations           ← Recomendações baseadas em inteligência
GET  /api/v1/ping                      ← Health check
```

Autenticação: **Laravel Sanctum** (tokens por aplicação)  
Rate limiting: Dedicado por aplicação  
Idempotência: Garantida por `event_id`

---

## Arquitetura Proposta: Plugin + Servidor Central

```
┌──────────────────────────────────────────────────────┐
│  Plataforma Cliente (ex.: Feira Esquerda Livre)      │
├──────────────────────────────────────────────────────┤
│                                                      │
│  ┌──────────────────────────────────────────────┐   │
│  │  Painel Admin - Plugin JMF CI                │   │
│  │  ├─ Dashboard com métricas                   │   │
│  │  ├─ Gerenciamento de contatos                │   │
│  │  ├─ Configuração (API Token)                 │   │
│  │  ├─ Visualização de eventos                  │   │
│  │  └─ Relatórios e análises                    │   │
│  └──────────────────────────────────────────────┘   │
│                                                      │
│  ┌──────────────────────────────────────────────┐   │
│  │  SDK JMF CI (Pacote Composer)                │   │
│  │  ├─ CustomerIntelligence::track()            │   │
│  │  ├─ CustomerIntelligence::identify()         │   │
│  │  ├─ CustomerIntelligence::conversion()       │   │
│  │  ├─ Middleware (visitor/session)             │   │
│  │  └─ SendPayloadJob (async)                   │   │
│  └──────────────────────────────────────────────┘   │
│                                                      │
└──────────────────────────────────────────────────────┘
            ↓ HTTPS (API Token)
┌──────────────────────────────────────────────────────┐
│  JMF Customer Intelligence (Servidor Central)       │
├──────────────────────────────────────────────────────┤
│                                                      │
│  ┌──────────────────────────────────────────────┐   │
│  │  Painel Admin Central                        │   │
│  │  ├─ Gerenciamento de Tenants                 │   │
│  │  ├─ Gerenciamento de Applications            │   │
│  │  ├─ Geração de tokens                        │   │
│  │  └─ Analytics agregado                       │   │
│  └──────────────────────────────────────────────┘   │
│                                                      │
│  ┌──────────────────────────────────────────────┐   │
│  │  Motor de Inteligência                       │   │
│  │  ├─ Ingestão de eventos                      │   │
│  │  ├─ Analytics (funis, conversões, UTMs)      │   │
│  │  ├─ CRM (visitantes, contatos, timeline)     │   │
│  │  ├─ Lead Scoring & Affinities                │   │
│  │  ├─ Recomendações                            │   │
│  │  └─ Dados compartilhados entre plataformas   │   │
│  └──────────────────────────────────────────────┘   │
│                                                      │
└──────────────────────────────────────────────────────┘
```

---

## Fluxo de Dados: Do Plugin ao Servidor

**Exemplo: Usuário compra na Feira Esquerda Livre**

```
1. Usuário navega/compra no site
   ↓
2. SDK detecta visitor_id via cookie
   ↓
3. App dispara CustomerIntelligence::track('purchase.completed', {...})
   ↓
4. SendPayloadJob enfileira o evento
   ↓
5. Job faz POST para https://jmf-ci.com/api/v1/events
   (Headers: Authorization: Bearer {token})
   ↓
6. Servidor processa via ProcessIncomingEventJob
   ↓
7. Visitor/Contact/Eventos salvos no banco central
   ↓
8. Inteligência recalculada (lead score, afinidades, recomendações)
   ↓
9. Dashboard da Feira acessa dados via GET /api/v1/recommendations
   ou mostra métricas no painel admin local
```

---

## 3 Camadas da Solução

### **Camada 1: SDK Laravel (Já existe — Fase 07)**
✅ Implementado | Responsável por:
- Rastrear visitante/sessão (cookies)
- Capturar eventos da aplicação
- Enviar dados de forma assíncrona e confiável

### **Camada 2: Plugin UI (Novo — Fase 20)**
❌ A implementar | Responsável por:
- Painel admin customizável para cada plataforma
- Configuração visual (API token, webhooks, etc.)
- Dashboard com métricas da plataforma
- Tabelas de contatos, eventos, conversões

### **Camada 3: Servidor Central (Já existe — Fases 02-10)**
✅ Implementado | Responsável por:
- Receber eventos de múltiplas plataformas
- Armazenar dados centralizados
- Calcular inteligência (lead scores, afinidades, etc.)
- Expor APIs para plugins consultarem

---

## Passos de Implementação

### **Fase 20: Plugin UI (3-4 semanas)**

#### Sprint 1: Refatoração do SDK (1 semana)
- [ ] Remover qualquer dependência específica da aplicação central
- [ ] Criar arquivo de configuração mais robusto (`.env` + config file)
- [ ] Documentar todos os eventos disponíveis
- [ ] Adicionar testes de integração completos
- [ ] Publicar versão 1.0.0 no Packagist (privado ou público)

#### Sprint 2: UI Componentizada (1.5 semanas)
- [ ] Criar Livewire component `AdminPlugin` reutilizável
- [ ] Dashboard básico (métricas do período, gráficos)
- [ ] Configuração (conectar API token, validar conexão)
- [ ] Tabela de contatos identificados
- [ ] Tabela de eventos recentes
- [ ] Filtros (período, tipo de evento, etc.)

#### Sprint 3: Integração + Testes (1.5 semanas)
- [ ] Integrar UI no painel admin da aplicação cliente
- [ ] Criar guia de instalação automática (Composer + publicação de assets)
- [ ] Testes end-to-end (rastrear evento → ver no dashboard)
- [ ] Documentação de integração

---

### **Fase 21: Integração com Feira Esquerda Livre (Piloto - 2-3 semanas)**

#### Sprint 1: Setup + Eventos (1.5 semanas)
- [ ] Instalar SDK na Feira via Composer
- [ ] Registrar "Feira Esquerda Livre" como Application na plataforma central
- [ ] Gerar token de API
- [ ] Mapear eventos de negócio:
  - `product.viewed` — visualização de produto
  - `product.search` — busca realizada
  - `cart.abandoned` — carrinho abandonado
  - `purchase.completed` — compra realizada (conversão)
  - `review.submitted` — avaliação deixada
  - `seller.contact` — contato com vendedor
- [ ] Instalar plugin UI na Feira

#### Sprint 2: Validação + Ajustes (1.5 semanas)
- [ ] Rastrear eventos reais da Feira
- [ ] Validar dados chegando na plataforma central
- [ ] Dashboard da Feira mostrando métricas
- [ ] Testar identificação de contatos (email/CPF)
- [ ] Teste de lead score (qual contato é mais valioso?)
- [ ] Teste de recomendações (produtos relacionados)
- [ ] Documentar aprendizados

---

## O que precisa ser implementado

### **No SDK (Refatoração — Fase 20, Sprint 1)**

```php
// Atualmente está assim (OK, mas pode melhorar):
class Client
{
    public function track(string $eventName, array $properties = []): void
    {
        $payload = [...];
        dispatch(new SendPayloadJob('events', $payload));
    }
}

// Adicionar:
// - Validação de payload antes de enviar
// - Retry inteligente com exponential backoff
// - Modo síncrono configurável (para testes)
// - Logging detalhado
// - Health check (ping) automático
```

### **Plugin UI (Novo — Fase 20, Sprints 2-3)**

```
resources/views/plugins/jmf-ci/
├── dashboard.blade.php
├── configuration.blade.php
├── contacts/index.blade.php
├── contacts/show.blade.php
├── events/index.blade.php
└── components/
    ├── metrics-card.blade.php
    ├── event-chart.blade.php
    └── contact-table.blade.php

app/Livewire/Plugins/JmfCi/
├── Dashboard.php
├── Configuration.php
├── Contacts/ContactIndex.php
└── Events/EventIndex.php
```

### **Documentação**

```
PLUGIN_STRATEGY.md (este arquivo)
├─ Como hospedar a plataforma
├─ Como instalar o SDK em outra app
├─ Como criar a aplicação no painel admin
├─ Como gerar um token de API
├─ Como configurar o plugin UI
└─ Troubleshooting

SDK README.md (Fase 07, refatorado)
├─ Instalação via Composer
├─ Configuração via .env
├─ Exemplo de código (track/identify/conversion)
├─ Cookies explicados
├─ Comportamento de retry
└─ O que fazer se falhar

PLUGIN_INSTALLATION.md (novo)
├─ Passo a passo para instalar em uma plataforma
├─ Screenshots do painel admin
├─ Debugging
└─ FAQ
```

---

## Casos de Uso Imediatos

### 1. **Feira Esquerda Livre** (Piloto)
- Rastrear comportamento de compradores
- Analytics por produto/vendedor
- Identificar compradores recorrentes
- Recomendar produtos relacionados
- Lead scoring para follow-up de vendedores

### 2. **Clube do Salão** (Fase 09)
- Rastrear agendamentos
- Analytics por profissional/serviço
- Clientes mais valiosos
- Padrões de agendamento
- Recomendações de serviços

### 3. **Site Pessoal** (Fase 08 — já integrado)
- Analytics do blog e portfólio
- Funil profissional (visitante → contato → cliente)
- Lead scoring para prospecção
- Recomendação de artigos relacionados

### 4. **Meu Canto Ideal** (Futuro)
- Rastrear busca de casas
- Padrões de pesquisa
- Contatos de interesse
- Recomendações personalizadas

---

## Benefícios da Abordagem

| Aspecto | Benefício |
|---------|-----------|
| **Desacoplamento** | Cada plataforma é independente; falha em uma não afeta outra |
| **Reuso** | SDK reutilizável em qualquer Laravel 11+ |
| **Escalabilidade** | Adicione plataformas apenas instalando pacote + plugin |
| **Inteligência Centralizada** | Uma única fonte de verdade; dados cruzados entre plataformas |
| **Segurança** | Isolamento via tokens; rate limiting por aplicação |
| **Observabilidade** | Todas as plataformas rastreadas em um painel central |
| **Simplicidade de Instalação** | `composer require jmf-system/customer-intelligence-sdk` |

---

## Roadmap Integrado

| Fase | Nome | Duração | Dependência | Status |
|------|------|---------|-------------|--------|
| 01-10 | Fundação + Inteligência | ✅ Concluído | — | ✅ Completo |
| 20 | **Plugin UI** | 3-4 semanas | Fase 07 | ❌ Pendente |
| 21 | **Integração Feira Esquerda Livre** | 2-3 semanas | Fase 20 | ❌ Pendente |
| 09 | Integração Clube do Salão | — | Fase 20 | ❌ Pendente |
| 12-18 | Visão Estratégica (AI, Intelligence Engine) | — | Fases 20-21 | ❌ Pendente |

---

## Tecnologias Necessárias

| Componente | Tecnologia | Já tem? |
|-----------|-----------|---------|
| Backend Central | Laravel 12 | ✅ Sim |
| SDK Cliente | Laravel Package (Composer) | ✅ Sim |
| Plugin UI | Livewire 3 + Tailwind | ✅ Sim |
| Autenticação API | Laravel Sanctum | ✅ Sim |
| Fila Assíncrona | Database Queue | ✅ Sim |
| Cache | Database ou Redis | ✅ Sim |
| Testes | Pest | ✅ Sim |

**Nenhuma tecnologia nova é necessária.** Tudo já está no stack.

---

## Segurança

### Por que é seguro?

✅ **Autenticação via Token**  
Cada aplicação tem seu próprio token gerado pelo servidor. Tokens podem ser revogados/rotacionados.

✅ **Isolamento por Tenant**  
Dados de um tenant nunca "vazam" para outro. Aplicação A não vê dados de Aplicação B, nem eventos, nem contatos.

✅ **Rate Limiting**  
Limite de requisições por aplicação previne DDoS e abuso.

✅ **HTTPS Obrigatório**  
Em produção, todas as requisições devem usar HTTPS.

✅ **Logs de Auditoria**  
Todas as operações administrativas são registradas.

✅ **Sem Credenciais Hardcoded**  
Token salvo em `.env`, nunca commitado no git.

---

## O que NÃO precisa fazer agora

- ❌ Marketplace de plugins (vem depois)
- ❌ Suporte a plataformas non-Laravel (WordPress, Shopify, etc.) — pode vir como extensão futura
- ❌ Interface de "drag-and-drop" de eventos — mapeamento manual ou via config é OK por enquanto
- ❌ Webhooks bidirecionais — apenas GET/POST basic OK
- ❌ Multi-linguagem no painel — português é suficiente para MVP
- ❌ Mobile app — web é o suficiente
- ❌ Pricing/Cobrança — é integração interna entre produtos JMF

---

## Recomendação: Próximos Passos

### **Esta semana (2026-08-07 a 2026-08-13)**
1. ✅ Validar análise com time técnico
2. ✅ Criar Fase 20 no ROADMAP.md
3. ✅ Estimar story points (Fase 20: ~13-21 pontos)
4. ✅ Priorizar na sprint seguinte

### **Próxima sprint**
1. Iniciar Sprint 1 da Fase 20 (refatoração SDK)
2. Publicar versão 1.0.0 do SDK
3. Criar guia de instalação

### **Sprint seguinte**
1. Sprint 2 da Fase 20 (UI Componentizada)
2. Começar Sprint 1 da Fase 21 (Piloto Feira)

---

## FAQ

**P: E se a plataforma central cair? A aplicação cliente quebra?**  
R: Não. O SDK despacha eventos via queue local. Se a plataforma estiver fora, os eventos ficam na fila esperando reconexão. Nenhuma operação crítica do cliente depende da plataforma estar online.

**P: Como os dados da Feira Esquerda Livre fica privado?**  
R: Cada plataforma é uma "Application" com seu próprio token. O isolamento é garantido no nível de banco de dados (`application_id` e `tenant_id`). Dados da Feira nunca são acessíveis por outra aplicação.

**P: Posso usar o SDK fora de Laravel?**  
R: Inicialmente não (MVP é Laravel). Mas a API REST é agnóstica — qualquer linguagem pode fazer POST para `/api/v1/events`. SDK de outras linguagens pode vir depois.

**P: Quanto custa hospedar?**  
R: Compatível com hospedagem compartilhada. Servidor único rodando Laravel 12 + MySQL é o suficiente. Não precisa de Redis/Horizon no MVP.

**P: Que tipo de dados são coletados?**  
R: Apenas eventos comportamentais (cliques, navegação, conversões) e contatos (email, nome, dados de consentimento). Nunca senhas, PII sensível ou dados financeiros completos.

---

## Referências

- `ROADMAP.md` — Roadmap completo do projeto
- `packages/jmf-system/customer-intelligence-sdk/README.md` — SDK Laravel
- `ARCHITECTURE.md` — Decisões arquiteturais
- `SECURITY.md` — Segurança e LGPD
- `EVENT_CATALOG.md` — Eventos disponíveis

---

**Próximo passo:** Adicionar Fase 20 (Plugin UI) e Fase 21 (Integração Feira Esquerda Livre) ao ROADMAP.md e iniciar planejamento da Sprint.
