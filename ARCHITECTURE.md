# Arquitetura — JMF Customer Intelligence

## Visão geral

A plataforma segue uma arquitetura em camadas inspirada em Clean Architecture, adaptada às convenções do Laravel. O objetivo é manter baixo acoplamento, alta testabilidade e regras de negócio isoladas de detalhes de framework e infraestrutura.

```
Aplicação cliente
   ↓
Controller (HTTP)
   ↓
Action (caso de uso)
   ↓
Service (fluxo de domínio / integração)
   ↓
Event → Job (processamento assíncrono)
   ↓
Database
```

Controllers apenas recebem requisições, validam via Form Requests e delegam para Actions. Toda regra de negócio vive em Actions e Services.

## Estrutura de pastas

```text
app/
├── Application/        # Casos de uso (Actions) organizados por módulo
│   ├── Applications/
│   ├── Analytics/
│   ├── Auth/
│   ├── Contacts/
│   ├── Events/
│   ├── Identity/
│   ├── Recommendations/
│   ├── Tenants/
│   └── Timeline/
├── Domain/              # Regras de domínio, Value Objects, Enums, contratos
│   ├── Analytics/
│   ├── Contacts/
│   ├── Events/
│   ├── Shared/
│   └── Tenancy/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
├── Jobs/
├── Listeners/
├── Models/
├── Policies/
└── Support/
```

Camadas são criadas apenas quando existe finalidade clara — evitar pastas vazias por estética. Módulos e subpastas listados acima serão criados progressivamente, conforme cada fase do `ROADMAP.md` exigir.

## Princípios

- **Controllers pequenos**: sem regra de negócio, apenas orquestração HTTP.
- **Actions**: um caso de uso por classe, com `__invoke` ou método `handle`.
- **Services**: fluxos de domínio mais amplos e integrações externas.
- **Form Requests**: toda validação de entrada HTTP.
- **Policies/Gates**: toda autorização.
- **DTOs / Enums / Value Objects**: usados quando aumentam clareza e segurança de tipos.
- **Jobs**: todo processamento assíncrono, projetados para serem idempotentes.
- **Events/Listeners**: usados para desacoplar efeitos colaterais do fluxo principal.
- **Repositories**: apenas quando houver persistência realmente complexa (não usados por padrão no MVP; Eloquent é usado diretamente nas Actions/Services quando suficiente).

## Multiempresa e isolamento

Todo dado de negócio (eventos, visitantes, sessões, contatos) é associado a um `tenant_id` e a um `application_id`. Consultas e regras de autorização devem sempre considerar esse isolamento — nunca expor dados entre tenants.

## Processamento assíncrono (MVP em hospedagem compartilhada)

- Fila: `QUEUE_CONNECTION=database`.
- Cache: `CACHE_STORE=database`.
- Sessão administrativa: `SESSION_DRIVER=database`.
- Tarefas recorrentes via Laravel Scheduler + Cron do sistema operacional.
- Sem dependência obrigatória de Redis, Horizon, Supervisor ou WebSockets no MVP.
- Jobs devem ser idempotentes e nunca bloquear a ingestão principal de eventos.
- A migração futura para Redis/Horizon/VPS deve ocorrer apenas por configuração, sem reescrever regras de negócio.

## Framework de testes

Decisão: **Pest**, sobre PHPUnit, como camada de sintaxe. Pest é compatível com o ecossistema PHPUnit/Laravel, oferece sintaxe mais legível para specs comportamentais (relevante para um domínio orientado a eventos) e mantém compatibilidade total com anotações e ferramentas do Laravel 12. Todos os testes (Feature e Unit) usarão a sintaxe Pest.

## Qualidade de código

- **Laravel Pint**: formatação de código (PSR-12).
- **PHPStan / Larastan**: análise estática.
- **Rector**: refatoração automatizada, quando aplicável.

Comandos documentados em `README.md` e expostos via Composer scripts (`composer analyse`, `composer refactor`).

## Referências

- Padrões: SOLID, Clean Code, PSR-12, Laravel Best Practices, DDD quando agregar valor.
- Consulte também `docs/DOMAIN.md`, `docs/EVENTS.md` e `docs/DEVELOPMENT_RULES.md` (criados nas fases seguintes) e `EVENT_CATALOG.md`.
