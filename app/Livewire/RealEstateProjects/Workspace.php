<?php

namespace App\Livewire\RealEstateProjects;

use App\Models\RealEstateProject;
use App\Models\User;
use App\Services\RealEstateProjectProposalWorkflowService;
use Livewire\Component;
use Illuminate\Validation\Rule;
use App\Models\ProjectFeasibilityAssessment;

class Workspace extends Component
{
    public RealEstateProject $project;
    public string $activeTab = 'proposal';
    public ?int $feasibilityAssessmentId = null;
    public array $feasibilityForm = [];

    private const TABS = [
        'overview',
        'proposal',
        'feasibility',
        'budget',
        'risks',
        'progress',
        'documents',
        'approvals',
        'history',
    ];

    private const TECHNICAL_CHOICES = [
    'site_building_suitability' => [
        'label' => 'Site / Building Suitability',
        'options' => ['Suitable', 'Suitable with Changes', 'Unsuitable', 'To Be Confirmed'],
    ],
    'structural_capacity' => [
        'label' => 'Structural Condition / Capacity',
        'options' => ['Adequate', 'Strengthening Needed', 'Not Applicable', 'To Be Confirmed'],
    ],
    'space_layout_suitability' => [
        'label' => 'Space / Layout Suitability',
        'options' => ['Adequate', 'Reconfiguration Needed', 'Not Applicable', 'To Be Confirmed'],
    ],
    'site_access' => [
        'label' => 'Access to the Site / Building',
        'options' => ['Adequate', 'Improvement Needed', 'To Be Confirmed'],
    ],
    'electricity_supply' => [
        'label' => 'Electricity Supply',
        'options' => ['Adequate', 'Upgrade Needed', 'Not Available', 'To Be Confirmed'],
    ],
    'water_supply' => [
        'label' => 'Water Supply',
        'options' => ['Adequate', 'Upgrade Needed', 'Not Available', 'To Be Confirmed'],
    ],
    'drainage_sewerage' => [
        'label' => 'Drainage / Sewerage',
        'options' => ['Adequate', 'Upgrade Needed', 'Not Available', 'To Be Confirmed'],
    ],
    'fire_safety_access' => [
        'label' => 'Fire Safety / Emergency Access',
        'options' => ['Adequate', 'Improvement Needed', 'To Be Confirmed'],
    ],
    'environmental_sheq_concerns' => [
        'label' => 'Environmental / SHEQ Concerns',
        'options' => ['None Identified', 'Manageable', 'Significant', 'To Be Confirmed'],
    ],
    'permits_statutory_requirements' => [
        'label' => 'Permits / Statutory Requirements',
        'options' => ['Not Required', 'Required', 'To Be Confirmed'],
    ],
];

    private const COST_CHOICES = [
        'basis_of_estimate' => [
            'label' => 'Basis of Estimate',
            'options' => ['Preliminary Estimate', 'Budget Quotation', 'Cost Plan', 'Other'],
        ],
        'funding_position' => [
            'label' => 'Indicative Funding Position',
            'options' => ['Budget Identified', 'Funding To Be Sought', 'Not Yet Confirmed'],
        ],
        'cost_position' => [
            'label' => 'Cost Position',
            'options' => ['Within Indicative Funding', 'Above Indicative Funding', 'Not Established'],
        ],
        'resource_availability' => [
            'label' => 'Availability of Required Resources',
            'options' => ['Likely Available', 'Constraints Identified', 'To Be Confirmed'],
        ],
        'operations_impact' => [
            'label' => 'Impact on ZESCO Operations',
            'options' => ['Minimal', 'Manageable with Arrangements', 'Significant', 'To Be Confirmed'],
        ],
        'delivery_timeframe' => [
            'label' => 'Delivery Timeframe',
            'options' => ['Achievable', 'Challenging', 'To Be Confirmed'],
        ],
    ];

    private const FINDING_OPTIONS = [
        'Feasible',
        'Feasible subject to stated conditions',
        'Further assessment required',
        'Not feasible',
    ];

    private const SUPPORTING_RECORD_OPTIONS = [
        'Site inspection report',
        'Drawings / layout',
        'Photographs',
        'Preliminary estimate',
        'Technical report',
        'Stakeholder comments',
        'Other',
    ];

