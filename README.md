# JMF Customer Intelligence

## Visão Geral

**JMF Customer Intelligence** é uma plataforma central de inteligência de clientes, capaz de receber eventos comportamentais de diferentes aplicações, organizar visitantes, sessões e contatos, construir timelines, gerar indicadores e funis e, futuramente, oferecer CRM, automações de marketing, segmentação e recomendações personalizadas.

A plataforma não substitui a lógica de negócio dos sistemas clientes. Cada sistema continua responsável pelas suas próprias regras; a JMF Customer Intelligence recebe apenas os eventos e dados necessários para Analytics, CRM, Marketing, jornada do cliente e recomendações.

```
Aplicações → SDK Laravel → API Central → Customer Intelligence Platform → CRM / Analytics / Campanhas / Recomendações
```

## Problema que resolve

Atualmente cada sistema da JMF System mantém seu próprio CRM, seu próprio Analytics e seu próprio histórico de clientes, o que gera duplicação de código, manutenção difícil, visão fragmentada do cliente e baixa reutilização entre projetos.

## Objetivos

- Permitir que qualquer sistema da JMF System envie eventos para uma plataforma central.
- Compreender o comportamento dos usuários e identificar interesses.
- Construir jornadas de cliente e calcular afinidade.
- Gerar recomendações e automatizar campanhas.
- Aumentar conversão e melhorar retenção.

## Visão de Longo Prazo

O núcleo já construído (ingestão de eventos, identidade de cliente, analytics, SDK) é a fundação de uma visão maior: o **JMF Customer Intelligence deve se tornar o motor central de inteligência da JMF System** — não apenas uma plataforma de Analytics e CRM, mas uma plataforma de inteligência para negócios digitais, composta por módulos independentes:

- **Analytics** — indicadores, funis, UTMs, páginas/produtos mais acessados.
- **CRM** — identidade de cliente, timeline, consentimentos.
- **Customer Journey** — jornada completa do visitante ao cliente recorrente.
- **Marketing Intelligence** — segmentação e campanhas orientadas a dados.
- **Recommendation Engine** — recomendações e produtos relacionados.
- **Lead Scoring** e **Customer Scoring** — pontuação de leads e clientes.
- **Fraud Detection** — detecção de comportamento suspeito e reputação.
- **AI Marketing** — geração automática de conteúdo e campanhas.
- **AI Studio** — geração de imagens e vídeos profissionais a partir de fotos simples.
- **Content Generation** — títulos, descrições, SEO, textos para redes sociais.
- **Business Intelligence** e **AI Insights** — previsões, tendências e oportunidades comerciais.
- **Trend Intelligence** — monitoramento de sinais de crescimento de interesse por produtos, categorias, marcas e assuntos em fontes externas (redes sociais, Google Trends, dados próprios), com séries históricas e **Trend Score** (0-100).
- **Affiliate Intelligence** — cadastro de programas e produtos de afiliados (Magazine Você/Magalu e futuros marketplaces), matching entre tendências e produtos (**Product Matcher**), rastreamento de links, campanhas, conteúdos e conversões.
- **Product Opportunity Engine** — combina tendência, intenção comercial, preço, comissão, popularidade e conversão histórica em um **Opportunity Score** (0-100) para apontar oportunidades comerciais reais.
- **Affiliate Analytics** — receita, cliques, CTR, EPC, conversão e desempenho por produto, conteúdo e canal.
- **Recommendation Engine** (JMF Recommendation) — combina Trend Score, Opportunity Score e Product Performance Score (desempenho real de vendas) para responder "o que divulgar hoje?".

Qualquer sistema desenvolvido pela JMF System poderá consumir um ou mais desses módulos sem precisar reimplementá-los. O **SDK Laravel** (`packages/jmf-system/customer-intelligence-sdk`) continua sendo a principal forma de integração entre aplicações clientes e a plataforma. O isolamento por tenant/aplicação e o desacoplamento entre plataforma e sistemas clientes, já estabelecidos nas fases concluídas, são a base sobre a qual esses módulos serão construídos — nenhuma aplicação cliente fica acoplada a outra, e toda inteligência permanece centralizada aqui.

### Trend Intelligence, Affiliate Intelligence e Product Opportunity Engine

