<?php

namespace App\Livewire\AssetRegister;

use App\Models\AssetRegister;
use App\Models\AssetRegisterConfiguration;
use Livewire\Component;

class Configurations extends Component
{
    public string $fieldKey = 'major_category';
    public string $newValue = '';

    public function mount(): void
    {
        $this->authorize('configuration.manage');
    }

    public function updatedFieldKey(): void
    {
        $this->newValue = '';
    }

    public function addValue(): void
    {
        $this->authorize('configuration.manage');

        $this->validate([
            'fieldKey' => ['required', 'in:' . implode(',', array_keys(AssetRegisterConfiguration::FIELDS))],
            'newValue' => ['required', 'string', 'max:255'],
        ]);

        AssetRegisterConfiguration::firstOrCreate(
            ['field_key' => $this->fieldKey, 'value' => $this->newValue],
            [
                'sort_order' => ((int) AssetRegisterConfiguration::where('field_key', $this->fieldKey)->max('sort_order')) + 1,
                'is_active' => true,
            ]
        )->update(['is_active' => true]);

        $this->reset('newValue');
        session()->flash('success', 'Asset Register option saved.');
    }

    public function toggle(int $id): void
    {
        $this->authorize('configuration.manage');

        $option = AssetRegisterConfiguration::query()
            ->where('field_key', $this->fieldKey)
            ->findOrFail($id);
        $option->update(['is_active' => ! $option->is_active]);
    }

    public function render()
    {
        $this->authorize('configuration.manage');

        $configured = AssetRegisterConfiguration::query()
            ->where('field_key', $this->fieldKey)
            ->orderBy('sort_order')
            ->orderBy('value')
            ->get();
        $configuredValues = $configured->pluck('value')->all();
        $column = $this->fieldKey;
        $importedOnlyValues = AssetRegister::query()
            ->whereNotNull($column)
            ->where($column, '<>', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->reject(fn ($value) => in_array($value, $configuredValues, true))
            ->values();

        return view('livewire.asset-register.configurations', [
            'fields' => AssetRegisterConfiguration::FIELDS,
            'configured' => $configured,
            'importedOnlyValues' => $importedOnlyValues,
        ])->layout('layouts.master', ['title' => 'Asset Register Configuration']);
    }
}
