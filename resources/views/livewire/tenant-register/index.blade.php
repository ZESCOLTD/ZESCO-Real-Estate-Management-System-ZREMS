<div>
    <x-slot name="title">ZESCO Tenants Register</x-slot>
    <x-slot name="pageHeader">ZESCO Tenants Register</x-slot>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-0 font-weight-bold">ZESCO Tenants Register</h5>
                <small class="text-muted">Tenant and housing records across stations</small>
            </div>
            @can('tenant_register.create')
                <a href="{{ route('tenant-register.import') }}" class="btn text-white bg-success"><i class="fas fa-file-excel mr-1"></i>Excel Import</a>
            @endcan
        </div>

        @if(session()->has('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <div class="row mb-3">
            <div class="col-md-3 mb-2">
                <div class="card border-0 elevation-2"><div class="card-body py-3">
                    <small class="text-muted text-uppercase">Housing Records</small>
                    <h4 class="mb-0 font-weight-bold">{{ number_format($totalTenants) }}</h4>
                </div></div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="card border-0 elevation-2"><div class="card-body py-3">
                    <small class="text-muted text-uppercase">Vacant Units</small>
                    <h4 class="mb-0 font-weight-bold">{{ number_format($vacantUnits) }}</h4>
                </div></div>
            </div>
        </div>

        <section class="card elevation-2 mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-6 col-xl-4 mb-2">
                        <label class="sr-only" for="tenant-search">Search tenant register</label>
                        <input id="tenant-search" type="search" class="form-control" placeholder="Search tenant, Man No., employer, address" wire:model.live.debounce.350ms="search">
                    </div>
                    <div class="form-group col-md-6 col-xl-3 mb-2">
                        <label class="sr-only" for="tenant-station">Station/Location</label>
                        <select id="tenant-station" class="form-control" wire:model.live="stationLocation">
                            <option value="">All Stations/Locations</option>
                            @foreach($stations as $station)<option value="{{ $station }}">{{ $station }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6 col-xl-3 mb-2">
                        <label class="sr-only" for="tenant-category">Category</label>
                        <select id="tenant-category" class="form-control" wire:model.live="category">
                            <option value="">All Categories</option>
                            @foreach($categories as $item)<option value="{{ $item }}">{{ $item }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6 col-xl-2 mb-2">
                        <label class="sr-only" for="tenant-employer">Employer</label>
                        <select id="tenant-employer" class="form-control" wire:model.live="employer">
                            <option value="">All Employers</option>
                            @foreach($employers as $item)<option value="{{ $item }}">{{ $item }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div wire:loading.delay wire:target="search,stationLocation,category,employer" class="mt-2 text-muted"><i class="fas fa-spinner fa-spin mr-1"></i>Searching tenant records...</div>
            </div>
        </section>

        <section class="card elevation-2">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0" style="min-width: 2700px;">
                    <thead style="background-color: {{ config('theme.color_primary') }}; color: {{ config('theme.color_primary_text_light') }}">
                        <tr>
                            <th>Serial No.</th><th>Station/Location</th><th>Category</th><th>Current Tenant</th><th>Man No.</th><th>Employer</th><th>Location</th><th>Physical Address/Hse No.</th><th>Area m²</th><th>No. of B/Rooms</th><th>Open/Closed Verandah m²</th><th>Garage m²</th><th>Servants Quarter m²</th><th>Condition</th><th>Current Rental Charge (ZMW)</th><th>Market Rental Charge GVD (ZMW)</th><th>Proposed Rental Charge @50% (ZMW)</th><th>Proposed Rentals after Verification (ZMW)</th><th>Proposed Percentage Increment (%)</th><th>Water Charges</th><th>Sewer Charges</th><th>Proposed Total Amount</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                            <tr>
                                <td>{{ $tenant->serial_no }}</td><td>{{ $tenant->station_location }}</td><td>{{ $tenant->category }}</td>
                                <td><a href="{{ route('tenant-register.edit', $tenant) }}">{{ $tenant->current_tenant ?: 'Vacant' }}</a></td>
                                <td>{{ $tenant->man_no }}</td><td>{{ $tenant->employer }}</td><td>{{ $tenant->location }}</td><td>{{ $tenant->physical_address }}</td>
                                <td>{{ $tenant->area_m2 }}</td><td>{{ $tenant->bedrooms }}</td><td>{{ $tenant->open_closed_verandah_m2 }}</td><td>{{ $tenant->garage_m2 }}</td><td>{{ $tenant->servants_quarter_m2 }}</td><td>{{ $tenant->condition }}</td>
                                <td>{{ $tenant->current_rental_charge !== null ? number_format((float) $tenant->current_rental_charge, 2) : '' }}</td>
                                <td>{{ $tenant->market_rental_charge_gvd !== null ? number_format((float) $tenant->market_rental_charge_gvd, 2) : '' }}</td>
                                <td>{{ $tenant->proposed_rental_charge_50_percent !== null ? number_format((float) $tenant->proposed_rental_charge_50_percent, 2) : '' }}</td>
                                <td>{{ $tenant->proposed_rentals_after_verification !== null ? number_format((float) $tenant->proposed_rentals_after_verification, 2) : '' }}</td>
                                <td>{{ $tenant->proposed_percentage_increment !== null ? number_format((float) $tenant->proposed_percentage_increment, 2) : '' }}</td>
                                <td>{{ $tenant->water_charges !== null ? number_format((float) $tenant->water_charges, 2) : '' }}</td>
                                <td>{{ $tenant->sewer_charges !== null ? number_format((float) $tenant->sewer_charges, 2) : '' }}</td>
                                <td>{{ $tenant->proposed_total_amount !== null ? number_format((float) $tenant->proposed_total_amount, 2) : '' }}</td>
                                <td><a class="btn btn-sm btn-outline-primary" href="{{ route('tenant-register.edit', $tenant) }}" aria-label="View or edit tenant record"><i class="fas fa-edit"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="23" class="text-center text-muted py-5">No tenant records match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-body">{{ $tenants->links() }}</div>
        </section>
    </div>
</div>