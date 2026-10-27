<div>
    @php
        $moneyUnit = ($form['estimate_currency'] ?? 'ZMW') === 'Other'
            ? ($form['currency_other'] ?: 'selected currency')
            : 'ZMW';
        $proposal = $project->proposal_data ?? [];
    @endphp

    @if(session()->has('budget_success'))
        <div class="alert alert-success" role="status">{{ session('budget_success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">Check the marked budget fields before saving.</div>
    @endif

    <form wire:submit.prevent="save">
        <fieldset @disabled(! auth()->user()?->can('real_estate_projects.update'))>
            <section class="card">
                <div class="card-header"><h5 class="mb-0">A. Project Details</h5></div>
                <div class="card-body">
                    <p class="text-muted">Project and proposal details are filled in as a starting point. Confirm them for this estimate.</p>
                    <div class="row">
                        @foreach ([
                            'project_title' => 'Project Title',
                            'project_reference' => 'Project Reference Number',
                            'property_site_name' => 'Property / Site Name',
                            'building_structure_name' => 'Building / Structure Name (if applicable)',
                            'town_province' => 'Town / Province',
                            'requesting_department' => 'Requesting Department / User Unit',
                            'estimate_prepared_by' => 'Cost Estimate Prepared By (Name and Department)',
                            'revision_number' => 'Estimate Revision Number',
                        ] as $field => $label)
                            <div class="col-md-6 form-group">
                                <label for="budget-{{ $field }}">{{ $label }}</label>
                                <input id="budget-{{ $field }}" type="text" class="form-control" wire:model="form.{{ $field }}">
                                @error("form.{$field}") <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        @endforeach
                        <div class="col-md-6 form-group">
                            <label for="budget-estimate-date">Date of Estimate</label>
                            <input id="budget-estimate-date" type="date" class="form-control" wire:model="form.estimate_date">
                            @error('form.estimate_date') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-12 form-group">
                            <label for="budget-project-description">Project Description (Brief)</label>
                            <textarea id="budget-project-description" class="form-control" rows="2" wire:model="form.project_description"></textarea>
                            @error('form.project_description') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    @if($project->indicative_cost !== null || filled($proposal['funding_information'] ?? null))
                        <div class="alert alert-light border mb-0">
                            <strong>Proposal reference</strong>
                            @if($project->indicative_cost !== null)
                                <div>Indicative proposal cost: ZMW {{ number_format((float) $project->indicative_cost, 2) }}</div>
                            @endif
                            @if(filled($proposal['funding_information'] ?? null) && is_scalar($proposal['funding_information']))
                                <div>Funding information: {{ $proposal['funding_information'] }}</div>
                            @endif
                        </div>
                    @endif
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">B. Estimate Classification and Basis</h5></div>
                <div class="card-body">
                    @php
                        $classification = [
                            'estimate_stage' => ['Stage of Estimate', ['Preliminary / Concept', 'Feasibility', 'Detailed / BOQ-Based', 'Revised Estimate']],
                            'estimate_method' => ['Method Used', ['Measured Quantities and Rates', 'Supplier Quotations', 'Benchmark / Similar Works', 'Other']],
                            'price_basis' => ['Price Basis', ['Current Market Rates', 'Recent Contract Rates', 'Budget Quotations', 'Other']],
                            'estimate_currency' => ['Estimate Currency', ['ZMW', 'Other']],
                            'vat_treatment' => ['VAT Treatment', ['Excluding VAT', 'Including VAT', 'Not Yet Determined']],
                        ];
                    @endphp
                    <div class="row">
                        @foreach ($classification as $field => [$label, $options])
                            <div class="col-md-6 form-group">
                                <label for="budget-{{ $field }}">{{ $label }}</label>
                                <select id="budget-{{ $field }}" class="form-control" wire:model.live="form.{{ $field }}">
                                    <option value="">Select one option</option>
                                    @foreach ($options as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach
                                </select>
                                @error("form.{$field}") <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        @endforeach
                        @foreach (['estimate_method' => ['estimate_method_other', 'Other method'], 'price_basis' => ['price_basis_other', 'Other price basis'], 'estimate_currency' => ['currency_other', 'Other currency']] as $parent => [$field, $label])
                            @if(($form[$parent] ?? '') === 'Other')
                                <div class="col-md-6 form-group">
                                    <label for="budget-{{ $field }}">{{ $label }}</label>
                                    <input id="budget-{{ $field }}" type="text" class="form-control" wire:model.live.debounce.500ms="form.{{ $field }}">
                                    @error("form.{$field}") <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            @endif
                        @endforeach
                        <div class="col-md-6 form-group">
                            <label for="budget-price-reference-date">Price Reference Date</label>
                            <input id="budget-price-reference-date" type="date" class="form-control" wire:model="form.price_reference_date">
                            @error('form.price_reference_date') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        @foreach (['scope_reference' => 'Drawing / BOQ / Scope Reference', 'estimated_duration' => 'Estimated Project Duration', 'expected_works_location' => 'Expected Location of Works'] as $field => $label)
                            <div class="col-md-6 form-group">
                                <label for="budget-{{ $field }}">{{ $label }}</label>
                                <input id="budget-{{ $field }}" type="text" class="form-control" wire:model="form.{{ $field }}">
                                @error("form.{$field}") <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">C. Itemised Project Cost Estimate</h5></div>
                <div class="card-body">
                    <p class="text-muted">Summarise major work components here. Enter quantity and rate together to calculate each amount. A detailed BOQ can be recorded in the checklist below.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th style="min-width: 55px;">No.</th><th style="min-width: 260px;">Work Component / Description</th><th style="min-width: 100px;">Qty</th><th style="min-width: 100px;">Unit</th><th style="min-width: 145px;">Rate ({{ $moneyUnit }})</th><th style="min-width: 145px;">Amount ({{ $moneyUnit }})</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($form['itemized_costs'] ?? [] as $index => $item)
                                    <tr wire:key="budget-item-{{ $index }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td><textarea class="form-control" rows="2" aria-label="Item {{ $index + 1 }} description" wire:model="form.itemized_costs.{{ $index }}.description"></textarea>@error("form.itemized_costs.{$index}.description") <small class="text-danger">{{ $message }}</small> @enderror</td>
                                        <td><input type="number" min="0" step="any" class="form-control" aria-label="Item {{ $index + 1 }} quantity" wire:model.live.debounce.500ms="form.itemized_costs.{{ $index }}.quantity">@error("form.itemized_costs.{$index}.quantity") <small class="text-danger">{{ $message }}</small> @enderror</td>
                                        <td><input type="text" class="form-control" aria-label="Item {{ $index + 1 }} unit" wire:model="form.itemized_costs.{{ $index }}.unit">@error("form.itemized_costs.{$index}.unit") <small class="text-danger">{{ $message }}</small> @enderror</td>
                                        <td><input type="number" min="0" step="0.01" class="form-control" aria-label="Item {{ $index + 1 }} rate" wire:model.live.debounce.500ms="form.itemized_costs.{{ $index }}.rate">@error("form.itemized_costs.{$index}.rate") <small class="text-danger">{{ $message }}</small> @enderror</td>
                                        <td class="text-right">{{ $itemAmounts[$index] === null ? '—' : number_format($itemAmounts[$index], 2) }}</td>
                                        <td>@can('real_estate_projects.update')<button type="button" class="btn btn-outline-danger btn-sm" aria-label="Remove cost item {{ $index + 1 }}" wire:click="removeCostItem({{ $index }})">Remove</button>@endcan</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot><tr><th colspan="5" class="text-right">Itemised subtotal</th><th class="text-right">{{ $hasItemAmount ? number_format($itemizedSubtotal, 2) : '—' }}</th><th></th></tr></tfoot>
                        </table>
                    </div>
                    @error('form.itemized_costs') <small class="text-danger d-block mb-2">{{ $message }}</small> @enderror
                    @can('real_estate_projects.update')<button type="button" class="btn btn-outline-secondary mb-3" wire:click="addCostItem">Add Cost Item</button>@endcan
                    <div class="form-group mb-0">
                        <label for="budget-boq-attached">Additional detailed costing sheet / BOQ attached</label>
                        <select id="budget-boq-attached" class="form-control" wire:model="form.boq_attached">
                            <option value="">Select one option</option>
                            @foreach (['Yes', 'No', 'Not applicable'] as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach
                        </select>
                        @error('form.boq_attached') <small class="text-danger">{{ $message }}</small> @enderror
                        <small class="form-text text-muted">This field records the document status; it does not upload a BOQ.</small>
                    </div>
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">D. Cost Summary</h5></div>
                <div class="card-body">
                    <p class="text-muted">Enter each category once. The subtotal and total are calculated from these category amounts, contingency, and taxes. Check them against the itemised subtotal above to avoid double counting.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th>Cost Category</th><th style="min-width: 210px;">Estimated Amount ({{ $moneyUnit }})</th></tr></thead>
                            <tbody>
                                @foreach (\App\Livewire\RealEstateProjects\Budget::COST_CATEGORIES as $key => $label)
                                    <tr><td>{{ $loop->iteration }}. {{ $label }}</td><td><input type="number" min="0" step="0.01" class="form-control" aria-label="{{ $label }} amount" wire:model.live.debounce.500ms="form.cost_categories.{{ $key }}">@error("form.cost_categories.{$key}") <small class="text-danger">{{ $message }}</small> @enderror</td></tr>
                                @endforeach
                                <tr><th>7. Subtotal Before Contingency and Tax</th><th class="text-right">{{ $hasSummaryAmount ? number_format($summarySubtotal, 2) : '—' }}</th></tr>
                                <tr><td>8. Contingency Allowance</td><td><input type="number" min="0" step="0.01" class="form-control" aria-label="Contingency allowance" wire:model.live.debounce.500ms="form.contingency">@error('form.contingency') <small class="text-danger">{{ $message }}</small> @enderror</td></tr>
                                <tr><td>9. VAT / Other Applicable Taxes</td><td><input type="number" min="0" step="0.01" class="form-control" aria-label="VAT and other applicable taxes" wire:model.live.debounce.500ms="form.taxes">@error('form.taxes') <small class="text-danger">{{ $message }}</small> @enderror</td></tr>
                                <tr class="table-light"><th>10. TOTAL ESTIMATED PROJECT COST</th><th class="text-right">{{ $hasTotal ? number_format($total, 2) : '—' }}</th></tr>
                            </tbody>
                        </table>
                    </div>
                    @if($hasItemAmount && $hasSummaryAmount && abs($itemizedSubtotal - $summarySubtotal) > 0.01)
                        <div class="alert alert-warning mb-0">The itemised subtotal and cost-category subtotal differ by {{ $moneyUnit }} {{ number_format(abs($itemizedSubtotal - $summarySubtotal), 2) }}. Check whether the item list is partial or a cost has been counted twice.</div>
                    @endif
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">E. Estimate Assumptions and Exclusions</h5></div>
                <div class="card-body row">
                    @foreach (['major_assumptions' => 'Major Assumptions', 'excluded_items' => 'Items Excluded from Estimate', 'provisional_sums' => 'Provisional Sums / Uncertain Costs', 'quotation_validity' => 'Quotation Validity / Price Escalation Considerations'] as $field => $label)
                        <div class="col-md-6 form-group"><label for="budget-{{ $field }}">{{ $label }}</label><textarea id="budget-{{ $field }}" class="form-control" rows="3" wire:model="form.{{ $field }}"></textarea>@error("form.{$field}") <small class="text-danger">{{ $message }}</small> @enderror</div>
                    @endforeach
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">F. Budget and Funding Position</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group"><label for="budget-funding-status">Funding Status</label><select id="budget-funding-status" class="form-control" wire:model="form.funding_status"><option value="">Select one option</option><option value="Budget Allocated">Budget Allocated</option><option value="No Budget Identified">No Budget Identified</option></select>@error('form.funding_status') <small class="text-danger">{{ $message }}</small> @enderror</div>
                        @foreach (['financial_year' => 'Financial Year', 'budget_reference' => 'Budget / Cost Centre Reference', 'budget_information_source' => 'Source of Budget Information'] as $field => $label)
                            <div class="col-md-6 form-group"><label for="budget-{{ $field }}">{{ $label }}</label><input id="budget-{{ $field }}" type="text" class="form-control" wire:model="form.{{ $field }}">@error("form.{$field}") <small class="text-danger">{{ $message }}</small> @enderror</div>
                        @endforeach
                        @foreach (['amount_allocated' => 'Amount Currently Allocated', 'funding_gap' => 'Estimated Funding Gap (if known)'] as $field => $label)
                            <div class="col-md-6 form-group"><label for="budget-{{ $field }}">{{ $label }} ({{ $moneyUnit }})</label><input id="budget-{{ $field }}" type="number" min="0" step="0.01" class="form-control" wire:model="form.{{ $field }}">@error("form.{$field}") <small class="text-danger">{{ $message }}</small> @enderror</div>
                        @endforeach
                    </div>
                    <p class="text-muted small mb-0">Budget allocation is recorded for planning only. Saving this form does not authorise procurement, expenditure, or implementation.</p>
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">G. Cost Risks and Qualifications</h5></div>
                <div class="card-body">
                    @foreach (\App\Livewire\RealEstateProjects\Budget::COST_RISKS as $key => $label)
                        <div class="row border-bottom py-2">
                            <div class="col-md-4"><label for="budget-risk-{{ $key }}">{{ $label }}</label></div>
                            <div class="col-md-3"><select id="budget-risk-{{ $key }}" class="form-control" wire:model="form.cost_risks.{{ $key }}.status"><option value="">Select one option</option>@foreach (['Yes', 'No', 'Not known'] as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach</select>@error("form.cost_risks.{$key}.status") <small class="text-danger">{{ $message }}</small> @enderror</div>
                            <div class="col-md-5"><input type="text" class="form-control" placeholder="Assessment / provision" aria-label="{{ $label }} details" wire:model="form.cost_risks.{{ $key }}.details">@error("form.cost_risks.{$key}.details") <small class="text-danger">{{ $message }}</small> @enderror</div>
                        </div>
                    @endforeach
                    <div class="form-group mt-3 mb-0"><label for="budget-other-cost-risk">Other Material Cost Risk</label><textarea id="budget-other-cost-risk" class="form-control" rows="2" wire:model="form.other_cost_risk"></textarea>@error('form.other_cost_risk') <small class="text-danger">{{ $message }}</small> @enderror</div>
                </div>
            </section>

            <section class="card mt-3">
                <div class="card-header"><h5 class="mb-0">H. Estimate Review and Record</h5></div>
                <div class="card-body">
                    <div class="row">
                        @foreach (['record_prepared_by' => 'Prepared By', 'prepared_designation_department' => 'Prepared By — Designation / Department', 'prepared_signature' => 'Signature (typed name)', 'reviewed_by' => 'Reviewed By', 'reviewed_designation_department' => 'Reviewer Designation / Department'] as $field => $label)
                            <div class="col-md-6 form-group"><label for="budget-{{ $field }}">{{ $label }}</label><input id="budget-{{ $field }}" type="text" class="form-control" wire:model="form.{{ $field }}">@error("form.{$field}") <small class="text-danger">{{ $message }}</small> @enderror</div>
                        @endforeach
                        @foreach (['prepared_date' => 'Prepared Date', 'review_date' => 'Review Date'] as $field => $label)
                            <div class="col-md-6 form-group"><label for="budget-{{ $field }}">{{ $label }}</label><input id="budget-{{ $field }}" type="date" class="form-control" wire:model="form.{{ $field }}">@error("form.{$field}") <small class="text-danger">{{ $message }}</small> @enderror</div>
                        @endforeach
                        <div class="col-12 form-group"><label for="budget-review-comments">Review Comments / Corrections</label><textarea id="budget-review-comments" class="form-control" rows="3" wire:model="form.review_comments"></textarea>@error('form.review_comments') <small class="text-danger">{{ $message }}</small> @enderror</div>
                    </div>
                    <div class="form-group">
                        <label class="d-block">Supporting Documents Attached</label>
                        <p class="text-muted small">Tick documents provided with the estimate. This checklist does not upload files.</p>
                        <div class="row">
                            @foreach (\App\Livewire\RealEstateProjects\Budget::SUPPORTING_DOCUMENTS as $index => $document)
                                <div class="col-md-4 form-check pl-4 mb-2"><input id="budget-document-{{ $index }}" type="checkbox" class="form-check-input" value="{{ $document }}" wire:model.live="form.supporting_documents"><label class="form-check-label" for="budget-document-{{ $index }}">{{ $document }}</label></div>
                            @endforeach
                        </div>
                        @error('form.supporting_documents') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    @if(in_array('Other', $form['supporting_documents'] ?? [], true))
                        <div class="form-group"><label for="budget-other-document">Other Supporting Document</label><input id="budget-other-document" type="text" class="form-control" wire:model="form.other_supporting_document">@error('form.other_supporting_document') <small class="text-danger">{{ $message }}</small> @enderror</div>
                    @endif
                    <p class="text-muted small">Review confirms that the estimate has been checked for its stated purpose. It is not approval to proceed with the project.</p>
                    @can('real_estate_projects.update')
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">Save Budget Estimate</button>
                    @endcan
                </div>
            </section>
        </fieldset>
    </form>
</div>
