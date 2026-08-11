# Sprint B Testing Plan — 2026-08-12

**Data:** 2026-08-12  
**Sprint:** B (B1 + B2 + B3)  
**Status:** Ready for Testing  
**Testes Automáticos:** 105/105 ✅  
**Próximo:** Browser + Functional Testing

---

## 📋 OVERVIEW

Sprint B foi completamente implementado com 3 etapas:
- **B1:** Recurrency Infrastructure (database + domain layer)
- **B2:** Real-Time Calculation (RecurrencyCalculator service)
- **B3:** Performance Updates + UI (UpdatePerformanceScoreAction + admin interface)

**Objetivo Amanhã:** Validar que tudo funciona corretamente em contexto real (browser + conversões reais)

---

## 🧪 TESTES AUTOMÁTICOS (Status: ✅ PASSING)

```bash
# Rodar todos os testes
php artisan test tests/Feature/Integration/ tests/Unit/Domain/Affiliate/

# Resultado: 105/105 passing (342 assertions)
```

### Testes por Etapa:
- ✅ Sprint A Regression: 49 tests
- ✅ Sprint B1: 24 tests (PerformanceScoreCalculator + Integration)
- ✅ Sprint B2: 18 tests (RecurrencyCalculator + CreateAction)
- ✅ Sprint B3: 7 tests (UpdatePerformanceScoreAction)

---

## 🌐 BROWSER TESTING — MANUAL

### Setup Initial
```bash
# 1. Garantir que migrations rodaram
php artisan migrate:fresh --seed

# 2. Acessar a aplicação
URL: http://jmf-customer-intelligence.test/admin/affiliate/product-opportunities

# 3. Login
User: teste@afiliado.com
Pass: senha123
```

---

## ✅ TEST CASES — ADMIN INTERFACE

### TC-001: Verificar Renderização da Tabela
**Objetivo:** Validar que a tabela exibe todas as colunas corretamente

**Passos:**
1. Acessar `/admin/affiliate/product-opportunities`
2. Verificar que a tabela tem as seguintes colunas:
   - ✓ Confiança
   - ✓ Tendência
   - ✓ Produto
   - ✓ Recurrency
   - ✓ Oportunidade
   - ✓ Intenção
   - ✓ Status
   - ✓ Ações

**Resultado Esperado:**
- Todas as 8 colunas visíveis
- Header com background slate-900
- Borders slate-700

---

### TC-002: Verificar HIGH Confidence Badge
**Objetivo:** Validar que opportunities com HIGH confidence exibem ⭐ badge corretamente

**Passos:**
1. Na tabela, procurar por uma opportunity com "⭐ HIGH" na coluna Confiança
2. Verificar visual do badge:
   - Texto: "⭐ HIGH"
   - Cor de fundo: amber-400 (semi-transparente)
   - Texto: amber-400

**Resultado Esperado:**
- Badge visível com ⭐ ícone
- Cor amber-400 consistente
- Posição na coluna Confiança

---

### TC-003: Verificar Recurrency Rate Display
**Objetivo:** Validar que recurrency_rate é exibido como percentual

**Passos:**
1. Na coluna Recurrency, verificar opportunities com dados:
   - Deve mostrar formato "X.XX%" (ex: "8.89%")
   - Cor: green-400
2. Verificar opportunities sem recurrency:
   - Deve mostrar "—" (travessão)
   - Cor: slate-500

**Resultado Esperado:**
- Valores com 2 casas decimais
- Green-400 para valores positivos
- Travessão para null

---

### TC-004: Testar Filtro de Confiança
**Objetivo:** Validar que o filtro por confidence_level funciona corretamente

**Passos:**
1. Clicar no dropdown "Confiança"
2. Selecionar "⭐ HIGH"
3. Verificar que a tabela mostra APENAS opportunities com confidence_level = 'HIGH'
4. Testar outras opções:
   - "Todos os níveis" → mostra tudo
   - "MEDIUM" → apenas MEDIUM
   - "LOW" → apenas LOW
   - "Sem dados" → apenas INSUFFICIENT_DATA

**Resultado Esperado:**
- Filtro aplica corretamente
- URL muda para incluir `confidenceFilter=HIGH`
- Contagem de linhas reduz conforme esperado

---

### TC-005: Testar Sorting por Confiança
**Objetivo:** Validar que sort por "Confiança" ordena HIGH → MEDIUM → LOW → INSUFFICIENT_DATA

**Passos:**
1. Clicar no header "Confiança" para ordenar
2. Verificar que primeira linha é ⭐ HIGH (se existir)
3. Verificar ordem: HIGH, MEDIUM, LOW, INSUFFICIENT_DATA
4. Clicar novamente para reverter ordem (descendente)

**Resultado Esperado:**
- HIGH opportunities aparecem primeiro
- Ordem respeitada
- Arrow indicator (↓/↑) muda no header

---

### TC-006: Verificar Border Highlight para HIGH
**Objetivo:** Validar que HIGH confidence opportunities têm border highlight

