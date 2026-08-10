<?php

namespace App\Livewire\Admin\Affiliate;

use App\Domain\Affiliate\AffiliateLinkGenerator;
use App\Models\AffiliateLink;
use App\Models\AffiliateProduct;
use App\Models\Campaign;
use App\Models\ProductOpportunity;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class AffiliateLinkForm extends Component
{
    public ?AffiliateLink $link = null;

    public int $campaign_id = 0;

    public int $product_opportunity_id = 0;

    public int $affiliate_product_id = 0;

    public string $slug = '';

    public string $utm_source = '';

    public string $utm_medium = '';

    public string $utm_campaign = '';

    public string $status = AffiliateLink::STATUS_ACTIVE;

    public ?string $redirectUrl = null;

    protected $generator;

    public function mount(?AffiliateLink $link = null): void
    {
        $this->generator = app(AffiliateLinkGenerator::class);

        if ($link) {
            $this->link = $link;
            $this->campaign_id = $link->campaign_id;
            $this->affiliate_product_id = $link->affiliate_product_id;
            $this->slug = $link->slug;
            $this->utm_source = $link->utm_source ?? '';
            $this->utm_medium = $link->utm_medium ?? '';
            $this->utm_campaign = $link->utm_campaign ?? '';
            $this->status = $link->status;
            $this->updatePreview();
        }
    }

    public function updatedSlug(): void
    {
        $this->updatePreview();
    }

    public function updatedUtmSource(): void
    {
        $this->updatePreview();
    }

    public function updatedUtmMedium(): void
    {
        $this->updatePreview();
    }

    public function updatedUtmCampaign(): void
    {
        $this->updatePreview();
    }

    public function generateSlug(): void
    {
        if ($this->product_opportunity_id) {
            $opportunity = ProductOpportunity::find($this->product_opportunity_id);
            if ($opportunity) {
                $this->slug = $this->generator->generateSlug($opportunity->product_name);
                $this->updatePreview();
            }
        }
    }

    public function updatePreview(): void
    {
        if (!$this->affiliate_product_id) {
            $this->redirectUrl = null;
            return;
        }

        $product = AffiliateProduct::find($this->affiliate_product_id);
        if (!$product) {
            $this->redirectUrl = null;
            return;
        }

        $this->redirectUrl = $this->generator->buildRedirectUrl(
            $product->affiliate_url,
            $this->utm_source,
            $this->utm_medium,
            $this->utm_campaign
        );
    }

    public function save(): void
    {
        $this->validate([
            'campaign_id' => 'required|integer|exists:campaigns,id',
            'affiliate_product_id' => 'required|integer|exists:affiliate_products,id',
            'slug' => 'required|string|max:100|regex:/^[a-z0-9-]+$/',
            'utm_source' => 'nullable|string|max:100',
            'utm_medium' => 'nullable|string|max:100',
            'utm_campaign' => 'nullable|string|max:100',
            'status' => 'required|in:active,paused,archived',
        ], [
            'slug.regex' => 'O slug deve conter apenas letras minúsculas, números e hífens.',
        ]);

        // Check for slug uniqueness
        $existing = AffiliateLink::where('slug', $this->slug)
            ->when($this->link, fn($q) => $q->where('id', '!=', $this->link->id))
            ->exists();

        if ($existing) {
            $this->addError('slug', 'Este slug já está em uso.');
            return;
        }

        $data = [
            'campaign_id' => $this->campaign_id,
            'affiliate_product_id' => $this->affiliate_product_id,
            'slug' => $this->slug,
            'utm_source' => $this->utm_source ?: null,
            'utm_medium' => $this->utm_medium ?: null,
            'utm_campaign' => $this->utm_campaign ?: null,
            'status' => $this->status,
        ];

        if ($this->link) {
            $this->link->update($data);
            session()->flash('message', 'Link atualizado com sucesso.');
        } else {
            $product = AffiliateProduct::find($this->affiliate_product_id);
            $data['affiliate_url'] = $product->affiliate_url;
            AffiliateLink::create($data);
            session()->flash('message', 'Link criado com sucesso.');
            redirect()->route('admin.affiliate.links.index');
        }
    }

    public function render()
    {
        $campaigns = Campaign::orderBy('name')->get();
        $opportunities = ProductOpportunity::where('status', 'pending')
            ->orderByDesc('opportunity_score')
            ->get();

        $affiliateProducts = AffiliateProduct::orderBy('product_name')->get();

        return view('livewire.admin.affiliate.affiliate-link-form', [
            'campaigns' => $campaigns,
            'opportunities' => $opportunities,
            'affiliateProducts' => $affiliateProducts,
        ]);
    }
}
