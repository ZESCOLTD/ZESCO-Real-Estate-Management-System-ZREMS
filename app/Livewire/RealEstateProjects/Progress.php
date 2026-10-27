<?php

namespace App\Livewire\RealEstateProjects;

use App\Models\ProjectProgressReport;
use App\Models\RealEstateProject;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Progress extends Component
{
    public RealEstateProject $project;

    public ?int $editingReportId = null;

    public array $form = [];

    public const PROJECT_STATUSES = ['Not started', 'In progress', 'On hold', 'Practically complete', 'Completed'];
    public const PROGRAMME_POSITIONS = ['On schedule', 'Behind schedule', 'Ahead of schedule', 'Not established'];
    public const COMPLETION_CHANGE_REASONS = ['None', 'Procurement delay', 'Funding', 'Design / scope change', 'Site access', 'Weather', 'Other'];
    public const COMPLETED_CATEGORIES = ['Site preparation', 'Demolition / removal', 'Civil / structural works', 'Roofing', 'Finishes', 'Electrical', 'Plumbing / mechanical', 'Inspection / testing', 'Procurement / delivery', 'Documentation / coordination', 'Other'];
    public const CURRENT_CATEGORIES = ['Civil / structural', 'Roofing', 'Finishes', 'Electrical', 'Plumbing / mechanical', 'External works', 'Procurement', 'Inspections', 'Coordination / documentation', 'Other'];
    public const PLANNED_CATEGORIES = ['Mobilisation', 'Civil / structural', 'Roofing', 'Finishes', 'Electrical', 'Plumbing / mechanical', 'External works', 'Inspection / testing', 'Procurement', 'Coordination', 'Other'];
    public const MILESTONE_STATUSES = ['Not started', 'On schedule', 'Behind schedule', 'Completed', 'On hold'];
    public const COST_ITEMS = [
        'approved_budget' => 'Approved Project Budget',
        'contract_value' => 'Current Approved Contract Value',
        'approved_variations' => 'Approved Variations / Changes',
        'approved_commitments' => 'Total Approved Commitments',
        'certified_expenditure' => 'Expenditure Certified / Verified to Date',
        'confirmed_payments' => 'Payments Confirmed to Date',
        'forecast_final_cost' => 'Forecast Final Project Cost',
    ];
    public const ISSUE_TYPES = ['Materials / Procurement', 'Funding', 'Design / Technical', 'Labour / Contractor', 'Weather', 'Access / Site clearance', 'Approvals', 'Utilities', 'No issues', 'Other'];
    public const REVIEW_ROLES = [
        'prepared' => 'Prepared by',
        'verified' => 'Progress verified by',
        'reviewed' => 'Reviewed by',
    ];
    public const ATTACHMENTS = ['Updated programme', 'Site progress photographs', 'Inspection / test records', 'Cost statement', 'Variation / instruction register', 'Updated risk register', 'Other'];

    private const TEXT_FIELDS = [
        'project_title', 'project_reference', 'property_site_name', 'building_structure_name',
        'town_location', 'implementing_department', 'coordinator', 'contractor',
        'completion_change_other', 'completion_change_details', 'completed_other',
        'completed_details', 'current_other', 'current_details', 'planned_other',
        'planned_details', 'cost_change_details', 'issue_type_other', 'safety_details',
        'inspection_reference', 'quality_details', 'attachment_other',
    ];

    private const DATE_FIELDS = [
        'period_from', 'period_to', 'report_date', 'planned_start', 'actual_start',
        'original_planned_completion', 'forecast_completion',
    ];

    private const SELECTS = [
        'current_status' => self::PROJECT_STATUSES,
        'programme_position' => self::PROGRAMME_POSITIONS,
        'completion_change_reason' => self::COMPLETION_CHANGE_REASONS,
        'completion_position' => ['All planned activities completed', 'Some planned activities completed', 'No activities completed', 'No activities scheduled'],
        'current_activity_progress' => ['Proceeding as planned', 'Slower than planned', 'Temporarily suspended', 'Awaiting materials / approvals', 'Not applicable'],
        'planned_readiness' => ['Ready', 'Subject to materials', 'Subject to approval / instruction', 'Subject to funding', 'Subject to access / site clearance', 'To be confirmed'],
        'budget_position' => ['Within budget', 'Potential overrun', 'Over budget', 'Not yet established', 'Information unavailable'],
        'pending_cost_changes' => ['None identified', 'Yes', 'Not confirmed'],
        'new_changed_risks' => ['No', 'Yes'],
        'safety_incident' => ['No', 'Yes'],
        'urgent_intervention' => ['No', 'Yes'],
        'quality_inspection' => ['Conducted and satisfactory', 'Conducted with observations', 'Not conducted', 'Not applicable'],
        'outstanding_defects' => ['None identified', 'Identified and being addressed', 'Identified; action pending', 'Not assessed'],
        'photos_evidence' => ['Yes', 'No'],
        'handover_documentation' => ['Attached', 'Pending', 'Not applicable'],
    ];

    public function mount(RealEstateProject $project): void
    {
        $this->authorize('real_estate_projects.view');
        $this->project = $project;
        $latest = $project->progressReports()->latest('id')->first();
        $this->editingReportId = $latest?->id;
        $this->form = $latest
            ? $this->normalizeForm($latest->form_data ?? [])
            : $this->defaults();
    }

    public function openReport(int $id): void
    {
        $this->authorize('real_estate_projects.view');
        $report = $this->project->progressReports()->findOrFail($id);
        $this->editingReportId = $report->id;
        $this->form = $this->normalizeForm($report->form_data ?? []);
        $this->resetValidation();
    }

    public function newReport(): void
    {
        $this->authorize('real_estate_projects.update');
        $this->editingReportId = null;
        $this->form = $this->defaults(true);
        $this->resetValidation();
    }

    public function addMilestone(): void
    {
        $this->authorize('real_estate_projects.update');
        if (count($this->form['milestones'] ?? []) < 50) {
            $this->form['milestones'][] = $this->emptyMilestone();
        }
    }

    public function removeMilestone(int $index): void
    {
        $this->authorize('real_estate_projects.update');
        $this->form['milestones'] = $this->removeRow($this->form['milestones'] ?? [], $index, $this->emptyMilestone());
    }

    public function addIssue(): void
    {
        $this->authorize('real_estate_projects.update');
        if (count($this->form['issues'] ?? []) < 30) {
            $this->form['issues'][] = $this->emptyIssue();
        }
    }

    public function removeIssue(int $index): void
    {
        $this->authorize('real_estate_projects.update');
        $this->form['issues'] = $this->removeRow($this->form['issues'] ?? [], $index, $this->emptyIssue());
    }

    public function addSupportDecision(): void
    {
        $this->authorize('real_estate_projects.update');
        if (count($this->form['support_decisions'] ?? []) < 30) {
            $this->form['support_decisions'][] = $this->emptySupportDecision();
        }
    }

    public function removeSupportDecision(int $index): void
    {
        $this->authorize('real_estate_projects.update');
        $this->form['support_decisions'] = $this->removeRow($this->form['support_decisions'] ?? [], $index, $this->emptySupportDecision());
    }

    public function save(): void
    {
        $this->authorize('real_estate_projects.update');

        $rules = [];
        foreach (self::TEXT_FIELDS as $field) {
            $rules["form.{$field}"] = ['nullable', 'string', 'max:5000'];
        }
        foreach (self::DATE_FIELDS as $field) {
            $rules["form.{$field}"] = ['nullable', 'date_format:Y-m-d'];
        }
        foreach (self::SELECTS as $field => $options) {
            $rules["form.{$field}"] = ['nullable', Rule::in($options)];
        }
        $rules['form.overall_progress'] = ['nullable', 'integer', 'between:0,100'];
        foreach ([
            'completed_categories' => self::COMPLETED_CATEGORIES,
            'current_categories' => self::CURRENT_CATEGORIES,
            'planned_categories' => self::PLANNED_CATEGORIES,
            'issue_types' => self::ISSUE_TYPES,
            'attachments' => self::ATTACHMENTS,
        ] as $field => $options) {
            $rules["form.{$field}"] = ['array'];
            $rules["form.{$field}.*"] = [Rule::in($options)];
        }
        $rules['form.milestones'] = ['required', 'array', 'min:1', 'max:50'];
        $rules['form.milestones.*'] = ['required', 'array'];
        foreach (['name', 'delay_reason', 'follow_up_action', 'follow_up_responsible'] as $field) {
            $rules["form.milestones.*.{$field}"] = ['nullable', 'string', 'max:5000'];
        }
        foreach (['planned_date', 'actual_forecast_date', 'follow_up_due_date'] as $field) {
            $rules["form.milestones.*.{$field}"] = ['nullable', 'date_format:Y-m-d'];
        }
        $rules['form.milestones.*.completion_percent'] = ['nullable', 'integer', 'between:0,100'];
        $rules['form.milestones.*.status'] = ['nullable', Rule::in(self::MILESTONE_STATUSES)];
        $rules['form.costs'] = ['required', 'array'];
        foreach (self::COST_ITEMS as $key => $label) {
            $rules["form.costs.{$key}"] = ['required', 'array'];
            $rules["form.costs.{$key}.amount"] = ['nullable', 'numeric', 'min:0', 'max:999999999999.99'];
            $rules["form.costs.{$key}.source"] = ['nullable', 'string', 'max:5000'];
        }
        $rules['form.issues'] = ['required', 'array', 'min:1', 'max:30'];
        $rules['form.issues.*'] = ['required', 'array'];
        foreach (['cause', 'effect', 'action', 'responsible'] as $field) {
            $rules["form.issues.*.{$field}"] = ['nullable', 'string', 'max:5000'];
        }
        $rules['form.issues.*.target_date'] = ['nullable', 'date_format:Y-m-d'];
        $rules['form.support_decisions'] = ['required', 'array', 'min:1', 'max:30'];
        $rules['form.support_decisions.*'] = ['required', 'array'];
        foreach (['decision', 'responsible'] as $field) {
            $rules["form.support_decisions.*.{$field}"] = ['nullable', 'string', 'max:5000'];
        }
        $rules['form.support_decisions.*.date_needed'] = ['nullable', 'date_format:Y-m-d'];
        $rules['form.reviews'] = ['required', 'array'];
        foreach (self::REVIEW_ROLES as $key => $label) {
            $rules["form.reviews.{$key}"] = ['required', 'array'];
            $rules["form.reviews.{$key}.name_designation"] = ['nullable', 'string', 'max:255'];
            $rules["form.reviews.{$key}.signature"] = ['nullable', 'string', 'max:255'];
            $rules["form.reviews.{$key}.date"] = ['nullable', 'date_format:Y-m-d'];
        }

        $data = $this->validate($rules)['form'];

        if (filled($data['period_from'] ?? null) && filled($data['period_to'] ?? null)
            && $data['period_to'] < $data['period_from']) {
            $this->addError('form.period_to', 'The reporting period end must be on or after its start.');
        }
        foreach ([
            ['completion_change_reason', 'Other', 'completion_change_other', 'Describe the other completion date change reason.'],
            ['completed_categories', 'Other', 'completed_other', 'Describe the other completed activity.'],
            ['current_categories', 'Other', 'current_other', 'Describe the other current activity.'],
            ['planned_categories', 'Other', 'planned_other', 'Describe the other planned activity.'],
            ['issue_types', 'Other', 'issue_type_other', 'Describe the other issue type.'],
            ['attachments', 'Other', 'attachment_other', 'Describe the other supporting record.'],
        ] as [$selector, $option, $field, $message]) {
            $selected = is_array($data[$selector] ?? null)
                ? in_array($option, $data[$selector], true)
                : ($data[$selector] ?? null) === $option;
            if ($selected && blank($data[$field] ?? null)) {
                $this->addError("form.{$field}", $message);
            }
            if (! $selected) {
                $data[$field] = null;
            }
        }
        if (in_array('No issues', $data['issue_types'] ?? [], true)
            && count($data['issue_types']) > 1) {
            $this->addError('form.issue_types', 'Choose No issues by itself, or select the applicable issue types.');
        }
        if (in_array('Yes', [
            $data['new_changed_risks'] ?? null,
            $data['safety_incident'] ?? null,
            $data['urgent_intervention'] ?? null,
        ], true) && blank($data['safety_details'] ?? null)) {
            $this->addError('form.safety_details', 'Describe the identified risk, safety concern, or urgent intervention.');
        }

        $milestones = [];
        foreach ($data['milestones'] as $index => $row) {
            if (! $this->rowHasContent($row)) {
                continue;
            }
            if (blank($row['name'] ?? null)) {
                $this->addError("form.milestones.{$index}.name", 'Name the milestone before saving this row.');
            }
            $milestones[] = [
                'name' => $row['name'] ?? '',
                'planned_date' => $row['planned_date'] ?? null,
                'actual_forecast_date' => $row['actual_forecast_date'] ?? null,
                'completion_percent' => filled($row['completion_percent'] ?? null) ? (int) $row['completion_percent'] : null,
                'status' => $row['status'] ?? '',
                'delay_reason' => $row['delay_reason'] ?? '',
                'follow_up_action' => $row['follow_up_action'] ?? '',
                'follow_up_responsible' => $row['follow_up_responsible'] ?? '',
                'follow_up_due_date' => $row['follow_up_due_date'] ?? null,
            ];
        }
        $issues = [];
        foreach ($data['issues'] as $index => $row) {
            if (! $this->rowHasContent($row)) {
                continue;
            }
            if (blank($row['cause'] ?? null)) {
                $this->addError("form.issues.{$index}.cause", 'Describe the issue or cause before saving this row.');
            }
            $issues[] = [
                'cause' => $row['cause'] ?? '', 'effect' => $row['effect'] ?? '',
                'action' => $row['action'] ?? '', 'responsible' => $row['responsible'] ?? '',
                'target_date' => $row['target_date'] ?? null,
            ];
        }
        $support = [];
        foreach ($data['support_decisions'] as $index => $row) {
            if (! $this->rowHasContent($row)) {
                continue;
            }
            if (blank($row['decision'] ?? null)) {
                $this->addError("form.support_decisions.{$index}.decision", 'Describe the support or decision needed.');
            }
            $support[] = [
                'decision' => $row['decision'] ?? '',
                'responsible' => $row['responsible'] ?? '',
                'date_needed' => $row['date_needed'] ?? null,
            ];
        }
        $reviews = [];
        foreach (self::REVIEW_ROLES as $key => $label) {
            $entry = $data['reviews'][$key] ?? [];
            if (filled($entry['signature'] ?? null) && blank($entry['date'] ?? null)) {
                $this->addError("form.reviews.{$key}.date", 'Enter the date for the typed signature.');
            }
            $reviews[$key] = [
                'name_designation' => $entry['name_designation'] ?? '',
                'signature' => $entry['signature'] ?? '',
                'date' => $entry['date'] ?? null,
            ];
        }
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        foreach (['completed_categories', 'current_categories', 'planned_categories', 'issue_types', 'attachments'] as $field) {
            $data[$field] = array_values(array_unique($data[$field] ?? []));
        }
        $data['milestones'] = $milestones;
        $data['issues'] = $issues;
        $data['support_decisions'] = $support;
        $data['reviews'] = $reviews;
        $data['costs'] = array_map(
            fn ($key) => [
                'amount' => $data['costs'][$key]['amount'] ?? null,
                'source' => $data['costs'][$key]['source'] ?? '',
            ],
            array_keys(self::COST_ITEMS)
        );
        $data['costs'] = array_combine(array_keys(self::COST_ITEMS), $data['costs']);

        $report = $this->editingReportId === null
            ? $this->project->progressReports()->make()
            : $this->project->progressReports()->findOrFail($this->editingReportId);
        $report->fill([
            'period_from' => filled($data['period_from'] ?? null) ? $data['period_from'] : null,
            'period_to' => filled($data['period_to'] ?? null) ? $data['period_to'] : null,
            'report_date' => filled($data['report_date'] ?? null) ? $data['report_date'] : null,
            'current_status' => filled($data['current_status'] ?? null) ? $data['current_status'] : null,
            'overall_progress' => filled($data['overall_progress'] ?? null) ? (int) $data['overall_progress'] : null,
            'form_data' => $data,
            'updated_by' => auth()->id(),
        ]);
        if (! $report->exists) {
            $report->created_by = auth()->id();
        }
        $report->save();
        $this->editingReportId = $report->id;
        $this->form = $this->normalizeForm($data);

        session()->flash('progress_success', 'Project progress report saved.');
    }

    public function render()
    {
        $reports = $this->project->progressReports()
            ->latest('id')
            ->get(['id', 'period_from', 'period_to', 'report_date', 'current_status', 'overall_progress']);

        return view('livewire.real-estate-projects.progress', compact('reports'));
    }

    private function defaults(bool $carryMilestones = false): array
    {
        $proposal = $this->project->proposal_data ?? [];
        $milestones = [];
        if ($carryMilestones) {
            $previous = $this->project->progressReports()->latest('id')->first();
            foreach (($previous?->form_data['milestones'] ?? []) as $row) {
                if (is_array($row) && filled($row['name'] ?? null)) {
                    $milestones[] = array_replace($this->emptyMilestone(), [
                        'name' => $row['name'],
                        'planned_date' => $row['planned_date'] ?? null,
                    ]);
                }
            }
        }
        while (count($milestones) < 5) {
            $milestones[] = $this->emptyMilestone();
        }
        $reviews = $this->emptyReviews();
        $reviews['prepared']['name_designation'] = auth()->user()?->name ?? '';
        $costs = $this->emptyCosts();
        if ($this->project->approved_budget !== null) {
            $costs['approved_budget'] = [
                'amount' => $this->project->approved_budget,
                'source' => 'Project register — verify against approved budget',
            ];
        }

        return [
            'project_title' => $this->project->project_title ?? '',
            'project_reference' => $this->project->reference_no ?? '',
            'property_site_name' => $this->project->property_name ?? '',
            'building_structure_name' => $proposal['building_structure_name'] ?? '',
            'town_location' => $this->project->town_location ?? '',
            'implementing_department' => $proposal['originating_department'] ?? '',
            'coordinator' => $this->project->responsible_officer ?? '',
            'contractor' => '',
            'period_from' => '', 'period_to' => '', 'report_date' => today()->toDateString(),
            'current_status' => '', 'overall_progress' => '', 'programme_position' => '',
            'planned_start' => $this->project->start_date?->format('Y-m-d') ?? '',
            'actual_start' => '',
            'original_planned_completion' => $this->project->expected_completion_date?->format('Y-m-d') ?? '',
            'forecast_completion' => '',
            'completion_change_reason' => '', 'completion_change_other' => '',
            'completion_change_details' => '',
            'completed_categories' => [], 'completed_other' => '',
            'completion_position' => '', 'completed_details' => '',
            'current_categories' => [], 'current_other' => '',
            'current_activity_progress' => '', 'current_details' => '',
            'planned_categories' => [], 'planned_other' => '',
            'planned_readiness' => '', 'planned_details' => '',
            'milestones' => $milestones,
            'costs' => $costs,
            'budget_position' => '', 'pending_cost_changes' => '',
            'cost_change_details' => '',
            'issue_types' => [], 'issue_type_other' => '',
            'issues' => array_fill(0, 3, $this->emptyIssue()),
            'new_changed_risks' => '', 'safety_incident' => '',
            'urgent_intervention' => '', 'safety_details' => '',
            'quality_inspection' => '', 'inspection_reference' => '',
            'outstanding_defects' => '', 'photos_evidence' => '',
            'handover_documentation' => '', 'quality_details' => '',
            'support_decisions' => array_fill(0, 2, $this->emptySupportDecision()),
            'reviews' => $reviews,
            'attachments' => [], 'attachment_other' => '',
        ];
    }

    private function normalizeForm(array $saved): array
    {
        $form = array_replace($this->defaults(), $saved);
        $form['costs'] = array_replace(
            $this->emptyCosts(),
            is_array($form['costs'] ?? null) ? $form['costs'] : []
        );
        $form['reviews'] = array_replace(
            $this->emptyReviews(),
            is_array($form['reviews'] ?? null) ? $form['reviews'] : []
        );
        foreach (['milestones' => $this->emptyMilestone(), 'issues' => $this->emptyIssue(), 'support_decisions' => $this->emptySupportDecision()] as $field => $blank) {
            $form[$field] = is_array($form[$field] ?? null) ? array_values($form[$field]) : [];
            if ($form[$field] === []) {
                $form[$field] = [$blank];
            }
        }
        foreach (['completed_categories', 'current_categories', 'planned_categories', 'issue_types', 'attachments'] as $field) {
            $form[$field] = is_array($form[$field] ?? null) ? $form[$field] : [];
        }

        return $form;
    }

    private function emptyMilestone(): array
    {
        return [
            'name' => '', 'planned_date' => null, 'actual_forecast_date' => null,
            'completion_percent' => null, 'status' => '', 'delay_reason' => '',
            'follow_up_action' => '', 'follow_up_responsible' => '',
            'follow_up_due_date' => null,
        ];
    }

    private function emptyIssue(): array
    {
        return ['cause' => '', 'effect' => '', 'action' => '', 'responsible' => '', 'target_date' => null];
    }

    private function emptySupportDecision(): array
    {
        return ['decision' => '', 'responsible' => '', 'date_needed' => null];
    }

    private function emptyCosts(): array
    {
        return array_fill_keys(array_keys(self::COST_ITEMS), ['amount' => '', 'source' => '']);
    }

    private function emptyReviews(): array
    {
        return array_fill_keys(array_keys(self::REVIEW_ROLES), [
            'name_designation' => '', 'signature' => '', 'date' => null,
        ]);
    }

    private function removeRow(array $rows, int $index, array $blank): array
    {
        if (array_key_exists($index, $rows)) {
            unset($rows[$index]);
        }

        return array_values($rows) ?: [$blank];
    }

    private function rowHasContent(array $row): bool
    {
        return collect($row)->contains(fn ($value) => filled($value));
    }
}