**Passos:**
1. Procurar por uma linha com ⭐ HIGH
2. Verificar que a linha tem:
   - Border LEFT de 4px na cor amber-400
   - Fundo: slate-800/50 on hover

**Resultado Esperado:**
- Border amber-400 visível na esquerda
- Distinct visual indication para HIGH

---

### TC-007: Testar Busca (Regressão Sprint A)
**Objetivo:** Validar que busca por tendência/produto ainda funciona

**Passos:**
1. Na search box, digitar um termo (ex: "iPhone" ou uma tendência)
2. Verificar que tabela filtra resultados
3. Limpar busca

**Resultado Esperado:**
- Busca funciona
- Sem breaking changes

---

### TC-008: Testar Filtro de Status (Regressão Sprint A)
**Objetivo:** Validar que filtro de status ainda funciona

**Passos:**
1. Clicar no dropdown "Status"
2. Selecionar um status (ex: "DISCOVERED")
3. Verificar que tabela mostra apenas esse status

**Resultado Esperado:**
- Filtro status funciona
- Sem breaking changes

---

### TC-009: Testar Sorting Secundário (Created At)
**Objetivo:** Validar que dentro de um confidence level, as oportunidades são ordenadas por data

**Passos:**
1. Filtrar por "⭐ HIGH"
2. Verificar que as oportunidades mais recentes aparecem primeiro

**Resultado Esperado:**
- Secondary sort por created_at desc funciona

---

### TC-010: Verificar Paginação
**Objetivo:** Validar que paginação (15 por página) funciona corretamente

**Passos:**
1. Se tiver mais de 15 opportunities, verificar que aparece pagination
2. Navegar para próxima página
3. Verificar que dados carregam corretamente

**Resultado Esperado:**
- Pagination funciona
- 15 oportunidades por página
- Navegação smooth

---

## 🎨 STYLING TESTS

### TC-S01: Dark Theme Consistency
**Objetivo:** Verificar que dark theme é mantido

**Verificações:**
- Background: slate-900 (main), slate-800 (accents)
- Text: slate-100 (primary), slate-400 (secondary), slate-500 (labels)
- Borders: slate-700, slate-800
- Accents: amber-400 (HIGH), green-400 (recurrency)

---

### TC-S02: Focus States
**Objetivo:** Verificar que focus rings estão corretos

**Passos:**
1. Tab/click em filtros
2. Verificar que focus ring é amber-400

**Resultado Esperado:**
- Focus ring visível
- Cor: amber-400

---

### TC-S03: Hover States
**Objetivo:** Verificar que hover feedback funciona

**Passos:**
1. Hover sobre linhas da tabela
2. Verificar que background muda para slate-800/50

**Resultado Esperado:**
- Hover effect visível
- Smooth transition

---

## 🔄 FUNCTIONAL TESTS

### TC-F01: Opportunity Creation com Contact
**Objetivo:** Validar que opportunity criada com contact_id calcula recurrency automaticamente

**Passos (via API ou teste manual):**
1. Criar ProductOpportunity com `contactId = 12345`
2. Criar 5 AffiliateConversions para esse contact nos últimos 90 dias
3. Verificar que opportunity tem:
   - `recurrency_rate` calculado (ex: 5.56%)
   - `confidence_level` = 'HIGH' (ou LOW/MEDIUM dependendo de outros fatores)

**Resultado Esperado:**
- Recurrency calculado automaticamente
- Confidence level determinado corretamente

---

### TC-F02: Opportunity Creation sem Contact
**Objetivo:** Validar que opportunity criada sem contact_id tem recurrency null

**Passos:**
1. Criar ProductOpportunity com `contactId = null`
2. Verificar que:
   - `recurrency_rate` = null
   - `confidence_level` = 'MEDIUM' (sem recurrency)

**Resultado Esperado:**
- Recurrency null quando sem contact
- Confidence degradado para MEDIUM

---

### TC-F03: Update Performance Score on Conversion
**Objetivo:** Validar que performance score é recalculado quando conversão é importada

**Passos:**
1. Criar ProductOpportunity com contact_id = 999
2. Criar histórico de conversões (5+ no último ano)
3. Criar nova AffiliateConversion e chamar UpdatePerformanceScoreAction
4. Verificar que:
   - `actual_performance_score` foi atualizado
   - `performance_score_breakdown` recalculado
   - `confidence_level` atualizado

**Resultado Esperado:**
- Score recalculado corretamente
- Dados persistidos no banco

---

### TC-F04: Multi-Tenant Isolation
**Objetivo:** Validar que dados estão isolados por tenant

**Passos (se tiver acesso a múltiplos tenants):**
1. Logar como user de Tenant A
2. Verificar que vê apenas oportunidades de Tenant A
3. Logar como user de Tenant B
4. Verificar que vê apenas oportunidades de Tenant B

**Resultado Esperado:**
- Isolamento mantido
- Sem data leakage entre tenants

---

## 📊 PERFORMANCE TESTS

