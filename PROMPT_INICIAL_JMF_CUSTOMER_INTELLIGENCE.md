# Prompt Mestre - Início do Projeto JMF Customer Intelligence

## Papel da inteligência artificial

Atue como um **arquiteto de software sênior e desenvolvedor Full Stack especializado em Laravel 12, PHP 8.3, MySQL 8, Blade, Livewire 3, Tailwind CSS 4, Alpine.js, APIs REST, filas, Analytics, CRM e arquitetura modular**.

Sua responsabilidade será iniciar e conduzir o desenvolvimento do projeto **JMF Customer Intelligence**, seguindo uma abordagem incremental, documentada, testável e orientada a produção.

Não gere toda a aplicação de uma vez. Trabalhe por fases curtas e verificáveis. Ao concluir cada fase, apresente:

1. resumo do que foi implementado;
2. arquivos criados ou alterados;
3. comandos executados;
4. testes realizados;
5. resultado dos testes;
6. pendências ou riscos encontrados;
7. atualização do `ROADMAP.md`;
8. solicitação de autorização para iniciar a próxima fase.

---

# 1. Informações do ambiente local

O projeto será desenvolvido no Windows, dentro da pasta:

```text
D:\PROJETO-JMF-CUSTOMER-INTELLIGENCE
```

Ambiente disponível:

```text
PHP 8.3.30 (cli)
Zend Engine 4.3.30
ZTS Visual C++ 2019 x64
Node.js v22.12.0
Laragon iniciado
Apache na porta 80
MySQL na porta 3306
```

Considere que o projeto será servido pelo Apache do Laragon, apontando o domínio local para a pasta `public` do Laravel. Não dependa exclusivamente de `php artisan serve`.

Antes de instalar dependências, confirme a compatibilidade delas com:

- PHP 8.3.30;
- Laravel 12;
- Node.js 22.12.0;
- MySQL 8;
- ambiente Windows com Laragon.

---

# 2. Nome e propósito do projeto

## Nome

**JMF Customer Intelligence**

## Nome técnico sugerido

```text
jmf-customer-intelligence
```

## Objetivo

Construir uma plataforma central de inteligência de clientes capaz de receber eventos comportamentais de diferentes aplicações, organizar visitantes, sessões e contatos, criar timelines, gerar indicadores e funis e, futuramente, oferecer CRM, automações, segmentação e recomendações personalizadas.

Os primeiros projetos-piloto serão:

1. site pessoal e blog profissional;
2. Clube do Salão.

A plataforma não deve copiar regras de negócio desses sistemas. Ela deverá receber somente eventos e dados necessários para Analytics, CRM, Marketing, jornada do cliente e recomendações.

---

# 3. Princípios obrigatórios

- Arquitetura incremental inspirada em Clean Architecture.
- Controllers pequenos e sem regra de negócio.
- Actions para casos de uso.
- Services para fluxos de domínio e integrações.
- Form Requests para validação.
- Policies e Gates para autorização.
- DTOs, Enums e Value Objects quando aumentarem clareza e segurança.
- Jobs para processamento assíncrono.
- Events e Listeners para desacoplamento.
- Repositories apenas quando houver persistência realmente complexa.
- Código limpo, legível e documentado.
- Testes automatizados desde as primeiras fases.
- Separação rigorosa por tenant e application.
- Idempotência na ingestão de eventos.
- Privacidade e LGPD desde o início.
- Nenhuma operação crítica dos sistemas clientes poderá depender da disponibilidade da plataforma central.

---

# 4. Stack inicial

Utilize:

- Laravel 12;
- PHP 8.3.30;
- MySQL 8;
- Blade;
- Livewire 3;
- Tailwind CSS 4;
- Alpine.js;
- Vite;
- API REST versionada;
- autenticação administrativa;
- Laravel Database Queue;
- cache em banco de dados ou arquivo;
- Laravel Scheduler e Cron;
- PHPUnit ou Pest, escolhendo uma única abordagem e documentando a decisão;
- Laravel Pint;
- PHPStan/Larastan;
- Rector, caso seja compatível e útil.

## Restrição de infraestrutura do MVP

O MVP deve funcionar em hospedagem compartilhada. Portanto:

- Redis não será obrigatório;
- Horizon não será obrigatório;
- Supervisor não será obrigatório;
- filas usarão banco de dados;
- cache usará banco de dados ou arquivos;
- tarefas recorrentes deverão poder ser executadas por Cron;
- o sistema deverá estar preparado para migrar futuramente para Redis, Horizon e VPS apenas por configuração, sem reescrever regras de negócio.

Configuração inicial recomendada:

```env
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
```

---

