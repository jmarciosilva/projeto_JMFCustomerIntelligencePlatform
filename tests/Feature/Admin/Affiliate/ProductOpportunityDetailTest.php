<?php

namespace Tests\Feature\Admin\Affiliate;

use App\Domain\Affiliate\Enums\StatusSprintA;
use App\Livewire\Admin\Affiliate\ProductOpportunityDetail;
use App\Models\AffiliateProduct;
use App\Models\Application;
use App\Models\ProductOpportunity;
use App\Models\Tenant;
use App\Models\Trend;
use App\Models\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductOpportunityDetailTest extends TestCase
{
    private Tenant $tenant;

    private Application $application;

    private User $admin;

    private ProductOpportunity $opportunity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->application = Application::factory()->for($this->tenant)->create();
        $this->admin = User::factory()->create();

        $trend = Trend::factory()->for($this->application)->create();
        $product = AffiliateProduct::factory()->for($this->application)->create();
        $this->opportunity = ProductOpportunity::factory()
            ->for($this->application)
            ->for($trend)
            ->create([
                'affiliate_product_id' => $product->id,
                'status_sprint_a' => StatusSprintA::DISCOVERED,
            ]);
    }

    #[Test]
    public function renders_opportunity_detail()
    {
        $this->actingAs($this->admin);

        Livewire::test(ProductOpportunityDetail::class, ['opportunity' => $this->opportunity])
            ->assertViewIs('livewire.admin.affiliate.product-opportunity-detail')
            ->assertSee($this->opportunity->trend->term);
    }

    #[Test]
    public function opens_approve_modal()
    {
        $this->actingAs($this->admin);

        Livewire::test(ProductOpportunityDetail::class, ['opportunity' => $this->opportunity])
            ->call('openApproveModal')
            ->assertSet('action', 'approve')
            ->assertSet('showModal', true);
    }

    #[Test]
    public function opens_reject_modal()
    {
        $this->actingAs($this->admin);

        Livewire::test(ProductOpportunityDetail::class, ['opportunity' => $this->opportunity])
            ->call('openRejectModal')
            ->assertSet('action', 'reject')
            ->assertSet('showModal', true);
    }

    #[Test]
    public function closes_modal()
    {
        $this->actingAs($this->admin);

        Livewire::test(ProductOpportunityDetail::class, ['opportunity' => $this->opportunity])
            ->call('openApproveModal')
            ->call('closeModal')
            ->assertSet('showModal', false)
            ->assertSet('action', '')
            ->assertSet('reason', '');
    }

    #[Test]
    public function approves_opportunity()
    {
        $this->actingAs($this->admin);

        Livewire::test(ProductOpportunityDetail::class, ['opportunity' => $this->opportunity])
            ->set('reason', 'Great opportunity')
            ->call('approve');

        $this->opportunity->refresh();
        $this->assertEquals(StatusSprintA::APPROVED, $this->opportunity->status_sprint_a);
    }

    #[Test]
    public function rejects_opportunity()
    {
        $this->actingAs($this->admin);

        Livewire::test(ProductOpportunityDetail::class, ['opportunity' => $this->opportunity])
            ->set('reason', 'Not reliable brand')
            ->call('reject');

        $this->opportunity->refresh();
        $this->assertEquals(StatusSprintA::REJECTED, $this->opportunity->status_sprint_a);
    }

    #[Test]
    public function publishes_approved_opportunity()
    {
        $this->opportunity->update(['status_sprint_a' => StatusSprintA::APPROVED]);
        $this->actingAs($this->admin);

        Livewire::test(ProductOpportunityDetail::class, ['opportunity' => $this->opportunity])
            ->call('publish');

        $this->opportunity->refresh();
        $this->assertEquals(StatusSprintA::PUBLISHED, $this->opportunity->status_sprint_a);
    }

    #[Test]
    public function rejects_approve_if_not_discovered()
    {
        $this->opportunity->update(['status_sprint_a' => StatusSprintA::APPROVED]);
        $this->actingAs($this->admin);

        Livewire::test(ProductOpportunityDetail::class, ['opportunity' => $this->opportunity])
            ->call('approve')
            ->assertHasErrors('opportunity');
    }
}
