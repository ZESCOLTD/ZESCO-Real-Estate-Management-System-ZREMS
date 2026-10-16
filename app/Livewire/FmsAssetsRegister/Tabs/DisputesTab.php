<?php

namespace App\Livewire\FmsAssetsRegister\Tabs;

use Livewire\Component;
use App\Models\ParcelDispute;

class DisputesTab extends Component
{
    public $asset;
    public $disputes = [];

    // form fields
    public $dispute_type, $description, $status = 'open', $reported_at;

    public function mount($asset)
    {
        $this->parcelId = $asset;
        $this->loadDisputes();
    }

    public function loadDisputes()
    {
        $this->disputes = ParcelDispute::where('land_parcel_id', $this->parcelId)
            ->latest()
            ->get();
    }

    public function saveDispute()
    {
        $this->validate([
            'description' => 'required',
        ]);

        ParcelDispute::create([
            'land_parcel_id' => $this->parcelId,
            'dispute_type' => $this->dispute_type,
            'description' => $this->description,
            'status' => $this->status,
            'reported_at' => $this->reported_at ?? now(),
        ]);

        $this->reset(['dispute_type', 'description', 'status', 'reported_at']);

        $this->loadDisputes();

        $this->dispatch('close-modal');
    }

    public function render()
    {
        return view('livewire.fms-assets-register.tabs.disputes-tab')->layout('layouts.master');
    }
}
