<div>
    <x-slot name="title">ZESCO as Landlord Lease Register</x-slot>
    <x-slot name="pageHeader">ZESCO as Landlord Lease Register</x-slot>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="font-weight-bold mb-1">ZESCO as Landlord Lease Register</h5>
                <small class="text-muted">Finalized agreements where ZESCO is the landlord</small>
            </div>
            <a href="{{ route('lease_agreements.new.landlord') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>New landlord application</a>
        </div>

        <div class="row mb-3">
            @foreach([
                ['Landlord leases', $totalLeases, 'fas fa-file-contract', 'primary'],
                ['Ongoing leases', $ongoingLeases, 'fas fa-calendar-check', 'success'],
            ] as [$label, $value, $icon, $color])
                <div class="col-sm-6 col-xl-3 mb-2">
                    <div class="card h-100 mb-0"><div class="card-body d-flex justify-content-between align-items-center">
                        <div><small class="text-muted">{{ $label }}</small><div class="h3 mb-0">{{ number_format($value) }}</div></div>
                        <i class="{{ $icon }} fa-2x text-{{ $color }}" aria-hidden="true"></i>
                    </div></div>
                </div>
            @endforeach
        </div>

        <section class="card elevation-2 mb-3">
            <div class="card-body"><div class="row">
                <div class="form-group col-md-8 mb-2">
                    <label class="sr-only" for="landlord-register-search">Search landlord lease register</label>
                    <input id="landlord-register-search" type="search" class="form-control" placeholder="Search tenant, property, lease number, contact" wire:model.live.debounce.300ms="search">
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label class="sr-only" for="landlord-register-status">Lease status</label>
                    <select id="landlord-register-status" class="form-control" wire:model.live="statusFilter">
                        <option value="">All statuses</option>
                        @foreach($statuses as $status)<option value="{{ $status->id }}">{{ $status->name }}</option>@endforeach
                    </select>
                </div>
            </div></div>
        </section>

        <section class="card elevation-2">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead style="background-color: {{ config('theme.color_primary') }}; color: {{ config('theme.color_primary_text_light') }}">
                        <tr><th>Lease Number</th><th>Tenant</th><th>Tenant Contact</th><th>Property / Asset</th><th>Rent Term</th><th>Start</th><th>End</th><th>Status</th><th>Responsible Officer</th></tr>
                    </thead>
                    <tbody>
                        @forelse($leases as $lease)
                            <tr>
                                <td class="font-weight-bold">{{ $lease->lease_number ?: '—' }}</td>
                                <td>{{ $lease->tenant_name ?: '—' }}</td>
                                <td>{{ $lease->tenant_contact_name ?: '—' }}<small class="d-block text-muted">{{ $lease->tenant_contact_phone ?: $lease->tenant_contact_email ?: '—' }}</small></td>
                                <td>{{ $lease->property_reference ?: '—' }}<small class="d-block text-muted">{{ $lease->contract_description }}</small></td>
                                <td>
                                    @if($lease->payment_amount !== null)
                                        K{{ number_format((float) $lease->payment_amount, 2) }} / {{ match($lease->payment_period) { 'monthly' => 'month', '3_months' => '3 months', '6_months' => '6 months', default => $lease->payment_period ?: 'term' } }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $lease->start_date?->format('d M Y') ?: '—' }}</td>
                                <td>{{ $lease->end_date?->format('d M Y') ?: '—' }}</td>
                                <td>{{ $lease->leaseStatus?->name ?: '—' }}</td>
                                <td>{{ $lease->responsibleOfficer?->name ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-5">No finalized ZESCO-as-landlord leases match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-body">{{ $leases->links() }}</div>
        </section>
    </div>
</div>