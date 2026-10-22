<?php

namespace App\Services;

use App\Models\RealEstateProject;
use App\Models\WorkflowAction;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStepApprover;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RealEstateProjectProposalWorkflowService
{
    private const MODULE = 'real_estate_project_proposal';

    public function submit(RealEstateProject $project): WorkflowInstance
    {
        return DB::transaction(function () use ($project): WorkflowInstance {
            $project = RealEstateProject::query()->lockForUpdate()->findOrFail($project->id);

            if ($project->record_origin !== 'proposal') {
                throw ValidationException::withMessages([
                    'proposal' => 'Only project proposals can be submitted for proposal review.',
                ]);
            }

            $proposalData = $project->proposal_data ?? [];
            $requiredFields = [
                'proposed_project_title' => 'Proposed project title',
                'primary_intervention' => 'Primary nature of the proposal',
                'intervention_description' => 'Brief description of the proposal',
                'operational_need' => 'Identified problem or operational need',
                'proposal_summary' => 'Proposal summary',
            ];
            $missingFields = collect($requiredFields)
                ->filter(fn (string $label, string $field) => blank($proposalData[$field] ?? null))
                ->values()
                ->all();

            if ($missingFields !== []) {
                throw ValidationException::withMessages([
                    'proposal' => 'Complete these proposal fields before submission: ' . implode(', ', $missingFields) . '.',
                ]);
            }

            if (! in_array($project->proposal_status, ['Draft', 'Returned for Revision', 'Rejected'], true)) {
                throw ValidationException::withMessages([
                    'proposal' => 'This proposal is not ready to start a new review round.',
                ]);
            }

            $definition = WorkflowDefinition::query()
                ->where('module', self::MODULE)
                ->where('is_active', true)
                ->where('is_default', true)
                ->first();

            if (! $definition) {
                throw ValidationException::withMessages([
                    'workflow' => 'No active default Project Proposal workflow is configured.',
                ]);
            }

            $steps = $definition->steps()
                ->where('is_active', true)
                ->orderBy('step_order')
                ->get();

            if ($steps->isEmpty()) {
                throw ValidationException::withMessages([
                    'workflow' => 'The Project Proposal workflow has no active stages.',
                ]);
            }

            $initiatorId = $project->created_by ?? Auth::id();
            $snapshotSteps = [];

            foreach ($steps as $step) {
                $approverIds = WorkflowStepApprover::query()
                    ->where('workflow_definition_id', $definition->id)
                    ->where('workflow_step_id', $step->id)
                    ->where('initiator_user_id', $initiatorId)
                    ->where('is_active', true)
                    ->pluck('user_id')
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();

                if ($approverIds === []) {
                    throw ValidationException::withMessages([
                        'workflow' => "Assign at least one approver to the {$step->step_name} stage for this initiator.",
                    ]);
                }

                $snapshotSteps[] = [
                    'step_id' => (int) $step->id,
                    'step_order' => (int) $step->step_order,
                    'step_name' => $step->step_name,
                    'approver_ids' => $approverIds,
                    'allow_approve' => (bool) $step->allow_approve,
                    'allow_reject' => (bool) $step->allow_reject,
                    'allow_revert' => (bool) $step->allow_revert,
                    'comment_required' => (bool) $step->comment_required,
                ];
            }

            $round = ((int) WorkflowInstance::query()
                ->where('workflowable_type', $project->getMorphClass())
                ->where('workflowable_id', $project->id)
                ->max('approval_round')) + 1;
            $firstStep = $snapshotSteps[0];

            $instance = WorkflowInstance::create([
                'workflow_definition_id' => $definition->id,
                'workflowable_type' => $project->getMorphClass(),
                'workflowable_id' => $project->id,
                'current_step_id' => $firstStep['step_id'],
                'current_step_order' => $firstStep['step_order'],
                'status' => 'In Progress',
                'approval_round' => $round,
                'workflow_snapshot' => ['steps' => $snapshotSteps],
                'current_approver_ids' => $firstStep['approver_ids'],
                'initiated_by' => Auth::id(),
                'initiated_at' => now(),
            ]);

            $this->recordAction($instance, $firstStep, 'submitted', 'Proposal submitted for review.');
            $project->update(['proposal_status' => 'Under Review']);

            return $instance->fresh();
        });
    }

    public function decide(WorkflowInstance $instance, string $decision, string $comment): WorkflowInstance
    {
        return DB::transaction(function () use ($instance, $decision, $comment): WorkflowInstance {
            $instance = WorkflowInstance::query()->lockForUpdate()->findOrFail($instance->id);

            if ($instance->status !== 'In Progress'
                || $instance->workflowable_type !== (new RealEstateProject())->getMorphClass()) {
                throw ValidationException::withMessages([
                    'approval' => 'This project review is no longer active.',
                ]);
            }

            $userId = Auth::id();
            if (! $userId || ! in_array((int) $userId, $instance->current_approver_ids ?? [], true)) {
                throw new AccessDeniedHttpException('You are not assigned to the current approval stage.');
            }

            $step = collect($instance->workflow_snapshot['steps'] ?? [])
                ->firstWhere('step_id', (int) $instance->current_step_id);

            if (! $step) {
                throw ValidationException::withMessages([
                    'approval' => 'The current workflow stage is missing from this review snapshot.',
                ]);
            }

            $decision = strtolower($decision);
            $action = match ($decision) {
                'approve' => 'approved',
                'return' => 'reverted',
                'reject' => 'rejected',
                default => null,
            };

            if (! $action) {
                throw ValidationException::withMessages([
                    'approval' => 'Choose approve, return for revision, or reject.',
                ]);
            }

            $allowed = match ($decision) {
                'approve' => $step['allow_approve'],
                'return' => $step['allow_revert'],
                'reject' => $step['allow_reject'],
            };

            if (! $allowed) {
                throw ValidationException::withMessages([
                    'approval' => 'This decision is not allowed at the current stage.',
                ]);
            }

            if (trim($comment) === '' && ($step['comment_required'] || $decision !== 'approve')) {
                throw ValidationException::withMessages([
                    'comment' => 'A comment is required for this decision.',
                ]);
            }

            $this->recordAction($instance, $step, $action, trim($comment));
            $project = RealEstateProject::query()->lockForUpdate()->findOrFail($instance->workflowable_id);

            if ($decision === 'approve') {
                $nextStep = collect($instance->workflow_snapshot['steps'])
                    ->first(fn (array $candidate) => $candidate['step_order'] > $step['step_order']);

                if ($nextStep) {
                    $instance->update([
                        'current_step_id' => $nextStep['step_id'],
                        'current_step_order' => $nextStep['step_order'],
                        'current_approver_ids' => $nextStep['approver_ids'],
                    ]);
                } else {
                    $instance->update([
                        'current_step_id' => null,
                        'current_step_order' => null,
                        'current_approver_ids' => [],
                        'status' => 'Completed',
                        'completed_by' => $userId,
                        'completed_at' => now(),
                    ]);
                    $project->update(['proposal_status' => 'Approved']);
                }
            } else {
                $instance->update([
                    'current_step_id' => null,
                    'current_step_order' => null,
                    'current_approver_ids' => [],
                    'status' => $decision === 'return' ? 'Reverted' : 'Rejected',
                    'completed_by' => $userId,
                    'completed_at' => now(),
                ]);
                $project->update([
                    'proposal_status' => $decision === 'return' ? 'Returned for Revision' : 'Rejected',
                ]);
            }

            return $instance->fresh();
        });
    }

    private function recordAction(WorkflowInstance $instance, array $step, string $action, string $comment): void
    {
        WorkflowAction::create([
            'workflow_instance_id' => $instance->id,
            'workflowable_type' => $instance->workflowable_type,
            'workflowable_id' => $instance->workflowable_id,
            'workflow_step_id' => $step['step_id'],
            'step_order' => $step['step_order'],
            'step_name' => $step['step_name'],
            'action' => $action,
            'comment' => $comment,
            'acted_by' => Auth::id(),
            'acted_at' => now(),
        ]);
    }
}
