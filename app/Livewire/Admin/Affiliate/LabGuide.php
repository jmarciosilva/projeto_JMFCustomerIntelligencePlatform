<?php

namespace App\Livewire\Admin\Affiliate;

use Livewire\Component;

class LabGuide extends Component
{
    public function render()
    {
        return view('livewire.admin.affiliate.lab-guide')->layout('layouts.admin', ['header' => '📚 Guia do Laboratório de Afiliados']);
    }
}
