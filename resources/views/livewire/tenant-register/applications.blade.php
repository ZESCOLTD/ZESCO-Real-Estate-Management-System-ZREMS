<div>
    <x-slot name="title">My Tenant Applications</x-slot>
    <x-slot name="pageHeader">My Tenant Applications</x-slot>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="font-weight-bold mb-0">My Tenant Applications</h5>
                <small class="text-muted">Track drafts, submissions and verification outcomes</small>
            </div>
            @can('tenant_register.create')
                <a href="{{ route('tenant-register.onboarding.create') }}" class="btn btn-primary"><i class="fas fa-user-plus mr-1"></i>Onboard New Tenant</a>
            @endcan
        </div>

        @if(session()->has('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <div class="row mb-3">
            @foreach([
                ['Applications', $totalApplications, 'All your records', 'text-primary'],
                ['Drafts', $draftApplications, 'Not yet submitted', 'text-secondary'],
                ['Pending verification', $pendingApplications, 'Awaiting reviewer action', 'text-warning'],
                ['Returned', $returnedApplications, 'Needs correction', 'text-danger'],
            ] as [$label, $count, $detail, $color])
                <div class="col-6 col-lg-3 mb-2">
                    <div class="card border-0 elevation-1 h-100"><div class="card-body py-3">
                        <small class="text-muted text-uppercase">{{ $label }}</small>
                        <div class="d-flex align-items-baseline"><strong class="{{ $color }}" style="font-size:1.55rem">{{ number_format($count) }}</strong><small class="text-muted ml-2">{{ $detail }}</small></div>
                    </div></div>
                </div>
            @endforeach
        </div>

        <section class="card elevation-2 mb-3">
            <div class="card-body"><div class="row">
                <div class="form-group col-md-8 mb-2">
                    <label class="sr-only" for="my-tenant-applications-search">Search applications</label>
                    <input id="my-tenant-applications-search" type="search" class="form-control" placeholder="Search reference, tenant, Man No. or station" wire:model.live.debounce.300ms="search">
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label class="sr-only" for="my-tenant-applications-status">Filter by status</label>
                    <select id="my-tenant-applications-status" class="form-control" wire:model.live="status">
                        <option value="">All statuses</option>
                        @foreach($statuses as $item)<option value="{{ $item }}">{{ $item }}</option>@endforeach
                    </select>
                </div>
            </div></div>
        </section>

        <section class="card elevation-2">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead style="background-color: {{ config('theme.color_primary') }}; color: {{ config('theme.color_primary_text_light') }}">
                        <tr><th>Application</th><th>Tenant</th><th>Type</th><th>Requested station / category</th><th>Submitted</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $application)
                            @php($statusTone = match($application->status) { 'Pending Verification' => 'warning', 'Verified' => 'success', 'Returned for Correction' => 'danger', default => 'secondary' })
                            <tr>
                                <td class="font-weight-bold">{{ $application->reference_no }}</td>
                                <td>{{ $application->applicant_name ?: 'Unnamed applicant' }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $application->tenant_type)) }}</td>
                                <td>{{ $application->station_location }}<small class="d-block text-muted">{{ $application->category }}</small></td>
                                <td>{{ $application->submitted_at?->format('d M Y') ?: '—' }}</td>
                                <td><span class="badge badge-{{ $statusTone }}">{{ $application->status }}</span></td>
                                <td class="text-nowrap">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-h mr-1"></i>Actions
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="{{ route('tenant-register.onboarding.show', $application) }}"><i class="fas fa-eye fa-fw mr-2"></i>View</a>
                                            @if(in_array($application->status, ['Draft', 'Returned for Correction'], true))
                                                <a class="dropdown-item" href="{{ route('tenant-register.onboarding.edit', $application) }}"><i class="fas fa-edit fa-fw mr-2"></i>{{ $application->status === 'Draft' ? 'Continue' : 'Revise' }}</a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-5">You haven’t started a tenant application yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-body">{{ $applications->links() }}</div>
        </section>
    </div>
</div>