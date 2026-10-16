<?php

namespace App\Livewire\FmsAssetsRegister;

use App\Models\FmsAssetRegister;
use Livewire\Component;

class Show extends Component
{
    public $assetId;
    public $asset;

    // 🔥 Derived / UI-friendly properties
    public $valuation;
    public $buildingsCount;
    public $documentsCount;
    public $totalPayments;

    public function mount($id)
    {
        $this->assetId = $id;

        $this->asset = FmsAssetRegister::findOrFail($id);

        // 🔥 Dummy / computed values (replace with real relations later)
        $this->hydrateComputedFields();
    }

    /**
     * Prepare computed UI data
     */
    private function hydrateComputedFields()
    {
        // 💰 You can replace these with real DB aggregations later

        $this->valuation = $this->estimateAssetValue();
        $this->buildingsCount = $this->estimateBuildings();
        $this->documentsCount = $this->estimateDocuments();
        $this->totalPayments = $this->estimatePayments();
    }

    /**
     * 🔹 Example: Estimate asset value (placeholder logic)
     */
    private function estimateAssetValue()
    {
        if ($this->asset->units && $this->asset->units > 0) {
            return $this->asset->units * 0; // fake valuation logic
        }

        return 0;// rand(500000, 5000000);
    }

    private function estimateBuildings()
    {
        return $this->asset->units;//rand(1, 5);
    }

    private function estimateDocuments()
    {
        return rand(2, 10);
    }

    private function estimatePayments()
    {
        return rand(10000, 500000);
    }

    /**
     * 🔥 Format helpers (for Blade)
     */
    public function getFormattedValueProperty()
    {
        return number_format($this->valuation, 2);
    }

    public function getFormattedPaymentsProperty()
    {
        return number_format($this->totalPayments, 2);
    }

    public function getFormattedUnitsProperty()
    {
        return $this->asset->units
            ? number_format($this->asset->units, 2)
            : 'N/A';
    }

    public function getServiceDateProperty()
    {
        return $this->asset->date_placed_in_service
           // ? \Carbon\Carbon::parse($this->asset->date_placed_in_service)->format('d M Y')
            ?? 'N/A';
    }

    public function render()
    {
        return view('livewire.fms-assets-register.show', [
            'asset' => $this->asset,
            'valuation' => $this->valuation,
            'buildingsCount' => $this->buildingsCount,
            'documentsCount' => $this->documentsCount,
            'totalPayments' => $this->totalPayments,
        ])->layout('layouts.master');
    }
}
