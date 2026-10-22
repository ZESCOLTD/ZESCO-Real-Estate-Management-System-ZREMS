<div>
    <x-slot name="title">{{ $project?->reference_no ?? 'New Project Proposal' }}</x-slot>
    <x-slot name="pageHeader">Real Estate Project Proposal</x-slot>

    @php
        $interventions = [
            'New development',
            'Rehabilitation',
            'Office reconfiguration / partitioning',
            'Extension',
            'Conversion of use',
            'Infrastructure improvement',
            'Other',
        ];
        $landStatuses = ['ZESCO land', 'Proposed Acquisition', 'Other Arrangement'];
        $layoutOptions = ['Site Sketch', 'Site Plan', 'Concept Drawing', 'Photographs', 'None Yet'];
        $stakeholderOptions = ['ID&M / Civil', 'Finance', 'Procurement', 'SHEQ', 'Legal', 'Security', 'ICT', 'HCD / User unit', 'Insurance', 'Fibrecom', 'Valuation', 'Other'];
        $supportingOptions = ['Request / correspondence', 'Photographs', 'Inspection report', 'Sketch / layout', 'Existing drawing', 'Preliminary cost information', 'Property details', 'Other'];
        $nextStepOptions = ['Preliminary site assessment', 'Technical consultation', 'Options assessment', 'Feasibility study', 'Preliminary costing', 'Stakeholder engagement', 'Further information from requesting unit', 'Other'];
    @endphp

    <style>
        .project-proposal-section-header {
            background: {{ config('theme.color_primary') }};
            color: {{ config('theme.color_primary_text_light') }};
        }

        .project-proposal-section-header .text-muted {
            color: rgba(255, 255, 255, .8) !important;
        }
    </style>

    <div class="container-fluid px-0">
        @if(session()->has('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($project)
            <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                <div>
                    <div class="text-muted">{{ $project->reference_no }} · {{ $project->proposal_status }}</div>
                    <h3 class="mb-1">{{ $project->project_title ?: 'Untitled proposal' }}</h3>
                    <div class="text-muted">{{ $project->responsible_officer ?: 'Responsible officer not recorded' }}</div>
                </div>
                <a href="{{ route('real-estate-projects.show', $project) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Project workspace
                </a>
            </div>
        @else
            <div class="mb-3">
                <a href="{{ route('real-estate-projects.proposals') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fas fa-arrow-left mr-1"></i> Proposals
                </a>
                <p class="text-muted mb-0">This proposal records a property-related need for further consideration. It is not an application for project approval or authority to incur expenditure.</p>
            </div>
        @endif

        <form wire:submit="saveDraft">
            <section class="card mb-3">
                <div class="card-header project-proposal-section-header"><h5 class="mb-0">A. Proposal Details</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Proposal reference</label>
                            <input class="form-control" value="{{ $project?->reference_no ?? 'Assigned when draft is saved' }}" readonly>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="date_prepared">Date prepared</label>
                            <input id="date_prepared" type="date" class="form-control" wire:model="form.date_prepared">
                            @error('form.date_prepared') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group col-md-4">
                            <label for="proposed_project_title">Proposed project title</label>
                            <input id="proposed_project_title" class="form-control" wire:model="form.proposed_project_title">
                            @error('form.proposed_project_title') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="property_name">Property name</label>
                            <input id="property_name" class="form-control" wire:model="form.property_name">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="property_reference">Property reference (if available)</label>
                            <input id="property_reference" class="form-control" wire:model="form.property_reference">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="town_location">Town / location</label>
                            <input id="town_location" class="form-control" wire:model="form.town_location">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="building_structure_name">Building / Structure Name (if applicable)</label>
                            <input id="building_structure_name" class="form-control" wire:model="form.building_structure_name">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="originating_department">Originating department / user unit</label>
                            <input id="originating_department" class="form-control" wire:model="form.originating_department">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="contact_officer">Contact officer</label>
                            <input id="contact_officer" class="form-control" wire:model="form.contact_officer">
                        </div>
                        <div class="form-group col-12 mb-0">
                            <label for="real_estate_officer_preparing">Real Estate officer preparing proposal</label>
                            <input id="real_estate_officer_preparing" class="form-control" wire:model="form.real_estate_officer_preparing">
                        </div>
                    </div>
                </div>
            </section>

            <section class="card mb-3">
                <div class="card-header project-proposal-section-header"><h5 class="mb-0">B. Proposed Intervention</h5></div>
                <div class="card-body">
                    <p class="mb-2">Select the primary nature of the proposal:</p>
                    <div class="row">
                        @foreach($interventions as $intervention)
                            <div class="col-md-6 col-lg-4 mb-2">
                                <label class="d-flex align-items-center font-weight-normal">
                                    <input type="radio" class="mr-2" value="{{ $intervention }}" wire:model="form.primary_intervention">
                                    {{ $intervention }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @if($form['primary_intervention'] === 'Other')
                        <div class="form-group mt-2">
                            <label for="other_intervention">Other</label>
                            <input id="other_intervention" class="form-control" wire:model="form.other_intervention">
                        </div>
                    @endif
                    <div class="form-group mb-0">
                        <label for="intervention_description">Brief description of what is being proposed</label>
                        <textarea id="intervention_description" class="form-control" rows="3" wire:model="form.intervention_description"></textarea>
                    </div>
                </div>
            </section>

            <section class="card mb-3">
                <div class="card-header project-proposal-section-header"><h5 class="mb-0">C. Justification — Why Is This Necessary?</h5></div>
                <div class="card-body">
                    <div class="form-group"><label>Current situation / existing use of the property</label><textarea class="form-control" rows="3" wire:model="form.current_situation"></textarea></div>
                    <div class="form-group"><label>Identified problem or operational need (include evidence or observations)</label><textarea class="form-control" rows="3" wire:model="form.operational_need"></textarea></div>
                    <div class="form-group"><label>Consequences of taking no action</label><textarea class="form-control" rows="3" wire:model="form.no_action_consequences"></textarea></div>
                    <div class="form-group mb-0"><label>Why the proposed intervention is preferable to maintaining the present arrangement</label><textarea class="form-control" rows="3" wire:model="form.present_arrangement_alternative"></textarea></div>
                </div>
            </section>

            <section class="card mb-3">
                <div class="card-header project-proposal-section-header"><h5 class="mb-0">D. Objectives and Expected Benefits</h5></div>
                <div class="card-body">
                    <div class="form-group"><label>Main objective of the proposed intervention</label><textarea class="form-control" rows="3" wire:model="form.main_objective"></textarea></div>
                    <div class="form-group"><label>Expected outcomes or benefits (e.g. accommodation capacity, safety, service delivery, cost avoidance)</label><textarea class="form-control" rows="3" wire:model="form.expected_benefits"></textarea></div>
                    <div class="row">
                        <div class="form-group col-md-6"><label>Current capacity / provision (if applicable)</label><input class="form-control" wire:model="form.current_capacity"></div>
                        <div class="form-group col-md-6"><label>Expected capacity / provision (if applicable)</label><input class="form-control" wire:model="form.expected_capacity"></div>
                    </div>
                    <div class="form-group mb-0"><label>Departments / operations expected to benefit</label><textarea class="form-control" rows="2" wire:model="form.beneficiary_departments"></textarea></div>
                </div>
            </section>

            <section class="card mb-3">
                <div class="card-header project-proposal-section-header"><h5 class="mb-0">E. Preliminary Technical Considerations</h5><small class="text-muted">Record what is known at proposal stage. Detailed designs, surveys, technical specifications and estimates are not required.</small></div>
                <div class="card-body">
                    <h6 class="font-weight-bold">E1. Existing Buildings and Structures</h6>
                    <div class="form-group"><label>Existing physical condition of the building, structure or site (key observations)</label><textarea class="form-control" rows="3" wire:model="form.existing_condition"></textarea></div>
                    <div class="form-group"><label>Proposed physical changes / principal work elements (what would need to change?)</label><textarea class="form-control" rows="3" wire:model="form.principal_work_elements"></textarea></div>
                    <div class="form-group"><label>Areas affected (building, floor, rooms, external areas or approximate extent)</label><textarea class="form-control" rows="2" wire:model="form.areas_affected"></textarea></div>
                    <div class="form-group"><label>Available supporting technical evidence (inspection findings, sketches, drawings, photos, reports)</label><textarea class="form-control" rows="2" wire:model="form.supporting_technical_evidence"></textarea></div>
                    <div class="form-group"><label>Known constraints (access, occupied premises, structural concerns, utilities, operational disruption)</label><textarea class="form-control" rows="2" wire:model="form.known_constraints"></textarea></div>
                    <div class="form-group"><label>Technical investigations or specialist input needed before feasibility can be established</label><textarea class="form-control" rows="2" wire:model="form.technical_investigations"></textarea></div>
                    <div class="form-group mb-4"><label>Potential statutory, safety or environmental considerations already identified</label><textarea class="form-control" rows="2" wire:model="form.statutory_safety_environmental_considerations"></textarea></div>

                    <h6 class="font-weight-bold">E2. Proposed New Developments (New Construction)</h6>
                    <p class="text-muted">Complete this part for projects proposed on a new or undeveloped site. Items not yet established may be marked “To be confirmed”.</p>
                    <div class="form-group"><label>Proposed Development / Facility Type</label><input class="form-control" placeholder="E.g. Office Building, Staff Houses, Stores or Other Facility" wire:model="form.facility_type"></div>
                    <div class="form-group"><label>Proposed Site / Plot Location</label><input class="form-control" placeholder="Identify the proposed site or state Site to be identified" wire:model="form.proposed_site_plot_location"></div>
                    <fieldset class="form-group">
                        <legend class="col-form-label pt-0">Land Availability / Site Status</legend>
                        <div class="d-flex flex-wrap">
                            @foreach($landStatuses as $landStatus)
                                <label class="mr-4 font-weight-normal"><input type="checkbox" value="{{ $landStatus }}" wire:model="form.e2_land_status"> {{ $landStatus }}</label>
                            @endforeach
                        </div>
                        @if(in_array('Other Arrangement', $form['e2_land_status'] ?? [], true))
                            <input class="form-control mt-2" placeholder="Other arrangement" wire:model="form.other_land_arrangement">
                        @endif
                    </fieldset>
                    <div class="form-group"><label>Proposed Development Size / Capacity</label><input class="form-control" placeholder="Approximate building size, number of units, floors or intended users, if known" wire:model="form.development_size_capacity"></div>
                    <div class="form-group"><label>Site Access and Existing Infrastructure</label><textarea class="form-control" rows="2" placeholder="Access road, electricity, water, drainage or sewerage: note what is available or unknown" wire:model="form.site_access_infrastructure"></textarea></div>
                    <div class="form-group"><label>Known Site Conditions or Constraints</label><textarea class="form-control" rows="2" placeholder="Terrain, flooding, access limitations, nearby operations or other observed constraints" wire:model="form.site_conditions_constraints"></textarea></div>
                    <fieldset class="form-group">
                        <legend class="col-form-label pt-0">Preliminary Layout / Site Information Available</legend>
                        <div class="d-flex flex-wrap">
                            @foreach($layoutOptions as $layoutOption)
                                <label class="mr-4 font-weight-normal"><input type="checkbox" value="{{ $layoutOption }}" wire:model="form.e2_layout_information"> {{ $layoutOption }}</label>
                            @endforeach
                        </div>
                    </fieldset>
                    <div class="form-group"><label>Further Technical or Site Investigations Required</label><textarea class="form-control" rows="2" placeholder="E.g. survey, ground investigation, services assessment or specialist input" wire:model="form.further_site_investigations"></textarea></div>
                    <div class="form-group mb-0"><label>Potential Planning, Environmental or Safety Requirements</label><textarea class="form-control" rows="2" placeholder="Record only requirements or concerns already identified" wire:model="form.planning_environmental_safety_requirements"></textarea></div>
                </div>
            </section>

            <section class="card mb-3">
                <div class="card-header project-proposal-section-header d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="mb-0">F. Possible Alternatives</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addAlternative"><i class="fas fa-plus mr-1"></i>Add option</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead style="background-color: {{ config('theme.color_primary') }}; color: {{ config('theme.color_primary_text_light') }}"><tr><th>Option considered</th><th>Potential advantage</th><th>Limitation / concern</th><th></th></tr></thead>
                            <tbody>
                                @foreach($form['alternatives'] as $index => $alternative)
                                    <tr>
                                        <td><textarea class="form-control" rows="2" wire:model="form.alternatives.{{ $index }}.option"></textarea></td>
                                        <td><textarea class="form-control" rows="2" wire:model="form.alternatives.{{ $index }}.advantage"></textarea></td>
                                        <td><textarea class="form-control" rows="2" wire:model="form.alternatives.{{ $index }}.limitation"></textarea></td>
                                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" aria-label="Remove option" wire:click="removeAlternative({{ $index }})"><i class="fas fa-times"></i></button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="form-group mb-0"><label>Reason for proposing the preferred approach</label><textarea class="form-control" rows="3" wire:model="form.preferred_approach_reason"></textarea></div>
                </div>
            </section>

            <section class="card mb-3">
                <div class="card-header project-proposal-section-header"><h5 class="mb-0">G. Preliminary Resource and Delivery Considerations</h5><small class="text-muted">Preliminary indications only; technical costing, programme development and budget confirmation are separate subsequent activities.</small></div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-6"><label>Indicative cost (only if known)</label><input type="number" min="0" step="0.01" class="form-control" wire:model="form.indicative_cost">@error('form.indicative_cost') <small class="text-danger">{{ $message }}</small> @enderror</div>
                        <div class="form-group col-md-6"><label>Source / basis of indicative cost</label><input class="form-control" wire:model="form.cost_basis"></div>
                    </div>
                    <div class="form-group"><label>Potential resource requirements or major cost drivers (if known)</label><textarea class="form-control" rows="2" wire:model="form.resource_requirements"></textarea></div>
                    <div class="form-group"><label>Funding or budget information currently known (if any)</label><textarea class="form-control" rows="2" wire:model="form.funding_information"></textarea></div>
                    <div class="row">
                        <div class="form-group col-md-6"><label>Desired timeframe (if known)</label><input class="form-control" wire:model="form.desired_timeframe"></div>
                        <div class="form-group col-md-6"><label>Reason for timing / urgency</label><input class="form-control" wire:model="form.timing_urgency_reason"></div>
                    </div>
                    <div class="form-group mb-0"><label>Possible implementation dependencies (e.g. access, decanting, technical design, procurement)</label><textarea class="form-control" rows="2" wire:model="form.implementation_dependencies"></textarea></div>
                </div>
            </section>

            <section class="card mb-3">
                <div class="card-header project-proposal-section-header d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="mb-0">H. Initial Risks and Stakeholder Input</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addInitialRisk"><i class="fas fa-plus mr-1"></i>Add concern</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead style="background-color: {{ config('theme.color_primary') }}; color: {{ config('theme.color_primary_text_light') }}"><tr><th>Initial concern / risk</th><th>Possible consequence</th><th>Further assessment required</th><th></th></tr></thead>
                            <tbody>
                                @foreach($form['initial_risks'] as $index => $risk)
                                    <tr>
                                        <td><textarea class="form-control" rows="2" wire:model="form.initial_risks.{{ $index }}.concern"></textarea></td>
                                        <td><textarea class="form-control" rows="2" wire:model="form.initial_risks.{{ $index }}.consequence"></textarea></td>
                                        <td><textarea class="form-control" rows="2" wire:model="form.initial_risks.{{ $index }}.further_assessment"></textarea></td>
                                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" aria-label="Remove concern" wire:click="removeInitialRisk({{ $index }})"><i class="fas fa-times"></i></button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <fieldset class="form-group">
                        <legend class="col-form-label pt-0">Stakeholders whose input may be required (tick those relevant)</legend>
                        <div class="row">
                            @foreach($stakeholderOptions as $stakeholder)
                                <label class="col-md-4 col-lg-3 mb-2 font-weight-normal"><input type="checkbox" value="{{ $stakeholder }}" wire:model="form.stakeholders"> {{ $stakeholder }}</label>
                            @endforeach
                        </div>
                        @if(in_array('Other', $form['stakeholders'] ?? [], true))
                            <input class="form-control" placeholder="Other stakeholder" wire:model="form.other_stakeholder">
                        @endif
                    </fieldset>
                    <div class="form-group"><label>Consultations or site discussions already undertaken</label><textarea class="form-control" rows="2" wire:model="form.consultations_undertaken"></textarea></div>
                    <div class="form-group mb-0"><label>Matters requiring further investigation</label><textarea class="form-control" rows="2" wire:model="form.matters_for_investigation"></textarea></div>
                </div>
            </section>

            <section class="card mb-3">
                <div class="card-header project-proposal-section-header"><h5 class="mb-0">I. Supporting Information Attached</h5></div>
                <div class="card-body">
                    <div class="row">
                        @foreach($supportingOptions as $supportingOption)
                            <label class="col-md-4 col-lg-3 mb-2 font-weight-normal"><input type="checkbox" value="{{ $supportingOption }}" wire:model="form.supporting_information"> {{ $supportingOption }}</label>
                        @endforeach
                    </div>
                    @if(in_array('Other', $form['supporting_information'] ?? [], true))
                        <input class="form-control mt-2" placeholder="Other supporting information" wire:model="form.other_supporting_information">
                    @endif
                </div>
            </section>

            <section class="card mb-3">
                <div class="card-header project-proposal-section-header"><h5 class="mb-0">J. Proposal Summary and Recommended Next Step</h5></div>
                <div class="card-body">
                    <div class="form-group"><label>Summary of the need and proposed response</label><textarea class="form-control" rows="3" wire:model="form.proposal_summary"></textarea></div>
                    <fieldset class="form-group">
                        <legend class="col-form-label pt-0">Suggested next step (select one)</legend>
                        <div class="row">
                            @foreach($nextStepOptions as $nextStep)
                                <label class="col-md-6 col-lg-4 mb-2 font-weight-normal"><input type="radio" value="{{ $nextStep }}" wire:model="form.next_step_options"> {{ $nextStep }}</label>
                            @endforeach
                        </div>
                        @if($form['next_step_options'] === 'Other')
                            <input class="form-control" placeholder="Other next step" wire:model="form.other_next_step">
                        @endif
                    </fieldset>
                    <div class="form-group mb-0"><label>Specific action proposed for the next stage</label><textarea class="form-control" rows="2" wire:model="form.specific_next_action"></textarea></div>
                </div>
            </section>

            <section class="card mb-3">
                <div class="card-header project-proposal-section-header"><h5 class="mb-0">K. Prepared By</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-6"><label>Name of proposing officer</label><input class="form-control" wire:model="form.proposing_officer_name"></div>
                        <div class="form-group col-md-6"><label>Designation</label><input class="form-control" wire:model="form.proposing_officer_designation"></div>
                        <div class="form-group col-md-6"><label>Department</label><input class="form-control" wire:model="form.proposing_officer_department"></div>
                        <div class="form-group col-md-6"><label>Date</label><input type="date" class="form-control" wire:model="form.proposing_officer_date"></div>
                        <div class="form-group col-12 mb-0"><label>Signature</label><input class="form-control" wire:model="form.proposing_officer_signature"></div>
                    </div>
                </div>
            </section>

            <section class="card mb-3">
                <div class="card-header project-proposal-section-header"><h5 class="mb-0">L. Acknowledgement of Receipt (Optional)</h5><small class="text-muted">Receipt for further consideration only; this does not constitute project approval.</small></div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-6"><label>Received by</label><input class="form-control" wire:model="form.acknowledged_by"></div>
                        <div class="form-group col-md-6"><label>Date received</label><input type="date" class="form-control" wire:model="form.date_received"></div>
                        <div class="form-group col-12 mb-0"><label>Remarks / information requested</label><textarea class="form-control" rows="3" wire:model="form.acknowledgement_remarks"></textarea></div>
                    </div>
                </div>
            </section>

            <div class="d-flex justify-content-end mb-4">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove><i class="fas fa-save mr-1"></i> Save Draft</span>
                    <span wire:loading><span class="spinner-border spinner-border-sm mr-1"></span> Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>
