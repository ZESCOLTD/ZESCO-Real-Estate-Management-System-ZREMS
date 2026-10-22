<?php

namespace App\Livewire\Configurations\LeaseStatuses;

use App\Models\LeaseStatus;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Illuminate\Support\Facades\Gate;

class Index extends Component
{
    public ?int $editingId = null;
    public string $name = '';
    public bool $isActive = true;

    public function edit(int $id): void
    {
        Gate::authorize('configuration.manage');
        $status = LeaseStatus::findOrFail($id);

        $this->editingId = $status->id;
        $this->name = $status->name;
        $this->isActive = $status->is_active;
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->isActive = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        Gate::authorize('configuration.manage');
        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lease_statuses', 'name')->ignore($this->editingId),
            ],
            'isActive' => ['boolean'],
        ]);

        if ($this->editingId !== null) {
            LeaseStatus::findOrFail($this->editingId)->update([
                'name' => $this->name,
                'is_active' => $this->isActive,
            ]);
        } else {
            $code = Str::slug($this->name, '_');

            if ($code === '' || LeaseStatus::where('code', $code)->exists()) {
                $this->addError('name', 'This status code is already in use.');
                return;
            }

            LeaseStatus::create([
                'code' => $code,
                'name' => $this->name,
                'is_active' => $this->isActive,
            ]);
        }

        session()->flash('success', 'Lease status saved.');
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.configurations.lease-statuses.index', [
            'statuses' => LeaseStatus::orderBy('name')->get(),
        ])->layout('layouts.master');
    }
}