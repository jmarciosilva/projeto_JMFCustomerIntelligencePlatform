# 📊 Guia de Dashboards — Análise de Dados do Marketplace

## Visão Geral

A plataforma JMF Customer Intelligence oferece **3 dashboards visuais** para apresentar dados da Feira Esquerda Livre e outras aplicações:

1. **Dashboard Principal** — Métricas do Marketplace
2. **Timeline de Jornada** — Jornada visual do comprador  
3. **Lista de Contatos** — CRM com filtros e busca

---

## 1. Dashboard Principal 📈

### O que mostra?

- **KPIs em tempo real:**
  - 👁️ Visualizações de produtos
  - 🛒 Adições ao carrinho
  - 💰 Receita total
  - ⭐ Compras realizadas
  - ⭐ Avaliações
  - 📊 Taxa de conversão
  - ⏸️ Taxa de abandono de carrinho
  - 👥 Visitantes únicos

- **Gráfico de tendência:** Visualizações vs Compras por dia

- **Tabelas top 10:**
  - Vendedores por receita
  - Produtos por visualizações

### Como acessar?

```blade
<!-- No arquivo de rotas/view -->
<livewire:marketplace.dashboard :application="$application" />
```

### Filtros disponíveis

- **Período:** Últimos 7/30/90 dias

```php
// No controlador
public function dashboard(Application $application)
{
    return view('admin.marketplace.dashboard', [
        'application' => $application
    ]);
}
```

### Exemplo de dados retornados

```php
$metrics = [
    'total_events' => 156,
    'product_views' => 89,
    'cart_adds' => 42,
    'purchases' => 12,
    'reviews' => 5,
    'revenue' => 1247.50,
    'unique_visitors' => 34,
    'cart_abandonment_rate' => 19.05,
];

$chartData = [
    'labels' => ['01/08', '02/08', '03/08', ...],
    'views' => [15, 20, 18, ...],
    'purchases' => [2, 3, 1, ...],
];

$sellers = [
    ['seller_id' => 5, 'views' => 45, 'purchases' => 8, 'revenue' => 800.00],
    ['seller_id' => 8, 'views' => 32, 'purchases' => 4, 'revenue' => 447.50],
];

$products = [
    ['product_id' => 42, 'views' => 23, 'purchases' => 4, 'conversion_rate' => 17.39],
    ['product_id' => 15, 'views' => 18, 'purchases' => 2, 'conversion_rate' => 11.11],
];
```

### Tipos de gráficos

- **Line Chart:** Tendência de visualizações e compras
- **Badges:** KPIs com cores (azul, verde, roxo, etc.)
- **Progress bars:** Taxa de conversão
- **Data tables:** Vendedores e produtos

---

## 2. Timeline de Jornada 🎯

### O que mostra?

**Visualização passo a passo da jornada de cada comprador:**

```
👁️ Visualizou Produto #42
   ↓ 14:23 (2 min depois)
❤️ Favoritou Produto
   ↓ 5 min depois
➕ Adicionou ao Carrinho
   ↓ 8 min depois
🛒 Visualizou Carrinho
   ↓ 3 min depois
💳 Iniciou Checkout
   ↓ 4 min depois
✅ Compra Realizada
   ↓ 2 dias depois
⭐ Deixou Avaliação
```

### Componentes da timeline

- **Ícones visuais** para cada tipo de evento
- **Cores diferenciadas** (azul, verde, vermelho, etc.)
- **Timestamps** de cada ação
- **Detalhes:** Produto ID, Vendedor ID, Valor

### Cards de informação

```
┌─────────────────────────────┐
│ 👤 João Silva               │
│ joao@exemplo.com            │
├─────────────────────────────┤
│ Total de Eventos: 15        │
│ Lead Score: 85 pts          │
│ Última Atividade: há 2 horas│
├─────────────────────────────┤
│ Status: ✅ Cliente Convertido│
└─────────────────────────────┘
```

### Estágios da jornada

A timeline identifica automaticamente os estágios:

- **Descoberta** → produto.viewed, product.search
- **Interesse** → product.filtered, product.favorited
- **Decisão** → cart.item_added, cart.viewed
- **Ação** → checkout.started, purchase.completed
- **Defesa** → review.submitted, social_media.clicked

### Recomendações automáticas

Baseado no status do cliente:

**✅ Cliente Convertido:**
- 🎁 Ofereça produtos relacionados
- 👑 Convide para programa VIP
- ⭐ Peça avaliação

**⏸️ Carrinho Abandonado:**
- 🎯 Envie recall com desconto
- 📧 Destaque produtos complementares
- 🚚 Ofereça frete grátis

**👁️ Em Navegação:**
- 🎯 Mostre produtos similares
- 💬 Ofereça suporte/chat
- 💡 Sugira produtos recomendados

### Como acessar?

```blade
<!-- Na view -->
<livewire:marketplace.customer-journey-timeline :contact="$contact" />
```

```php
// No controlador
public function journey(Contact $contact)
{
    return view('admin.marketplace.journey', [
        'contact' => $contact
    ]);
}
```

---

## 3. Lista de Contatos 👥

### O que mostra?

**Tabela de todos os contatos/clientes da aplicação com:**

- Nome e e-mail
- Lead Score (com barra de progresso)
- Status (Cliente, Abandonado, Navegando)
- Número de eventos
- Link para ver jornada completa

### Filtros disponíveis