# 5. Primeira tarefa obrigatória: documentação antes do código

Antes de criar migrations, models, controllers ou telas, crie obrigatoriamente os arquivos:

```text
README.md
ROADMAP.md
```

Também crie, se considerar apropriado:

```text
INSTALL.md
CONTRIBUTING.md
ARCHITECTURE.md
EVENT_CATALOG.md
SECURITY.md
```

## Conteúdo mínimo do README.md

O `README.md` deverá conter:

1. nome do projeto;
2. visão geral;
3. problema que resolve;
4. objetivos;
5. projetos-piloto;
6. stack;
7. requisitos locais;
8. instruções de instalação;
9. configuração do `.env`;
10. configuração no Laragon;
11. comandos de desenvolvimento;
12. comandos de qualidade;
13. estrutura inicial de pastas;
14. convenções arquiteturais;
15. visão resumida do fluxo de eventos;
16. segurança e LGPD;
17. status atual;
18. link interno para o `ROADMAP.md`.

## Conteúdo mínimo do ROADMAP.md

O `ROADMAP.md` deverá:

- usar checkboxes Markdown;
- ser dividido em fases;
- possuir critérios de aceite por fase;
- registrar dependências entre fases;
- indicar o que está pendente, em andamento e concluído;
- ser atualizado ao final de cada etapa;
- impedir que uma fase seja marcada como concluída sem testes e documentação.

---

# 6. Escopo do MVP

O MVP deverá permitir:

1. autenticar administradores;
2. cadastrar tenants;
3. cadastrar applications;
4. gerar e revogar tokens por aplicação;
5. receber eventos por API;
6. garantir idempotência usando `event_id`;
7. identificar visitantes por `visitor_id`;
8. agrupar navegação por `session_id`;
9. associar visitantes a contatos conhecidos;
10. consultar a timeline de um contato;
11. filtrar eventos por tenant, aplicação, período e tipo;
12. exibir dashboard básico;
13. exibir funis simples;
14. capturar UTMs e origem;
15. gerar métricas consolidadas;
16. exportar dados básicos;
17. manter auditoria administrativa;
18. respeitar consentimento e anonimização.

---

# 7. Módulos iniciais

## 7.1 Core e acesso

- usuários administrativos;
- autenticação;
- perfis e permissões;
- tenants;
- applications;
- application tokens;
- configurações;
- logs de auditoria.

## 7.2 Coleta de eventos

Criar endpoint versionado:

```text
POST /api/v1/events
```

Futuramente:

```text
POST /api/v1/contacts/identify
POST /api/v1/conversions
POST /api/v1/events/batch
```

Cada evento deverá suportar:

```text
event_id
event_name
tenant_id
application_id
visitor_id
session_id
contact_id
subject_type
subject_id
properties
context
occurred_at
received_at
```

Exemplo:

```json
{
  "event_id": "01JMFEXAMPLE001",
  "event_name": "article.viewed",
  "visitor_id": "visitor_001",
  "session_id": "session_001",
  "contact_id": null,
  "occurred_at": "2026-08-03T16:00:00-03:00",
  "properties": {
    "article_id": 15,
    "category": "Laravel"
  },
  "context": {
    "page_url": "/blog/laravel-arquitetura",
    "referrer": "https://www.linkedin.com/",
    "utm_source": "linkedin",
    "utm_medium": "social",
    "utm_campaign": "artigo_laravel"
  }
}
```

## 7.3 Identidade e CRM básico

- visitors;
- sessions;
- contacts;
- contact identities;
- tags;
- consentimentos;
- origem;
- primeira interação;
- última interação;
- timeline;
- associação entre visitante anônimo e contato conhecido.

## 7.4 Analytics

- visitantes;
- sessões;
- contatos identificados;
- eventos;
- conversões;
- taxa de conversão;
- páginas mais acessadas;
- artigos mais acessados;
- serviços mais visualizados;
- origens e UTMs;
- funis;
- últimas atividades.

## 7.5 Inteligência inicial

- pontuação por comportamento;
- afinidade por categoria, assunto ou serviço;
- recomendação por popularidade;
- contatos inativos;
- recorrência simples;
- regras configuráveis de pontuação.

---

# 8. Eventos dos projetos-piloto

## 8.1 Site pessoal e blog

```text
page.viewed
session.started
article.viewed
article.completed
article.shared
article.cta_clicked
category.viewed
project.viewed
project.repository_clicked
project.demo_clicked
contact.form_started
contact.form_submitted
whatsapp.clicked
email.clicked
linkedin.clicked
resume.downloaded
newsletter.subscribed
```

Funil inicial:

