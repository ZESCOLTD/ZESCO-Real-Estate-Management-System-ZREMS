<?php

namespace App\Livewire\RealEstateProjects;

use App\Models\RealEstateProject;
use App\Models\User;
use App\Services\RealEstateProjectProposalWorkflowService;
use Livewire\Component;

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

    public function mount(RealEstateProject $project): void
    {
        $this->authorize('real_estate_projects.view');
        $this->project = $project;
        $this->activeTab = $project->proposal_status === null ? 'overview' : 'proposal';
        $assessment = $project->feasibilityAssessments()->latest('id')->first();
        $proposal = $project->proposal_data ?? [];

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
        ];
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

        return view('livewire.real-estate-projects.workspace', compact('workflow', 'currentApprovers', 'assessmentPreparers'))
            ->layout('layouts.master', ['title' => $this->project->reference_no]);
    }
}
