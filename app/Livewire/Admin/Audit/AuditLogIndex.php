<?php

namespace App\Livewire\Admin\Audit;

use App\Domain\Shared\Enums\Permission;
use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class AuditLogIndex extends Component
{
    use WithPagination;

    public function mount(): void
    {
        $this->authorize(Permission::AuditView->value);
    }

    public function render(): View
    {
        $logs = AuditLog::query()
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('livewire.admin.audit.audit-log-index', [
            'logs' => $logs,
        ]);
    }
}
