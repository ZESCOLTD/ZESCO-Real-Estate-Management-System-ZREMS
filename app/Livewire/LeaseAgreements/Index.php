<?php

namespace App\Livewire\LeaseAgreements;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.lease-agreements.index')
            ->layout('layouts.master');
    }
}