    public function mount(RealEstateProject $project): void
    {
        $this->authorize('real_estate_projects.view');
        $this->project = $project;
        $this->activeTab = $project->proposal_status === null ? 'overview' : 'proposal';
        $assessment = $project->feasibilityAssessments()->latest('id')->first();
        $proposal = $project->proposal_data ?? [];

        $proposedWorksType = match ($project->project_category) {
    'New development' => 'New Development',
    'Office reconfiguration / partitioning', 'Conversion of use'
        => 'Alteration / Conversion',
    'Rehabilitation' => 'Rehabilitation',
    'Extension' => 'Expansion',
    'Other' => 'Other',
    default => '',
};

$issueDraft = $project->project_category === 'New development'
    ? ($proposal['site_conditions_constraints'] ?? '')
    : ($proposal['existing_condition'] ?? '');

$issueDraft = trim($issueDraft ?: ($proposal['known_constraints'] ?? ''));

        $eligiblePreparers = User::permission('real_estate_projects.update')
    ->get(['id', 'name']);

$preparerId = $assessment?->prepared_by_user_id;

// Match assessments saved before we added the user ID column.
if ($preparerId === null && $assessment?->prepared_by) {
    $matches = $eligiblePreparers->where('name', $assessment->prepared_by);

    if ($matches->count() === 1) {
        $preparerId = $matches->first()->id;
    }
}

// Keep the signed-in user as the default when no preparer was saved.
if ($preparerId === null && ! $assessment?->prepared_by) {
    $preparerId = $eligiblePreparers
        ->firstWhere('id', auth()->id())?->id;
}

        $this->feasibilityAssessmentId = $assessment?->id;

        $this->feasibilityForm = [
            'project_title' => $assessment?->project_title
                ?? $project->project_title
                ?? '',
            'proposal_reference' => $assessment?->proposal_reference
                ?? ($project->record_origin === 'proposal' ? $project->reference_no : '')
                ?? '',
            'property_site_name' => $assessment?->property_site_name
                ?? $project->property_name
                ?? '',
            'building_structure_name' => $assessment?->building_structure_name
                ?? ($proposal['building_structure_name'] ?? ''),
            'town_location' => $assessment?->town_location
                ?? $project->town_location
                ?? '',
            'assessment_date' => $assessment?->assessment_date?->format('Y-m-d')
                ?? today()->toDateString(),
            'lead_assessing_department' => $assessment?->lead_assessing_department
                ?? '',
            'prepared_by_user_id' => (string) ($preparerId ?? ''),
            'other_departments_consulted' => $assessment?->other_departments_consulted
                ?? '',
                'proposed_works_type' => $assessment?->proposed_works_type
                ?? $proposedWorksType,
            'site_condition' => $assessment?->site_condition ?? '',
            'existing_use' => $assessment?->existing_use ?? '',
            'site_information_availability' =>
                $assessment?->site_information_availability ?? '',
            'key_existing_issue' => $assessment?->key_existing_issue
                ?? $issueDraft,
                    ];

    foreach (array_keys(self::TECHNICAL_CHOICES) as $field) {
    $this->feasibilityForm[$field] = $assessment?->{$field} ?? '';
}

$this->feasibilityForm['technical_limitations_measures'] =
    $assessment?->technical_limitations_measures ?? '';

$sourceOptions = $assessment?->options_considered
    ?? ($proposal['alternatives'] ?? []);

$sourceOptions = is_array($sourceOptions)
    ? array_values($sourceOptions)
    : [];

$optionRows = [];

for ($index = 0; $index < 3; $index++) {
    $row = is_array($sourceOptions[$index] ?? null)
        ? $sourceOptions[$index]
        : [];

    $optionRows[] = [
        'option' => $row['option'] ?? '',
        'advantage' => $row['advantage'] ?? '',
        'limitation' => $row['limitation'] ?? '',
    ];
}

$this->feasibilityForm['options_considered'] = $optionRows;
$this->feasibilityForm['preferred_option_number'] =
    (string) ($assessment?->preferred_option_number ?? '');
$this->feasibilityForm['preference_reason'] =
    $assessment?->preference_reason
    ?? ($proposal['preferred_approach_reason'] ?? '');

        $proposalCostBasis = trim((string) ($proposal['cost_basis'] ?? ''));
        $basisOfEstimate = in_array(
            $proposalCostBasis,
            self::COST_CHOICES['basis_of_estimate']['options'],
            true
        ) ? $proposalCostBasis : '';

        $this->feasibilityForm['preliminary_cost'] = $assessment?->cost_not_available
            ? ''
            : ($assessment?->preliminary_cost ?? $project->indicative_cost ?? '');
        $this->feasibilityForm['cost_not_available'] =
            (bool) ($assessment?->cost_not_available ?? false);
        $this->feasibilityForm['basis_of_estimate'] =
            $assessment?->basis_of_estimate ?? $basisOfEstimate;
        $this->feasibilityForm['funding_position'] =
            $assessment?->funding_position ?? '';
        $this->feasibilityForm['implementation_months'] =
            (string) ($assessment?->implementation_months ?? '');
        $this->feasibilityForm['period_not_established'] =
            (bool) ($assessment?->period_not_established ?? false);
        foreach (['cost_position', 'resource_availability', 'operations_impact', 'delivery_timeframe'] as $field) {
            $this->feasibilityForm[$field] = $assessment?->{$field} ?? '';
        }
        $this->feasibilityForm['cost_implementation_constraints'] =
            $assessment?->cost_implementation_constraints ?? '';

        $sourceRisks = $assessment?->significant_risks;
        if (! is_array($sourceRisks)) {
            $sourceRisks = [];
            foreach (($proposal['initial_risks'] ?? []) as $risk) {
                if (is_array($risk) && filled($risk['concern'] ?? null)) {
                    $sourceRisks[] = [
                        'issue' => $risk['concern'],
                        'action' => '',
                        'responsible_department' => '',
                    ];
                }
            }
        }

        $riskRows = [];
        foreach ($sourceRisks as $risk) {
            if (! is_array($risk)) {
                continue;
            }
            $riskRows[] = [
                'issue' => $risk['issue'] ?? '',
                'action' => $risk['action'] ?? '',
                'responsible_department' => $risk['responsible_department'] ?? '',
            ];
        }
        while (count($riskRows) < 3) {
            $riskRows[] = ['issue' => '', 'action' => '', 'responsible_department' => ''];
        }
        $this->feasibilityForm['significant_risks'] = $riskRows;

        $this->feasibilityForm['overall_finding'] = $assessment?->overall_finding ?? '';
        $this->feasibilityForm['finding_basis'] = $assessment?->finding_basis ?? '';
        $this->feasibilityForm['next_stage_conditions'] =
            $assessment?->next_stage_conditions ?? '';

        $assessingUser = auth()->user();
        $designation = $assessingUser?->job_title ?: $assessingUser?->position;
        $assessorLabel = $assessingUser?->name ?? '';
        if (filled($designation)) {
            $assessorLabel .= ' - ' . $designation;
        }

        $this->feasibilityForm['supporting_records'] =
            $assessment?->supporting_records ?? [];
        $this->feasibilityForm['other_supporting_record'] =
            $assessment?->other_supporting_record ?? '';
        $this->feasibilityForm['assessed_by_name_designation'] =
            $assessment?->assessed_by_name_designation ?? $assessorLabel;
        $this->feasibilityForm['assessed_department'] =
            $assessment?->assessed_department
            ?? ($assessingUser?->bu_name ?: ($assessingUser?->cc_name ?: ''));
        $this->feasibilityForm['signature_text'] = $assessment?->signature_text ?? '';
        $this->feasibilityForm['confirmation_date'] =
            $assessment?->confirmation_date?->format('Y-m-d') ?? '';
    }

