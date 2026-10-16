<?php

namespace App\Livewire\FmsAssetsRegister;

use App\Models\DocumentType;
use Livewire\Component;
use App\Models\FmsAssetRegister;

class Edit extends Component
{
    public $asset;

    public function mount($id)
    {
        $this->asset = FmsAssetRegister::findOrFail($id);

    }

    public function updateAsset()
    {
        $this->asset->save();

        $this->dispatch('close-modal');
        session()->flash('success', 'Asset updated');
    }

    public function render()
    {
        $category = strtolower($this->asset->major_category ?? '');

        $icon = match(true) {
            str_contains($category, 'land') => '🌍',
            str_contains($category, 'building') => '🏢',
            str_contains($category, 'vehicle') => '🚗',
            str_contains($category, 'equipment') => '🛠️',
            default => '📦',
        };

        return view('livewire.fms-assets-register.edit', [
            'icon' => $icon
        ])->layout('layouts.master');
    }
}
