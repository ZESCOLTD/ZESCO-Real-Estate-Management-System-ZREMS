<?php

namespace App\Livewire\TenantRegister;

use App\Models\TenantRegister as TenantRecord;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Edit extends Component
{
    public TenantRecord $tenant;
    public array $form = [];
    public bool $canEdit = false;

    private const FIELDS = [
        'serial_no', 'station_location', 'category', 'current_tenant', 'man_no', 'employer',
        'location', 'physical_address', 'area_m2', 'bedrooms', 'open_closed_verandah_m2',
        'garage_m2', 'servants_quarter_m2', 'condition', 'current_rental_charge',
        'market_rental_charge_gvd', 'proposed_rental_charge_50_percent',
        'proposed_rentals_after_verification', 'proposed_percentage_increment',
        'water_charges', 'sewer_charges', 'proposed_total_amount',
    ];

    public function mount(TenantRecord $tenant): void
    {
        $this->authorize('tenant_register.view');
        $this->tenant = $tenant;
        $this->canEdit = auth()->user()->can('tenant_register.update');
        foreach (self::FIELDS as $field) {
            $this->form[$field] = $tenant->{$field} ?? '';
        }
    }

    public function save(): void
    {
        $this->authorize('tenant_register.update');
        $rules = [
            'form.serial_no' => ['nullable', 'string', 'max:255'],
            'form.station_location' => ['required', 'string', 'max:255'],
            'form.category' => ['required', 'string', 'max:255'],
            'form.current_tenant' => ['nullable', 'string', 'max:255'],
            'form.man_no' => ['nullable', 'string', 'max:255'],
            'form.employer' => ['nullable', 'string', 'max:255'],
            'form.location' => ['nullable', 'string', 'max:255'],
            'form.physical_address' => ['nullable', 'string', 'max:255'],
            'form.area_m2' => ['nullable', 'numeric', 'min:0'],
            'form.bedrooms' => ['nullable', 'integer', 'min:0'],
            'form.open_closed_verandah_m2' => ['nullable', 'numeric', 'min:0'],
            'form.garage_m2' => ['nullable', 'numeric', 'min:0'],
            'form.servants_quarter_m2' => ['nullable', 'numeric', 'min:0'],
            'form.condition' => ['nullable', 'string', 'max:255'],
            'form.current_rental_charge' => ['nullable', 'numeric', 'min:0'],
            'form.market_rental_charge_gvd' => ['nullable', 'numeric', 'min:0'],
            'form.proposed_rental_charge_50_percent' => ['nullable', 'numeric', 'min:0'],
            'form.proposed_rentals_after_verification' => ['nullable', 'numeric', 'min:0'],
            'form.proposed_percentage_increment' => ['nullable', 'numeric', 'min:0'],
            'form.water_charges' => ['nullable', 'numeric', 'min:0'],
            'form.sewer_charges' => ['nullable', 'numeric', 'min:0'],
            'form.proposed_total_amount' => ['nullable', 'numeric', 'min:0'],
        ];
        $data = $this->validate($rules)['form'];

        if (blank($data['physical_address']) && blank($data['serial_no'])) {
            throw ValidationException::withMessages([
                'form.physical_address' => 'Enter a physical address or serial number to identify this property.',
            ]);
        }

        $data['unit_key'] = TenantRecord::unitKeyFor($data);
        try {
            $this->tenant->fill($data)->save();
        } catch (QueryException) {
            throw ValidationException::withMessages([
                'form.physical_address' => 'Another tenant record already uses this station, category, location, and property reference.',
            ]);
        }

        session()->flash('success', 'Tenant register record updated.');
        $this->tenant->refresh();
    }

    public function render()
    {
        $this->authorize('tenant_register.view');

        return view('livewire.tenant-register.edit')
            ->layout('layouts.master', ['title' => $this->tenant->current_tenant ?: $this->tenant->physical_address]);
    }
}