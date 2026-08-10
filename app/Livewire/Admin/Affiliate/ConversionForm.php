<?php

namespace App\Livewire\Admin\Affiliate;

use App\Domain\Affiliate\RegisterAffiliateConversionAction;
use App\Models\AffiliateConversion;
use App\Models\AffiliateProgram;
use App\Models\Application;
use App\Models\Campaign;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class ConversionForm extends Component
{
    public ?AffiliateConversion $conversion = null;

    public int $affiliate_program_id = 0;

    public int $affiliate_product_id = 0;

    public int $campaign_id = 0;

    public string $order_reference = '';

    public string $order_date = '';

    public float $product_price = 0;

    public float $commission_rate = 0;

    public float $commission_value = 0;

    public string $status = AffiliateConversion::STATUS_PENDING;

    public string $notes = '';

    protected RegisterAffiliateConversionAction $action;

    public function mount(?AffiliateConversion $conversion = null): void
    {
        $this->action = app(RegisterAffiliateConversionAction::class);

        if ($conversion) {
            $this->conversion = $conversion;
            $this->affiliate_program_id = $conversion->affiliate_program_id;
            $this->affiliate_product_id = $conversion->affiliate_product_id;
            $this->campaign_id = $conversion->campaign_id ?? 0;
            $this->order_reference = $conversion->order_reference;
            $this->order_date = $conversion->order_date->toDateString();
            $this->product_price = $conversion->product_price;
            $this->commission_rate = $conversion->commission_rate;
            $this->commission_value = $conversion->commission_value;
            $this->status = $conversion->status;
            $this->notes = $conversion->notes ?? '';
        } else {
            $this->order_date = now()->toDateString();
        }
    }

    public function save(): void
    {
        $this->validate([
            'affiliate_program_id' => 'required|integer|exists:affiliate_programs,id',
            'affiliate_product_id' => 'required|integer|exists:affiliate_products,id',
            'order_reference' => 'required|string|max:100|unique:affiliate_conversions,order_reference,' . ($this->conversion?->id ?? 'NULL'),
            'order_date' => 'required|date',
            'product_price' => 'required|numeric|min:0',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'commission_value' => 'required|numeric|min:0',
            'status' => 'required|in:pending,approved,paid,cancelled',
            'notes' => 'nullable|string',
        ]);

        $app = auth()->user()->application ?? Application::first();

        if ($this->conversion) {
            $this->conversion->update([
                'product_price' => $this->product_price,
                'commission_rate' => $this->commission_rate,
                'commission_value' => $this->commission_value,
                'status' => $this->status,
                'notes' => $this->notes,
            ]);
            session()->flash('message', 'Conversão atualizada com sucesso.');
        } else {
            $this->action->execute([
                'application_id' => $app->id,
                'affiliate_program_id' => $this->affiliate_program_id,
                'affiliate_product_id' => $this->affiliate_product_id,
                'campaign_id' => $this->campaign_id ?: null,
                'order_reference' => $this->order_reference,
                'order_date' => $this->order_date,
                'product_price' => $this->product_price,
                'commission_rate' => $this->commission_rate,
                'commission_value' => $this->commission_value,
                'status' => $this->status,
                'notes' => $this->notes,
            ]);
            session()->flash('message', 'Conversão registrada com sucesso.');
            redirect()->route('admin.affiliate.conversions.index');
        }
    }

    public function render()
    {
        $programs = AffiliateProgram::orderBy('name')->get();
        $products = [];
        if ($this->affiliate_program_id) {
            $products = AffiliateProgram::find($this->affiliate_program_id)
                ?->affiliateProducts()
                ->orderBy('name')
                ->get() ?? [];
        }
        $campaigns = Campaign::orderBy('name')->get();

        return view('livewire.admin.affiliate.conversion-form', [
            'programs' => $programs,
            'products' => $products,
            'campaigns' => $campaigns,
        ]);
    }
}
