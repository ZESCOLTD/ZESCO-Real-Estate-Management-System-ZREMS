<div>
    <x-slot name="title">Lease Dashboard</x-slot>
    <x-slot name="pageHeader">Lease Management</x-slot>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="font-weight-bold mb-1">Lease Dashboard</h5>
                <small class="text-muted">Register overview and Real Estate follow-ups</small>
            </div>
            @can('lease_tenant_workflow.manage')
                <a href="{{ route('lease_agreements.new') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>New Lease</a>
            @endcan
        </div>

        <div class="alert alert-info py-2">
            <i class="fas fa-info-circle mr-1"></i>
            Follow-ups track external department, landlord, Legal and MD steps. They do not represent in-app approvals or change a lease’s status.
        </div>

        <div class="row">
            @foreach([
                ['Total leases', $stats['total'], 'fas fa-file-contract', 'primary'],
                ['ZESCO as tenant', $stats['zesco_as_tenant'], 'fas fa-building', 'info'],
                ['ZESCO as landlord', $stats['zesco_as_landlord'], 'fas fa-home', 'secondary'],
                ['Ongoing leases', $stats['active'], 'fas fa-calendar-check', 'success'],
                ['Open tenant processes', $stats['open_processes'], 'fas fa-tasks', 'warning'],
            ] as [$label, $value, $icon, $color])
                <div class="col-sm-6 col-xl-{{ $loop->last ? '4' : '3' }} mb-3">
                    <div class="card h-100 mb-0"><div class="card-body d-flex justify-content-between align-items-center">
                        <div><small class="text-muted">{{ $label }}</small><div class="h3 mb-0">{{ number_format($value) }}</div></div>
                        <i class="{{ $icon }} fa-2x text-{{ $color }}" aria-hidden="true"></i>
                    </div></div>
                </div>
            @endforeach
        </div>

        <section class="card mb-3">
            <div class="card-header"><h6 class="font-weight-bold mb-0">Lease expiries</h6></div>
            <div class="row no-gutters">
                @foreach([
                    ['Within 30 days', $stats['expiring_30']],
                    ['31–60 days', $stats['expiring_60']],
                    ['61–90 days', $stats['expiring_90']],
                ] as [$label, $value])
                    <div class="col-4 border-right"><div class="p-3"><small class="text-muted">{{ $label }}</small><div class="h4 mb-0">{{ number_format($value) }}</div></div></div>
                @endforeach
            </div>
        </section>

        <section class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div><h6 class="font-weight-bold mb-0">Lease follow-up queue</h6><small class="text-muted">Oldest updated processes first</small></div>
                <a href="{{ route('lease_agreements.new') }}">All processes <i class="fas fa-arrow-right ml-1"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Lease</th><th>Department</th><th>Current stage</th><th>Next follow-up</th><th>Last updated</th><th></th></tr></thead>
                    <tbody>
                        @forelse($queue as $workflow)
                            @php
                                $nextActions = [
                                    'department_confirmation' => 'Confirm department willingness',
                                    'awaiting_landlord_lease' => 'Follow up for landlord lease',
                                    'legal_review' => 'Follow up with Legal',
                                    'md_signature' => 'Follow up for MD signature',
                                    'awaiting_landlord_signature' => 'Follow up for landlord signature',
                                ];
                            @endphp
                            <tr>
                                <td><strong>{{ $workflow->leaseAgreement->lease_number }}</strong><small class="d-block text-muted">{{ $workflow->leaseAgreement->landlord_name }}</small></td>
                                <td>{{ $workflow->department_name }}</td>
                                <td>{{ $statusLabels->statusLabel($workflow->status) }}</td>
                                <td>{{ $nextActions[$workflow->status] ?? 'Review process' }}</td>
                                <td>{{ $workflow->events->last()?->acted_at?->format('d M Y') ?: '—' }}</td>
                                <td><a class="btn btn-sm btn-outline-primary" href="{{ route('lease_agreements.new', ['workflow' => $workflow->id]) }}">Open</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No open tenant-side follow-ups.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white"><a href="{{ route('lease_agreements.index') }}">Open Lease Register</a></div>
        </section>
    </div>
</div>