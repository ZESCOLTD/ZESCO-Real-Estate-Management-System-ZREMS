<?php

namespace App\Livewire\AssetRegister;

use App\Models\AssetRegister;
use App\Models\AssetRegisterConfiguration;
use Livewire\Component;

class Edit extends Component
{
    public AssetRegister $asset;
    public array $form = [];
    public bool $canEdit = false;

    public function mount(AssetRegister $asset): void
    {
        $this->authorize('asset_register.view');
        $this->asset = $asset;
        $this->canEdit = auth()->user()->can('asset_register.update');

        $this->form = [
            'business_unit' => $asset->business_unit ?? '',
            'cost_centre' => $asset->cost_centre ?? '',
            'description' => $asset->description ?? '',
            'area' => $asset->area ?? '',
            'location' => $asset->location ?? '',
            'sub_location' => $asset->sub_location ?? '',
            'major_category' => $asset->major_category ?? '',
            'sub_major_category' => $asset->sub_major_category ?? '',
            'minor_category' => $asset->minor_category ?? '',
            'date_placed_in_service' => $asset->date_placed_in_service?->format('Y-m-d') ?? '',
            'life_in_months' => $asset->life_in_months ?? '',
            'asset_cost' => $asset->asset_cost ?? '',
            'status' => $asset->status ?? '',
            'reportline' => $asset->reportline ?? '',
        ];
    }

    public function save(): void
    {
        $this->authorize('asset_register.update');

        $validated = $this->validate([
            'form.business_unit' => ['nullable', 'string', 'max:255'],
            'form.cost_centre' => ['nullable', 'string', 'max:255'],
            'form.description' => ['nullable', 'string', 'max:5000'],
            'form.area' => ['nullable', 'string', 'max:255'],
            'form.location' => ['nullable', 'string', 'max:255'],
            'form.sub_location' => ['nullable', 'string', 'max:255'],
            'form.major_category' => ['nullable', 'string', 'max:255'],
            'form.sub_major_category' => ['nullable', 'string', 'max:255'],
            'form.minor_category' => ['nullable', 'string', 'max:255'],
            'form.date_placed_in_service' => ['nullable', 'date'],
            'form.life_in_months' => ['nullable', 'integer', 'min:0'],
            'form.asset_cost' => ['nullable', 'numeric', 'min:0'],
            'form.status' => ['nullable', 'string', 'max:255'],
            'form.reportline' => ['nullable', 'string', 'max:255'],
        ])['form'];

        $this->asset->fill([
            ...$validated,
            'date_placed_in_service' => $validated['date_placed_in_service'] ?: null,
            'life_in_months' => $validated['life_in_months'] === '' ? null : $validated['life_in_months'],
            'asset_cost' => $validated['asset_cost'] === '' ? null : $validated['asset_cost'],
        ])->save();

        session()->flash('success', 'Asset record updated.');
        $this->asset->refresh();
    }

    public function render()
    {
        $this->authorize('asset_register.view');

        return view('livewire.asset-register.edit', [
            'businessUnits' => AssetRegisterConfiguration::valuesFor('business_unit'),
            'costCentres' => AssetRegisterConfiguration::valuesFor('cost_centre'),
            'areas' => AssetRegisterConfiguration::valuesFor('area'),
            'locations' => AssetRegisterConfiguration::valuesFor('location'),
            'subLocations' => AssetRegisterConfiguration::valuesFor('sub_location'),
            'majorCategories' => AssetRegisterConfiguration::valuesFor('major_category'),
            'subMajorCategories' => AssetRegisterConfiguration::valuesFor('sub_major_category'),
            'minorCategories' => AssetRegisterConfiguration::valuesFor('minor_category'),
            'statuses' => AssetRegisterConfiguration::valuesFor('status'),
            'reportlines' => AssetRegisterConfiguration::valuesFor('reportline'),
        ])->layout('layouts.master', ['title' => $this->asset->asset_number]);
    }
}