    public function selectTab(string $tab): void
    {
        if (in_array($tab, self::TABS, true)
            && ! ($tab === 'proposal' && $this->project->proposal_status === null)
            && ! ($tab === 'overview' && $this->project->proposal_status !== null)) {
            $this->activeTab = $tab;
        }
    }

    public function submitProposal(RealEstateProjectProposalWorkflowService $workflowService)
    {
        $this->authorize('real_estate_projects.submit');

        $workflowService->submit($this->project);
        $this->project->refresh();

        session()->flash('success', 'Proposal submitted for review.');

        return redirect()->route('real-estate-projects.show', $this->project);
    }

    public function saveFeasibilitySectionA(): void
{
    $this->authorize('real_estate_projects.update');

    $data = $this->validate([
        'feasibilityForm.project_title' => ['nullable', 'string', 'max:255'],
        'feasibilityForm.proposal_reference' => ['nullable', 'string', 'max:255'],
        'feasibilityForm.property_site_name' => ['nullable', 'string', 'max:255'],
        'feasibilityForm.building_structure_name' => ['nullable', 'string', 'max:255'],
        'feasibilityForm.town_location' => ['nullable', 'string', 'max:255'],
        'feasibilityForm.assessment_date' => ['nullable', 'date_format:Y-m-d'],
        'feasibilityForm.lead_assessing_department' => ['nullable', 'string', 'max:255'],
        'feasibilityForm.prepared_by_user_id' => ['required', 'integer'],
        'feasibilityForm.other_departments_consulted' => ['nullable', 'string', 'max:5000'],
    ])['feasibilityForm'];

    $preparer = User::permission('real_estate_projects.update')
    ->whereKey((int) $data['prepared_by_user_id'])
    ->first();

if (! $preparer) {
    $this->addError(
        'feasibilityForm.prepared_by_user_id',
        'Choose a user with permission to update real estate projects.'
    );

    return;
}

$data['prepared_by'] = $preparer->name;

    if ($this->feasibilityAssessmentId) {
        $assessment = $this->project->feasibilityAssessments()
            ->findOrFail($this->feasibilityAssessmentId);

        $assessment->update($data);
    } else {
        $assessment = $this->project->feasibilityAssessments()->create([
            ...$data,
            'created_by' => auth()->id(),
        ]);

        $this->feasibilityAssessmentId = $assessment->id;
    }

    session()->flash('success', 'Feasibility assessment Section A saved.');
}

