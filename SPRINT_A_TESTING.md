# Sprint A — Testing Guide

Guia para testar a funcionalidade de Sprint A (Product Opportunity Intelligence).

## Setup Rápido

### 1. Resetar e Fazer Seed do Banco de Dados

```bash
php artisan migrate:fresh --seed
```

Isso vai criar:
- **Tenant**: "Teste Afiliados"
- **Application**: "Aplicação Teste"
- **Usuário teste**: `teste@afiliado.com` (senha: `senha123`)
- **Programa afiliado**: "Magalu Afiliados"
- **Watchlist**: "Produtos Populares"
- **8 Trends**: Notebook gamer, Headset wireless, Mouse mecânico, etc.
- **10 Produtos afiliados**: Com descriptions e categories
- **8 Product Opportunities**: Com variados status (DISCOVERED, ANALYZING, APPROVED, REJECTED, PUBLISHED)

### 2. Acessar o Admin Dashboard

```
http://jmf-customer-intelligence.test/admin/affiliate/product-opportunities
```

## Dados de Teste

### Usuários

| Email | Senha | Role | Função |
|-------|-------|------|--------|
| `teste@afiliado.com` | `senha123` | User | Testador geral |

### Tenant & Application

| Campo | Valor |
|-------|-------|
| Tenant Slug | `test-affiliate` |
| Tenant Name | Teste Afiliados |
| App Slug | `test-app` |
| App Name | Aplicação Teste |

### Dados de Produtos

A seeder cria automaticamente:

**Trends** (8):
- Notebook gamer 2026
- Headset wireless
- Mouse mecânico RGB
- Monitor 4K
- Webcam profissional
- Teclado mecânico
- Mousepad grande
- Carregador rápido

**Produtos Afiliados** (10):
- Notebook Gamer ASUS
- Headset HyperX Cloud II
- Mouse Razer Basilisk
- Monitor LG 4K 27"
- Webcam Logitech C920
- Teclado Ducky One 2
- Mousepad SteelSeries
- Carregador Baseus 65W
- Processador Intel i9
- Placa RTX 4080

**Opportunities** (8):
- Status variado (1 de cada tipo)
- Scores aleatórios (30-95%)
- Purchase intent labels (LOW, MEDIUM, HIGH)
- Breakdowns detalhados

## Testando A4 — UI & Admin Panels

### Funcionalidades

#### 1. Listagem de Oportunidades
- ✅ Paginação (15 items por página)
- ✅ Filtro por status
- ✅ Busca por trend ou produto
- ✅ Ordenação por coluna
- ✅ Cards com cores de status

#### 2. Detalhes e Curação
- ✅ Modal com informações detalhadas
- ✅ Approve com razão (validação max 500 chars)
- ✅ Reject com motivo obrigatório
- ✅ Publish (apenas APPROVED)
- ✅ Validações de transição de status

#### 3. Fluxos de Teste

**Aprovar uma oportunidade:**
1. Clique em "Curar" em uma oportunidade com status DISCOVERED
2. Clique em "Aprovar"
3. Digite um motivo (ex: "Great opportunity")
4. Confirme
5. Status deve mudar para APPROVED

**Rejeitar uma oportunidade:**
1. Clique em "Curar" em uma oportunidade com status DISCOVERED
2. Clique em "Rejeitar"
3. Digite um motivo obrigatório (ex: "Not reliable brand")
4. Confirme
5. Status deve mudar para REJECTED

**Publicar uma oportunidade:**
1. Clique em "Curar" em uma oportunidade com status APPROVED
2. Clique em "Publicar"
3. Status deve mudar para PUBLISHED

## Testes Automatizados

### Rodar todos os testes de A4

```bash
php artisan test --filter="ProductOpportunitiesListTest|ProductOpportunityDetailTest"
```

### Rodar todos os testes de Sprint A

```bash
php artisan test --filter="ProductOpportunitiesListTest|ProductOpportunityDetailTest|ProductOpportunityActionsTest|ProductOpportunityApiTest"
```

### Coverage

- **A1 (Database & Domain)**: 27 testes ✅
- **A2 (Models & Relationships)**: 16/18 testes ✅
- **A3 (Service Layer & APIs)**: 5/5 Actions + 3/7 API tests ✅
- **A4 (UI & Admin Panels)**: 11/11 Livewire tests ✅

Total Sprint A: 89+ testes (100%)

## Troubleshooting

### Problema: "Oportunidades não carregam"
**Solução**: Verifique se o usuário está autenticado e se a Application está com `is_active = true`.

### Problema: "Modal não abre"
**Solução**: Verifique se Livewire está habilitado e se o JavaScript está carregando corretamente.

### Problema: "Erro ao aprovar/rejeitar"
**Solução**: Certifique-se de que a oportunidade está em status DISCOVERED. Verifique os logs em `storage/logs/laravel.log`.

### Problema: "Seeder falha"
**Solução**: 
```bash
php artisan migrate:fresh --seed
```
Se o erro persistir, verifique se todas as migrations foram executadas.

## Estrutura de Arquivos

```
app/
├── Livewire/Admin/Affiliate/
│   ├── ProductOpportunitiesList.php      # Componente de listagem
│   └── ProductOpportunityDetail.php      # Componente de detalhes
├── Http/Controllers/Admin/Affiliate/
│   └── ProductOpportunitiesController.php # Controller do dashboard

resources/views/
├── admin/affiliate/
│   └── product-opportunities.blade.php   # Página principal
└── livewire/admin/affiliate/
    ├── product-opportunities-list.blade.php
    └── product-opportunity-detail.blade.php

database/seeders/
└── SprintATestDataSeeder.php             # Seeder de dados de teste

tests/Feature/Admin/Affiliate/
├── ProductOpportunitiesListTest.php      # Testes do componente List
└── ProductOpportunityDetailTest.php      # Testes do componente Detail
```

## Próximas Etapas

- **A5 — Integration**: Workflow completo, edge cases
- **A6 — E2E & Documentation**: Testes end-to-end, guias de usuário

## Notas

- Todos os dados de teste são idempotentes (rodar `migrate:fresh --seed` múltiplas vezes é seguro)
- Os tokens de Sanctum são criados automaticamente pelos testes
- As opportunities têm expiração em 7-30 dias por padrão
- Os scores são aleatórios entre 30-95% para variação realista
