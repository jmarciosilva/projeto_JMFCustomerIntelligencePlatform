# Catálogo de Eventos — JMF Customer Intelligence

> Este documento descreve o contrato de eventos aceito pela plataforma e o catálogo inicial de eventos dos projetos-piloto. O endpoint de ingestão foi implementado na Fase 04 do `ROADMAP.md`; este catálogo é a referência de contrato desde a Fase 01.

## Endpoint

```text
POST /api/v1/events
```

Endpoints futuros:

```text
POST /api/v1/contacts/identify
POST /api/v1/conversions
POST /api/v1/events/batch
```

## Estrutura de um evento

| Campo            | Tipo               | Obrigatório | Descrição                                             |
|-------------------|--------------------|:-----------:|--------------------------------------------------------|
| `event_id`        | string (ULID/UUID) | sim         | Identificador único do evento, usado para idempotência |
| `event_name`      | string              | sim         | Nome do evento, ex.: `article.viewed`                  |
| `tenant_id`        | string/int         | sim         | Identifica o tenant dono do evento                      |
| `application_id`   | string/int         | sim         | Identifica a aplicação de origem                        |
| `visitor_id`       | string              | sim         | Identificador do visitante (anônimo)                    |
| `session_id`       | string              | não         | Identificador da sessão de navegação                    |
| `contact_id`       | string/int/null    | não         | Identificador do contato conhecido, quando já identificado |
| `subject_type`     | string              | não         | Tipo da entidade relacionada ao evento (ex.: `Article`) |
| `subject_id`       | string/int          | não         | Identificador da entidade relacionada                   |
| `properties`       | objeto (JSON)       | não         | Propriedades específicas do evento                       |
| `context`          | objeto (JSON)       | não         | Contexto da requisição (URL, referrer, UTMs, user agent) |
| `occurred_at`      | datetime ISO 8601   | sim         | Momento em que o evento ocorreu na aplicação cliente     |
| `received_at`      | datetime ISO 8601   | gerado pela API | Momento em que o evento foi recebido pela plataforma |

### Exemplo

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

## Regras de contrato

- `event_id` deve ser único por aplicação; eventos repetidos são descartados (idempotência).
- `properties` e `context` possuem tamanho máximo de 10 KB cada, validado pela API (`StoreEventRequest`).
- Nenhum evento pode conter senhas, dados de cartão ou qualquer conteúdo sensível.
- Eventos sem `tenant_id`/`application_id` válidos (via token de aplicação) são rejeitados.

## Catálogo — Site pessoal e blog

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

### Funil inicial — Site pessoal

```text
Visitante
Leitor
Leitor engajado
Visitou portfólio
Clicou em contato
Enviou mensagem
Oportunidade profissional
```

## Catálogo — Clube do Salão

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

### Funil inicial — Clube do Salão

```text
Visitante
Visualizou serviço
Selecionou profissional
Iniciou agendamento
Concluiu agendamento
Atendimento realizado
Cliente recorrente
```

## Evolução

Novos eventos e novos projetos-piloto devem ser adicionados a este catálogo antes de serem implementados, mantendo a nomenclatura `entidade.acao` em inglês, minúsculas, separada por ponto.
