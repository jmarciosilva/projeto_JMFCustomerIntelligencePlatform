<?php

namespace Tests\Feature\Admin\Affiliate;

use App\Domain\Affiliate\Enums\StatusSprintA;
use App\Livewire\Admin\Affiliate\ProductOpportunitiesList;
use App\Models\AffiliateProduct;
use App\Models\Application;
use App\Models\ProductOpportunity;
use App\Models\Tenant;
use App\Models\Trend;
use App\Models\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductOpportunitiesListTest extends TestCase
{
    private Tenant $tenant;

    private Application $application;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->application = Application::factory()->for($this->tenant)->create();
        $this->admin = User::factory()->create();
    }

    #[Test]
    public function renders_product_opportunities_list()
    {
        $this->actingAs($this->admin);

        Livewire::test(ProductOpportunitiesList::class)
            ->assertViewIs('livewire.admin.affiliate.product-opportunities-list');
    }

    #[Test]
    public function filters_by_status()
    {
        $trend = Trend::factory()->for($this->application)->create();
        $product1 = AffiliateProduct::factory()->for($this->application)->create();
        $product2 = AffiliateProduct::factory()->for($this->application)->create();

        ProductOpportunity::factory()
            ->for($this->application)
            ->for($trend)
            ->create([
                'affiliate_product_id' => $product1->id,
                'status_sprint_a' => StatusSprintA::DISCOVERED,
            ]);

        ProductOpportunity::factory()
            ->for($this->application)
            ->for($trend, 'trend')
            ->create([
                'affiliate_product_id' => $product2->id,
                'status_sprint_a' => StatusSprintA::APPROVED,
            ]);

        $this->actingAs($this->admin);

        Livewire::test(ProductOpportunitiesList::class)
            ->set('statusFilter', StatusSprintA::DISCOVERED->value)
            ->assertViewIs('livewire.admin.affiliate.product-opportunities-list');
    }

    #[Test]
    public function sorts_by_discovery_opportunity_score()
    {
        $trend = Trend::factory()->for($this->application)->create();
        $product1 = AffiliateProduct::factory()->for($this->application)->create();
        $product2 = AffiliateProduct::factory()->for($this->application)->create();

        ProductOpportunity::factory()
            ->for($this->application)
            ->for($trend)
            ->create(['affiliate_product_id' => $product1->id, 'discovery_opportunity_score' => 50]);

        ProductOpportunity::factory()
            ->for($this->application)
            ->for($trend, 'trend')
            ->create(['affiliate_product_id' => $product2->id, 'discovery_opportunity_score' => 80]);

        $this->actingAs($this->admin);

        Livewire::test(ProductOpportunitiesList::class)
            ->call('sort', 'discovery_opportunity_score')
            ->assertSet('sortBy', 'discovery_opportunity_score')
            ->assertSet('sortDirection', 'asc');
    }
}
