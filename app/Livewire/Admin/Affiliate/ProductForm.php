<?php

namespace App\Livewire\Admin\Affiliate;

use App\Application\Affiliate\Actions\CreateAffiliateProductAction;
use App\Application\Affiliate\Actions\UpdateAffiliateProductAction;
use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class ProductForm extends Component
{
    public ?AffiliateProduct $product = null;

    public ?int $affiliateProgramId = null;

    public string $externalProductId = '';

    public string $name = '';

    public string $description = '';

    public string $category = '';

    public string $brand = '';

    public string $price = '';

    public string $originalPrice = '';

    public string $commissionPercentage = '';

    public string $affiliateUrl = '';

    public string $imageUrl = '';

    public string $availability = AffiliateProduct::AVAILABILITY_UNKNOWN;

    public function mount(?AffiliateProduct $product = null): void
    {
        $this->product = $product;

        // $this->authorize($this->product ? 'update' : 'create', $this->product ?? AffiliateProduct::class);

        if ($this->product) {
            $this->affiliateProgramId = $this->product->affiliate_program_id;
            $this->externalProductId = (string) $this->product->external_product_id;
            $this->name = $this->product->name;
            $this->description = (string) $this->product->description;
            $this->category = (string) $this->product->category;
            $this->brand = (string) $this->product->brand;
            $this->price = (string) $this->product->price;
            $this->originalPrice = (string) $this->product->original_price;
            $this->commissionPercentage = (string) $this->product->commission_percentage;
            $this->affiliateUrl = $this->product->affiliate_url;
            $this->imageUrl = (string) $this->product->image_url;
            $this->availability = $this->product->availability;
        } else {
            $this->affiliateProgramId = request()->integer('program') ?: null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'affiliateProgramId' => ['required', 'integer', 'exists:affiliate_programs,id'],
            'externalProductId' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'originalPrice' => ['nullable', 'numeric', 'min:0'],
            'commissionPercentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'affiliateUrl' => ['required', 'url', 'max:2048'],
            'imageUrl' => ['nullable', 'url', 'max:2048'],
            'availability' => ['required', 'in:'.implode(',', [
                AffiliateProduct::AVAILABILITY_IN_STOCK,
                AffiliateProduct::AVAILABILITY_OUT_OF_STOCK,
                AffiliateProduct::AVAILABILITY_UNKNOWN,
            ])],
        ];
    }

    public function save(CreateAffiliateProductAction $createAction, UpdateAffiliateProductAction $updateAction): void
    {
        $validated = $this->validate();

        $data = [
            'external_product_id' => $validated['externalProductId'] ?: null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?: null,
            'category' => $validated['category'] ?: null,
            'brand' => $validated['brand'] ?: null,
            'price' => $validated['price'] !== '' ? $validated['price'] : null,
            'original_price' => $validated['originalPrice'] !== '' ? $validated['originalPrice'] : null,
            'commission_percentage' => $validated['commissionPercentage'] !== '' ? $validated['commissionPercentage'] : null,
            'affiliate_url' => $validated['affiliateUrl'],
            'image_url' => $validated['imageUrl'] ?: null,
            'availability' => $validated['availability'],
        ];

        if ($this->product) {
            $updateAction->handle($this->product, $data);
        } else {
            $program = AffiliateProgram::findOrFail($this->affiliateProgramId);
            $createAction->handle($program, $data);
        }

        $this->redirectRoute('admin.affiliate.products.index', navigate: false);
    }

    public function render(): View
    {
        return view('livewire.admin.affiliate.product-form', [
            'programs' => AffiliateProgram::query()->orderBy('name')->get(),
        ]);
    }
}
