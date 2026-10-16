<?php

namespace App\Livewire;

use Livewire\Component;
class DashboardIndex extends Component
{
    public function render()
    {
        return view('welcome')->layout('layouts.master', [
            'title' => 'Dashboard',
        ]);
    }
}
