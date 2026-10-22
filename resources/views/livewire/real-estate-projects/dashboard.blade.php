<div>
    <x-slot name="title">Project Dashboard</x-slot>
    <x-slot name="pageHeader">Real Estate Projects</x-slot>

    <div class="container-fluid px-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div><h3 class="mb-1">Project Dashboard</h3><p class="text-muted mb-0">Portfolio summary from project records</p></div>
            @can('real_estate_projects.create')
                <a href="{{ route('real-estate-projects.proposals.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Create New Proposal</a>
            @endcan
        </div>

        <div class="row">
            @foreach([
                ['Total projects', $stats['total'], 'fas fa-file-alt', 'primary'],
                ['Awaiting approval', $stats['under_review'], 'fas fa-hourglass-half', 'warning'],
                ['Approved', $stats['approved'], 'fas fa-check-circle', 'success'],
                ['Ongoing', $stats['implementation'], 'fas fa-hard-hat', 'info'],
                ['Completed', $stats['completed'], 'fas fa-flag-checkered', 'secondary'],
                ['Drafts', $stats['draft'], 'fas fa-pencil-alt', 'secondary'],
            ] as [$label, $value, $icon, $color])
                <div class="col-sm-6 col-xl-4 mb-3">
                    <div class="card h-100 mb-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><div class="text-muted">{{ $label }}</div><div class="h3 mb-0">{{ number_format($value) }}</div></div>
                            <i class="{{ $icon }} text-{{ $color }} fa-2x" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="col-sm-6 col-xl-4 mb-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted">Indicative proposal budget</div>
                        <div class="h3 mb-0">{{ number_format((float) $stats['indicative_budget'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <section class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recently updated projects</h5>
                <a href="{{ route('real-estate-projects.register') }}">Project Register <i class="fas fa-arrow-right ml-1"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background-color: {{ config('theme.color_primary') }}; color: {{ config('theme.color_primary_text_light') }}"><tr><th>Reference</th><th>Project title</th><th>Property / location</th><th>Responsible officer</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($recentProjects as $project)
                            <tr>
                                <td><a href="{{ route('real-estate-projects.show', $project) }}">{{ $project->reference_no }}</a></td>
                                <td>{{ $project->project_title ?: 'Untitled proposal' }}</td>
                                <td>{{ $project->property_name ?: '—' }}<div class="small text-muted">{{ $project->town_location ?: '—' }}</div></td>
                                <td>{{ $project->responsible_officer ?: '—' }}</td>
                                <td><span class="badge badge-secondary">{{ $project->lifecycle_status }}@if($project->proposal_status) <span class="text-muted">· Proposal: {{ $project->proposal_status }}</span>@endif</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No project records yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