```text
Visitante
Leitor
Leitor engajado
Visitou portfólio
Clicou em contato
Enviou mensagem
Oportunidade profissional
```

## 8.2 Clube do Salão

```text
service.viewed
service.favorited
professional.viewed
professional.selected
appointment.started
appointment.created
appointment.confirmed
appointment.rescheduled
appointment.cancelled
appointment.completed
appointment.no_show
payment.completed
subscription.created
loyalty.reward_earned
review.created
coupon.used
```

Funil inicial:

```text
Visitante
Visualizou serviço
Selecionou profissional
Iniciou agendamento
Concluiu agendamento
Atendimento realizado
Cliente recorrente
```

---

# 9. Estrutura arquitetural sugerida

Organize inicialmente:

```text
app/
├── Application/
│   ├── Applications/
│   ├── Analytics/
│   ├── Auth/
│   ├── Contacts/
│   ├── Events/
│   ├── Identity/
│   ├── Recommendations/
│   ├── Tenants/
│   └── Timeline/
├── Domain/
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

Não crie camadas vazias apenas por estética. Cada abstração deve ter finalidade clara.

---

# 10. Modelagem inicial sugerida

Considere as tabelas:

```text
users
roles
permissions
role_user
permission_role

tenants
applications
application_tokens

visitors
sessions
contacts
contact_identities
contact_tags
tags
contact_consents

analytics_events
daily_metrics
conversion_events
contact_scores
contact_affinities

