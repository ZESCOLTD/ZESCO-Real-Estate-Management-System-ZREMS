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

    public function render()
    {
        $workflow = $this->project->workflowInstances()
            ->with(['currentStep', 'actions.actor'])
            ->first();
        $currentApprovers = $workflow
            ? User::query()->whereIn('id', $workflow->current_approver_ids ?? [])->get()
            : collect();

        return view('livewire.real-estate-projects.workspace', compact('workflow', 'currentApprovers'))
            ->layout('layouts.master', ['title' => $this->project->reference_no]);
    }
}
