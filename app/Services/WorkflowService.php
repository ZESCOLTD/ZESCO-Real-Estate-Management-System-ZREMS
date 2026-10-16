<?php

namespace App\Services;

use App\Models\AcquisitionRequest;
use App\Models\WorkflowAction;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkflowService
{
    /**
     * Start workflow for an acquisition request
     */
    public function startAcquisitionWorkflow(AcquisitionRequest $request): WorkflowInstance
    {
        return DB::transaction(function () use ($request) {
            // Prevent duplicate workflow
            $existing = WorkflowInstance::where('acquisition_request_id', $request->id)->first();
            if ($existing) {
                return $existing;
            }

            // Get active default workflow for acquisition requests
            $workflowDefinition = WorkflowDefinition::with(['steps' => function ($q) {
                $q->where('is_active', true)->orderBy('step_order');
            }])
                ->where('module', 'acquisition_request')
                ->where('is_active', true)
                ->where('is_default', true)
                ->firstOrFail();

            $firstStep = $workflowDefinition->steps->first();

            $instance = WorkflowInstance::create([
                'workflow_definition_id' => $workflowDefinition->id,
                'acquisition_request_id' => $request->id,
                'current_step_id' => $firstStep?->id,
                'current_step_order' => $firstStep?->step_order,
                'status' => 'In Progress',
                'initiated_by' => Auth::id(),
                'initiated_at' => now(),
            ]);

            WorkflowAction::create([
                'workflow_instance_id' => $instance->id,
                'acquisition_request_id' => $request->id,
                'workflow_step_id' => $firstStep?->id,
                'step_order' => $firstStep?->step_order,
                'step_name' => $firstStep?->step_name,
                'action' => 'submitted',
                'comment' => 'Request submitted for approval.',
                'acted_by' => Auth::id(),
                'acted_at' => now(),
            ]);

            return $instance;
        });
    }

    /**
     * Approve current step
     */
    public function approve(WorkflowInstance $instance, string $comment): WorkflowInstance
    {
        return DB::transaction(function () use ($instance, $comment) {
            $currentStep = $instance->currentStep;

            WorkflowAction::create([
                'workflow_instance_id' => $instance->id,
                'acquisition_request_id' => $instance->acquisition_request_id,
                'workflow_step_id' => $currentStep?->id,
                'step_order' => $currentStep?->step_order,
                'step_name' => $currentStep?->step_name,
                'action' => 'approved',
                'comment' => $comment,
                'acted_by' => Auth::id(),
                'acted_at' => now(),
            ]);

            $nextStep = $instance->workflowDefinition
                ->steps()
                ->where('is_active', true)
                ->where('step_order', '>', $instance->current_step_order)
                ->orderBy('step_order')
                ->first();

            if ($nextStep) {
                $instance->update([
                    'current_step_id' => $nextStep->id,
                    'current_step_order' => $nextStep->step_order,
                    'status' => 'In Progress',
                ]);

                $instance->acquisitionRequest()->update([
                    'status' => 'Under Review',
                ]);
            } else {
                // Final approval
                $instance->update([
                    'current_step_id' => null,
                    'current_step_order' => null,
                    'status' => 'Completed',
                    'completed_by' => Auth::id(),
                    'completed_at' => now(),
                ]);

                $instance->acquisitionRequest()->update([
                    'status' => 'Approved',
                    'approved_at' => now(),
                ]);
            }

            return $instance->fresh();
        });
    }

    /**
     * Reject current step
     */
    public function reject(WorkflowInstance $instance, string $comment): WorkflowInstance
    {
        return DB::transaction(function () use ($instance, $comment) {
            $currentStep = $instance->currentStep;

            WorkflowAction::create([
                'workflow_instance_id' => $instance->id,
                'acquisition_request_id' => $instance->acquisition_request_id,
                'workflow_step_id' => $currentStep?->id,
                'step_order' => $currentStep?->step_order,
                'step_name' => $currentStep?->step_name,
                'action' => 'rejected',
                'comment' => $comment,
                'acted_by' => Auth::id(),
                'acted_at' => now(),
            ]);

            $instance->update([
                'status' => 'Rejected',
                'completed_by' => Auth::id(),
                'completed_at' => now(),
            ]);

            $instance->acquisitionRequest()->update([
                'status' => 'Rejected',
                'rejected_at' => now(),
            ]);

            return $instance->fresh();
        });
    }

    /**
     * Revert current step
     */
    public function revert(WorkflowInstance $instance, string $comment): WorkflowInstance
    {
        return DB::transaction(function () use ($instance, $comment) {
            $currentStep = $instance->currentStep;

            WorkflowAction::create([
                'workflow_instance_id' => $instance->id,
                'acquisition_request_id' => $instance->acquisition_request_id,
                'workflow_step_id' => $currentStep?->id,
                'step_order' => $currentStep?->step_order,
                'step_name' => $currentStep?->step_name,
                'action' => 'reverted',
                'comment' => $comment,
                'acted_by' => Auth::id(),
                'acted_at' => now(),
            ]);

            $instance->update([
                'status' => 'Reverted',
            ]);

            $instance->acquisitionRequest()->update([
                'status' => 'Reverted',
            ]);

            return $instance->fresh();
        });
    }
}
