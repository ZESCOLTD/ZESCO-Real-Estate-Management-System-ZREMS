<?php

namespace App\Services;

use App\Models\TenantOnboardingApplication;
use App\Models\User;
use App\Models\WorkflowAction;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStepApprover;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TenantOnboardingWorkflowService
{
    private const MODULE = 'tenant_onboarding';

    public function submit(TenantOnboardingApplication $application): WorkflowInstance
    {
        return DB::transaction(function () use ($application): WorkflowInstance {
            $application = TenantOnboardingApplication::query()->lockForUpdate()->findOrFail($application->id);

            if (! in_array($application->status, ['Draft', 'Returned for Correction'], true)) {
                throw ValidationException::withMessages([
                    'workflow' => 'Only draft or returned applications can be submitted for verification.',
                ]);
            }

            $definition = WorkflowDefinition::query()
                ->where('module', self::MODULE)
                ->where('is_active', true)
                ->where('is_default', true)
                ->first();

            if (! $definition) {
                throw ValidationException::withMessages([
                    'workflow' => 'No active default Tenant Verification workflow is configured.',
                ]);
            }

            $steps = $definition->steps()->where('is_active', true)->orderBy('step_order')->get();
            if ($steps->isEmpty()) {
                throw ValidationException::withMessages([
                    'workflow' => 'The Tenant Verification workflow has no active stages.',
                ]);
            }

            $snapshotSteps = [];
            foreach ($steps as $step) {
                $approverIds = $this->approverIdsFor($definition, $step, (int) $application->created_by);

                if ($approverIds === []) {
                    throw ValidationException::withMessages([
                        'workflow' => "Assign at least one reviewer to the {$step->step_name} stage.",
                    ]);
                }

                $snapshotSteps[] = [
                    'step_id' => (int) $step->id,
                    'step_order' => (int) $step->step_order,
                    'step_name' => $step->step_name,
                    'approver_ids' => $approverIds,
                    'allow_approve' => (bool) $step->allow_approve,
                    'allow_reject' => false,
                    'allow_revert' => (bool) $step->allow_revert,
                    'comment_required' => (bool) $step->comment_required,
                ];
            }

            $round = ((int) WorkflowInstance::query()
                ->where('workflowable_type', $application->getMorphClass())
                ->where('workflowable_id', $application->id)
                ->max('approval_round')) + 1;
            $firstStep = $snapshotSteps[0];

            $instance = WorkflowInstance::create([
                'workflow_definition_id' => $definition->id,
                'workflowable_type' => $application->getMorphClass(),
                'workflowable_id' => $application->id,
                'current_step_id' => $firstStep['step_id'],
                'current_step_order' => $firstStep['step_order'],
                'status' => 'In Progress',
                'approval_round' => $round,
                'workflow_snapshot' => ['steps' => $snapshotSteps],
                'current_approver_ids' => $firstStep['approver_ids'],
                'initiated_by' => Auth::id(),
                'initiated_at' => now(),
            ]);

            $this->recordAction($instance, $firstStep, 'submitted', 'Tenant application submitted for verification.');
            $application->update([
                'status' => 'Pending Verification',
                'declaration_confirmed' => true,
                'submitted_at' => now(),
            ]);
            app(TenantHistoryRecorder::class)->record(
                $application,
                'submitted_for_verification',
                'Tenant application submitted for verification.',
                ['workflow_instance_id' => $instance->id, 'approval_round' => $round]
            );

            return $instance->fresh();
        });
    }

    public function decide(WorkflowInstance $instance, string $decision, string $comment): WorkflowInstance
    {
        return DB::transaction(function () use ($instance, $decision, $comment): WorkflowInstance {
            $instance = WorkflowInstance::query()->lockForUpdate()->findOrFail($instance->id);

            if ($instance->status !== 'In Progress'
                || $instance->workflowable_type !== (new TenantOnboardingApplication())->getMorphClass()) {
                throw ValidationException::withMessages([
                    'verification' => 'This tenant verification is no longer active.',
                ]);
            }

            $userId = Auth::id();
            if (! $userId || ! in_array((int) $userId, $instance->current_approver_ids ?? [], true)) {
                throw new AccessDeniedHttpException('You are not assigned to this tenant verification.');
            }

            $step = collect($instance->workflow_snapshot['steps'] ?? [])
                ->firstWhere('step_id', (int) $instance->current_step_id);
            if (! $step) {
                throw ValidationException::withMessages([
                    'verification' => 'The current verification stage is missing from this review snapshot.',
                ]);
            }

            if (! in_array($decision, ['verify', 'return'], true)) {
                throw ValidationException::withMessages([
                    'verification' => 'Choose verify or return for correction.',
                ]);
            }

            if (($decision === 'verify' && ! $step['allow_approve'])
                || ($decision === 'return' && ! $step['allow_revert'])) {
                throw ValidationException::withMessages([
                    'verification' => 'This decision is not allowed at the current stage.',
                ]);
            }

            if (($decision === 'return' || $step['comment_required']) && trim($comment) === '') {
                throw ValidationException::withMessages([
                    'comment' => 'A comment is required when returning an application.',
                ]);
            }

            $action = $decision === 'verify' ? 'approved' : 'reverted';
            $this->recordAction($instance, $step, $action, trim($comment));
            $application = TenantOnboardingApplication::query()->lockForUpdate()->findOrFail($instance->workflowable_id);

            if ($decision === 'verify') {
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
                    $application->update(['status' => 'Verified']);
                    app(TenantHistoryRecorder::class)->record(
                        $application,
                        'verified',
                        'Tenant application verified.',
                        ['workflow_instance_id' => $instance->id, 'approval_round' => $instance->approval_round]
                    );
                }
            } else {
                $instance->update([
                    'current_step_id' => null,
                    'current_step_order' => null,
                    'current_approver_ids' => [],
                    'status' => 'Reverted',
                    'completed_by' => $userId,
                    'completed_at' => now(),
                ]);
                $application->update(['status' => 'Returned for Correction']);
                app(TenantHistoryRecorder::class)->record(
                    $application,
                    'returned_for_correction',
                    'Tenant application returned for correction.',
                    ['workflow_instance_id' => $instance->id, 'approval_round' => $instance->approval_round, 'comment' => trim($comment)]
                );
            }

            return $instance->fresh();
        });
    }

    private function approverIdsFor($definition, $step, int $initiatorId): array
    {
        if ($step->approver_type === 'role' && filled($step->approver_value)) {
            $userIds = User::query()->role($step->approver_value)->pluck('users.id');
            $role = Role::query()
                ->where('name', $step->approver_value)
                ->where('guard_name', 'web')
                ->first();

            try {
                if ($role && Schema::hasColumn((new User())->getTable(), 'role_id')) {
                    $userIds = $userIds->merge(
                        User::query()->where('role_id', $role->id)->pluck('id')
                    );
                }
            } catch (\Throwable) {
                // Some production drivers do not reliably report column metadata.
            }

            return $userIds->map(fn ($id) => (int) $id)->unique()->values()->all();
        }

        if ($step->approver_type === 'user') {
            return WorkflowStepApprover::query()
                ->where('workflow_definition_id', $definition->id)
                ->where('workflow_step_id', $step->id)
                ->where('initiator_user_id', $initiatorId)
                ->where('is_active', true)
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)->unique()->values()->all();
        }

        return [];
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