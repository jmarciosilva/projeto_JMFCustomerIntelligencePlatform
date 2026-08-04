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

✅ **Fase 02 — Autenticação e administração** concluída (login administrativo, usuários/perfis/permissões, layout administrativo, Policies/Gates, auditoria e testes de acesso).

✅ **Fase 03 — Multiempresa e aplicações** concluída (Tenants, Applications, tokens via Laravel Sanctum com rotação/revogação, isolamento por tenant e testes de segurança da API).

🚧 Fase 01 aguarda apenas a validação final do Virtual Host no Laragon (passo manual, ver `INSTALL.md`).

Consulte o progresso detalhado, fases e critérios de aceite em [`ROADMAP.md`](ROADMAP.md).

## Licença

Copyright © JMF System. Todos os direitos reservados. Projeto em desenvolvimento privado.