    public function saveFeasibilitySectionB(): void
{
    $this->authorize('real_estate_projects.update');

    $data = $this->validate([
        'feasibilityForm.proposed_works_type' => [
            'nullable',
            Rule::in([
                'New Development',
                'Alteration / Conversion',
                'Rehabilitation',
                'Expansion',
                'Other',
            ]),
        ],
        'feasibilityForm.site_condition' => [
            'nullable',
            Rule::in([
                'Good',
                'Fair',
                'Poor',
                'Not Applicable',
                'Not Assessed',
            ]),
        ],
        'feasibilityForm.existing_use' => [
            'nullable',
            Rule::in([
                'Occupied',
                'Partly Occupied',
                'Vacant',
                'Undeveloped',
                'Other',
            ]),
        ],
        'feasibilityForm.site_information_availability' => [
            'nullable',
            Rule::in([
                'Available',
                'Partly Available',
                'Not Available',
            ]),
        ],
        'feasibilityForm.key_existing_issue' => [
            'nullable',
            'string',
            'max:5000',
        ],
    ])['feasibilityForm'];

    if ($this->feasibilityAssessmentId) {
        $assessment = $this->project->feasibilityAssessments()
            ->findOrFail($this->feasibilityAssessmentId);

        $assessment->update($data);
    } else {
        $proposal = $this->project->proposal_data ?? [];

        $assessment = $this->project->feasibilityAssessments()->create([
            'project_title' => $this->project->project_title,
            'proposal_reference' => $this->project->record_origin === 'proposal'
                ? $this->project->reference_no
                : null,
            'property_site_name' => $this->project->property_name,
            'building_structure_name' =>
                $proposal['building_structure_name'] ?? null,
            'town_location' => $this->project->town_location,
            ...$data,
            'created_by' => auth()->id(),
        ]);

        $this->feasibilityAssessmentId = $assessment->id;
    }

    session()->flash('success', 'Feasibility assessment Section B saved.');
}

