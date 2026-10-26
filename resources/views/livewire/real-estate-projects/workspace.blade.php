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
    @php
    $sectionBChoices = [
        'proposed_works_type' => [
            'label' => 'Type of Proposed Works',
            'options' => [
                'New Development',
                'Alteration / Conversion',
                'Rehabilitation',
                'Expansion',
                'Other',
            ],
        ],
        'site_condition' => [
            'label' => 'Current Property / Site Condition',
            'options' => [
                'Good',
                'Fair',
                'Poor',
                'Not Applicable',
                'Not Assessed',
            ],
        ],
        'existing_use' => [
            'label' => 'Existing Use of Property',
            'options' => [
                'Occupied',
                'Partly Occupied',
                'Vacant',
                'Undeveloped',
                'Other',
            ],
        ],
        'site_information_availability' => [
            'label' => 'Availability of Drawings / Site Information',
            'options' => [
                'Available',
                'Partly Available',
                'Not Available',
            ],
        ],
    ];

    $sourceNotes = collect([
        'Proposed intervention' => $project->project_category,
        'Existing condition' => $proposal['existing_condition'] ?? null,
        'Current situation / use' => $proposal['current_situation'] ?? null,
        'Known constraints' => $proposal['known_constraints'] ?? null,
        'Site conditions' => $proposal['site_conditions_constraints'] ?? null,
        'Supporting technical evidence' =>
            $proposal['supporting_technical_evidence'] ?? null,
        'Layout information reported' =>
            is_array($proposal['e2_layout_information'] ?? null)
                ? implode(', ', $proposal['e2_layout_information'])
                : null,
    ])->filter(fn ($value) => filled($value));
@endphp

<section class="card mt-3">
    <div class="card-header">
        <h5 class="mb-0">B. Existing Property / Site</h5>
    </div>

    <div class="card-body">
        @if ($sourceNotes->isNotEmpty())
            <div class="alert alert-light border">
                <strong>Information recorded in the proposal</strong>

                @foreach ($sourceNotes as $label => $value)
                    <div class="mt-2">
                        <span class="font-weight-bold">{{ $label }}:</span>
                        {{ $value }}
                    </div>
                @endforeach
            </div>
        @endif

        <form wire:submit.prevent="saveFeasibilitySectionB">
            <fieldset @disabled(! auth()->user()?->can('real_estate_projects.update'))>
                <div class="row">
                    @foreach ($sectionBChoices as $field => $choice)
                        <div class="col-md-6 form-group">
                            <label for="feasibility-{{ $field }}">
                                {{ $choice['label'] }}
                            </label>

                            <select
                                id="feasibility-{{ $field }}"
                                class="form-control"
                                wire:model="feasibilityForm.{{ $field }}">
                                <option value="">Select one option</option>

                                @foreach ($choice['options'] as $option)
                                    <option value="{{ $option }}">
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>

                            @error("feasibilityForm.{$field}")
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    @endforeach

                    <div class="col-12 form-group">
                        <label for="feasibility-key-existing-issue">
                            Key existing condition or site issue affecting
                            the proposal
                        </label>

                        <textarea
                            id="feasibility-key-existing-issue"
                            class="form-control"
                            rows="3"
                            wire:model="feasibilityForm.key_existing_issue"></textarea>

                        @error('feasibilityForm.key_existing_issue')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                @can('real_estate_projects.update')
                    <button
                        type="submit"
                        class="btn btn-primary"
                        wire:loading.attr="disabled"
                        wire:target="saveFeasibilitySectionB">
                        Save Section B
                    </button>
                @endcan
            </fieldset>
        </form>
    </div>
