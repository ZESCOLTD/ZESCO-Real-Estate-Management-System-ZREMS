<?php

namespace App\Livewire\RealEstateProjects;

use App\Models\RealEstateProject;
use App\Models\User;
use App\Models\WorkflowInstance;
use App\Services\RealEstateProjectProposalWorkflowService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Approvals extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public array $comments = [];

    public function decide(int $workflowInstanceId, string $decision, RealEstateProjectProposalWorkflowService $workflowService): void
    {
        $this->authorize('real_estate_projects.approve');

        $instance = WorkflowInstance::query()->findOrFail($workflowInstanceId);
        $comment = trim((string) ($this->comments[$workflowInstanceId] ?? ''));

        if (in_array($decision, ['return', 'reject'], true) && $comment === '') {
            $this->addError("comments.{$workflowInstanceId}", 'A comment is required for this decision.');
            return;
        }

        $workflowService->decide($instance, $decision, $comment);
        unset($this->comments[$workflowInstanceId]);

        session()->flash('success', match ($decision) {
            'approve' => 'Approval recorded.',
            'return' => 'Proposal returned for revision.',
            'reject' => 'Proposal rejected. It can be revised and resubmitted.',
        });
    }

    public function render()
    {
        $this->authorize('real_estate_projects.view');

        $workflows = WorkflowInstance::query()
            ->where('workflowable_type', (new RealEstateProject())->getMorphClass())
            ->where('status', 'In Progress')
            ->whereJsonContains('current_approver_ids', (int) Auth::id())
            ->with(['workflowable', 'currentStep', 'initiator'])
            ->latest('updated_at')
            ->paginate(15);
        $approverIds = $workflows->getCollection()
            ->flatMap(fn (WorkflowInstance $workflow) => $workflow->current_approver_ids ?? [])
            ->unique()
            ->values();
        $approverNames = User::query()->whereIn('id', $approverIds)->pluck('name', 'id');

        return view('livewire.real-estate-projects.approvals', compact('workflows', 'approverNames'))
            ->layout('layouts.master', ['title' => 'Project Approvals']);
    }
}
