<div>
    <x-slot name="title">Lease Agreement Register</x-slot>
    <x-slot name="pageHeader">Lease Agreement Register</x-slot>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-0 font-weight-bold">Lease Agreements</h5>
                <small class="text-muted">Manage lease agreements</small>
            </div>

            <div class="d-flex flex-wrap gap-2">
                @can('lease_agreements.manage')
                    <a
                        href="{{ route('lease_agreements.import') }}"
                        class="btn text-white bg-success mr-2">
                        <i class="fas fa-file-excel mr-1"></i>
                        Excel Import
                    </a>
                @endcan

                @can('lease_agreements.manage')
                    <a
                        href="{{ route('lease_agreements.import') }}#staged-batches"
                        class="btn text-white bg-primary mr-2">
                        <i class="fas fa-sync-alt mr-1"></i>
                        Sync Lease Agreements
                    </a>
                @endcan
            
        </div>

        </div>
            {{-- LEASE STATS --}}
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card elevation-2 border-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted text-uppercase">Total Leases</small>
                    <h3 class="mb-0 font-weight-bold">{{ $totalLeases }}</h3>
                </div>

                <div style="font-size: 2.2rem; color: {{ config('theme.color_primary') }};">
                    <i class="fas fa-file-contract text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card elevation-2 border-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted text-uppercase">Ongoing Leases</small>
                    <h3 class="mb-0 font-weight-bold">{{ $ongoingLeases }}</h3>
                </div>

                <div style="font-size: 2.2rem; color: {{ config('theme.color_primary') }};">
                    <i class="fas fa-calendar-check text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    
</div>
{{-- LEASE FILTERS --}}
<div class="card elevation-2 mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8 mb-2">
                <label for="lease-search">Search lease</label>
                <input
                    id="lease-search"
                    type="text"
                    class="form-control"
                    placeholder="Search description, landlord, town, phone..."
                    wire:model.live.debounce.400ms="search">
            </div>

            <div class="col-md-4 mb-2">
                <label for="lease-status">Status</label>
                <select
                    id="lease-status"
                    class="form-control"
                    wire:model.live="statusFilter">
                    <option value="">All statuses</option>

                    @foreach ($statuses as $status)
                        <option value="{{ $status->id }}">
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
{{-- LEASE REGISTER TABLE --}}
<div class="card elevation-2 border-0 mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0 font-weight-bold">Lease Agreements</h5>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
            <thead class="bg-primary text-white text-nowrap">
        <tr>
            <th>ID</th>
            <th>Lease Number</th>
            <th style="min-width: 320px;">Contract Description</th>
            <th>Landlord</th>
            <th>Contact Phone</th>
            <th>Town</th>
            <th>Province</th>
            <th>Lease Duration</th>
            <th>Tenure (Years)</th>
            <th>Monthly Payment</th>
            <th>Annual Payment</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th style="min-width: 220px;">Comment</th>
            <th>Responsible Officer</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($leases as $lease)
            <tr>
                <td>{{ $lease->id }}</td>
                <td>{{ $lease->lease_number ?? '—' }}</td>
                <td>{{ $lease->contract_description }}</td>
                <td>{{ $lease->landlord_name ?? '—' }}</td>
                <td>{{ $lease->landlord_contact_phone ?? '—' }}</td>
                <td>{{ $lease->town ?? '—' }}</td>
                <td>{{ $lease->province?->name ?? '—' }}</td>
                <td>{{ $lease->lease_duration ?? '—' }}</td>
                <td>{{ $lease->lease_tenure_years ?? '—' }}</td>
                <td>
                    {{ $lease->monthly_payment !== null
                        ? number_format((float) $lease->monthly_payment, 2)
                        : '—' }}
                </td>
                <td>
                    {{ $lease->annual_payment !== null
                        ? number_format((float) $lease->annual_payment, 2)
                        : '—' }}
                </td>
                <td>{{ $lease->start_date?->format('d M Y') ?? '—' }}</td>
                <td>{{ $lease->end_date?->format('d M Y') ?? '—' }}</td>
                <td>{{ $lease->leaseStatus?->name ?? '—' }}</td>
                <td>{{ $lease->comment ?: '—' }}</td>
                <td>{{ $lease->responsibleOfficer?->name ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="16" class="text-center text-muted py-4">
                    No lease agreements match your search or status filter.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
        </div>
    </div>
</div>
    </div>
</div>