- **Busca:** Por nome ou e-mail
- **Status:** Todos, Convertidos, Abandonados, Navegando
- **Ordenação:** Por nome, lead score, data

### Dados da tabela

```
Nome              | E-mail              | Lead Score | Status        | Eventos | Ação
─────────────────────────────────────────────────────────────────────────────────────
João Silva        | joao@exemplo.com    | 85  ████   | ✅ Cliente    | 15      | Ver →
Maria Santos      | maria@exemplo.com   | 42  ██     | 👁️ Navegando | 7       | Ver →
Carlos Costa      | carlos@exemplo.com  | 0   ░░     | ⏸️ Abandonado | 3       | Ver →
```

### Estatísticas rápidas

```
┌──────────────────┬──────────────────┬──────────────────┐
│ Total Contatos   │ Página Atual      │ Por Página       │
│      156         │ 1 de 11           │ 15               │
└──────────────────┴──────────────────┴──────────────────┘
```

### Como acessar?

```blade
<!-- Na view -->
<livewire:marketplace.contacts-list :tenant="$tenant" />
```

```php
// No controlador
public function contacts(Tenant $tenant)
{
    return view('admin.marketplace.contacts', [
        'tenant' => $tenant
    ]);
}
```

### Filtros em ação

```php
// Buscar por nome
searchTerm: "joao" → mostra "João Silva"

// Filtrar por status
filterBy: "converted" → mostra apenas clientes que compraram

// Ordenar por lead score
sortBy: "lead_score", sortDirection: "desc" → maior para menor
```

---

## Integração com Rotas

### Criar rotas para os dashboards

```php
// routes/web.php

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('admin/marketplace')->name('marketplace.')->group(function () {
        // Dashboard principal
        Route::get('/', function (Application $application) {
            return view('admin.marketplace.dashboard', [
                'application' => $application
            ]);
        })->name('dashboard');

        // Lista de contatos
        Route::get('contacts', function (Tenant $tenant) {
            return view('admin.marketplace.contacts', [
                'tenant' => $tenant
            ]);
        })->name('contacts.index');

        // Jornada do comprador
        Route::get('contacts/{contact}', function (Contact $contact) {
            return view('admin.marketplace.journey', [
                'contact' => $contact
            ]);
        })->name('journey.show');
    });
});
```

---

## Exemplo de Views Blade

### Dashboard

```blade
@extends('layouts.admin')

@section('content')
    <livewire:marketplace.dashboard :application="auth()->user()->application" />
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
```

### Jornada

```blade
@extends('layouts.admin')

@section('content')
    <livewire:marketplace.customer-journey-timeline :contact="$contact" />
@endsection
```

### Contatos

```blade
@extends('layouts.admin')

@section('content')
    <livewire:marketplace.contacts-list :tenant="auth()->user()->tenant" />
@endsection
```

---

## Dados de Exemplo

### Evento do Marketplace

```json
{
  "event_id": "evt_12345",
  "event_name": "product.viewed",
  "visitor_id": "visitor_123",
  "contact_id": 1,
  "application_id": 1,
  "tenant_id": 1,
  "occurred_at": "2026-08-08T14:30:00Z",
  "properties": {
    "product_id": 42,
    "seller_id": 5,
    "category": "Artesanato",
    "price": 49.90
  },
  "context": {
    "page_url": "/produtos/42",
    "referrer": "https://google.com",
    "utm_source": "google",
    "utm_medium": "organic"
  }
}
```

### Contato

```json
{
  "id": 1,
  "tenant_id": 1,
  "name": "João Silva",
  "email": "joao@exemplo.com",
  "lead_score": 85,
  "lead_score_computed_at": "2026-08-08T10:00:00Z",
  "first_identified_at": "2026-07-25T14:00:00Z",
  "last_seen_at": "2026-08-08T18:00:00Z"
}
```

---

## Performance & Otimizações

### Queries otimizadas

- Dashboard usa agregações eficientes (groupBy, sum)
- Timeline carrega apenas eventos do contato
- Lista de contatos usa paginação (15 por página)
- Índices em `events(application_id, event_name, occurred_at)`

### Cache

```php
// Cache de métricas por 1 hora
$metrics = cache()->remember("app_{$appId}_metrics_{$period}", 60, function () {
    return calculateMetrics($appId, $period);
});
```

### Requisições em tempo real

Livewire permite atualizações reativas:

```php
// Atualizar period dispara loadData()
public function updatedPeriod()
{
    $this->loadData();
}
```

---

## Troubleshooting

### Dashboard não aparece

- Verifique se Livewire 3 está instalado
- Confirme que Chart.js está no `<head>`
- Verifique permissões de acesso à aplicação

### Dados não atualizam

- Limpe o cache: `php artisan cache:clear`
- Verifique se eventos estão sendo capturados em `events` table
- Confirme que `application_id` está correto

### Timeline vazia

- Verifique se contato tem eventos associados
- Confirme `contact_id` é preenchido nos eventos
- Valide que `Contact` model existe

---

## Próximas Melhorias

- ✅ Export de dados (PDF, CSV)
- ✅ Gráficos de funil de conversão
- ✅ Heatmap de produtos mais clicados
- ✅ Comparação período a período
- ✅ Recomendações IA por contato
- ✅ Segmentação automática de clientes
- ✅ Dashboard por vendedor/seller

---

**Versão:** 1.0  
**Data:** 2026-08-08  
**Compatibilidade:** Laravel 12.x, Livewire 3.x, Tailwind CSS 4.x
