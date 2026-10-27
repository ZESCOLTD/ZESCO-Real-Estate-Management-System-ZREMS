<?php

namespace App\Livewire\RealEstateProjects;

use App\Models\RealEstateProject;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Risks extends Component
{
    public RealEstateProject $project;

    public array $form = [];

    public const STAGES = ['Proposal', 'Feasibility', 'Planning / Design', 'Procurement', 'Implementation', 'Other'];

    public const RISK_AREAS = [
        'technical_site' => ['Technical / site', 'Unforeseen defects; unsuitable site conditions; design changes; inaccurate measurements; service interruptions'],
        'cost_funding' => ['Cost / funding', 'Insufficient funding; cost escalation; omissions in estimates; delayed payments; unforeseen additional works'],
        'procurement_contractor' => ['Procurement / contractor', 'Delayed procurement; unavailable materials; contractor capacity; supplier non-performance'],
        'programme_delivery' => ['Programme / delivery', 'Late access to site; unrealistic durations; delayed decisions; weather; conflicting activities'],
        'health_safety' => ['Health / safety', 'Unsafe access; work at height; hazardous materials; electrical exposure; injury to workers or occupants'],
        'environment_compliance' => ['Environment / compliance', 'Waste handling; asbestos; permits; environmental impacts; statutory non-compliance'],
        'legal_land' => ['Legal / land', 'Ownership or boundary disputes; contractual obligations; wayleaves; unresolved approvals'],
        'operations_stakeholders' => ['Operations / stakeholders', 'Disruption to staff or customers; relocation needs; stakeholder objections; security concerns'],
        'other' => ['Other', 'Project-specific risks not covered above'],
    ];

    public const OVERALL_POSITIONS = [
        'Manageable with routine controls',
        'Manageable subject to specified actions',
        'Significant unresolved risks',
        'Specialist assessment required',
    ];

    public const ACKNOWLEDGEMENT_ROLES = [
        'prepared' => 'Assessment prepared by',
        'technical' => 'Technical input / review',
        'real_estate' => 'Real Estate review',
        'sheq' => 'SHEQ / specialist review',
    ];

    public const SUPPORTING_RECORDS = [
        'Site inspection report', 'Photographs', 'Drawings', 'Cost estimate',
        'Programme', 'SHEQ assessment', 'Other',
    ];

    private const TEXT_FIELDS = [
        'project_title', 'project_reference', 'property_site_name', 'building_structure_name',
        'project_location', 'assessment_prepared_by', 'responsible_department',
        'other_stage', 'other_risk_area', 'main_outstanding_risks',
        'immediate_measures', 'priority_actions', 'other_supporting_record',
        'document_references',
    ];

    private const RISK_TEXT_FIELDS = [
        'reference', 'specific_risk', 'cause', 'effect', 'existing_controls',
        'action', 'responsible_officer', 'resources', 'evidence',
    ];

    public function mount(RealEstateProject $project): void
    {
        $this->authorize('real_estate_projects.view');
        $this->project = $project;

        $saved = $project->riskAssessment?->form_data;
        $this->form = array_replace($this->defaults(), is_array($saved) ? $saved : []);
        $this->form['checklist'] = array_replace(
            $this->emptyChecklist(),
            is_array($this->form['checklist'] ?? null) ? $this->form['checklist'] : []
        );
        $this->form['acknowledgements'] = array_replace(
            $this->emptyAcknowledgements(),
            is_array($this->form['acknowledgements'] ?? null) ? $this->form['acknowledgements'] : []
        );
        $this->form['risks'] = is_array($this->form['risks'] ?? null)
            ? array_values($this->form['risks'])
            : [$this->emptyRisk()];
        if ($this->form['risks'] === []) {
            $this->form['risks'] = [$this->emptyRisk()];
        }
        $this->form['supporting_records'] = is_array($this->form['supporting_records'] ?? null)
            ? $this->form['supporting_records']
            : [];
    }

    public function addRisk(): void
    {
        $this->authorize('real_estate_projects.update');
        if (count($this->form['risks'] ?? []) < 50) {
            $this->form['risks'][] = $this->emptyRisk();
        }
    }

    public function removeRisk(int $index): void
    {
        $this->authorize('real_estate_projects.update');
        $risks = $this->form['risks'] ?? [];
        if (array_key_exists($index, $risks)) {
            unset($risks[$index]);
        }
        $this->form['risks'] = array_values($risks) ?: [$this->emptyRisk()];
    }

    public function save(): void
    {
        $this->authorize('real_estate_projects.update');

        $rules = [];
        foreach (self::TEXT_FIELDS as $field) {
            $rules["form.{$field}"] = ['nullable', 'string', 'max:5000'];
        }
        foreach (['assessment_date', 'assessment_review_date', 'next_review_date'] as $field) {
            $rules["form.{$field}"] = ['nullable', 'date_format:Y-m-d'];
        }
        $rules['form.project_stage'] = ['nullable', Rule::in(self::STAGES)];
        $rules['form.overall_position'] = ['nullable', Rule::in(self::OVERALL_POSITIONS)];
        $rules['form.checklist'] = ['required', 'array'];
        foreach (self::RISK_AREAS as $key => $area) {
            $rules["form.checklist.{$key}"] = ['nullable', Rule::in(['Yes', 'No', 'N/A'])];
        }
        $rules['form.risks'] = ['required', 'array', 'min:1', 'max:50'];
        $rules['form.risks.*'] = ['required', 'array'];
        $rules['form.risks.*.category'] = ['nullable', Rule::in(array_keys(self::RISK_AREAS))];
        $rules['form.risks.*.reference'] = ['nullable', 'string', 'max:50'];
        foreach (self::RISK_TEXT_FIELDS as $field) {
            if ($field === 'reference') {
                continue;
            }
            $rules["form.risks.*.{$field}"] = ['nullable', 'string', 'max:5000'];
        }
        foreach (['initial_likelihood', 'initial_impact', 'expected_likelihood', 'expected_impact'] as $field) {
            $rules["form.risks.*.{$field}"] = ['nullable', 'integer', 'between:1,5'];
        }
        foreach (['initial_specialist_required', 'residual_reassess'] as $field) {
            $rules["form.risks.*.{$field}"] = ['boolean'];
        }
        foreach (['target_date', 'last_reviewed'] as $field) {
            $rules["form.risks.*.{$field}"] = ['nullable', 'date_format:Y-m-d'];
        }
        $rules['form.risks.*.action_status'] = ['nullable', Rule::in(['Not started', 'In progress', 'Completed', 'Overdue'])];
        $rules['form.acknowledgements'] = ['required', 'array'];
        foreach (self::ACKNOWLEDGEMENT_ROLES as $key => $role) {
            $rules["form.acknowledgements.{$key}"] = ['required', 'array'];
            $rules["form.acknowledgements.{$key}.name_designation"] = ['nullable', 'string', 'max:255'];
            $rules["form.acknowledgements.{$key}.signature"] = ['nullable', 'string', 'max:255'];
            $rules["form.acknowledgements.{$key}.date"] = ['nullable', 'date_format:Y-m-d'];
        }
        $rules['form.supporting_records'] = ['array'];
        $rules['form.supporting_records.*'] = [Rule::in(self::SUPPORTING_RECORDS)];

        $data = $this->validate($rules)['form'];

        if (($data['project_stage'] ?? '') === 'Other' && blank($data['other_stage'] ?? null)) {
            $this->addError('form.other_stage', 'Describe the project stage.');
        }
        if (($data['project_stage'] ?? '') !== 'Other') {
            $data['other_stage'] = null;
        }
        if (($data['checklist']['other'] ?? '') === 'Yes' && blank($data['other_risk_area'] ?? null)) {
            $this->addError('form.other_risk_area', 'Describe the other risk area.');
        }
        if (($data['checklist']['other'] ?? '') !== 'Yes') {
            $data['other_risk_area'] = null;
        }
        if (in_array('Other', $data['supporting_records'] ?? [], true) && blank($data['other_supporting_record'] ?? null)) {
            $this->addError('form.other_supporting_record', 'Describe the other supporting record.');
        }
        if (! in_array('Other', $data['supporting_records'] ?? [], true)) {
            $data['other_supporting_record'] = null;
        }

        $acknowledgements = [];
        foreach (self::ACKNOWLEDGEMENT_ROLES as $key => $role) {
            $entry = $data['acknowledgements'][$key] ?? [];
            if (filled($entry['signature'] ?? null) && blank($entry['date'] ?? null)) {
                $this->addError("form.acknowledgements.{$key}.date", 'Enter the date for the typed signature.');
            }
            $acknowledgements[$key] = [
                'name_designation' => $entry['name_designation'] ?? '',
                'signature' => $entry['signature'] ?? '',
                'date' => $entry['date'] ?? null,
            ];
        }

        $risks = [];
        $references = [];
        foreach ($data['risks'] as $index => $risk) {
            $hasContent = collect($risk)->except([
                'initial_specialist_required', 'residual_reassess',
            ])->contains(fn ($value) => filled($value));
            if (! $hasContent && ! ($risk['initial_specialist_required'] ?? false) && ! ($risk['residual_reassess'] ?? false)) {
                continue;
            }
            if (blank($risk['specific_risk'] ?? null)) {
                $this->addError("form.risks.{$index}.specific_risk", 'Describe the specific risk before saving this record.');
            }
            $reference = trim((string) ($risk['reference'] ?? ''));
            $reference = $reference !== '' ? $reference : sprintf('R-%02d', $index + 1);
            if (in_array(mb_strtolower($reference), $references, true)) {
                $this->addError("form.risks.{$index}.reference", 'Risk references must be unique.');
            }
            $references[] = mb_strtolower($reference);

            $record = ['reference' => $reference, 'category' => $risk['category'] ?? ''];
            foreach (array_slice(self::RISK_TEXT_FIELDS, 1) as $field) {
                $record[$field] = $risk[$field] ?? '';
            }
            foreach (['target_date', 'last_reviewed', 'action_status'] as $field) {
                $record[$field] = $risk[$field] ?? null;
            }
            $record['initial_specialist_required'] = (bool) ($risk['initial_specialist_required'] ?? false);
            $record['residual_reassess'] = (bool) ($risk['residual_reassess'] ?? false);
            foreach (['initial_likelihood', 'initial_impact', 'expected_likelihood', 'expected_impact'] as $field) {
                $record[$field] = filled($risk[$field] ?? null) ? (int) $risk[$field] : null;
            }
            if ($record['initial_specialist_required']) {
                $record['initial_likelihood'] = null;
                $record['initial_impact'] = null;
            }
            if ($record['residual_reassess']) {
                $record['expected_likelihood'] = null;
                $record['expected_impact'] = null;
            }
            $risks[] = $record;
        }
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $data['checklist'] = array_intersect_key($data['checklist'], self::RISK_AREAS);
        $data['acknowledgements'] = $acknowledgements;
        $data['risks'] = $risks;
        $data['supporting_records'] = array_values(array_unique($data['supporting_records'] ?? []));

        $assessment = $this->project->riskAssessment()->firstOrNew();
        $assessment->fill([
            'form_data' => $data,
            'risk_count' => count($risks),
            'overall_position' => filled($data['overall_position'] ?? null) ? $data['overall_position'] : null,
            'next_review_date' => filled($data['next_review_date'] ?? null) ? $data['next_review_date'] : null,
            'updated_by' => auth()->id(),
        ]);
        if (! $assessment->exists) {
            $assessment->created_by = auth()->id();
        }
        $assessment->save();
        $this->form = $data;
        if ($this->form['risks'] === []) {
            $this->form['risks'] = [$this->emptyRisk()];
        }

        session()->flash('risks_success', 'Project risk assessment saved.');
    }

    public static function rating(mixed $likelihood, mixed $impact, bool $deferred = false, string $deferredLabel = 'Requires specialist assessment'): array
    {
        if ($deferred) {
            return [null, $deferredLabel];
        }
        $likelihood = filter_var($likelihood, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 5],
        ]);
        $impact = filter_var($impact, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 5],
        ]);
        if (! is_int($likelihood) || ! is_int($impact)) {
            return [null, 'Not assessed'];
        }
        $score = $likelihood * $impact;

        return [$score, match (true) {
            $score <= 4 => 'Low',
            $score <= 9 => 'Moderate',
            $score <= 16 => 'High',
            default => 'Critical',
        }];
    }

    public function render()
    {
        $register = [];
        foreach ($this->form['risks'] ?? [] as $index => $risk) {
            if (blank($risk['specific_risk'] ?? null)) {
                continue;
            }
            [$score, $level] = self::rating(
                $risk['initial_likelihood'] ?? null,
                $risk['initial_impact'] ?? null,
                (bool) ($risk['initial_specialist_required'] ?? false)
            );
            $register[] = [
                'reference' => filled($risk['reference'] ?? null) ? $risk['reference'] : sprintf('R-%02d', $index + 1),
                'description' => $risk['specific_risk'],
                'score' => $score,
                'level' => $level,
                'action' => $risk['action'] ?? '',
                'responsible_officer' => $risk['responsible_officer'] ?? '',
                'target_date' => $risk['target_date'] ?? null,
                'action_status' => $risk['action_status'] ?? '',
            ];
        }

        return view('livewire.real-estate-projects.risks', compact('register'));
    }

    private function defaults(): array
    {
        $proposal = $this->project->proposal_data ?? [];
        $user = auth()->user();
        $designation = $user?->job_title ?: $user?->position;
        $preparedBy = $user?->name ?? '';
        if (filled($designation)) {
            $preparedBy .= ' - '.$designation;
        }

        $sourceRisks = $this->project->feasibilityAssessments()->latest('id')->first()?->significant_risks;
        $draftRisks = [];
        if (is_array($sourceRisks)) {
            foreach ($sourceRisks as $risk) {
                if (is_array($risk) && filled($risk['issue'] ?? null)) {
                    $draftRisks[] = array_replace($this->emptyRisk(), [
                        'specific_risk' => $risk['issue'],
                        'action' => $risk['action'] ?? '',
                        'responsible_officer' => $risk['responsible_department'] ?? '',
                    ]);
                }
            }
        }
        if ($draftRisks === []) {
            foreach (($proposal['initial_risks'] ?? []) as $risk) {
                if (is_array($risk) && filled($risk['concern'] ?? null)) {
                    $draftRisks[] = array_replace($this->emptyRisk(), [
                        'specific_risk' => $risk['concern'],
                        'effect' => $risk['consequence'] ?? '',
                        'action' => $risk['further_assessment'] ?? '',
                    ]);
                }
            }
        }

        $acknowledgements = $this->emptyAcknowledgements();
        $acknowledgements['prepared']['name_designation'] = $preparedBy;

        return [
            'project_title' => $this->project->project_title ?? '',
            'project_reference' => $this->project->reference_no ?? '',
            'property_site_name' => $this->project->property_name ?? '',
            'building_structure_name' => $proposal['building_structure_name'] ?? '',
            'project_location' => $this->project->town_location ?? '',
            'project_stage' => '',
            'other_stage' => '',
            'assessment_date' => today()->toDateString(),
            'assessment_prepared_by' => $preparedBy,
            'responsible_department' => $proposal['originating_department'] ?? '',
            'assessment_review_date' => '',
            'checklist' => $this->emptyChecklist(),
            'other_risk_area' => '',
            'risks' => $draftRisks ?: [$this->emptyRisk()],
            'overall_position' => '',
            'main_outstanding_risks' => '',
            'immediate_measures' => '',
            'priority_actions' => '',
            'next_review_date' => '',
            'acknowledgements' => $acknowledgements,
            'supporting_records' => [],
            'other_supporting_record' => '',
            'document_references' => '',
        ];
    }

    private function emptyRisk(): array
    {
        return [
            'reference' => '', 'category' => '', 'specific_risk' => '', 'cause' => '',
            'effect' => '', 'existing_controls' => '', 'initial_likelihood' => null,
            'initial_impact' => null, 'initial_specialist_required' => false,
            'action' => '', 'responsible_officer' => '', 'target_date' => null,
            'resources' => '', 'expected_likelihood' => null, 'expected_impact' => null,
            'residual_reassess' => false, 'action_status' => '', 'evidence' => '',
            'last_reviewed' => null,
        ];
    }

    private function emptyChecklist(): array
    {
        return array_fill_keys(array_keys(self::RISK_AREAS), '');
    }

    private function emptyAcknowledgements(): array
    {
        return array_fill_keys(array_keys(self::ACKNOWLEDGEMENT_ROLES), [
            'name_designation' => '', 'signature' => '', 'date' => null,
        ]);
    }
}
