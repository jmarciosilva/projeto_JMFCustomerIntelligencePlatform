<?php

namespace App\Livewire\Admin\Affiliate;

use App\Application\Affiliate\Actions\DeleteAffiliateProductAction;
use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class ProductIndex extends Component
{
    use WithPagination;

    public ?int $affiliateProgramId = null;

    public string $search = '';

    public function mount(): void
    {
        // Removed authorization check to allow all authenticated users
        // $this->authorize('viewAny', AffiliateProduct::class);

        $this->affiliateProgramId = request()->integer('program') ?: null;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedAffiliateProgramId(): void
    {
        $this->resetPage();
    }

    public function delete(AffiliateProduct $product, DeleteAffiliateProductAction $action): void
    {
        // Removed authorization check to allow all authenticated users
        // $this->authorize('delete', $product);

        $action->handle($product);
    }

    public function render(): View
    {
        $products = AffiliateProduct::query()
            ->when($this->affiliateProgramId, fn ($query) => $query->where('affiliate_program_id', $this->affiliateProgramId))
            ->when($this->search !== '', function ($query): void {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->with('affiliateProgram')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.admin.affiliate.product-index', [
            'products' => $products,
            'programs' => AffiliateProgram::query()->orderBy('name')->get(),
        ]);
    }
}
