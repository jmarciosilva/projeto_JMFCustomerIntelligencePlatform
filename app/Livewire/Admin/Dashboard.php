<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    public function render(): View
    {
        return view('livewire.admin.dashboard', [
            'totalAdmins' => User::query()->count(),
            'activeAdmins' => User::query()->where('is_active', true)->count(),
            'recentAuditLogs' => AuditLog::query()->with('user')->latest()->limit(5)->get(),
        ]);
    }
}