A partir da Fase 22, o JMF Customer Intelligence passa a responder também: **"quais produtos, categorias ou assuntos estão apresentando sinais de crescimento de interesse e podem representar uma oportunidade comercial?"**. Essa evolução nasce de um caso de uso real: o uso da própria plataforma como laboratório de inteligência comercial para uma operação de marketing de afiliados (Influenciador Magalu/Magazine Você), cobrindo o ciclo completo — detectar tendência → selecionar produto → produzir conteúdo → divulgar → medir clique/venda/comissão → aprender.

- **Trend Intelligence** monitora palavras-chave/hashtags/temas cadastrados em **Watchlists**, gera séries históricas (`TrendSnapshot`) e calcula o **Trend Score**. Fontes de dados são sempre oficiais/públicas/permitidas: o Google Trends não possui API pública oficial e o Instagram Graph API exige App Review da Meta para busca de hashtags — por isso o MVP usa apenas `ManualTrendProvider` (observação manual) e `InternalBehaviorProvider` (dados próprios de comportamento já coletados pela plataforma); providers de Instagram/Google/YouTube existem como interface (`TrendProviderInterface`) e stub, prontos para ativação futura quando o acesso oficial for viabilizado.
- **Affiliate Intelligence** cadastra `AffiliateProgram`/`AffiliateProduct` via `AffiliateProviderInterface` (implementação inicial manual/importação CSV; Magalu, Amazon, Mercado Livre e Shopee como implementações futuras).
- **Product Matcher** relaciona tendências a produtos de afiliados cadastrados; **Product Opportunity Engine** calcula o Opportunity Score combinando tendência, intenção comercial, preço, comissão, popularidade, concorrência e conversão histórica.
- **Content & Link Tracking** registra ideias de conteúdo, publicações (`ContentPublication`) e links de rastreamento próprios (`/go/{slug}`) que redirecionam para o link oficial de afiliado sem alterar parâmetros obrigatórios de atribuição de comissão.
- **Affiliate Analytics** e o **Recommendation Engine** fecham o ciclo, aprendendo quais tendências realmente geram vendas (alta tendência não implica alta conversão).

Essas novas tabelas seguem o mesmo princípio de isolamento por `tenant_id`/`application_id` do restante da plataforma — nenhum dado pessoal de terceiros (ex.: seguidores de redes sociais) é armazenado, apenas contagens agregadas. Detalhes completos nas Fases 22-31 do [`ROADMAP.md`](ROADMAP.md).

> Fornecer inteligência artificial reutilizável para qualquer produto digital desenvolvido pela JMF System, auxiliando empresas, marketplaces e pequenos empreendedores a compreender seus clientes, automatizar marketing, gerar conteúdo, detectar fraudes, recomendar produtos e tomar melhores decisões baseadas em dados.

O roadmap completo dessa evolução — incluindo a Feira Esquerda Livre como laboratório de validação e as fases de IA (Business Intelligence, Business Assistant, AI Marketing, AI Studio, Fraud Detection, Intelligence Engine) — está em [`ROADMAP.md`](ROADMAP.md).

## Projetos-piloto

1. **Site pessoal e blog profissional** — navegação, blog, portfólio, contato, newsletter.
2. **Clube do Salão** — agenda, serviços, profissionais, fidelidade, marketing.

Projetos futuros (Meu Canto Ideal, Feira Esquerda Livre e outros) poderão integrar-se à mesma plataforma, reutilizando o SDK Laravel.

## Stack

### Backend
- Laravel 12
- PHP 8.3.30
- MySQL 8

### Frontend
- Blade
- Livewire 3
- Alpine.js
- Tailwind CSS 4

### Build
- Node.js 22.12.0
- Vite

### Qualidade
- Laravel Pint
- PHPStan / Larastan
- Pest (testes automatizados — ver decisão em `ARCHITECTURE.md`)

### Infraestrutura do MVP

Compatível com hospedagem compartilhada. Não são obrigatórios no MVP: Redis, Horizon, Supervisor, WebSockets.

- Filas: `QUEUE_CONNECTION=database`
- Cache: `CACHE_STORE=database`
- Sessões: `SESSION_DRIVER=database`
- Tarefas recorrentes: Cron + Laravel Scheduler

