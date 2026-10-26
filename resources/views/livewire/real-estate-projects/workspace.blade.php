<div>
    <x-slot name="title">{{ $project->reference_no }}</x-slot>
    <x-slot name="pageHeader">Project Workspace</x-slot>

    @php
        $tabs = [
            $project->proposal_status === null ? 'overview' : 'proposal' => $project->proposal_status === null ? 'Project Overview' : 'Overview & Justification',
            'feasibility' => 'Feasibility Assessment',
            'budget' => 'Budget & Costing',
            'risks' => 'Project Risks',
            'progress' => 'Milestones & Progress',
            'documents' => 'Documents',
            'approvals' => 'Approvals & Decisions',
            'history' => 'History',
        ];
        $proposal = $project->proposal_data ?? [];
    @endphp

    <div class="container-fluid px-0">
        @if(session()->has('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->has('proposal') || $errors->has('workflow'))
            <div class="alert alert-danger" role="alert">
                @if($errors->has('proposal'))<div>{{ $errors->first('proposal') }}</div>@endif
                @if($errors->has('workflow'))<div>{{ $errors->first('workflow') }}</div>@endif
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
            <div>
                <div class="text-muted">{{ $project->reference_no }} <span class="mx-1">·</span> {{ $project->lifecycle_status }}@if($project->proposal_status) <span class="mx-1">·</span> Proposal: {{ $project->proposal_status }}@endif</div>
                <h3 class="mb-1">{{ $project->project_title ?: 'Untitled proposal' }}</h3>
                <div class="text-muted">Responsible officer: {{ $project->responsible_officer ?: 'Not recorded' }}</div>
            </div>
            <div class="d-flex mt-2 mt-md-0">
                <a href="{{ route($project->proposal_status === null ? 'real-estate-projects.register' : 'real-estate-projects.proposals') }}" class="btn btn-outline-secondary mr-2"><i class="fas fa-arrow-left mr-1"></i> {{ $project->proposal_status === null ? 'Project Register' : 'Proposals' }}</a>
                @can('real_estate_projects.update')
                    @if($project->record_origin === 'proposal' && in_array($project->proposal_status, ['Draft', 'Returned for Revision', 'Rejected'], true))
                        <a href="{{ route('real-estate-projects.proposal.edit', $project) }}" class="btn btn-outline-primary mr-2"><i class="fas fa-edit mr-1"></i> Edit Proposal</a>
                    @endif
                @endcan
                @can('real_estate_projects.submit')
                    @if($project->record_origin === 'proposal' && in_array($project->proposal_status, ['Draft', 'Returned for Revision', 'Rejected'], true))
                        <button type="button" class="btn btn-primary" wire:click="submitProposal" wire:loading.attr="disabled">
                            <span wire:loading.remove><i class="fas fa-paper-plane mr-1"></i> Submit for Review</span>
                            <span wire:loading><span class="spinner-border spinner-border-sm mr-1"></span> Submitting...</span>
                        </button>
                    @endif
                @endcan
            </div>
        </div>

        <div class="border-bottom mb-3 overflow-auto">
            <nav class="nav nav-tabs flex-nowrap" aria-label="Project sections">
                @foreach($tabs as $key => $label)
                    <button type="button" class="nav-link text-nowrap {{ $activeTab === $key ? 'active' : '' }}" aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}" wire:click="selectTab('{{ $key }}')">{{ $label }}</button>
                @endforeach
            </nav>
        </div>

        @if(in_array($activeTab, ['proposal', 'overview'], true))
            <section class="card">
            <div class="card-header"><h5 class="mb-0">{{ $activeTab === 'proposal' ? 'Project Proposal and Justification' : 'Project Overview' }}</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="text-muted small">Proposed intervention</div><div>{{ $project->project_category ?: '—' }}</div></div>
                        <div class="col-md-6 mb-3"><div class="text-muted small">Property name</div><div>{{ $project->property_name ?: '—' }}</div></div>
                        <div class="col-md-6 mb-3"><div class="text-muted small">Property reference</div><div>{{ $project->property_reference ?: '—' }}</div></div>
                        <div class="col-md-6 mb-3"><div class="text-muted small">Town / location</div><div>{{ $project->town_location ?: '—' }}</div></div>
                        <div class="col-md-6 mb-3"><div class="text-muted small">Originating department / user unit</div><div>{{ $proposal['originating_department'] ?? '—' }}</div></div>
                        <div class="col-md-6 mb-3"><div class="text-muted small">Contact officer</div><div>{{ $proposal['contact_officer'] ?? '—' }}</div></div>
                        <div class="col-md-6 mb-3"><div class="text-muted small">Real Estate officer preparing proposal</div><div>{{ $project->responsible_officer ?: '—' }}</div></div>
                        <div class="col-md-6 mb-3"><div class="text-muted small">Indicative cost</div><div>{{ $project->indicative_cost !== null ? number_format((float) $project->indicative_cost, 2) : '—' }}</div></div>
                    </div>
                    <hr>
                    @if($activeTab === 'proposal')
                        <div class="mb-3"><div class="text-muted small">Brief description</div><div class="text-break">{{ $proposal['intervention_description'] ?? '—' }}</div></div>
                        <div class="mb-3"><div class="text-muted small">Identified problem or operational need</div><div class="text-break">{{ $proposal['operational_need'] ?? '—' }}</div></div>
                        <div class="mb-0"><div class="text-muted small">Proposal summary</div><div class="text-break">{{ $proposal['proposal_summary'] ?? '—' }}</div></div>
                    @else
                        <p class="text-muted mb-0">This project was registered directly and has no proposal attached.</p>
                    @endif
                </div>
            </section>
        @elseif($activeTab === 'feasibility')
    <section class="card">
        <div class="card-header">
            <h5 class="mb-0">A. Project and Assessment Details</h5>
        </div>

        <div class="card-body">
            <form wire:submit.prevent="saveFeasibilitySectionA">
                <fieldset @disabled(! auth()->user()?->can('real_estate_projects.update'))>
                    <div class="row">
                        @foreach ([
                            'project_title' => 'Project Title',
                            'proposal_reference' => 'Project Proposal Reference',
                            'property_site_name' => 'Property / Site Name',
                            'building_structure_name' => 'Building / Structure Name (if applicable)',
                            'town_location' => 'Town / Location',
                        ] as $field => $label)
                            <div class="col-md-6 form-group">
                                <label for="feasibility-{{ $field }}">{{ $label }}</label>
                                <input
                                    id="feasibility-{{ $field }}"
                                    type="text"
                                    class="form-control"
                                    wire:model="feasibilityForm.{{ $field }}">

                                @error("feasibilityForm.{$field}")
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        @endforeach

                        <div class="col-md-6 form-group">
                            <label for="feasibility-assessment-date">Assessment Date</label>
                            <input
                                id="feasibility-assessment-date"
                                type="date"
                                class="form-control"
                                wire:model="feasibilityForm.assessment_date">

                            @error('feasibilityForm.assessment_date')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="feasibility-lead-department">
                                Lead Assessing Department
                            </label>
                            <input
                                id="feasibility-lead-department"
                                type="text"
                                class="form-control"
                                wire:model="feasibilityForm.lead_assessing_department">

                            @error('feasibilityForm.lead_assessing_department')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                       <div class="col-md-6 form-group">
    <label for="feasibility-prepared-by">
        Assessment Prepared By
    </label>

    <select
        id="feasibility-prepared-by"
        class="form-control"
        wire:model="feasibilityForm.prepared_by_user_id">
        <option value="">Select a user</option>

        @foreach ($assessmentPreparers as $preparer)
            <option value="{{ $preparer->id }}">
                {{ $preparer->name }}
                @if ($preparer->man_no)
                    ({{ $preparer->man_no }})
                @endif
            </option>
        @endforeach
    </select>

    @error('feasibilityForm.prepared_by_user_id')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div>

                        <div class="col-12 form-group">
                            <label for="feasibility-departments-consulted">
                                Other Departments Consulted
                            </label>
                            <textarea
                                id="feasibility-departments-consulted"
                                class="form-control"
                                rows="2"
                                wire:model="feasibilityForm.other_departments_consulted"></textarea>

                            @error('feasibilityForm.other_departments_consulted')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    @can('real_estate_projects.update')
                        <button
                            type="submit"
                            class="btn btn-primary"
                            wire:loading.attr="disabled"
                            wire:target="saveFeasibilitySectionA">
                            Save Section A
                        </button>
                    @endcan
                </fieldset>
            </form>
        </div>
    </section>
        @elseif($activeTab === 'approvals')
            <section class="card">
                <div class="card-header"><h5 class="mb-0">Approvals and Decisions</h5></div>
                <div class="card-body">
                    @if($workflow)
                        <div class="row mb-3">
                            <div class="col-md-4"><div class="text-muted small">Review round</div><div>{{ $workflow->approval_round }}</div></div>
                            <div class="col-md-4"><div class="text-muted small">Review status</div><div>{{ $workflow->status }}</div></div>
                            <div class="col-md-4"><div class="text-muted small">Current stage</div><div>{{ $workflow->currentStep?->step_name ?? 'Review complete' }}</div></div>
                        </div>
                        @if($currentApprovers->isNotEmpty())
                            <div class="mb-3"><div class="text-muted small">Assigned approver(s)</div><div>{{ $currentApprovers->pluck('name')->join(', ') }}</div></div>
                        @endif
                        <h6>Decision history</h6>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background-color: {{ config('theme.color_primary') }}; color: {{ config('theme.color_primary_text_light') }}"><tr><th>Date</th><th>Stage</th><th>Decision</th><th>Officer</th><th>Comment</th></tr></thead>
                                <tbody>
                                    @forelse($workflow->actions->sortBy('acted_at') as $action)
                                        <tr>
                                            <td>{{ $action->acted_at?->format('d M Y H:i') ?: '—' }}</td>
                                            <td>{{ $action->step_name ?: '—' }}</td>
                                            <td>{{ ucfirst($action->action) }}</td>
                                            <td>{{ $action->actor?->name ?: '—' }}</td>
                                            <td>{{ $action->comment ?: '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4">No decisions recorded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">This project has no proposal approval workflow.</p>
                    @endif
                </div>
            </section>
        @else
            <section class="border rounded bg-white p-4">
                <h5 class="mb-2">{{ $tabs[$activeTab] }}</h5>
                <p class="text-muted mb-0">No {{ strtolower($tabs[$activeTab]) }} records are available for this project yet.</p>
            </section>
        @endif
    </div>
</div>
