<div>
    @if(session()->has('risks_success'))
        <div class="alert alert-success" role="status">{{ session('risks_success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">Check the marked risk assessment fields before saving.</div>
    @endif

    <form wire:submit.prevent="save">
        <fieldset @disabled(! auth()->user()?->can('real_estate_projects.update'))>
            <section class="card">
                <div class="card-header"><h5 class="mb-0">A. Project Information</h5></div>
                <div class="card-body">
                    <p class="text-muted">Project and proposal information is filled in as a draft. Confirm it for this assessment.</p>
                    <div class="row">
                        @foreach ([
                            'project_title' => 'Project Title',
                            'project_reference' => 'Project Reference Number',
                            'property_site_name' => 'Property / Site Name',
                            'building_structure_name' => 'Building / Structure Name',
                            'project_location' => 'Project Location',
                            'assessment_prepared_by' => 'Assessment Prepared By (Name and Designation)',
                            'responsible_department' => 'Responsible Department / User Unit',
                        ] as $field => $label)
                            <div class="col-md-6 form-group">
                                <label for="risk-{{ $field }}">{{ $label }}</label>
                                <input id="risk-{{ $field }}" type="text" class="form-control" wire:model="form.{{ $field }}">
                                @error("form.{$field}") <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        @endforeach
                        <div class="col-md-6 form-group">
                            <label for="risk-project-stage">Project Stage</label>
                            <select id="risk-project-stage" class="form-control" wire:model.live="form.project_stage">
                                <option value="">Select one stage</option>
                                @foreach (\App\Livewire\RealEstateProjects\Risks::STAGES as $stage)
                                    <option value="{{ $stage }}">{{ $stage }}</option>
                                @endforeach
                            </select>
                            @error('form.project_stage') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        @if(($form['project_stage'] ?? '') === 'Other')
                            <div class="col-md-6 form-group">
                                <label for="risk-other-stage">Other Project Stage</label>
                                <input id="risk-other-stage" type="text" class="form-control" wire:model="form.other_stage">
                                @error('form.other_stage') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        @endif
                        @foreach (['assessment_date' => 'Assessment Date', 'assessment_review_date' => 'Assessment Review Date'] as $field => $label)
                            <div class="col-md-6 form-group">
                                <label for="risk-{{ $field }}">{{ $label }}</label>
                                <input id="risk-{{ $field }}" type="date" class="form-control" wire:model="form.{{ $field }}">
                                @error("form.{$field}") <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">B. Risk Identification Checklist</h5></div>
                <div class="card-body">
                    <p class="text-muted">Mark areas of concern. A Yes selection does not replace a specific risk record in Section D.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th style="min-width: 190px;">Risk Area</th><th style="min-width: 340px;">Common Risks to Consider</th><th style="min-width: 145px;">Identified?</th></tr></thead>
                            <tbody>
                                @foreach (\App\Livewire\RealEstateProjects\Risks::RISK_AREAS as $key => [$label, $examples])
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td>{{ $examples }}</td>
                                        <td>
                                            <select class="form-control" aria-label="{{ $label }} identified" wire:model.live="form.checklist.{{ $key }}">
                                                <option value="">Select</option>
                                                <option value="Yes">Yes</option><option value="No">No</option><option value="N/A">N/A</option>
                                            </select>
                                            @error("form.checklist.{$key}") <small class="text-danger">{{ $message }}</small> @enderror
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(($form['checklist']['other'] ?? '') === 'Yes')
                        <div class="form-group mb-0">
                            <label for="risk-other-area">Other Risk Area</label>
                            <input id="risk-other-area" type="text" class="form-control" wire:model="form.other_risk_area">
                            @error('form.other_risk_area') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    @endif
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">C. Risk Evaluation Guide</h5></div>
                <div class="card-body">
                    <div class="alert alert-warning">This is the proposed scale from the supplied form. Confirm it against ZESCO's approved risk management and SHEQ framework before formal use. Serious safety or statutory concerns require specialist escalation regardless of score.</div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th>Score</th><th>Likelihood</th><th>Impact</th></tr></thead>
                            <tbody>
                                @foreach ([
                                    ['Rare — highly unlikely', 'Insignificant — negligible disruption or loss'],
                                    ['Unlikely — possible but not expected', 'Minor — manageable disruption or limited cost'],
                                    ['Possible — could reasonably occur', 'Moderate — material delay, cost or operational effect'],
                                    ['Likely — expected in many circumstances', 'Major — serious delay, cost, injury or disruption'],
                                    ['Almost certain — expected to occur', 'Severe — critical harm, major loss or project failure'],
                                ] as [$likelihood, $impact])
                                    <tr><td>{{ $loop->iteration }}</td><td>{{ $likelihood }}</td><td>{{ $impact }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="mb-0">Score = Likelihood × Impact. Proposed levels: <strong>1–4 Low</strong>, <strong>5–9 Moderate</strong>, <strong>10–16 High</strong>, <strong>17–25 Critical</strong>. Leave ratings blank where information is insufficient and refer the risk for specialist assessment.</p>
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">D. Individual Risk Assessment Records</h5></div>
                <div class="card-body">
                    <p class="text-muted">Add a detailed record for each significant risk. Risks from the feasibility assessment or proposal appear as drafts where available.</p>
                    @foreach ($form['risks'] ?? [] as $index => $risk)
                        @php
                            [$initialScore, $initialLevel] = \App\Livewire\RealEstateProjects\Risks::rating(
                                $risk['initial_likelihood'] ?? null,
                                $risk['initial_impact'] ?? null,
                                (bool) ($risk['initial_specialist_required'] ?? false)
                            );
                            [$residualScore, $residualLevel] = \App\Livewire\RealEstateProjects\Risks::rating(
                                $risk['expected_likelihood'] ?? null,
                                $risk['expected_impact'] ?? null,
                                (bool) ($risk['residual_reassess'] ?? false),
                                'To be reassessed'
                            );
                        @endphp
                        <details class="card mb-3" wire:key="project-risk-record-{{ $index }}" @if($index === 0) open @endif>
                            <summary class="card-header" style="cursor: pointer;">
                                <strong>Risk {{ $index + 1 }}:</strong> {{ filled($risk['specific_risk'] ?? null) ? \Illuminate\Support\Str::limit($risk['specific_risk'], 110) : 'New risk' }}
                            </summary>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 form-group"><label for="risk-ref-{{ $index }}">Risk Reference</label><input id="risk-ref-{{ $index }}" type="text" class="form-control" placeholder="R-{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}" wire:model.live.debounce.500ms="form.risks.{{ $index }}.reference">@error("form.risks.{$index}.reference") <small class="text-danger">{{ $message }}</small> @enderror</div>
                                    <div class="col-md-4 form-group"><label for="risk-category-{{ $index }}">Risk Category</label><select id="risk-category-{{ $index }}" class="form-control" wire:model="form.risks.{{ $index }}.category"><option value="">Select a category</option>@foreach (\App\Livewire\RealEstateProjects\Risks::RISK_AREAS as $key => [$label, $examples])<option value="{{ $key }}">{{ $label }}</option>@endforeach</select>@error("form.risks.{$index}.category") <small class="text-danger">{{ $message }}</small> @enderror</div>
                                    <div class="col-md-5 form-group"><label for="risk-specific-{{ $index }}">Specific Risk / Event</label><textarea id="risk-specific-{{ $index }}" class="form-control" rows="2" wire:model.live.debounce.500ms="form.risks.{{ $index }}.specific_risk"></textarea>@error("form.risks.{$index}.specific_risk") <small class="text-danger">{{ $message }}</small> @enderror</div>
                                    @foreach (['cause' => 'Main Cause or Trigger', 'effect' => 'Possible Effect on Project / People / Operations', 'existing_controls' => 'Existing Controls Already in Place'] as $field => $label)
                                        <div class="col-md-4 form-group"><label for="risk-{{ $field }}-{{ $index }}">{{ $label }}</label><textarea id="risk-{{ $field }}-{{ $index }}" class="form-control" rows="2" wire:model="form.risks.{{ $index }}.{{ $field }}"></textarea>@error("form.risks.{$index}.{$field}") <small class="text-danger">{{ $message }}</small> @enderror</div>
                                    @endforeach
                                </div>

                                <h6 class="mt-2">Initial Assessment</h6>
                                <div class="row align-items-end">
                                    @foreach (['initial_likelihood' => 'Likelihood (1–5)', 'initial_impact' => 'Impact (1–5)'] as $field => $label)
                                        <div class="col-md-3 form-group"><label for="risk-{{ $field }}-{{ $index }}">{{ $label }}</label><select id="risk-{{ $field }}-{{ $index }}" class="form-control" wire:model.live="form.risks.{{ $index }}.{{ $field }}" @disabled($risk['initial_specialist_required'] ?? false)><option value="">Not assessed</option>@for($value = 1; $value <= 5; $value++)<option value="{{ $value }}">{{ $value }}</option>@endfor</select>@error("form.risks.{$index}.{$field}") <small class="text-danger">{{ $message }}</small> @enderror</div>
                                    @endforeach
                                    <div class="col-md-3 form-group"><div class="small text-muted">Initial Score</div><strong>{{ $initialScore ?? '—' }}</strong></div>
                                    <div class="col-md-3 form-group"><div class="small text-muted">Initial Level</div><strong>{{ $initialLevel }}</strong></div>
                                    <div class="col-12 form-check pl-4 mb-3"><input id="risk-specialist-{{ $index }}" type="checkbox" class="form-check-input" wire:model.live="form.risks.{{ $index }}.initial_specialist_required"><label class="form-check-label" for="risk-specialist-{{ $index }}">Requires specialist assessment — do not assign a numerical rating yet</label></div>
                                </div>

                                <div class="row">
                                    <div class="col-12 form-group"><label for="risk-action-{{ $index }}">Additional Action Required</label><textarea id="risk-action-{{ $index }}" class="form-control" rows="2" wire:model.live.debounce.500ms="form.risks.{{ $index }}.action"></textarea>@error("form.risks.{$index}.action") <small class="text-danger">{{ $message }}</small> @enderror</div>
                                    <div class="col-md-6 form-group"><label for="risk-responsible-{{ $index }}">Responsible Officer / Department</label><input id="risk-responsible-{{ $index }}" type="text" class="form-control" wire:model.live.debounce.500ms="form.risks.{{ $index }}.responsible_officer">@error("form.risks.{$index}.responsible_officer") <small class="text-danger">{{ $message }}</small> @enderror</div>
                                    <div class="col-md-3 form-group"><label for="risk-target-{{ $index }}">Target Completion Date</label><input id="risk-target-{{ $index }}" type="date" class="form-control" wire:model.live="form.risks.{{ $index }}.target_date">@error("form.risks.{$index}.target_date") <small class="text-danger">{{ $message }}</small> @enderror</div>
                                    <div class="col-md-3 form-group"><label for="risk-resources-{{ $index }}">Resources / Support Required</label><input id="risk-resources-{{ $index }}" type="text" class="form-control" wire:model="form.risks.{{ $index }}.resources">@error("form.risks.{$index}.resources") <small class="text-danger">{{ $message }}</small> @enderror</div>
                                </div>

                                <h6 class="mt-2">Risk Position After Planned Controls</h6>
                                <div class="row align-items-end">
                                    @foreach (['expected_likelihood' => 'Expected Likelihood (1–5)', 'expected_impact' => 'Expected Impact (1–5)'] as $field => $label)
                                        <div class="col-md-3 form-group"><label for="risk-{{ $field }}-{{ $index }}">{{ $label }}</label><select id="risk-{{ $field }}-{{ $index }}" class="form-control" wire:model.live="form.risks.{{ $index }}.{{ $field }}" @disabled($risk['residual_reassess'] ?? false)><option value="">Not assessed</option>@for($value = 1; $value <= 5; $value++)<option value="{{ $value }}">{{ $value }}</option>@endfor</select>@error("form.risks.{$index}.{$field}") <small class="text-danger">{{ $message }}</small> @enderror</div>
                                    @endforeach
                                    <div class="col-md-3 form-group"><div class="small text-muted">Residual Score</div><strong>{{ $residualScore ?? '—' }}</strong></div>
                                    <div class="col-md-3 form-group"><div class="small text-muted">Residual Level</div><strong>{{ $residualLevel }}</strong></div>
                                    <div class="col-12 form-check pl-4 mb-3"><input id="risk-reassess-{{ $index }}" type="checkbox" class="form-check-input" wire:model.live="form.risks.{{ $index }}.residual_reassess"><label class="form-check-label" for="risk-reassess-{{ $index }}">Residual position to be reassessed</label></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group"><label for="risk-status-{{ $index }}">Action Status</label><select id="risk-status-{{ $index }}" class="form-control" wire:model.live="form.risks.{{ $index }}.action_status"><option value="">Select status</option>@foreach (['Not started', 'In progress', 'Completed', 'Overdue'] as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach</select>@error("form.risks.{$index}.action_status") <small class="text-danger">{{ $message }}</small> @enderror</div>
                                    <div class="col-md-4 form-group"><label for="risk-reviewed-{{ $index }}">Date Risk Last Reviewed</label><input id="risk-reviewed-{{ $index }}" type="date" class="form-control" wire:model="form.risks.{{ $index }}.last_reviewed">@error("form.risks.{$index}.last_reviewed") <small class="text-danger">{{ $message }}</small> @enderror</div>
                                    <div class="col-12 form-group"><label for="risk-evidence-{{ $index }}">Evidence / Remarks</label><textarea id="risk-evidence-{{ $index }}" class="form-control" rows="2" wire:model="form.risks.{{ $index }}.evidence"></textarea>@error("form.risks.{$index}.evidence") <small class="text-danger">{{ $message }}</small> @enderror</div>
                                </div>
                                @can('real_estate_projects.update')<button type="button" class="btn btn-outline-danger btn-sm" wire:click="removeRisk({{ $index }})">Remove Risk {{ $index + 1 }}</button>@endcan
                            </div>
                        </details>
                    @endforeach
                    @error('form.risks') <small class="text-danger d-block mb-2">{{ $message }}</small> @enderror
                    @can('real_estate_projects.update')<button type="button" class="btn btn-outline-secondary" wire:click="addRisk">Add Risk</button>@endcan
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">E. Consolidated Project Risk Register</h5></div>
                <div class="card-body">
                    <p class="text-muted">This summary is generated from the individual records above.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th>Risk ID</th><th style="min-width: 260px;">Risk Description</th><th>Initial Level</th><th style="min-width: 240px;">Required Control / Action</th><th>Responsible Officer</th><th>Due Date</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse ($register as $entry)
                                    <tr><td>{{ $entry['reference'] }}</td><td>{{ $entry['description'] }}</td><td>{{ $entry['level'] }}@if($entry['score'] !== null) ({{ $entry['score'] }})@endif</td><td>{{ $entry['action'] ?: '—' }}</td><td>{{ $entry['responsible_officer'] ?: '—' }}</td><td>{{ $entry['target_date'] ?: '—' }}</td><td>{{ $entry['action_status'] ?: '—' }}</td></tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted">No specific risks recorded yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">F. Overall Project Risk Position</h5></div>
                <div class="card-body">
                    <div class="form-group"><label for="risk-overall-position">Overall Assessment</label><select id="risk-overall-position" class="form-control" wire:model="form.overall_position"><option value="">Select one position</option>@foreach (\App\Livewire\RealEstateProjects\Risks::OVERALL_POSITIONS as $position)<option value="{{ $position }}">{{ $position }}</option>@endforeach</select>@error('form.overall_position') <small class="text-danger">{{ $message }}</small> @enderror</div>
                    @foreach (['main_outstanding_risks' => 'Main Outstanding Risks Requiring Attention', 'immediate_measures' => 'Immediate Protective Measures / Restrictions', 'priority_actions' => 'Priority Actions Before the Next Project Stage'] as $field => $label)
                        <div class="form-group"><label for="risk-{{ $field }}">{{ $label }}</label><textarea id="risk-{{ $field }}" class="form-control" rows="2" wire:model="form.{{ $field }}"></textarea>@error("form.{$field}") <small class="text-danger">{{ $message }}</small> @enderror</div>
                    @endforeach
                    <div class="form-group mb-0"><label for="risk-next-review">Next Risk Review Date</label><input id="risk-next-review" type="date" class="form-control" wire:model="form.next_review_date">@error('form.next_review_date') <small class="text-danger">{{ $message }}</small> @enderror</div>
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">G. Review and Acknowledgement</h5></div>
                <div class="card-body">
                    <p class="text-muted">Typed names record preparation or review. They do not approve implementation or expenditure.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th style="min-width: 175px;">Role</th><th style="min-width: 210px;">Name / Designation</th><th style="min-width: 180px;">Signature (Typed Name)</th><th style="min-width: 160px;">Date</th></tr></thead>
                            <tbody>
                                @foreach (\App\Livewire\RealEstateProjects\Risks::ACKNOWLEDGEMENT_ROLES as $key => $role)
                                    <tr><td>{{ $role }}</td><td><input type="text" class="form-control" aria-label="{{ $role }} name and designation" wire:model="form.acknowledgements.{{ $key }}.name_designation">@error("form.acknowledgements.{$key}.name_designation") <small class="text-danger">{{ $message }}</small> @enderror</td><td><input type="text" class="form-control" aria-label="{{ $role }} typed signature" wire:model="form.acknowledgements.{{ $key }}.signature">@error("form.acknowledgements.{$key}.signature") <small class="text-danger">{{ $message }}</small> @enderror</td><td><input type="date" class="form-control" aria-label="{{ $role }} date" wire:model="form.acknowledgements.{{ $key }}.date">@error("form.acknowledgements.{$key}.date") <small class="text-danger">{{ $message }}</small> @enderror</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">H. Supporting Records</h5></div>
                <div class="card-body">
                    <p class="text-muted small">Tick records provided with the assessment. This checklist does not upload files.</p>
                    <div class="row mb-3">
                        @foreach (\App\Livewire\RealEstateProjects\Risks::SUPPORTING_RECORDS as $index => $record)
                            <div class="col-md-4 form-check pl-4 mb-2"><input id="risk-document-{{ $index }}" type="checkbox" class="form-check-input" value="{{ $record }}" wire:model.live="form.supporting_records"><label class="form-check-label" for="risk-document-{{ $index }}">{{ $record }}</label></div>
                        @endforeach
                    </div>
                    @error('form.supporting_records') <small class="text-danger d-block">{{ $message }}</small> @enderror
                    @if(in_array('Other', $form['supporting_records'] ?? [], true))
                        <div class="form-group"><label for="risk-other-record">Other Supporting Record</label><input id="risk-other-record" type="text" class="form-control" wire:model="form.other_supporting_record">@error('form.other_supporting_record') <small class="text-danger">{{ $message }}</small> @enderror</div>
                    @endif
                    <div class="form-group"><label for="risk-document-references">Reference Numbers / Document Names</label><textarea id="risk-document-references" class="form-control" rows="2" wire:model="form.document_references"></textarea>@error('form.document_references') <small class="text-danger">{{ $message }}</small> @enderror</div>
                    @can('real_estate_projects.update')<button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">Save Risk Assessment</button>@endcan
                </div>
            </section>
        </fieldset>
    </form>
</div>
