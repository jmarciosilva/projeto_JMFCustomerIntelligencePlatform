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
article.liked
article.unliked
comment.submitted
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

## Catálogo — Feira Esquerda Livre (Marketplace)

```text
product.viewed
product.search
product.filtered
product.favorited
product.unfavorited
cart.item_added
cart.item_removed
cart.viewed
cart.abandoned
checkout.started
purchase.completed
purchase.cancelled
review.submitted
seller.contacted
seller.profile_viewed
social_media.clicked
traffic_source.detected
```

### Estrutura de `properties` por evento

#### `product.viewed`
```json
{
  "product_id": 123,
  "category": "Artesanato",
  "seller_id": 5,
  "price": 49.90
}
```

#### `product.search`
```json
{
  "search_term": "vaso cerâmica",
  "results_count": 15,
  "filters_applied": ["category", "price"]
}
```

#### `product.filtered`
```json
{
  "filter_type": "category",
  "filter_value": "Artesanato",
  "results_count": 45
}
```

#### `product.favorited` / `product.unfavorited`
```json
{
  "product_id": 123,
  "seller_id": 5
}
```

#### `cart.item_added` / `cart.item_removed`
```json
{
  "product_id": 123,
  "quantity": 2,
  "price": 49.90,
  "seller_id": 5
}
```

#### `cart.viewed`
```json
{
  "items_count": 3,
  "total_value": 149.70,
  "sellers_involved": [5, 8, 12]
}
```

#### `cart.abandoned`
```json
{
  "items_count": 3,
  "total_value": 149.70,
  "sellers_involved": [5, 8, 12],
  "time_to_abandon": 1800
}
```

#### `checkout.started`
```json
{
  "items_count": 3,
  "total_value": 149.70
}
```

#### `purchase.completed` / `purchase.cancelled`
```json
{
  "order_id": "ORD-2026-0001",
  "items_count": 3,
  "total_value": 149.70,
  "sellers": [
    {"seller_id": 5, "items_count": 1, "subtotal": 49.90},
    {"seller_id": 8, "items_count": 2, "subtotal": 99.80}
  ],
  "payment_method": "credit_card",
  "shipping_type": "standard"
}
```

#### `review.submitted`
```json
{
  "product_id": 123,
  "seller_id": 5,
  "rating": 5,
  "review_text": "Produto excelente!"
}
```

#### `seller.contacted`
```json
{
  "seller_id": 5,
  "contact_method": "whatsapp",
  "product_id": 123
}
```

#### `seller.profile_viewed`
```json
{
  "seller_id": 5,
  "seller_name": "Artesanato da Maria"
}
```

#### `social_media.clicked`
```json
{
  "platform": "instagram",
  "seller_id": 5
}
```

#### `traffic_source.detected`
```json
{
  "source": "instagram",
  "medium": "social",
  "campaign": "summer_collection"
}
```

### Funil inicial — Feira Esquerda Livre

```text
Visitante
Produto visualizado
Produto favoritado
Item no carrinho
Carrinho visualizado
Checkout iniciado
Compra concluída
Cliente recorrente
Avaliação submetida
Indicação (rede social)
```

## Evolução

Novos eventos e novos projetos-piloto devem ser adicionados a este catálogo antes de serem implementados, mantendo a nomenclatura `entidade.acao` em inglês, minúsculas, separada por ponto.