</section>

        @php
    $technicalSourceNotes = collect([
        'Existing condition' => $proposal['existing_condition'] ?? null,
        'Supporting technical evidence' =>
            $proposal['supporting_technical_evidence'] ?? null,
        'Known constraints' => $proposal['known_constraints'] ?? null,
        'Site access and infrastructure' =>
            $proposal['site_access_infrastructure'] ?? null,
        'Site conditions' => $proposal['site_conditions_constraints'] ?? null,
        'Technical investigations identified' =>
            $proposal['technical_investigations'] ?? null,
        'Statutory, safety or environmental considerations' =>
            $proposal['statutory_safety_environmental_considerations'] ?? null,
    ])->filter(fn ($value) => filled($value));
@endphp

<section class="card mt-3">
    <div class="card-header">
        <h5 class="mb-0">C. Technical Feasibility</h5>
    </div>

    <div class="card-body">
        <p class="text-muted">
            Select each finding based on available evidence.
            “To Be Confirmed” means a further technical check is required.
        </p>

        @if ($technicalSourceNotes->isNotEmpty())
            <div class="alert alert-light border">
                <strong>Technical information in the proposal</strong>

                @foreach ($technicalSourceNotes as $label => $value)
                    <div class="mt-2">
                        <span class="font-weight-bold">{{ $label }}:</span>
                        {{ $value }}
                    </div>
                @endforeach
            </div>
        @endif

        <form wire:submit.prevent="saveFeasibilitySectionC">
            <fieldset @disabled(! auth()->user()?->can('real_estate_projects.update'))>
                <div class="row">
                    @foreach ($technicalChoices as $field => $choice)
                        <div class="col-md-6 form-group">
                            <label for="feasibility-{{ $field }}">
                                {{ $choice['label'] }}
                            </label>

                            <select
                                id="feasibility-{{ $field }}"
                                class="form-control"
                                wire:model="feasibilityForm.{{ $field }}">
                                <option value="">Select one option</option>

                                @foreach ($choice['options'] as $option)
                                    <option value="{{ $option }}">
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>

                            @error("feasibilityForm.{$field}")
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    @endforeach

                    <div class="col-12 form-group">
                        <label for="feasibility-technical-limitations">
                            Main technical limitations and proposed measures
                        </label>

                        <textarea
                            id="feasibility-technical-limitations"
                            class="form-control"
                            rows="4"
                            wire:model="feasibilityForm.technical_limitations_measures"></textarea>

                        @error('feasibilityForm.technical_limitations_measures')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                @can('real_estate_projects.update')
                    <button
                        type="submit"
                        class="btn btn-primary"
                        wire:loading.attr="disabled"
                        wire:target="saveFeasibilitySectionC">
                        Save Section C
                    </button>
                @endcan
            </fieldset>
        </form>
    </div>
</section>

