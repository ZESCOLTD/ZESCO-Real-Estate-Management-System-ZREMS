<div>
    <x-slot name="title">Project Reports</x-slot>
    <x-slot name="pageHeader">Project Reports</x-slot>

    <div class="container-fluid px-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div><h3 class="mb-1">Project Reports</h3><p class="text-muted mb-0">Report data reflects information recorded in project proposals.</p></div>
            @can('real_estate_projects.export')
                <button type="button" class="btn btn-outline-primary" wire:click="export"><i class="fas fa-download mr-1"></i>Export CSV</button>
            @endcan
        </div>

        <section class="card mb-3">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <div class="text-muted">Indicative budget in filtered projects</div>
                    <div class="h3 mb-0">{{ number_format((float) $totalBudget, 2) }}</div>
                </div>
                <div class="form-group mb-0 mt-3 mt-md-0">
                    <label class="sr-only" for="report-status">Filter by status</label>
                    <select id="report-status" class="form-control" wire:model.live="status">
                        <option value="">All statuses</option>
                        @foreach($statuses as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background-color: {{ config('theme.color_primary') }}; color: {{ config('theme.color_primary_text_light') }}"><tr><th>Reference</th><th>Title</th><th>Category</th><th>Origin</th><th>Property / location</th><th>Responsible officer</th><th>Start date</th><th>Expected completion</th><th class="text-right">Indicative budget</th><th class="text-right">Approved budget</th><th>Lifecycle status</th><th>Proposal status</th></tr></thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr>
                                <td>{{ $project->reference_no }}</td>
                                <td>{{ $project->project_title ?: 'Untitled proposal' }}</td>
                                <td>{{ $project->project_category ?: '—' }}</td>
                                <td>{{ $project->record_origin === 'proposal' ? 'Proposal' : 'Existing project' }}</td>
                                <td>{{ $project->property_reference ?: $project->property_name ?: '—' }}<div class="small text-muted">{{ $project->town_location ?: '—' }}</div></td>
                                <td>{{ $project->responsible_officer ?: '—' }}</td>
                                <td>{{ $project->start_date?->format('d M Y') ?: '—' }}</td>
                                <td>{{ $project->expected_completion_date?->format('d M Y') ?: '—' }}</td>
                                <td class="text-right">{{ $project->indicative_cost !== null ? number_format((float) $project->indicative_cost, 2) : '—' }}</td>
                                <td class="text-right">{{ $project->approved_budget !== null ? number_format((float) $project->approved_budget, 2) : '—' }}</td>
                                <td>{{ $project->lifecycle_status }}</td>
                                <td>{{ $project->proposal_status ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="12" class="text-center text-muted py-5">No project data matches this report filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-body">{{ $projects->links() }}</div>
        </section>
    </div>
</div>
