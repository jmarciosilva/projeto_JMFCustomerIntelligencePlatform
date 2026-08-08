<?php

namespace App\Livewire\Admin\Marketing;

use App\Actions\GenerateEmailCampaignAction;
use App\Actions\GenerateProductContentAction;
use App\Actions\GenerateSocialContentAction;
use App\Models\Application;
use App\Models\MarketingContent;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class ContentDashboard extends Component
{
    public ?int $applicationId = null;

    public ?int $subjectId = null;

    public string $productName = '';

    public string $productCategory = '';

    public ?float $productPrice = null;

    public string $productDescription = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $editingContent = '';

    public function mount(): void
    {
        $this->applicationId = Application::query()->where('is_active', true)->orderBy('name')->value('id')
            ?? Application::query()->orderBy('name')->value('id');

        $this->syncSubjectId();
    }

    public function updatedApplicationId(): void
    {
        $this->subjectId = null;
        $this->syncSubjectId();
    }

    public function generate(
        GenerateProductContentAction $productAction,
        GenerateSocialContentAction $socialAction,
        GenerateEmailCampaignAction $emailAction,
    ): void {
        $this->validate([
            'productName' => ['required', 'string', 'max:255'],
            'productCategory' => ['required', 'string', 'max:255'],
            'productPrice' => ['nullable', 'numeric', 'min:0'],
            'productDescription' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! $this->applicationId) {
            return;
        }

        $newSubjectId = (int) (MarketingContent::where('application_id', $this->applicationId)->max('subject_id') ?? 0) + 1;

        $product = array_filter([
            'name' => $this->productName,
            'category' => $this->productCategory,
            'price' => $this->productPrice,
            'description' => $this->productDescription ?: null,
        ], fn ($value) => $value !== null);

        $productAction->execute($this->applicationId, 'product', $newSubjectId, $product);
        $socialAction->execute($this->applicationId, 'product', $newSubjectId, $product);
        $emailAction->execute($this->applicationId, 'product', $newSubjectId, $product);

        $this->reset(['productName', 'productCategory', 'productPrice', 'productDescription']);
        $this->showForm = false;
        $this->subjectId = $newSubjectId;
    }

    public function approve(int $contentId): void
    {
        $this->reviewContent($contentId, MarketingContent::STATUS_APPROVED);
    }

    public function reject(int $contentId): void
    {
        $this->reviewContent($contentId, MarketingContent::STATUS_REJECTED);
    }

    public function startEdit(int $contentId): void
    {
        $content = MarketingContent::find($contentId);

        if (! $content || $content->application_id !== $this->applicationId) {
            return;
        }

        $this->editingId = $contentId;
        $this->editingContent = $content->content;
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editingContent = '';
    }

    public function saveEdit(): void
    {
        $content = MarketingContent::find($this->editingId);

        if (! $content || $content->application_id !== $this->applicationId) {
            return;
        }

        $content->update([
            'content' => $this->editingContent,
            'status' => MarketingContent::STATUS_APPROVED,
            'reviewed_at' => now(),
        ]);

        $this->cancelEdit();
    }

    public function render(): View
    {
        $applications = Application::query()->orderBy('name')->get();
        $application = $applications->firstWhere('id', $this->applicationId);

        $subjectIds = $application
            ? MarketingContent::where('application_id', $application->id)->where('subject_type', 'product')->distinct()->orderByDesc('subject_id')->pluck('subject_id')
            : collect();

        $content = ($application && $this->subjectId)
            ? MarketingContent::where('application_id', $application->id)
                ->forSubject('product', $this->subjectId)
                ->orderBy('type')
                ->get()
            : collect();

        return view('livewire.admin.marketing.content-dashboard', [
            'applications' => $applications,
            'application' => $application,
            'subjectIds' => $subjectIds,
            'content' => $content,
            'activeDriver' => config('marketing.driver'),
        ]);
    }

    private function reviewContent(int $contentId, string $status): void
    {
        $content = MarketingContent::find($contentId);

        if (! $content || $content->application_id !== $this->applicationId) {
            return;
        }

        $content->update(['status' => $status, 'reviewed_at' => now()]);
    }

    private function syncSubjectId(): void
    {
        if ($this->subjectId || ! $this->applicationId) {
            return;
        }

        $this->subjectId = MarketingContent::where('application_id', $this->applicationId)
            ->where('subject_type', 'product')
            ->orderByDesc('subject_id')
            ->value('subject_id');
    }
}
