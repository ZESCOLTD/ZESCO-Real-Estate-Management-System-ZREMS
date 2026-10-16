<?php

namespace App\Livewire\FmsAssetsRegister\Tabs;

use Livewire\Component;

class BuiltAssetsTab extends Component
{
    public $assets = [];

    public function mount($assetId)
    {
        $this->assets = \App\Models\FmsAssetRegister::where('link_id', $assetId)->get();
    }

    public function render()
    {
        return view('livewire.fms-assets-register.tabs.built-assets-tab')->layout('layouts.master');
    }
}