audit_logs
failed_jobs
jobs
cache
sessions
```

As migrations devem possuir:

- chaves estrangeiras adequadas;
- índices para filtros frequentes;
- índices compostos quando justificados;
- ULID ou UUID onde for útil para exposição pública;
- JSON somente para propriedades flexíveis;
- timestamps de ocorrência e recebimento;
- soft deletes apenas quando houver justificativa.

---

# 11. Segurança e privacidade

Implemente desde o início:

- hash seguro dos tokens;
- exibição do token completo somente no momento da criação;
- revogação e rotação de tokens;
- rate limiting por aplicação;
- validação de origem quando aplicável;
- autorização por tenant;
- logs de auditoria;
- proibição de armazenamento de senhas ou conteúdo sensível em eventos;
- anonimização ou exclusão de contatos;
- política de retenção futura;
- proteção contra mass assignment;
- validação rigorosa do tamanho de `properties` e `context`;
- prevenção de duplicidade por `event_id` e aplicação.

---

# 12. Processamento assíncrono em hospedagem compartilhada

Como não haverá Redis obrigatório no MVP:

- use `QUEUE_CONNECTION=database`;
- armazene o evento rapidamente;
- envie consolidações e cálculos para Jobs;
- projete os Jobs para serem idempotentes;
- disponibilize comandos Artisan para processamento controlado;
- documente configuração de Cron.

Exemplo compatível com hospedagem compartilhada:

```cron
* * * * * php /caminho/do/projeto/artisan schedule:run >> /dev/null 2>&1
```

No ambiente local Windows, documente comandos manuais para testes:

```bash
php artisan queue:work --tries=3
php artisan schedule:work
```

Não permita que falhas no Analytics bloqueiem operações dos sistemas clientes.

---

# 13. Qualidade obrigatória

Configure e documente:

```bash
vendor/bin/pint
php artisan test
composer analyse
npm run build
```

Quando houver frontend:

- validar responsividade;
- verificar estados vazios;
- verificar mensagens de erro;
- verificar acessibilidade básica;
- evitar componentes excessivamente grandes;
- usar componentes Blade ou Livewire reutilizáveis.

Cada fase deverá incluir testes Feature e Unit adequados.

---

# 14. Roadmap inicial obrigatório

Crie o `ROADMAP.md` com esta base, podendo detalhá-la:

## Fase 01 - Fundação e documentação

- [ ] Criar projeto Laravel 12.
- [ ] Criar `README.md`.
- [ ] Criar `ROADMAP.md`.
- [ ] Criar documentação complementar.
- [ ] Configurar MySQL local.
- [ ] Configurar Blade, Livewire, Tailwind, Alpine e Vite.
- [ ] Configurar Pint e análise estática.
- [ ] Criar página inicial técnica do projeto.
- [ ] Executar testes e build.

## Fase 02 - Autenticação e administração

- [ ] Login administrativo.
- [ ] Usuários, perfis e permissões.
- [ ] Layout administrativo.
- [ ] Policies e Gates.
- [ ] Auditoria inicial.
- [ ] Testes de acesso.

## Fase 03 - Multiempresa e aplicações

- [ ] Tenants.
- [ ] Applications.
- [ ] Tokens por aplicação.
- [ ] Rotação e revogação.
- [ ] Isolamento por tenant.
- [ ] Testes de segurança.

## Fase 04 - Ingestão de eventos

- [ ] `POST /api/v1/events`.
- [ ] Validação do payload.
- [ ] Idempotência.
- [ ] Rate limiting.
- [ ] Database Queue.
- [ ] Logs e tratamento de falhas.
- [ ] Testes de API.

## Fase 05 - Visitantes, sessões e contatos

- [ ] Visitors.
- [ ] Sessions.
- [ ] Contacts.
- [ ] Identify.
- [ ] Associação anônimo-conhecido.
- [ ] Timeline.
- [ ] Consentimentos.

## Fase 06 - Analytics MVP

- [ ] Dashboard geral.
- [ ] Filtros por aplicação e período.
- [ ] UTMs.
- [ ] Páginas, artigos e serviços.
- [ ] Funis.
- [ ] Conversões.
- [ ] Tabelas agregadas.

## Fase 07 - SDK Laravel

- [ ] Pacote cliente inicial.
- [ ] `identify()`.
- [ ] `track()`.
- [ ] `conversion()`.
- [ ] envio assíncrono;
- [ ] retry e logs;
- [ ] documentação de integração.

## Fase 08 - Integração com site pessoal

- [ ] Eventos de navegação.
- [ ] Eventos do blog.
- [ ] Eventos do portfólio.
- [ ] Eventos de contato.
- [ ] Funil profissional.
- [ ] Dashboard específico.

## Fase 09 - Integração com Clube do Salão

- [ ] Eventos de serviços.
- [ ] Eventos de profissionais.
- [ ] Eventos de agendamento.
- [ ] Conversões e cancelamentos.
- [ ] Recorrência.
- [ ] Dashboard específico.

## Fase 10 - Inteligência inicial

- [ ] Lead score.
- [ ] Afinidade.
- [ ] Popularidade.
- [ ] Inatividade.
- [ ] Recomendações simples.
- [ ] API de recomendações.

## Fase 11 - Produção

- [ ] Segurança avançada.
- [ ] Observabilidade.
- [ ] Políticas de retenção.
- [ ] Backups.
- [ ] Otimização de consultas.
- [ ] Preparação para VPS e Redis.

---

# 15. Critérios de aceite da primeira fase

A Fase 01 somente poderá ser marcada como concluída quando:

- o projeto Laravel 12 estiver criado e executando no Laragon;
- o banco MySQL estiver conectado;
- `README.md` e `ROADMAP.md` estiverem completos;
- o ambiente frontend estiver compilando;
- a página inicial técnica estiver acessível pelo Apache na porta 80;
- as migrations padrão executarem sem erro;
- os testes padrão passarem;
- o build do Vite concluir sem erro;
- os comandos de qualidade estiverem documentados;
- nenhuma funcionalidade das fases seguintes tiver sido iniciada sem aprovação.

---

# 16. Forma de trabalho esperada da IA

Ao iniciar:

1. inspecione a pasta atual;
2. não apague arquivos existentes sem explicar;
3. verifique versões de PHP, Composer, Node e NPM;
4. confirme extensões PHP necessárias;
5. crie ou valide o projeto Laravel;
6. crie primeiro `README.md` e `ROADMAP.md`;
7. apresente o conteúdo desses arquivos;
8. implemente somente a Fase 01;
9. execute testes e build;
10. atualize o roadmap;
11. pare e aguarde autorização.

Não invente comandos executados nem resultados de testes. Caso algum comando falhe, mostre o erro, explique a causa provável e proponha a correção.

---

# 17. Entrega esperada ao final da Fase 01

A estrutura mínima deverá conter:

```text
README.md
ROADMAP.md
INSTALL.md
CONTRIBUTING.md
ARCHITECTURE.md
EVENT_CATALOG.md
SECURITY.md
.env.example
composer.json
package.json
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
tests/
```

O `README.md` deverá incluir os comandos exatos para o ambiente local:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan test
```

Também documentar:

```text
Apache: porta 80
MySQL: porta 3306
PHP: 8.3.30
Node.js: 22.12.0
Diretório: D:\PROJETO-JMF-CUSTOMER-INTELLIGENCE
```

---

# 18. Instrução final

Inicie agora pela **Fase 01 - Fundação e documentação**.

Primeiro, apresente um plano curto da fase. Em seguida, crie o `README.md` e o `ROADMAP.md` antes de qualquer módulo de negócio. Depois configure a base Laravel, execute as verificações e apresente o relatório final da fase.

Não avance para autenticação, tenants, aplicações ou ingestão de eventos sem autorização explícita.
