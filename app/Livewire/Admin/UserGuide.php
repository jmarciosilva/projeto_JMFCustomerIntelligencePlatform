<?php

namespace App\Livewire\Admin;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class UserGuide extends Component
{
    public function render(): View
    {
        return view('livewire.admin.user-guide');
    }
}
