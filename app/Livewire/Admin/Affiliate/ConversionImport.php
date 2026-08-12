<?php

namespace App\Livewire\Admin\Affiliate;

use App\Domain\Affiliate\ImportAffiliateConversionsFromCsvAction;
use App\Models\AffiliateProgram;
use App\Models\Application;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class ConversionImport extends Component
{
    use WithFileUploads;

    public $csv_file;

    public int $program_id = 0;

    public array $result = [];

    protected ImportAffiliateConversionsFromCsvAction $action;

    public function mount(): void
    {
        $this->action = app(ImportAffiliateConversionsFromCsvAction::class);
    }

    public function import(): void
    {
        $this->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
            'program_id' => 'required|integer|exists:affiliate_programs,id',
        ]);

        $app = auth()->user()->application ?? Application::first();
        $program = AffiliateProgram::findOrFail($this->program_id);

        $path = $this->csv_file->store('imports', 'local');

        try {
            $this->result = $this->action->execute(
                storage_path("app/{$path}"),
                $app,
                $program
            );
            session()->flash('message', "Import realizado: {$this->result['successful']} sucesso, {$this->result['failed']} falhas");
        } catch (\Exception $e) {
            session()->flash('error', 'Erro no import: '.$e->getMessage());
        }
    }

    public function render()
    {
        $programs = AffiliateProgram::orderBy('name')->get();

        return view('livewire.admin.affiliate.conversion-import', [
            'programs' => $programs,
        ]);
    }
}
