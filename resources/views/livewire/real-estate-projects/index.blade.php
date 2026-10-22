<div>
    <x-slot name="title">{{ $scope === 'proposals' ? 'Project Proposals' : 'Project Register' }}</x-slot>
    <x-slot name="pageHeader">{{ $scope === 'proposals' ? 'Project Proposals' : 'Project Register' }}</x-slot>

    <div class="container-fluid px-0">
        @if(session()->has('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div>
                <h3 class="mb-1">{{ $scope === 'proposals' ? 'Project Proposals' : 'Project Register' }}</h3>
                <p class="text-muted mb-0">{{ $projects->total() }} {{ $scope === 'proposals' ? 'proposals' : 'project records' }}</p>
            </div>
            <div class="d-flex flex-wrap">
                @can('real_estate_projects.create')
                    @if($scope === 'proposals')
                        <a href="{{ route('real-estate-projects.proposals.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Create New Proposal</a>
                    @else
                        <a href="{{ route('real-estate-projects.existing.create') }}" class="btn btn-outline-primary mr-2"><i class="fas fa-folder-plus mr-1"></i> Register Existing Project</a>
                        <a href="{{ route('real-estate-projects.proposals.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Create New Proposal</a>
                    @endif
                @endcan
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8 mb-2 mb-md-0">
                        <label class="sr-only" for="project-search">Search projects</label>
                        <input id="project-search" type="search" class="form-control" placeholder="Search reference, title, property, location or officer" wire:model.live.debounce.350ms="search">
                    </div>
                    <div class="col-md-4">
                        <label class="sr-only" for="project-status">Filter by status</label>
                        <select id="project-status" class="form-control" wire:model.live="status">
                            <option value="">All statuses</option>
                            @foreach($statusOptions as $statusOption)
                                <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: {{ config('theme.color_primary') }}; color: {{ config('theme.color_primary_text_light') }}">
                            <tr>
                                <th>Project reference / title</th>
                                <th>Category</th>
                                <th>Origin</th>
                                <th>Property / location</th>
                                <th>Responsible officer</th>
                                <th>Start / expected completion</th>
                                <th class="text-right">Indicative / approved budget</th>
                                <th>Status</th>
                                <th><span class="sr-only">Open</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $project)
                                <tr>
                                    <td>
                                        <a href="{{ route('real-estate-projects.show', $project) }}" class="font-weight-bold">{{ $project->project_title ?: 'Untitled proposal' }}</a>
                                        <div class="small text-muted">{{ $project->reference_no }}</div>
                                    </td>
                                    <td>{{ $project->project_category ?: '—' }}</td>
                                    <td>{{ $project->record_origin === 'proposal' ? 'Proposal' : 'Existing project' }}</td>
                                    <td>
                                        {{ $project->property_name ?: '—' }}
                                        <div class="small text-muted">{{ $project->town_location ?: 'Location not recorded' }}</div>
                                    </td>
                                    <td>{{ $project->responsible_officer ?: '—' }}</td>
                                    <td>
                                        {{ $project->start_date?->format('d M Y') ?: '—' }}
                                        <div class="small text-muted">Expected: {{ $project->expected_completion_date?->format('d M Y') ?: '—' }}</div>
                                    </td>
                                    <td class="text-right">
                                        {{ $project->indicative_cost !== null ? number_format((float) $project->indicative_cost, 2) : '—' }}
                                        <div class="small text-muted">Approved: {{ $project->approved_budget !== null ? number_format((float) $project->approved_budget, 2) : '—' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $scope === 'proposals' ? $project->proposal_status : $project->lifecycle_status }}</span>
                                        @if($scope === 'register' && $project->proposal_status)
                                            <div class="small text-muted">Proposal: {{ $project->proposal_status }}</div>
                                        @endif
                                    </td>
                                    <td class="text-right"><a class="btn btn-sm btn-outline-secondary" href="{{ route('real-estate-projects.show', $project) }}" aria-label="Open {{ $project->reference_no }}"><i class="fas fa-arrow-right"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted py-5">No project records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $projects->links() }}</div>
            </div>
        </div>
    </div>
</div>