### TC-P01: Query Performance
**Objetivo:** Validar que queries são otimizadas

**Verificações:**
1. Abrir Developer Tools (F12)
2. Verificar Network tab — queries devem ser < 200ms
3. Verificar que não há N+1 queries (use Laravel Debugbar se disponível)

**Resultado Esperado:**
- Page load < 1 segundo
- Sem N+1 queries

---

### TC-P02: Large Dataset
**Objetivo:** Validar performance com muitos dados

**Passos (se possível):**
1. Criar 1000+ opportunities
2. Filtrar por HIGH confidence
3. Verificar que resposta é rápida

**Resultado Esperado:**
- Index em (app_id, confidence_level) funciona
- Paginação é eficiente

---

## 🐛 REGRESSION TESTS

### TC-R01: Sprint A Features
**Objetivo:** Validar que Sprint A não foi quebrado

**Verificações:**
- [ ] Tabela renderiza corretamente
- [ ] Filtro de status funciona
- [ ] Busca funciona
- [ ] Sorting por Tendência/Oportunidade funciona
- [ ] Botão "Curar" funciona
- [ ] Componente de detalhe abre corretamente

---

### TC-R02: Dark Theme Consistency
**Objetivo:** Validar que tema escuro foi mantido

**Verificações:**
- [ ] Cores slate-900/slate-800 consistent
- [ ] Não há elementos light mode visíveis
- [ ] Accents (amber-400) aplicados corretamente

---

## 🚀 EDGE CASES

### TC-E01: Opportunity sem Recurrency
**Objetivo:** Validar behavior quando recurrency_rate é null

**Esperado:**
- Coluna Recurrency mostra "—"
- Confidence level = MEDIUM (sem recurrency)
- Não quebra a página

---

### TC-E02: Opportunity sem Confidence Level
**Objetivo:** Validar quando confidence_level ainda é null (dados históricos)

**Esperado:**
- Coluna Confiança mostra "—"
- Filtro "Sem dados" funciona

---

### TC-E03: Muito Alto Recurrency
**Objetivo:** Validar quando recurrency_rate > 100 (capped)

**Esperado:**
- Exibe 100.00%
- Não quebra layout

---

## 📝 CHECKLIST FINAL

### Antes de Testar
- [ ] Migrations foram rodadas (`php artisan migrate`)
- [ ] Seeder foi executado (`php artisan migrate:fresh --seed`)
- [ ] Servidor Laravel está rodando (`php artisan serve`)
- [ ] Browser acessando: `http://jmf-customer-intelligence.test/`
- [ ] Logged in como: `teste@afiliado.com`

### Testes Críticos (MUST PASS)
- [ ] TC-001: Todas as colunas renderizam
- [ ] TC-002: HIGH badge exibe corretamente
- [ ] TC-003: Recurrency rate exibe corretamente
- [ ] TC-004: Filtro de confiança funciona
- [ ] TC-005: Sort por confiança funciona
- [ ] TC-R01: Sprint A features não quebradas

### Testes Nice-to-Have
- [ ] TC-006: Border highlight
- [ ] TC-007: Busca funciona
- [ ] TC-008: Filtro status funciona
- [ ] TC-P01: Performance aceitável

### Go-Live Criteria
- ✅ 105/105 testes automáticos passando
- ✅ Todas as colunas renderizam
- ✅ Filtro de confiança funciona
- ✅ Sort por confiança funciona
- ✅ Recurrency rate exibido
- ✅ HIGH badge visível
- ✅ Sprint A não quebrado
- ✅ Dark theme mantido

---

## 🔗 RECURSOS

### URLs Úteis
- Admin UI: `/admin/affiliate/product-opportunities`
- Database: `jmf_ci_testing` (test DB)
- Code: `/var/www/jmf-ci-dev`

### Comandos Úteis
```bash
# Rodar testes
php artisan test tests/Feature/Integration/ tests/Unit/Domain/Affiliate/

# Rodar migrations
php artisan migrate:fresh --seed

# Limpar cache
php artisan cache:clear

# Ver logs
tail -f storage/logs/laravel.log
```

### Arquivos Principais
- Blade Template: `resources/views/livewire/admin/affiliate/product-opportunities-list.blade.php`
- Livewire Component: `app/Livewire/Admin/Affiliate/ProductOpportunitiesList.php`
- UpdatePerformanceScoreAction: `app/Domain/Affiliate/Actions/UpdatePerformanceScoreAction.php`

---

## 📞 NOTAS

- **Problemas de styling?** Check tailwind.config.js e rebuild CSS se necessário
- **Dados não aparecem?** Verify application_id em auth()->user()
- **Queries lentas?** Check database indices criados em migrations
- **Filtros não funcionam?** Verify wire:model.live bindings em Livewire

---

## ✅ STATUS

**Automáticos:** 105/105 ✅  
**Prontos para Browser Testing:** ✅  
**Última Atualização:** 2026-08-11  
**Próximo:** Browser + Functional Testing (2026-08-12)

---

**Boa sorte com os testes! 🚀**