    public function saveFeasibilitySectionC(): void
{
    $this->authorize('real_estate_projects.update');

    $rules = [
        'feasibilityForm.technical_limitations_measures' => [
            'nullable', 'string', 'max:5000',
        ],
    ];

    foreach (self::TECHNICAL_CHOICES as $field => $choice) {
        $rules["feasibilityForm.{$field}"] = [
            'nullable',
            Rule::in($choice['options']),
        ];
    }

    $data = $this->validate($rules)['feasibilityForm'];

    $this->feasibilityAssessmentForEdit()->update($data);

    session()->flash('success', 'Feasibility assessment Section C saved.');
}

public function saveFeasibilitySectionD(): void
{
    $this->authorize('real_estate_projects.update');

    $data = $this->validate([
        'feasibilityForm.options_considered' => [
            'required', 'array', 'size:3',
        ],
        'feasibilityForm.options_considered.*' => [
            'required', 'array',
        ],
        'feasibilityForm.options_considered.*.option' => [
            'nullable', 'string', 'max:255',
        ],
        'feasibilityForm.options_considered.*.advantage' => [
            'nullable', 'string', 'max:5000',
        ],
        'feasibilityForm.options_considered.*.limitation' => [
            'nullable', 'string', 'max:5000',
        ],
        'feasibilityForm.preferred_option_number' => [
            'nullable', Rule::in(['1', '2', '3']),
        ],
        'feasibilityForm.preference_reason' => [
            'nullable', 'string', 'max:5000',
        ],
    ])['feasibilityForm'];

    $preferred = $data['preferred_option_number'] ?? null;

    if (
        filled($preferred)
        && blank($data['options_considered'][(int) $preferred - 1]['option'] ?? null)
    ) {
        $this->addError(
            'feasibilityForm.preferred_option_number',
            'Enter the selected option before marking it as preferred.'
        );

        return;
    }

    $data['preferred_option_number'] = filled($preferred)
        ? (int) $preferred
        : null;

    $this->feasibilityAssessmentForEdit()->update($data);

    session()->flash('success', 'Feasibility assessment Section D saved.');
}

    public function saveFeasibilitySectionE(): void
    {
        $this->authorize('real_estate_projects.update');

        if ($this->feasibilityForm['cost_not_available'] ?? false) {
            $this->feasibilityForm['preliminary_cost'] = null;
        }
        if ($this->feasibilityForm['period_not_established'] ?? false) {
            $this->feasibilityForm['implementation_months'] = null;
        }

        $rules = [
            'feasibilityForm.preliminary_cost' => ['nullable', 'numeric', 'min:0'],
            'feasibilityForm.cost_not_available' => ['boolean'],
            'feasibilityForm.implementation_months' => ['nullable', 'integer', 'min:1', 'max:1200'],
            'feasibilityForm.period_not_established' => ['boolean'],
            'feasibilityForm.cost_implementation_constraints' => ['nullable', 'string', 'max:5000'],
        ];
        foreach (self::COST_CHOICES as $field => $choice) {
            $rules["feasibilityForm.{$field}"] = ['nullable', Rule::in($choice['options'])];
        }

        $data = $this->validate($rules)['feasibilityForm'];
        $data['cost_not_available'] = (bool) ($data['cost_not_available'] ?? false);
        $data['period_not_established'] = (bool) ($data['period_not_established'] ?? false);
        $data['preliminary_cost'] = $data['cost_not_available'] || blank($data['preliminary_cost'] ?? null)
            ? null
            : $data['preliminary_cost'];
        $data['implementation_months'] = $data['period_not_established'] || blank($data['implementation_months'] ?? null)
            ? null
            : (int) $data['implementation_months'];

        $this->feasibilityAssessmentForEdit()->update($data);

        session()->flash('success', 'Feasibility assessment Section E saved.');
    }

    public function addSignificantRisk(): void
    {
        $this->authorize('real_estate_projects.update');

        if (count($this->feasibilityForm['significant_risks'] ?? []) < 30) {
            $this->feasibilityForm['significant_risks'][] = [
                'issue' => '',
                'action' => '',
                'responsible_department' => '',
            ];
        }
    }

    public function removeSignificantRisk(int $index): void
    {
        $this->authorize('real_estate_projects.update');

        $risks = $this->feasibilityForm['significant_risks'] ?? [];
        if (array_key_exists($index, $risks)) {
            unset($risks[$index]);
            $risks = array_values($risks);
        }

        $this->feasibilityForm['significant_risks'] = $risks ?: [
            ['issue' => '', 'action' => '', 'responsible_department' => ''],
        ];
    }

    public function saveFeasibilitySectionF(): void
    {
        $this->authorize('real_estate_projects.update');

        $data = $this->validate([
            'feasibilityForm.significant_risks' => ['required', 'array', 'min:1', 'max:30'],
            'feasibilityForm.significant_risks.*' => ['required', 'array'],
            'feasibilityForm.significant_risks.*.issue' => ['nullable', 'string', 'max:5000'],
            'feasibilityForm.significant_risks.*.action' => ['nullable', 'string', 'max:5000'],
            'feasibilityForm.significant_risks.*.responsible_department' => ['nullable', 'string', 'max:255'],
        ])['feasibilityForm'];

        $this->feasibilityAssessmentForEdit()->update($data);

        session()->flash('success', 'Feasibility assessment Section F saved.');
    }

