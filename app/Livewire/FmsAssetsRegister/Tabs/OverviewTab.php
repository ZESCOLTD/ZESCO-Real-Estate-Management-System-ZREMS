<?php

namespace App\Livewire\FmsAssetsRegister\Tabs;

use App\Models\FmsAssetRegister;
use Livewire\Component;

class OverviewTab extends Component
{
    public $assetId;
    public $asset;

    public function mount($assetId)
    {
        $this->assetId = $assetId;

        $this->asset = FmsAssetRegister::findOrFail($assetId);
    }

    public function render()
    {
        return view('livewire.fms-assets-register.tabs.overview-tab');
    }
}