## Requisitos locais

```
PHP 8.3.30 (cli)
Composer 2.9+
Node.js 22.12.0
npm 10.9+
MySQL 8
Laragon + Apache (porta 80)
MySQL (porta 3306)
```

## Instalação

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan test
```

`php artisan migrate --seed` já cria o primeiro Super Admin automaticamente se `ADMIN_EMAIL`/`ADMIN_PASSWORD` estiverem definidas no `.env` (recomendado em hospedagem/deploy). Caso contrário, crie um administrador a qualquer momento com `php artisan admin:create` (interativo, sem credenciais commitadas). Detalhes em [`INSTALL.md`](INSTALL.md).

## Configuração do `.env`

Principais variáveis a configurar após copiar `.env.example` para `.env`:

```env
APP_NAME="JMF Customer Intelligence"
APP_URL=http://jmf-customer-intelligence.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jmf_customer_intelligence
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database

# Opcional: cria o primeiro Super Admin automaticamente ao rodar `php artisan migrate --seed`
ADMIN_NAME="Seu Nome"
ADMIN_EMAIL=admin@exemplo.com
ADMIN_PASSWORD=
```

Crie o banco de dados `jmf_customer_intelligence` no MySQL local antes de rodar as migrations.

## Configuração no Laragon

1. Colocar o projeto em `D:\PROJETO-JMF-CUSTOMER-INTELLIGENCE`.
2. Apontar o Virtual Host do Laragon para a pasta `public/` do projeto.
3. Domínio local sugerido: `jmf-customer-intelligence.test`.
4. Apache servindo na porta 80, MySQL na porta 3306.
5. Não depender exclusivamente de `php artisan serve`; o projeto deve funcionar via Apache/Laragon.

## Comandos de desenvolvimento

```bash
npm run dev
php artisan serve
```

Ou acessar diretamente pelo Virtual Host configurado no Laragon.

Processamento assíncrono local (sem Redis/Horizon no MVP):

```bash
php artisan queue:work --tries=3
php artisan schedule:work
```

## Comandos de qualidade

```bash
vendor/bin/pint
composer analyse
composer refactor
php artisan test
npm run build
```

Nenhum Pull Request deve ser aprovado sem todos os testes passando.

## Estrutura inicial de pastas

```
app/
├── Application/
├── Domain/
├── Infrastructure/
├── Http/
├── Models/
├── Policies/
├── Jobs/
├── Events/
├── Services/
└── Actions/
resources/
routes/
tests/
docs/
```

Camadas são criadas apenas quando existe uma finalidade clara — nenhuma abstração vazia por estética.

## Convenções arquiteturais

- Controllers pequenos, sem regra de negócio.
- Actions para casos de uso.
- Services para fluxos de domínio e integrações.
- Form Requests para validação.
- Policies e Gates para autorização.
- DTOs, Enums e Value Objects quando aumentam clareza e segurança.
- Jobs para processamento assíncrono; Events/Listeners para desacoplamento.
- Repositories apenas quando houver persistência realmente complexa.
- SOLID, Clean Code, PSR-12, Laravel Best Practices e DDD quando agregar valor.

Regras completas em [`docs/DEVELOPMENT_RULES.md`](docs/DEVELOPMENT_RULES.md) (a ser criado nas próximas fases) e em [`ARCHITECTURE.md`](ARCHITECTURE.md).

## Fluxo de eventos (visão resumida)

```
Aplicação cliente → SDK Laravel/API HTTP → POST /api/v1/events → Fila (database)
→ Job de processamento → Visitor / Session / Contact / Timeline → Analytics / CRM / Recomendações
```

Cada evento carrega `event_id`, `event_name`, `tenant_id`, `application_id`, `visitor_id`, `session_id`, `contact_id`, `properties`, `context`, `occurred_at` e `received_at`. Idempotência garantida por `event_id` + aplicação. Detalhes em [`EVENT_CATALOG.md`](EVENT_CATALOG.md).

## SDK Laravel e Plugin Instalável

Aplicações clientes (Site Pessoal, Clube do Salão, Feira Esquerda Livre e futuros projetos) integram-se via o pacote em [`packages/jmf-system/customer-intelligence-sdk`](packages/jmf-system/customer-intelligence-sdk) — `identify()`, `track()` e `conversion()`, com visitor/sessão automáticos e envio assíncrono.

**A plataforma é instalável como plugin** em qualquer aplicação Laravel, permitindo que cada plataforma tenha seu próprio painel admin com métricas e inteligência centralizada. Ver [`PLUGIN_STRATEGY.md`](PLUGIN_STRATEGY.md) para análise completa de arquitetura, viabilidade e roadmap de implementação. O [`README`](packages/jmf-system/customer-intelligence-sdk/README.md) do pacote cobre instalação e uso do SDK.

## Segurança e LGPD

- Tokens de aplicação com hash seguro; exibição completa apenas na criação.
- Revogação e rotação de tokens; rate limiting por aplicação.
- Autorização e isolamento rigoroso por tenant.
- Logs de auditoria administrativa.
- Proibido armazenar senhas ou conteúdo sensível em `properties`/`context` dos eventos.
- Suporte a consentimento, anonimização e exclusão de contatos.
- Nenhuma operação crítica dos sistemas clientes depende da disponibilidade desta plataforma.

Detalhes em [`SECURITY.md`](SECURITY.md).

## Status atual

✅ **Fase 01 — Fundação e documentação** concluída (Laravel 12, MySQL, Livewire, Tailwind, Vite configurados; página inicial técnica operacional; 116 testes passando; qualidade de código validada).

✅ **Fase 02 — Autenticação e administração** concluída (login administrativo, usuários/perfis/permissões, layout administrativo, Policies/Gates, auditoria e testes de acesso).

✅ **Fase 03 — Multiempresa e aplicações** concluída (Tenants, Applications, tokens via Laravel Sanctum com rotação/revogação, isolamento por tenant e testes de segurança da API).

✅ **Fase 04 — Ingestão de eventos** concluída (`POST /api/v1/events`, validação via Form Request, idempotência por `event_id`, ingestão assíncrona via fila `database`, rate limiting dedicado e logs de falha).

✅ **Fase 05 — Visitantes, sessões e contatos** concluída (`Visitor`/`VisitorSession` materializados automaticamente a partir dos eventos, `Contact` único por tenant com `POST /api/v1/contacts/identify`, associação anônimo-conhecido, consentimentos LGPD e Timeline por contato no painel admin).

✅ **Fase 06 — Analytics MVP** concluída (painel `/admin/analytics` com totais, tendência diária, páginas/artigos/serviços mais acessados, UTMs, funis por sequência de eventos e conversões; tabela agregada `daily_metrics` populada pelo comando agendado `metrics:aggregate-daily`).

✅ **Fase 07 — SDK Laravel** concluída (pacote `packages/jmf-system/customer-intelligence-sdk`, `identify()`/`track()`/`conversion()`, visitor/sessão automáticos via cookies e envio assíncrono com retry/logs).

✅ **Fase 10 — Inteligência inicial** concluída (lead score por contato, afinidade entre produtos, recomendações simples com fallback de popularidade via `GET /api/v1/recommendations`, filtro de contatos inativos; recalculados pelo comando agendado `intelligence:compute`).

✅ **Fase 19 — Ajuda contextual e documentação de usuário** concluída (modal de ajuda em todas as telas do painel administrativo e página de Guia do Usuário em `/admin/guia`).

✅ **Fase 20 — Plugin UI Instalável** concluída (plataforma instalável como plugin em outras aplicações Laravel; 5 componentes Livewire, documentação de instalação/publicação SDK, 121 testes).

✅ **Fase 12 — Integração com Feira Esquerda Livre (Marketplace Analytics & Customer Journey)** concluída (3 dashboards visuais: Analytics Principal em `/admin/marketplace`, CRM de Contatos em `/admin/marketplace/contacts`, Customer Journey Timeline em `/admin/marketplace/contacts/{id}`; 17 eventos de marketplace documentados; Chart.js integrado; isolamento multi-tenant; 7 commits).

✅ **Fase 13 — AI Business Intelligence** concluída em 3 sprints (Customer Score RFV e 5 segmentos automáticos; análise de tendências e previsão de vendas por produto/vendedor; detecção de 4 tipos de oportunidade comercial — cross-sell, up-sell, win-back, bundle — expostos via `GET /api/v1/opportunities/{type}`; 5 comandos agendados de inteligência; 59 novos testes).

✅ **Fase 14 — AI Business Assistant** concluída em 2 sprints (motor de recomendações textuais para o expositor — queda de vendas, oportunidade de kit, preço fora da média, horário ideal de venda — 100% a partir de dados já coletados; API de consumo em `GET /api/v1/marketplace/sellers/{seller_id}/recommendations`; 22 novos testes).

✅ **Fase 15 — AI Marketing** concluída em 2 sprints (geração de título/descrição/SEO, textos + hashtags para Instagram/Facebook/WhatsApp e campanha de e-mail marketing; driver plugável — `template` sem custo por padrão, `Anthropic Claude API` pronto para ativar via `.env`; conteúdo revisável/editável antes de publicar via `PATCH /api/v1/marketing/content/{id}`; 42 novos testes). Geração de banners fica para a Fase 16.

📋 **Fase 21 — Integração com Feira Esquerda Livre (Piloto)** pendente (depende das Fases 20, 12, 13, 14 e 15 — todas concluídas).

✅ **Fase 22 — Affiliate Intelligence (fundação)** concluída — primeira fase da evolução Trend Intelligence/Affiliate Intelligence (Fases 22-31), validada por um caso de uso real (Influenciador Magalu/Magazine Você): `AffiliateProgram`/`AffiliateProduct` isolados por `application_id`, `AffiliateProviderInterface` (`ManualAffiliateProvider` padrão + stub Magalu), import de produtos via CSV, `IntegrationLog`, CRUD administrativo completo (`/admin/affiliate/programs`, `/admin/affiliate/products`).

✅ **Fase 23 — Trend Intelligence (fundação)** concluída: `Watchlist`/`Trend`/`TrendSnapshot`, `TrendProviderInterface` (`ManualTrendProvider` + `InternalBehaviorProvider`, dados próprios do marketplace; stubs documentados Instagram/Google Trends/YouTube), coleta agendada (`trends:collect`) e CRUD administrativo com histórico filtrável e gráfico (`/admin/trends/watchlists`).

✅ **Fase 24 — Trend Score** concluída: `TrendScoreCalculator` (regras baseadas em crescimento, volume, recorrência, estabilidade e engajamento quando disponível) gera uma pontuação 0-100 por tendência, recalculada diariamente (`trends:calculate-scores`) ou sob demanda no painel — exibida como badge colorido com breakdown dos fatores em `/admin/trends/watchlists` e no detalhe da tendência. Ver seção "Trend Intelligence, Affiliate Intelligence e Product Opportunity Engine" acima e Fases 22-31 do [`ROADMAP.md`](ROADMAP.md).

✅ **Fase 25 — Product Matcher** concluída: `TrendProductMatch` (pivot trend × affiliate_product, `match_score` 0-100) com `ProductMatcher` service (similaridade ponderada: palavra-chave 40% + categoria 35% + marca 25%), integrada ao comando `trends:calculate-scores`, UI em Livewire exibindo produtos relacionados com scores/breakdown/preço/comissão. Resolvida a pergunta: **"Que produtos devo divulgar quando essa tendência surge?"**.

✅ **Fase 26 — Product Opportunity Engine** concluída: `ProductOpportunity` (trend × product, score 0-100) com `CommercialIntentClassifier` (detecta intenção HIGH/MEDIUM/LOW) + `OpportunityScoreCalculator` (5 fatores ponderados: Trend 35% + Match 25% + Intent 20% + Commission 10% + Popularity 10%). Resolvida a pergunta: **"Essa oportunidade é viável comercialmente?"**. Integrada ao comando `trends:calculate-scores`.

📋 **Fase 27 — Content & Link Tracking** próxima: registrar campanhas e gerar links de rastreamento curtos para afiliados.

Consulte o progresso detalhado, fases e critérios de aceite em [`ROADMAP.md`](ROADMAP.md).

## Licença

Copyright © JMF System. Todos os direitos reservados. Projeto em desenvolvimento privado.
