<?php

namespace App\Livewire\TenantRegister;

use App\Models\TenantOnboardingApplication;
use App\Models\WorkflowInstance;
use App\Services\TenantOnboardingWorkflowService;
use Livewire\Component;
use Livewire\WithPagination;

class Verification extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public array $comments = [];

    public function decide(int $workflowInstanceId, string $decision, TenantOnboardingWorkflowService $workflowService): void
    {
        $this->authorize('tenant_register.verify');

        $instance = WorkflowInstance::query()
            ->where('workflowable_type', (new TenantOnboardingApplication())->getMorphClass())
            ->findOrFail($workflowInstanceId);
        $comment = trim((string) ($this->comments[$workflowInstanceId] ?? ''));

        if ($decision === 'return' && $comment === '') {
            $this->addError("comments.{$workflowInstanceId}", 'A comment is required when returning an application.');

            return;
        }

        $workflowService->decide($instance, $decision, $comment);
        unset($this->comments[$workflowInstanceId]);

        session()->flash('success', $decision === 'verify'
            ? 'Tenant application verified.'
            : 'Tenant application returned for correction.');
    }

    public function render()
    {
        $this->authorize('tenant_register.verify');

        $query = WorkflowInstance::query()
            ->where('workflowable_type', (new TenantOnboardingApplication())->getMorphClass())
            ->where('status', 'In Progress')
            ->whereJsonContains('current_approver_ids', (int) auth()->id());

        return view('livewire.tenant-register.verification', [
            'workflows' => (clone $query)
                ->with(['workflowable.documents', 'currentStep', 'initiator'])
                ->latest('updated_at')
                ->paginate(10),
            'pendingCount' => (clone $query)->count(),
        ])->layout('layouts.master', ['title' => 'Tenant Verification']);
    }
}