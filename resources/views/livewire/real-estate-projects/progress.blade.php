<div>
    @if(session()->has('progress_success'))
        <div class="alert alert-success" role="status">{{ session('progress_success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">Check the marked progress report fields before saving.</div>
    @endif

    <section class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0">Progress Reports</h5>
            @can('real_estate_projects.update')
                <button type="button" class="btn btn-outline-primary btn-sm" wire:click="newReport">New Reporting Period</button>
            @endcan
        </div>
        <div class="card-body">
            @if($editingReportId)
                <p class="text-muted">Editing saved report #{{ $editingReportId }}. Select New Reporting Period to create a separate update.</p>
            @else
                <p class="text-muted">New report. Project details and milestone names are draft starting points; enter verified results for this period.</p>
            @endif
            @if($reports->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Report</th><th>Reporting Period</th><th>Report Date</th><th>Status</th><th>Progress</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($reports as $report)
                                <tr class="{{ $editingReportId === $report->id ? 'table-active' : '' }}">
                                    <td>#{{ $report->id }}</td>
                                    <td>{{ $report->period_from?->format('d M Y') ?? '—' }} – {{ $report->period_to?->format('d M Y') ?? '—' }}</td>
                                    <td>{{ $report->report_date?->format('d M Y') ?? '—' }}</td>
                                    <td>{{ $report->current_status ?: '—' }}</td>
                                    <td>{{ $report->overall_progress === null ? '—' : $report->overall_progress.'%' }}</td>
                                    <td><button type="button" class="btn btn-outline-secondary btn-sm" wire:click="openReport({{ $report->id }})">Open</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    <form wire:submit.prevent="save">
        <fieldset @disabled(! auth()->user()?->can('real_estate_projects.update'))>
            <section class="card">
                <div class="card-header"><h5 class="mb-0">A. Project Information</h5></div>
                <div class="card-body">
                    <p class="text-muted">Project details are filled in as a draft. Confirm them for this reporting period.</p>
                    <div class="row">
                        @foreach ([
                            'project_title' => 'Project Title',
                            'project_reference' => 'Project Reference Number',
                            'property_site_name' => 'Property / Site Name',
                            'building_structure_name' => 'Building / Structure Name',
                            'town_location' => 'Town / Location',
                            'implementing_department' => 'Implementing Department / Team',
                            'coordinator' => 'Project Coordinator / Reporting Officer',
                            'contractor' => 'Contractor (if applicable)',
                        ] as $field => $label)
                            <div class="col-md-6 form-group">
                                <label for="progress-{{ $field }}">{{ $label }}</label>
                                <input id="progress-{{ $field }}" type="text" class="form-control" wire:model="form.{{ $field }}">
                                @error("form.{$field}") <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        @endforeach
                        @foreach (['period_from' => 'Reporting Period From', 'period_to' => 'Reporting Period To', 'report_date' => 'Date of Report'] as $field => $label)
                            <div class="col-md-4 form-group">
                                <label for="progress-{{ $field }}">{{ $label }}</label>
                                <input id="progress-{{ $field }}" type="date" class="form-control" wire:model="form.{{ $field }}">
                                @error("form.{$field}") <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">B. Project Status at Reporting Date</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 form-group"><label for="progress-current-status">Current Project Status</label><select id="progress-current-status" class="form-control" wire:model="form.current_status"><option value="">Select one status</option>@foreach (\App\Livewire\RealEstateProjects\Progress::PROJECT_STATUSES as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach</select>@error('form.current_status') <small class="text-danger">{{ $message }}</small> @enderror</div>
                        <div class="col-md-4 form-group"><label for="progress-overall-progress">Overall Progress (Verified Estimate, %)</label><input id="progress-overall-progress" type="number" min="0" max="100" step="1" class="form-control" wire:model="form.overall_progress">@error('form.overall_progress') <small class="text-danger">{{ $message }}</small> @enderror</div>
                        <div class="col-md-4 form-group"><label for="progress-programme-position">Progress Against Agreed Programme</label><select id="progress-programme-position" class="form-control" wire:model="form.programme_position"><option value="">Select one position</option>@foreach (\App\Livewire\RealEstateProjects\Progress::PROGRAMME_POSITIONS as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach</select>@error('form.programme_position') <small class="text-danger">{{ $message }}</small> @enderror</div>
                        @foreach (['planned_start' => 'Planned Commencement Date', 'actual_start' => 'Actual Commencement Date', 'original_planned_completion' => 'Original Planned Completion Date', 'forecast_completion' => 'Current Forecast Completion Date'] as $field => $label)
                            <div class="col-md-3 form-group"><label for="progress-{{ $field }}">{{ $label }}</label><input id="progress-{{ $field }}" type="date" class="form-control" wire:model="form.{{ $field }}">@error("form.{$field}") <small class="text-danger">{{ $message }}</small> @enderror</div>
                        @endforeach
                        <div class="col-md-6 form-group"><label for="progress-change-reason">Reason for Change to Completion Date</label><select id="progress-change-reason" class="form-control" wire:model.live="form.completion_change_reason"><option value="">Select one reason</option>@foreach (\App\Livewire\RealEstateProjects\Progress::COMPLETION_CHANGE_REASONS as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach</select>@error('form.completion_change_reason') <small class="text-danger">{{ $message }}</small> @enderror</div>
                        @if(($form['completion_change_reason'] ?? '') === 'Other')
                            <div class="col-md-6 form-group"><label for="progress-change-other">Other Reason</label><input id="progress-change-other" type="text" class="form-control" wire:model="form.completion_change_other">@error('form.completion_change_other') <small class="text-danger">{{ $message }}</small> @enderror</div>
                        @endif
                        <div class="col-12 form-group"><label for="progress-change-details">Brief Details of Completion Date Change</label><textarea id="progress-change-details" class="form-control" rows="2" wire:model="form.completion_change_details"></textarea>@error('form.completion_change_details') <small class="text-danger">{{ $message }}</small> @enderror</div>
                    </div>
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">C. Progress Summary</h5></div>
                <div class="card-body">
                    @foreach ([
                        ['C1. Activities Completed During the Reporting Period', 'completed_categories', \App\Livewire\RealEstateProjects\Progress::COMPLETED_CATEGORIES, 'completed_other', 'completion_position', 'Completion Position', ['All planned activities completed', 'Some planned activities completed', 'No activities completed', 'No activities scheduled'], 'completed_details', 'Completed Work / Quantities / Areas'],
                        ['C2. Activities Currently in Progress', 'current_categories', \App\Livewire\RealEstateProjects\Progress::CURRENT_CATEGORIES, 'current_other', 'current_activity_progress', 'Progress of Ongoing Activities', ['Proceeding as planned', 'Slower than planned', 'Temporarily suspended', 'Awaiting materials / approvals', 'Not applicable'], 'current_details', 'Current Work, Location and Outstanding Tasks'],
                        ['C3. Activities Planned for the Next Reporting Period', 'planned_categories', \App\Livewire\RealEstateProjects\Progress::PLANNED_CATEGORIES, 'planned_other', 'planned_readiness', 'Readiness to Proceed', ['Ready', 'Subject to materials', 'Subject to approval / instruction', 'Subject to funding', 'Subject to access / site clearance', 'To be confirmed'], 'planned_details', 'Planned Activities and Expected Deliverables'],
                    ] as [$heading, $categoriesField, $categories, $otherField, $positionField, $positionLabel, $positions, $detailsField, $detailsLabel])
                        <div class="border-bottom pb-3 mb-3">
                            <h6>{{ $heading }}</h6>
                            <label class="d-block">Activity Categories (select all that apply)</label>
                            <div class="row mb-2">
                                @foreach ($categories as $index => $category)
                                    <div class="col-md-4 form-check pl-4 mb-2"><input id="progress-{{ $categoriesField }}-{{ $index }}" type="checkbox" class="form-check-input" value="{{ $category }}" wire:model.live="form.{{ $categoriesField }}"><label class="form-check-label" for="progress-{{ $categoriesField }}-{{ $index }}">{{ $category }}</label></div>
                                @endforeach
                            </div>
                            @error("form.{$categoriesField}") <small class="text-danger d-block">{{ $message }}</small> @enderror
                            @if(in_array('Other', $form[$categoriesField] ?? [], true))
                                <div class="form-group"><label for="progress-{{ $otherField }}">Other Activity</label><input id="progress-{{ $otherField }}" type="text" class="form-control" wire:model="form.{{ $otherField }}">@error("form.{$otherField}") <small class="text-danger">{{ $message }}</small> @enderror</div>
                            @endif
                            <div class="form-group"><label for="progress-{{ $positionField }}">{{ $positionLabel }}</label><select id="progress-{{ $positionField }}" class="form-control" wire:model="form.{{ $positionField }}"><option value="">Select one option</option>@foreach ($positions as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach</select>@error("form.{$positionField}") <small class="text-danger">{{ $message }}</small> @enderror</div>
                            <div class="form-group mb-0"><label for="progress-{{ $detailsField }}">{{ $detailsLabel }}</label><textarea id="progress-{{ $detailsField }}" class="form-control" rows="2" wire:model="form.{{ $detailsField }}"></textarea>@error("form.{$detailsField}") <small class="text-danger">{{ $message }}</small> @enderror</div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">D. Milestone Progress Record</h5></div>
                <div class="card-body">
                    <p class="text-muted">Record the main programme milestones. Follow-up fields apply where a milestone is delayed, changed, or suspended.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th>No.</th><th style="min-width: 230px;">Milestone / Deliverable</th><th style="min-width: 155px;">Planned Completion</th><th style="min-width: 155px;">Actual / Revised Forecast</th><th style="min-width: 110px;">Completion %</th><th style="min-width: 160px;">Status</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($form['milestones'] ?? [] as $index => $milestone)
                                    <tr wire:key="progress-milestone-{{ $index }}"><td>{{ $index + 1 }}</td>
                                        <td><input type="text" class="form-control" aria-label="Milestone {{ $index + 1 }} name" wire:model="form.milestones.{{ $index }}.name">@error("form.milestones.{$index}.name") <small class="text-danger">{{ $message }}</small> @enderror</td>
                                        <td><input type="date" class="form-control" aria-label="Milestone {{ $index + 1 }} planned date" wire:model="form.milestones.{{ $index }}.planned_date">@error("form.milestones.{$index}.planned_date") <small class="text-danger">{{ $message }}</small> @enderror</td>
                                        <td><input type="date" class="form-control" aria-label="Milestone {{ $index + 1 }} actual or forecast date" wire:model="form.milestones.{{ $index }}.actual_forecast_date">@error("form.milestones.{$index}.actual_forecast_date") <small class="text-danger">{{ $message }}</small> @enderror</td>
                                        <td><input type="number" min="0" max="100" step="1" class="form-control" aria-label="Milestone {{ $index + 1 }} completion percentage" wire:model="form.milestones.{{ $index }}.completion_percent">@error("form.milestones.{$index}.completion_percent") <small class="text-danger">{{ $message }}</small> @enderror</td>
                                        <td><select class="form-control" aria-label="Milestone {{ $index + 1 }} status" wire:model="form.milestones.{{ $index }}.status"><option value="">Select status</option>@foreach (\App\Livewire\RealEstateProjects\Progress::MILESTONE_STATUSES as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach</select>@error("form.milestones.{$index}.status") <small class="text-danger">{{ $message }}</small> @enderror</td>
                                        <td>@can('real_estate_projects.update')<button type="button" class="btn btn-outline-danger btn-sm" wire:click="removeMilestone({{ $index }})">Remove</button>@endcan</td>
                                    </tr>
                                    <tr wire:key="progress-milestone-followup-{{ $index }}"><td></td><td colspan="6">
                                        <div class="row">
                                            <div class="col-md-3 form-group"><label for="progress-milestone-reason-{{ $index }}">Reason if Delayed / Changed</label><input id="progress-milestone-reason-{{ $index }}" type="text" class="form-control" wire:model="form.milestones.{{ $index }}.delay_reason">@error("form.milestones.{$index}.delay_reason") <small class="text-danger">{{ $message }}</small> @enderror</div>
                                            <div class="col-md-3 form-group"><label for="progress-milestone-action-{{ $index }}">Follow-up Action</label><input id="progress-milestone-action-{{ $index }}" type="text" class="form-control" wire:model="form.milestones.{{ $index }}.follow_up_action">@error("form.milestones.{$index}.follow_up_action") <small class="text-danger">{{ $message }}</small> @enderror</div>
                                            <div class="col-md-3 form-group"><label for="progress-milestone-responsible-{{ $index }}">Responsible Unit / Officer</label><input id="progress-milestone-responsible-{{ $index }}" type="text" class="form-control" wire:model="form.milestones.{{ $index }}.follow_up_responsible">@error("form.milestones.{$index}.follow_up_responsible") <small class="text-danger">{{ $message }}</small> @enderror</div>
                                            <div class="col-md-3 form-group"><label for="progress-milestone-due-{{ $index }}">Action Due Date</label><input id="progress-milestone-due-{{ $index }}" type="date" class="form-control" wire:model="form.milestones.{{ $index }}.follow_up_due_date">@error("form.milestones.{$index}.follow_up_due_date") <small class="text-danger">{{ $message }}</small> @enderror</div>
                                        </div>
                                    </td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @can('real_estate_projects.update')<button type="button" class="btn btn-outline-secondary" wire:click="addMilestone">Add Milestone</button>@endcan
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">E. Cost and Budget Position</h5></div>
                <div class="card-body">
                    <p class="text-muted">Use verified budget, contract, and financial records. Paid means confirmed payment, not a payment request. Amounts here are separate measures and are not added together.</p>
                    @if($project->budgetEstimate?->total_estimated_cost !== null)
                        <div class="alert alert-light border">Budget & Costing estimate for reference: ZMW {{ number_format((float) $project->budgetEstimate->total_estimated_cost, 2) }}. Verify figures against approved records before entering them below.</div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th>Cost Information</th><th style="min-width: 190px;">Amount (ZMW)</th><th style="min-width: 270px;">Source / Reference</th></tr></thead>
                            <tbody>
                                @foreach (\App\Livewire\RealEstateProjects\Progress::COST_ITEMS as $key => $label)
                                    <tr><td>{{ $label }}</td><td><input type="number" min="0" step="0.01" class="form-control" aria-label="{{ $label }} amount" wire:model="form.costs.{{ $key }}.amount">@error("form.costs.{$key}.amount") <small class="text-danger">{{ $message }}</small> @enderror</td><td><input type="text" class="form-control" aria-label="{{ $label }} source or reference" wire:model="form.costs.{{ $key }}.source">@error("form.costs.{$key}.source") <small class="text-danger">{{ $message }}</small> @enderror</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group"><label for="progress-budget-position">Budget Position</label><select id="progress-budget-position" class="form-control" wire:model="form.budget_position"><option value="">Select one position</option>@foreach (['Within budget', 'Potential overrun', 'Over budget', 'Not yet established', 'Information unavailable'] as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach</select>@error('form.budget_position') <small class="text-danger">{{ $message }}</small> @enderror</div>
                        <div class="col-md-6 form-group"><label for="progress-pending-cost">Unapproved or Pending Cost Changes</label><select id="progress-pending-cost" class="form-control" wire:model="form.pending_cost_changes"><option value="">Select one option</option>@foreach (['None identified', 'Yes', 'Not confirmed'] as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach</select>@error('form.pending_cost_changes') <small class="text-danger">{{ $message }}</small> @enderror</div>
                        <div class="col-12 form-group"><label for="progress-cost-details">Cost Changes or Budget Concerns</label><textarea id="progress-cost-details" class="form-control" rows="2" wire:model="form.cost_change_details"></textarea>@error('form.cost_change_details') <small class="text-danger">{{ $message }}</small> @enderror</div>
                    </div>
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">F. Delays, Issues and Required Actions</h5></div>
                <div class="card-body">
                    <label class="d-block">Issue Types (select all that apply)</label>
                    <div class="row mb-2">
                        @foreach (\App\Livewire\RealEstateProjects\Progress::ISSUE_TYPES as $index => $option)
                            <div class="col-md-4 form-check pl-4 mb-2"><input id="progress-issue-type-{{ $index }}" type="checkbox" class="form-check-input" value="{{ $option }}" wire:model.live="form.issue_types"><label class="form-check-label" for="progress-issue-type-{{ $index }}">{{ $option }}</label></div>
                        @endforeach
                    </div>
                    @error('form.issue_types') <small class="text-danger d-block">{{ $message }}</small> @enderror
                    @if(in_array('Other', $form['issue_types'] ?? [], true))
                        <div class="form-group"><label for="progress-issue-other">Other Issue Type</label><input id="progress-issue-other" type="text" class="form-control" wire:model="form.issue_type_other">@error('form.issue_type_other') <small class="text-danger">{{ $message }}</small> @enderror</div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th style="min-width: 220px;">Issue / Cause</th><th style="min-width: 220px;">Effect on Cost, Time or Operations</th><th style="min-width: 220px;">Action Required</th><th style="min-width: 190px;">Responsible Unit / Officer</th><th style="min-width: 155px;">Target Date</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($form['issues'] ?? [] as $index => $issue)
                                    <tr wire:key="progress-issue-{{ $index }}">
                                        @foreach (['cause', 'effect', 'action', 'responsible'] as $field)
                                            <td><textarea class="form-control" rows="2" aria-label="Issue {{ $index + 1 }} {{ $field }}" wire:model="form.issues.{{ $index }}.{{ $field }}"></textarea>@error("form.issues.{$index}.{$field}") <small class="text-danger">{{ $message }}</small> @enderror</td>
                                        @endforeach
                                        <td><input type="date" class="form-control" aria-label="Issue {{ $index + 1 }} target date" wire:model="form.issues.{{ $index }}.target_date">@error("form.issues.{$index}.target_date") <small class="text-danger">{{ $message }}</small> @enderror</td>
                                        <td>@can('real_estate_projects.update')<button type="button" class="btn btn-outline-danger btn-sm" wire:click="removeIssue({{ $index }})">Remove</button>@endcan</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @can('real_estate_projects.update')<button type="button" class="btn btn-outline-secondary" wire:click="addIssue">Add Issue</button>@endcan
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">G. Risks and Safety Matters</h5></div>
                <div class="card-body">
                    @foreach (['new_changed_risks' => 'New or Changed Project Risks', 'safety_incident' => 'Safety / Environmental Incident or Concern', 'urgent_intervention' => 'Work Requiring Urgent Intervention'] as $field => $label)
                        <div class="form-group"><label for="progress-{{ $field }}">{{ $label }}</label><select id="progress-{{ $field }}" class="form-control" wire:model="form.{{ $field }}"><option value="">Select one option</option><option value="No">No</option><option value="Yes">Yes</option></select>@error("form.{$field}") <small class="text-danger">{{ $message }}</small> @enderror</div>
                    @endforeach
                    <div class="form-group"><label for="progress-safety-details">Risk, Safety or Environmental Matters Requiring Explanation</label><textarea id="progress-safety-details" class="form-control" rows="3" wire:model="form.safety_details"></textarea>@error('form.safety_details') <small class="text-danger">{{ $message }}</small> @enderror</div>
                    <p class="text-muted small mb-0">Update the Project Risks tab for new or changed risks. Notify the responsible SHEQ team separately for safety or environmental incidents.</p>
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">H. Quality, Inspections and Completion Evidence</h5></div>
                <div class="card-body row">
                    @foreach ([
                        'quality_inspection' => ['Quality / Technical Inspection During Period', ['Conducted and satisfactory', 'Conducted with observations', 'Not conducted', 'Not applicable']],
                        'outstanding_defects' => ['Outstanding Defects or Incomplete Works', ['None identified', 'Identified and being addressed', 'Identified; action pending', 'Not assessed']],
                        'photos_evidence' => ['Photographs / Progress Evidence Attached', ['Yes', 'No']],
                        'handover_documentation' => ['Completion / Handover Documentation', ['Attached', 'Pending', 'Not applicable']],
                    ] as $field => [$label, $options])
                        <div class="col-md-6 form-group"><label for="progress-{{ $field }}">{{ $label }}</label><select id="progress-{{ $field }}" class="form-control" wire:model="form.{{ $field }}"><option value="">Select one option</option>@foreach ($options as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach</select>@error("form.{$field}") <small class="text-danger">{{ $message }}</small> @enderror</div>
                    @endforeach
                    <div class="col-md-6 form-group"><label for="progress-inspection-reference">Inspection / Test Record Reference</label><input id="progress-inspection-reference" type="text" class="form-control" wire:model="form.inspection_reference">@error('form.inspection_reference') <small class="text-danger">{{ $message }}</small> @enderror</div>
                    <div class="col-12 form-group"><label for="progress-quality-details">Outstanding Defects or Quality Concerns</label><textarea id="progress-quality-details" class="form-control" rows="3" wire:model="form.quality_details"></textarea>@error('form.quality_details') <small class="text-danger">{{ $message }}</small> @enderror</div>
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">I. Support or Decisions Required</h5></div>
                <div class="card-body">
                    <div class="table-responsive"><table class="table table-bordered"><thead><tr><th style="min-width: 300px;">Required Support / Decision</th><th style="min-width: 240px;">Responsible Department / Authority</th><th style="min-width: 160px;">Date Needed</th><th></th></tr></thead><tbody>
                        @foreach ($form['support_decisions'] ?? [] as $index => $decision)
                            <tr wire:key="progress-support-{{ $index }}"><td><textarea class="form-control" rows="2" aria-label="Support {{ $index + 1 }} decision" wire:model="form.support_decisions.{{ $index }}.decision"></textarea>@error("form.support_decisions.{$index}.decision") <small class="text-danger">{{ $message }}</small> @enderror</td><td><input type="text" class="form-control" aria-label="Support {{ $index + 1 }} responsible authority" wire:model="form.support_decisions.{{ $index }}.responsible">@error("form.support_decisions.{$index}.responsible") <small class="text-danger">{{ $message }}</small> @enderror</td><td><input type="date" class="form-control" aria-label="Support {{ $index + 1 }} date needed" wire:model="form.support_decisions.{{ $index }}.date_needed">@error("form.support_decisions.{$index}.date_needed") <small class="text-danger">{{ $message }}</small> @enderror</td><td>@can('real_estate_projects.update')<button type="button" class="btn btn-outline-danger btn-sm" wire:click="removeSupportDecision({{ $index }})">Remove</button>@endcan</td></tr>
                        @endforeach
                    </tbody></table></div>
                    @can('real_estate_projects.update')<button type="button" class="btn btn-outline-secondary" wire:click="addSupportDecision">Add Support / Decision</button>@endcan
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">J. Report Preparation and Review</h5></div>
                <div class="card-body">
                    <p class="text-muted">Typed signatures record preparation or review. They do not approve expenditure, variations, revised dates, or handover.</p>
                    <div class="table-responsive"><table class="table table-bordered"><thead><tr><th style="min-width: 180px;">Record</th><th style="min-width: 230px;">Name / Designation</th><th style="min-width: 190px;">Signature (Typed Name)</th><th style="min-width: 160px;">Date</th></tr></thead><tbody>
                        @foreach (\App\Livewire\RealEstateProjects\Progress::REVIEW_ROLES as $key => $label)
                            <tr><td>{{ $label }}</td><td><input type="text" class="form-control" aria-label="{{ $label }} name and designation" wire:model="form.reviews.{{ $key }}.name_designation">@error("form.reviews.{$key}.name_designation") <small class="text-danger">{{ $message }}</small> @enderror</td><td><input type="text" class="form-control" aria-label="{{ $label }} typed signature" wire:model="form.reviews.{{ $key }}.signature">@error("form.reviews.{$key}.signature") <small class="text-danger">{{ $message }}</small> @enderror</td><td><input type="date" class="form-control" aria-label="{{ $label }} date" wire:model="form.reviews.{{ $key }}.date">@error("form.reviews.{$key}.date") <small class="text-danger">{{ $message }}</small> @enderror</td></tr>
                        @endforeach
                    </tbody></table></div>
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">K. Attachments</h5></div>
                <div class="card-body">
                    <p class="text-muted small">Tick the supporting records provided. This checklist does not upload files.</p>
                    <div class="row">
                        @foreach (\App\Livewire\RealEstateProjects\Progress::ATTACHMENTS as $index => $attachment)
                            <div class="col-md-4 form-check pl-4 mb-2"><input id="progress-attachment-{{ $index }}" type="checkbox" class="form-check-input" value="{{ $attachment }}" wire:model.live="form.attachments"><label class="form-check-label" for="progress-attachment-{{ $index }}">{{ $attachment }}</label></div>
                        @endforeach
                    </div>
                    @error('form.attachments') <small class="text-danger d-block">{{ $message }}</small> @enderror
                    @if(in_array('Other', $form['attachments'] ?? [], true))
                        <div class="form-group"><label for="progress-other-attachment">Other Supporting Record</label><input id="progress-other-attachment" type="text" class="form-control" wire:model="form.attachment_other">@error('form.attachment_other') <small class="text-danger">{{ $message }}</small> @enderror</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">The report was not saved. Check the marked fields above.</div>
                    @endif
                    @can('real_estate_projects.update')
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">{{ $editingReportId ? 'Save Changes to This Report' : 'Save New Progress Report' }}</button>
                    @endcan
                </div>
            </section>
        </fieldset>
    </form>
</div>