    public function saveFeasibilitySectionG(): void
    {
        $this->authorize('real_estate_projects.update');

        $data = $this->validate([
            'feasibilityForm.overall_finding' => ['nullable', Rule::in(self::FINDING_OPTIONS)],
            'feasibilityForm.finding_basis' => ['nullable', 'string', 'max:5000'],
            'feasibilityForm.next_stage_conditions' => ['nullable', 'string', 'max:5000'],
        ])['feasibilityForm'];

        $this->feasibilityAssessmentForEdit()->update($data);

        session()->flash('success', 'Feasibility assessment Section G saved.');
    }

    public function saveFeasibilitySectionH(): void
    {
        $this->authorize('real_estate_projects.update');

        $data = $this->validate([
            'feasibilityForm.supporting_records' => ['array'],
            'feasibilityForm.supporting_records.*' => [Rule::in(self::SUPPORTING_RECORD_OPTIONS)],
            'feasibilityForm.other_supporting_record' => ['nullable', 'string', 'max:255'],
            'feasibilityForm.assessed_by_name_designation' => ['nullable', 'string', 'max:255'],
            'feasibilityForm.assessed_department' => ['nullable', 'string', 'max:255'],
            'feasibilityForm.signature_text' => ['nullable', 'string', 'max:255'],
            'feasibilityForm.confirmation_date' => ['nullable', 'date_format:Y-m-d'],
        ])['feasibilityForm'];

        $records = $data['supporting_records'] ?? [];
        if (in_array('Other', $records, true) && blank($data['other_supporting_record'] ?? null)) {
            $this->addError('feasibilityForm.other_supporting_record', 'Describe the other supporting record.');
            return;
        }
        if (! in_array('Other', $records, true)) {
            $data['other_supporting_record'] = null;
        }
        if (filled($data['signature_text'] ?? null) && blank($data['confirmation_date'] ?? null)) {
            $this->addError('feasibilityForm.confirmation_date', 'Enter the date for the typed signature.');
            return;
        }
        $data['supporting_records'] = array_values(array_unique($records));
        $data['confirmation_date'] = blank($data['confirmation_date'] ?? null)
            ? null
            : $data['confirmation_date'];

        $this->feasibilityAssessmentForEdit()->update($data);

        session()->flash('success', 'Feasibility assessment Section H saved.');
    }

private function feasibilityAssessmentForEdit(): ProjectFeasibilityAssessment
{
    if ($this->feasibilityAssessmentId !== null) {
        return $this->project->feasibilityAssessments()
            ->findOrFail($this->feasibilityAssessmentId);
    }

    $proposal = $this->project->proposal_data ?? [];

    $assessment = $this->project->feasibilityAssessments()->create([
        'project_title' => $this->project->project_title,
        'proposal_reference' => $this->project->record_origin === 'proposal'
            ? $this->project->reference_no
            : null,
        'property_site_name' => $this->project->property_name,
        'building_structure_name' =>
            $proposal['building_structure_name'] ?? null,
        'town_location' => $this->project->town_location,
        'created_by' => auth()->id(),
    ]);

    $this->feasibilityAssessmentId = $assessment->id;

    return $assessment;
}

    public function render()
    {
        $workflow = $this->project->workflowInstances()
            ->with(['currentStep', 'actions.actor'])
            ->first();
        $currentApprovers = $workflow
            ? User::query()->whereIn('id', $workflow->current_approver_ids ?? [])->get()
            : collect();
        $assessmentPreparers = User::permission('real_estate_projects.update')
    ->orderBy('name')
    ->get(['id', 'name', 'man_no']);
    $technicalChoices = self::TECHNICAL_CHOICES;

        $costChoices = self::COST_CHOICES;
        $findingOptions = self::FINDING_OPTIONS;
        $supportingRecordOptions = self::SUPPORTING_RECORD_OPTIONS;

        return view('livewire.real-estate-projects.workspace', compact(
            'workflow',
            'currentApprovers',
            'assessmentPreparers',
            'technicalChoices',
            'costChoices',
            'findingOptions',
            'supportingRecordOptions'
        ))
            ->layout('layouts.master', ['title' => $this->project->reference_no]);
    }
}