<section class="card mt-3">
    <div class="card-header">
        <h5 class="mb-0">D. Options Considered</h5>
    </div>

    <div class="card-body">
        <p class="text-muted">
            Review the alternatives carried over from the proposal.
            Include retaining the current arrangement if relevant.
        </p>

        <form wire:submit.prevent="saveFeasibilitySectionD">
            <fieldset @disabled(! auth()->user()?->can('real_estate_projects.update'))>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="min-width: 240px;">Option</th>
                                <th style="min-width: 260px;">Main advantage</th>
                                <th style="min-width: 260px;">Main limitation</th>
                            </tr>
                        </thead>

                        <tbody>
                            @for ($index = 0; $index < 3; $index++)
                                <tr wire:key="feasibility-option-{{ $index }}">
                                    <td>
                                        <label for="feasibility-option-{{ $index }}">
                                            Option {{ $index + 1 }}
                                        </label>
                                        <input
                                            id="feasibility-option-{{ $index }}"
                                            class="form-control"
                                            wire:model="feasibilityForm.options_considered.{{ $index }}.option">

                                        @error("feasibilityForm.options_considered.{$index}.option")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </td>
                                    <td>
                                        <textarea
                                            class="form-control"
                                            rows="2"
                                            aria-label="Option {{ $index + 1 }} main advantage"
                                            wire:model="feasibilityForm.options_considered.{{ $index }}.advantage"></textarea>

                                        @error("feasibilityForm.options_considered.{$index}.advantage")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </td>
                                    <td>
                                        <textarea
                                            class="form-control"
                                            rows="2"
                                            aria-label="Option {{ $index + 1 }} main limitation"
                                            wire:model="feasibilityForm.options_considered.{{ $index }}.limitation"></textarea>

                                        @error("feasibilityForm.options_considered.{$index}.limitation")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                <div class="form-group">
                    <label for="feasibility-preferred-option">
                        Preferred Option
                    </label>

                    <select
                        id="feasibility-preferred-option"
                        class="form-control"
                        wire:model="feasibilityForm.preferred_option_number">
                        <option value="">Select an option</option>
                        <option value="1">Option 1</option>
                        <option value="2">Option 2</option>
                        <option value="3">Option 3</option>
                    </select>

                    @error('feasibilityForm.preferred_option_number')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="feasibility-preference-reason">
                        Reason for Preference
                    </label>

                    <textarea
                        id="feasibility-preference-reason"
                        class="form-control"
                        rows="3"
                        wire:model="feasibilityForm.preference_reason"></textarea>

                    @error('feasibilityForm.preference_reason')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                @can('real_estate_projects.update')
                    <button
                        type="submit"
                        class="btn btn-primary"
                        wire:loading.attr="disabled"
                        wire:target="saveFeasibilitySectionD">
                        Save Section D
                    </button>
                @endcan
            </fieldset>
        </form>
    </div>
