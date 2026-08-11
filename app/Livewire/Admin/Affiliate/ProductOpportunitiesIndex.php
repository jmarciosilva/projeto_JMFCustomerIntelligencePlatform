<?php

namespace App\Livewire\Admin\Affiliate;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class ProductOpportunitiesIndex extends Component
{
    public function render(): View
    {
        return view('livewire.admin.affiliate.product-opportunities-index');
    }
}
