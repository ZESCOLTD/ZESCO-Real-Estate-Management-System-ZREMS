<?php
/**
 * scripts/advance-stuck-workflow.php
 *
 * When an approval action rolled back due to ORA-00904 / other DB error,
 * the workflow_instance is stuck at the current step even though the user
 * "clicked approve". Use this to move it forward manually.
 *
 * Usage:
 *   php scripts/advance-stuck-workflow.php <acquisition_request_id> <actor_user_id>
 *
 * It will:
 *   - insert a WorkflowAction row (action=approved) for the current step
 *   - set instance.current_step_id to the next step (or mark fully approved)
 *   - if fully approved, create the AcquisitionDueDiligence row
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AcquisitionRequest;
use App\Models\AcquisitionDueDiligence;
use App\Models\WorkflowAction;
use App\Enums\Acquisition\ApprovalStatus;
use Illuminate\Support\Facades\DB;

$acqId  = (int) ($argv[1] ?? 0);
$userId = (int) ($argv[2] ?? 0);

if (!$acqId || !$userId) {
    echo "Usage: php scripts/advance-stuck-workflow.php <acquisition_request_id> <actor_user_id>\n";
    exit(1);
}

$request = AcquisitionRequest::with([
    'workflowInstance.workflowDefinition.steps',
    'workflowInstance.currentStep',
])->find($acqId);

if (!$request) { echo "AcquisitionRequest #{$acqId} not found.\n"; exit(1); }
$instance = $request->workflowInstance;
if (!$instance) { echo "No workflow instance.\n"; exit(1); }
$current = $instance->currentStep;
if (!$current) { echo "No current step.\n"; exit(1); }

echo "Currently on step #{$current->id} '{$current->step_name}' (order {$current->step_order})\n";

DB::transaction(function () use ($request, $instance, $current, $userId) {
    WorkflowAction::create([
        'workflow_instance_id' => $instance->id,
        'workflowable_id'      => $request->id,
        'workflowable_type'    => AcquisitionRequest::class,
        'workflow_step_id'     => $current->id,
        'step_order'           => $current->step_order,
        'step_name'            => $current->step_name,
        'action'               => 'approved',
        'comment'              => 'Manually advanced via script after rollback',
        'acted_by'             => $userId,
        'acted_at'             => now(),
    ]);

    $next = $instance->workflowDefinition->steps
        ->where('step_order', '>', $current->step_order)
        ->sortBy('step_order')
        ->first();

    if ($next) {
        $instance->update([
            'current_step_id'    => $next->id,
            'current_step_order' => $next->step_order,
            'status'             => ApprovalStatus::UnderReview->value,
        ]);
        $request->update(['status' => ApprovalStatus::UnderReview->value]);
        echo "Moved to next step #{$next->id} '{$next->step_name}'\n";
    } else {
        $instance->update(['status' => ApprovalStatus::Approved->value]);
        $request->update([
            'status'      => ApprovalStatus::Approved->value,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
        AcquisitionDueDiligence::firstOrCreate(
            ['acquisition_request_id' => $request->id],
            [
                'acquisition_request_reference_no' => $request->reference_no,
                'ref_no'                           => $request->reference_no,
                'request_date'                     => now()->toDateString(),
            ]
        );
        echo "Fully approved. AcquisitionDueDiligence ensured.\n";
    }
});

echo "Done.\n";