</section>
<section class="card mt-3">
    <div class="card-header"><h5 class="mb-0">E. Cost and Implementation Feasibility</h5></div>
    <div class="card-body">
        <p class="text-muted">The proposed cost is carried over as a draft. Check the estimate and record the current funding and delivery position.</p>
        @if(filled($proposal['cost_basis'] ?? null) || filled($proposal['funding_information'] ?? null) || filled($proposal['resource_requirements'] ?? null) || filled($proposal['desired_timeframe'] ?? null) || filled($proposal['implementation_dependencies'] ?? null))
            <div class="alert alert-light border">
                <strong>Proposal context</strong>
                @foreach (['cost_basis' => 'Cost basis', 'funding_information' => 'Funding', 'resource_requirements' => 'Required resources', 'desired_timeframe' => 'Desired timeframe', 'implementation_dependencies' => 'Dependencies'] as $sourceField => $sourceLabel)
                    @if(filled($proposal[$sourceField] ?? null) && is_scalar($proposal[$sourceField]))
                        <div><span class="text-muted">{{ $sourceLabel }}:</span> {{ $proposal[$sourceField] }}</div>
                    @endif
                @endforeach
            </div>
        @endif
        <form wire:submit.prevent="saveFeasibilitySectionE">
            <fieldset @disabled(! auth()->user()?->can('real_estate_projects.update'))>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="feasibility-preliminary-cost">Preliminary Estimated Project Cost (ZMW)</label>
                        <input id="feasibility-preliminary-cost" type="number" min="0" step="0.01" class="form-control" wire:model="feasibilityForm.preliminary_cost" @disabled($feasibilityForm['cost_not_available'] ?? false)>
                        <div class="form-check mt-2">
                            <input id="feasibility-cost-not-available" type="checkbox" class="form-check-input" wire:model.live="feasibilityForm.cost_not_available">
                            <label class="form-check-label" for="feasibility-cost-not-available">Not Yet Available</label>
                        </div>
                        @error('feasibilityForm.preliminary_cost') <small class="text-danger">{{ $message }}</small> @enderror
                        @error('feasibilityForm.cost_not_available') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="feasibility-implementation-months">Indicative Implementation Period (Months)</label>
                        <input id="feasibility-implementation-months" type="number" min="1" step="1" class="form-control" wire:model="feasibilityForm.implementation_months" @disabled($feasibilityForm['period_not_established'] ?? false)>
                        <div class="form-check mt-2">
                            <input id="feasibility-period-not-established" type="checkbox" class="form-check-input" wire:model.live="feasibilityForm.period_not_established">
                            <label class="form-check-label" for="feasibility-period-not-established">Not Yet Established</label>
                        </div>
                        @error('feasibilityForm.implementation_months') <small class="text-danger">{{ $message }}</small> @enderror
                        @error('feasibilityForm.period_not_established') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    @foreach ($costChoices as $field => $choice)
                        <div class="col-md-6 form-group">
                            <label for="feasibility-{{ $field }}">{{ $choice['label'] }}</label>
                            <select id="feasibility-{{ $field }}" class="form-control" wire:model="feasibilityForm.{{ $field }}">
                                <option value="">Select one option</option>
                                @foreach ($choice['options'] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @error("feasibilityForm.{$field}") <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    @endforeach
                    <div class="col-12 form-group">
                        <label for="feasibility-cost-constraints">Important cost, funding or implementation constraints</label>
                        <textarea id="feasibility-cost-constraints" class="form-control" rows="3" wire:model="feasibilityForm.cost_implementation_constraints"></textarea>
                        @error('feasibilityForm.cost_implementation_constraints') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
                @can('real_estate_projects.update')
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveFeasibilitySectionE">Save Section E</button>
                @endcan
            </fieldset>
        </form>
    </div>
</section>

<section class="card mt-3">
    <div class="card-header"><h5 class="mb-0">F. Significant Risks and Required Actions</h5></div>
    <div class="card-body">
        <p class="text-muted">Record matters that could materially affect feasibility. Proposal risks are included as draft issues where available.</p>
        <form wire:submit.prevent="saveFeasibilitySectionF">
            <fieldset @disabled(! auth()->user()?->can('real_estate_projects.update'))>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead><tr><th style="min-width: 260px;">Significant Issue / Risk</th><th style="min-width: 260px;">Proposed Action</th><th style="min-width: 220px;">Responsible Department</th><th class="text-center">Remove</th></tr></thead>
                        <tbody>
                            @foreach ($feasibilityForm['significant_risks'] ?? [] as $index => $risk)
                                <tr wire:key="feasibility-risk-{{ $index }}">
                                    <td>
                                        <textarea rows="2" class="form-control" aria-label="Risk {{ $index + 1 }} issue" wire:model="feasibilityForm.significant_risks.{{ $index }}.issue"></textarea>
                                        @error("feasibilityForm.significant_risks.{$index}.issue") <small class="text-danger">{{ $message }}</small> @enderror
                                    </td>
                                    <td>
                                        <textarea rows="2" class="form-control" aria-label="Risk {{ $index + 1 }} proposed action" wire:model="feasibilityForm.significant_risks.{{ $index }}.action"></textarea>
                                        @error("feasibilityForm.significant_risks.{$index}.action") <small class="text-danger">{{ $message }}</small> @enderror
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" aria-label="Risk {{ $index + 1 }} responsible department" wire:model="feasibilityForm.significant_risks.{{ $index }}.responsible_department">
                                        @error("feasibilityForm.significant_risks.{$index}.responsible_department") <small class="text-danger">{{ $message }}</small> @enderror
                                    </td>
                                    <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" aria-label="Remove risk {{ $index + 1 }}" wire:click="removeSignificantRisk({{ $index }})">Remove</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @error('feasibilityForm.significant_risks') <small class="text-danger d-block mb-2">{{ $message }}</small> @enderror
                @can('real_estate_projects.update')
                    <button type="button" class="btn btn-outline-secondary mr-2" wire:click="addSignificantRisk">Add Risk</button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveFeasibilitySectionF">Save Section F</button>
                @endcan
            </fieldset>
        </form>
    </div>
</section>

<section class="card mt-3">
    <div class="card-header"><h5 class="mb-0">G. Feasibility Finding</h5></div>
    <div class="card-body">
        <form wire:submit.prevent="saveFeasibilitySectionG">
            <fieldset @disabled(! auth()->user()?->can('real_estate_projects.update'))>
                <div class="form-group">
                    <label for="feasibility-overall-finding">Overall assessment</label>
                    <select id="feasibility-overall-finding" class="form-control" wire:model="feasibilityForm.overall_finding">
                        <option value="">Select one finding</option>
                        @foreach ($findingOptions as $finding)
                            <option value="{{ $finding }}">{{ $finding }}</option>
                        @endforeach
                    </select>
                    @error('feasibilityForm.overall_finding') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label for="feasibility-finding-basis">Basis for the assessment (key evidence)</label>
                    <textarea id="feasibility-finding-basis" class="form-control" rows="3" wire:model="feasibilityForm.finding_basis"></textarea>
                    @error('feasibilityForm.finding_basis') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label for="feasibility-next-stage-conditions">Conditions / investigations required before the next stage</label>
                    <textarea id="feasibility-next-stage-conditions" class="form-control" rows="3" wire:model="feasibilityForm.next_stage_conditions"></textarea>
                    @error('feasibilityForm.next_stage_conditions') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                @can('real_estate_projects.update')
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveFeasibilitySectionG">Save Section G</button>
                @endcan
            </fieldset>
        </form>
    </div>
</section>

<section class="card mt-3">
    <div class="card-header"><h5 class="mb-0">H. Supporting Records and Assessment Confirmation</h5></div>
    <div class="card-body">
        <form wire:submit.prevent="saveFeasibilitySectionH">
            <fieldset @disabled(! auth()->user()?->can('real_estate_projects.update'))>
                <div class="form-group">
                    <label class="d-block">Documents provided</label>
                    <p class="text-muted small mb-2">Tick the records provided with the assessment. This checklist does not upload files.</p>
                    <div class="row">
                        @foreach ($supportingRecordOptions as $index => $record)
                            <div class="col-md-4 form-check mb-2 pl-4">
                                <input id="feasibility-record-{{ $index }}" type="checkbox" class="form-check-input" value="{{ $record }}" wire:model.live="feasibilityForm.supporting_records">
                                <label class="form-check-label" for="feasibility-record-{{ $index }}">{{ $record }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('feasibilityForm.supporting_records') <small class="text-danger">{{ $message }}</small> @enderror
                    @error('feasibilityForm.supporting_records.*') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                @if(in_array('Other', $feasibilityForm['supporting_records'] ?? [], true))
                    <div class="form-group">
                        <label for="feasibility-other-record">Other supporting record</label>
                        <input id="feasibility-other-record" type="text" class="form-control" wire:model="feasibilityForm.other_supporting_record">
                        @error('feasibilityForm.other_supporting_record') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                @endif
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="feasibility-assessed-by">Assessed By — Name and Designation</label>
                        <input id="feasibility-assessed-by" type="text" class="form-control" wire:model="feasibilityForm.assessed_by_name_designation">
                        @error('feasibilityForm.assessed_by_name_designation') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="feasibility-assessed-department">Department</label>
                        <input id="feasibility-assessed-department" type="text" class="form-control" wire:model="feasibilityForm.assessed_department">
                        @error('feasibilityForm.assessed_department') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="feasibility-signature-text">Signature (typed name)</label>
                        <input id="feasibility-signature-text" type="text" class="form-control" wire:model="feasibilityForm.signature_text">
                        @error('feasibilityForm.signature_text') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="feasibility-confirmation-date">Date</label>
                        <input id="feasibility-confirmation-date" type="date" class="form-control" wire:model="feasibilityForm.confirmation_date">
                        @error('feasibilityForm.confirmation_date') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
                <p class="text-muted small">The finding reflects information available on the assessment date. Project approvals and expenditure authorisations are handled separately.</p>
                @can('real_estate_projects.update')
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveFeasibilitySectionH">Save Section H</button>
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
