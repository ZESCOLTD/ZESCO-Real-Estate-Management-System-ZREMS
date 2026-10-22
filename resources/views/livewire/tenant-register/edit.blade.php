<div>
    <x-slot name="title">Tenant Record</x-slot>
    <x-slot name="pageHeader">Tenant Record</x-slot>

    <div class="container-fluid">
        <div class="mb-3">
            <a href="{{ route('tenant-register.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i>Tenants Register</a>
            <h5 class="font-weight-bold mt-3 mb-0">{{ $tenant->current_tenant ?: 'Vacant Unit' }}</h5>
            <small class="text-muted">{{ $tenant->station_location }} / {{ $tenant->category }} / {{ $tenant->physical_address }}</small>
        </div>

        @if(session()->has('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(!$canEdit)<div class="alert alert-info">You have read-only access to this tenant record.</div>@endif

        <form wire:submit="save">
            <section class="card elevation-2">
                <div class="card-header"><h6 class="mb-0 font-weight-bold">Tenant and Property Details</h6></div>
                <div class="card-body"><div class="row">
                    @foreach([
                        'serial_no' => 'Serial No.', 'station_location' => 'Station/Location', 'category' => 'Category',
                        'current_tenant' => 'Current Tenant', 'man_no' => 'Man No.', 'employer' => 'Employer',
                        'location' => 'Location', 'physical_address' => 'Physical Address/Hse No.',
                        'area_m2' => 'Area m²', 'bedrooms' => 'No. of B/Rooms',
                        'open_closed_verandah_m2' => 'Open/Closed Verandah m²', 'garage_m2' => 'Garage m²',
                        'servants_quarter_m2' => 'Servants Quarter m²', 'condition' => 'Condition',
                        'current_rental_charge' => 'Current Rental Charge (ZMW)',
                        'market_rental_charge_gvd' => 'Market Rental Charge GVD (ZMW)',
                        'proposed_rental_charge_50_percent' => 'Proposed Rental Charge @50% (ZMW)',
                        'proposed_rentals_after_verification' => 'Proposed Rentals after Verification (ZMW)',
                        'proposed_percentage_increment' => 'Proposed Percentage Increment (%)',
                        'water_charges' => 'Water Charges', 'sewer_charges' => 'Sewer Charges',
                        'proposed_total_amount' => 'Proposed Total Amount',
                    ] as $field => $label)
                        <div class="form-group col-md-6 col-xl-4">
                            <label for="tenant-{{ $field }}">{{ $label }}</label>
                            <input id="tenant-{{ $field }}" type="text" class="form-control" wire:model="form.{{ $field }}" @disabled(!$canEdit)>
                            @error('form.' . $field)<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    @endforeach
                </div></div>
                @if($canEdit)<div class="card-footer text-right"><button type="submit" class="btn btn-primary" wire:loading.attr="disabled"><i class="fas fa-save mr-1"></i>Save Changes</button></div>@endif
            </section>
        </form>
    </div>
</div>