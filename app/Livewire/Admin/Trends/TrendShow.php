<?php

namespace App\Livewire\Admin\Trends;

use App\Application\Trends\Actions\CalculateTrendScoresAction;
use App\Application\Trends\Actions\CollectTrendSignalsAction;
use App\Application\Trends\Actions\RegisterManualTrendSnapshotAction;
use App\Models\Trend;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class TrendShow extends Component
{
    public Trend $trend;

    public int $period = 30;

    public string $mentions = '';

    public string $engagement = '';

    public string $velocity = '';

    public string $score = '';

    public string $notes = '';

    public function mount(Trend $trend): void
    {
        $this->authorize('view', $trend);

        $this->trend = $trend;
    }

    public function collectNow(CollectTrendSignalsAction $action): void
    {
        $this->authorize('update', $this->trend);

        $action->handle($this->trend);
    }

    public function recalculateScore(CalculateTrendScoresAction $action): void
    {
        $this->authorize('update', $this->trend);

        $action->handleOne($this->trend);

        $this->trend->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'mentions' => ['nullable', 'integer', 'min:0'],
            'engagement' => ['nullable', 'integer', 'min:0'],
            'velocity' => ['nullable', 'numeric'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function registerManualSnapshot(RegisterManualTrendSnapshotAction $action): void
    {
        $this->authorize('update', $this->trend);

        $validated = $this->validate();

        $action->handle($this->trend, [
            'mentions' => $validated['mentions'] !== '' ? $validated['mentions'] : null,
            'engagement' => $validated['engagement'] !== '' ? $validated['engagement'] : null,
            'velocity' => $validated['velocity'] !== '' ? $validated['velocity'] : null,
            'score' => $validated['score'] !== '' ? $validated['score'] : null,
            'notes' => $validated['notes'] ?: null,
        ]);

        $this->reset(['mentions', 'engagement', 'velocity', 'score', 'notes']);
    }

    public function render(): View
    {
        $snapshots = $this->trend->snapshots()
            ->since(now()->subDays($this->period))
            ->orderBy('collected_at')
            ->get();

        return view('livewire.admin.trends.trend-show', [
            'snapshots' => $snapshots,
            'chartData' => [
                'labels' => $snapshots->map(fn ($snapshot) => $snapshot->collected_at->format('d/m'))->values(),
                'mentions' => $snapshots->map(fn ($snapshot) => $snapshot->mentions)->values(),
            ],
        ]);
    }
}
