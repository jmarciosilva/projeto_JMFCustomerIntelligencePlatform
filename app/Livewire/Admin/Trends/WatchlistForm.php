<?php

namespace App\Livewire\Admin\Trends;

use App\Application\Trends\Actions\CreateWatchlistAction;
use App\Application\Trends\Actions\UpdateWatchlistAction;
use App\Models\Application;
use App\Models\Watchlist;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class WatchlistForm extends Component
{
    public ?Watchlist $watchlist = null;

    public ?int $applicationId = null;

    public string $name = '';

    public string $description = '';

    public string $keywords = '';

    public string $hashtags = '';

    public string $categories = '';

    public string $collectionFrequency = Watchlist::FREQUENCY_DAILY;

    public string $status = Watchlist::STATUS_ACTIVE;

    public function mount(?Watchlist $watchlist = null): void
    {
        $this->watchlist = $watchlist;

        $this->authorize($this->watchlist ? 'update' : 'create', $this->watchlist ?? Watchlist::class);

        if ($this->watchlist) {
            $this->applicationId = $this->watchlist->application_id;
            $this->name = $this->watchlist->name;
            $this->description = (string) $this->watchlist->description;
            $this->keywords = implode("\n", $this->watchlist->keywords ?? []);
            $this->hashtags = implode("\n", $this->watchlist->hashtags ?? []);
            $this->categories = implode("\n", $this->watchlist->categories ?? []);
            $this->collectionFrequency = $this->watchlist->collection_frequency;
            $this->status = $this->watchlist->status;
        } else {
            $this->applicationId = Application::query()->where('is_active', true)->orderBy('name')->value('id')
                ?? Application::query()->orderBy('name')->value('id');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'applicationId' => ['required', 'integer', 'exists:applications,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'keywords' => ['nullable', 'string'],
            'hashtags' => ['nullable', 'string'],
            'categories' => ['nullable', 'string'],
            'collectionFrequency' => ['required', 'in:'.Watchlist::FREQUENCY_DAILY.','.Watchlist::FREQUENCY_WEEKLY],
            'status' => ['required', 'in:'.Watchlist::STATUS_ACTIVE.','.Watchlist::STATUS_INACTIVE],
        ];
    }

    public function save(CreateWatchlistAction $createAction, UpdateWatchlistAction $updateAction): void
    {
        $validated = $this->validate();

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?: null,
            'keywords' => $this->splitLines($validated['keywords']),
            'hashtags' => $this->splitLines($validated['hashtags']),
            'categories' => $this->splitLines($validated['categories']),
            'collection_frequency' => $validated['collectionFrequency'],
            'status' => $validated['status'],
        ];

        if ($this->watchlist) {
            $updateAction->handle($this->watchlist, $data);
        } else {
            $application = Application::findOrFail($this->applicationId);
            $createAction->handle($application, $data);
        }

        $this->redirectRoute('admin.trends.watchlists.index', navigate: false);
    }

    public function render(): View
    {
        return view('livewire.admin.trends.watchlist-form', [
            'applications' => Application::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * @return list<string>
     */
    private function splitLines(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $items = preg_split('/[\r\n,]+/', $value) ?: [];

        return array_values(array_unique(array_filter(array_map('trim', $items), fn (string $item) => $item !== '')));
    }
}
