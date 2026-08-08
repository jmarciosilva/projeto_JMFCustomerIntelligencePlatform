<?php

use App\Livewire\Admin\Intelligence\BusinessIntelligenceDashboard;
use App\Livewire\Admin\Intelligence\RecommendationsDashboard;
use App\Livewire\Admin\Marketing\ContentDashboard;
use App\Models\Application;
use App\Models\BusinessRecommendation;
use App\Models\Contact;
use App\Models\MarketingContent;
use App\Models\Opportunity;
use App\Models\ProductTrend;
use App\Models\SalesForecast;
use App\Models\Tenant;
use Livewire\Livewire;

test('super admin visualiza o dashboard de business intelligence vazio', function () {
    $admin = superAdmin();
    Application::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.intelligence.dashboard'))
        ->assertOk()
        ->assertSeeLivewire(BusinessIntelligenceDashboard::class)
        ->assertSee('Segmentação de clientes');
});

test('dashboard de business intelligence exibe segmentos, trends, forecast e oportunidades', function () {
    $admin = superAdmin();
    $tenant = Tenant::factory()->create();
    $application = Application::factory()->for($tenant)->create();

    Contact::factory()->create(['tenant_id' => $tenant->id, 'segment' => 'vip', 'customer_score' => 90]);
    Contact::factory()->create(['tenant_id' => $tenant->id, 'segment' => 'inactive', 'customer_score' => 10]);

    ProductTrend::create([
        'application_id' => $application->id, 'product_id' => 42, 'direction' => 'rising',
        'growth_rate' => 55.5, 'computed_at' => now(),
    ]);

    SalesForecast::create([
        'application_id' => $application->id, 'seller_id' => null, 'forecast_date' => now()->toDateString(),
        'horizon_days' => 7, 'predicted_revenue' => 1234.56, 'predicted_purchases' => 10,
        'confidence' => 'high', 'method' => 'moving_average', 'computed_at' => now(),
    ]);

    Opportunity::create([
        'application_id' => $application->id, 'type' => 'cross_sell', 'product_id' => 1, 'related_product_id' => 2,
        'score' => 80, 'reason' => 'Produtos vistos juntos', 'detected_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.intelligence.dashboard').'?applicationId='.$application->id)
        ->assertOk();

    Livewire::test(BusinessIntelligenceDashboard::class)
        ->set('applicationId', $application->id)
        ->assertSee('Produto #42')
        ->assertSee('1.234,56')
        ->assertSee('Produtos vistos juntos');
});

test('super admin visualiza o dashboard de recomendações ia', function () {
    $admin = superAdmin();
    Application::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.intelligence.recommendations'))
        ->assertOk()
        ->assertSeeLivewire(RecommendationsDashboard::class);
});

test('dashboard de recomendações exibe recomendações do vendedor selecionado', function () {
    $admin = superAdmin();
    $application = Application::factory()->create();

    BusinessRecommendation::create([
        'application_id' => $application->id, 'seller_id' => 5, 'type' => 'sales_drop',
        'priority' => 85, 'title' => 'Queda de vendas no produto #10', 'message' => 'Mensagem de teste',
        'generated_at' => now(),
    ]);

    Livewire::test(RecommendationsDashboard::class)
        ->set('applicationId', $application->id)
        ->set('sellerId', 5)
        ->assertSee('Queda de vendas no produto #10')
        ->assertSee('Mensagem de teste');
});

test('super admin visualiza o dashboard de ai marketing', function () {
    $admin = superAdmin();
    Application::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.marketing.dashboard'))
        ->assertOk()
        ->assertSeeLivewire(ContentDashboard::class);
});

test('dashboard de marketing gera conteúdo completo via formulário', function () {
    $admin = superAdmin();
    $application = Application::factory()->create();

    $this->actingAs($admin);

    Livewire::test(ContentDashboard::class)
        ->set('applicationId', $application->id)
        ->set('showForm', true)
        ->set('productName', 'Vaso de Cerâmica')
        ->set('productCategory', 'Artesanato')
        ->set('productPrice', 89.90)
        ->call('generate')
        ->assertHasNoErrors();

    expect(MarketingContent::where('application_id', $application->id)->count())->toBe(7);
});

test('dashboard de marketing aprova conteúdo gerado', function () {
    $admin = superAdmin();
    $application = Application::factory()->create();

    $content = MarketingContent::create([
        'application_id' => $application->id, 'subject_type' => 'product', 'subject_id' => 1,
        'type' => 'title', 'content' => 'Título gerado', 'status' => 'draft',
        'generator' => 'template', 'generated_at' => now(),
    ]);

    $this->actingAs($admin);

    Livewire::test(ContentDashboard::class)
        ->set('applicationId', $application->id)
        ->set('subjectId', 1)
        ->call('approve', $content->id);

    expect($content->fresh()->status)->toBe('approved');
});
