<?php

namespace App\Livewire\Admin\Affiliate;

use App\Application\Affiliate\Actions\ImportAffiliateProductsFromCsvAction;
use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class ProductImport extends Component
{
    use WithFileUploads;

    public ?int $affiliateProgramId = null;

    /** @var TemporaryUploadedFile|null */
    public $file = null;

    /**
     * @var array{processed: int, failed: int, errors: list<string>}|null
     */
    public ?array $summary = null;

    public function mount(): void
    {
        // $this->authorize('create', AffiliateProduct::class);

        $this->affiliateProgramId = AffiliateProgram::query()->orderBy('name')->value('id');
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'affiliateProgramId' => ['required', 'integer', 'exists:affiliate_programs,id'],
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ];
    }

    public function import(ImportAffiliateProductsFromCsvAction $action): void
    {
        $this->validate();

        $program = AffiliateProgram::findOrFail($this->affiliateProgramId);

        $this->summary = $action->handle($program, $this->file->getRealPath());

        $this->reset('file');
    }

    public function render(): View
    {
        return view('livewire.admin.affiliate.product-import', [
            'programs' => AffiliateProgram::query()->orderBy('name')->get(),
        ]);
    }
}
