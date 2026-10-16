<?php
/**
 * scripts/inspect-dd-scoping.php
 *
 * Diagnose why "Start Scoping" is disabled for the current PM.
 * Pass the due_diligence id as arg 1, and the logged-in user_id as arg 2.
 *
 * Example:
 *   php scripts/inspect-dd-scoping.php 12 5
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$ddId   = (int) ($argv[1] ?? 0);
$userId = (int) ($argv[2] ?? 0);

if (!$ddId) {
    echo "Usage: php scripts/inspect-dd-scoping.php <due_diligence_id> [<user_id>]\n";
    exit(1);
}

echo "=== Due Diligence #{$ddId} ===\n";
$dd = DB::table('acquisition_due_diligences')->where('id', $ddId)->first();
if (!$dd) { echo "Not found.\n"; exit(1); }
$acqId = $dd->acquisition_request_id;
echo "  acquisition_request_id = {$acqId}\n";

echo "\n=== AcquisitionRequest #{$acqId} owner fields ===\n";
$acq = DB::table('acquisition_requests')->where('id', $acqId)->first();
if ($acq) {
    $keys = ['user_id','project_accountant_user_id','approved_by'];
    foreach ($keys as $k) {
        echo "  {$k} = " . ($acq->$k ?? 'NULL') . "\n";
    }
}

echo "\n=== workflow_definitions ===\n";
foreach (DB::table('workflow_definitions')->get() as $d) {
    echo "  #{$d->id} module='{$d->module}' name='{$d->name}'\n";
}

echo "\n=== workflow_steps for acquisition workflow ===\n";
$steps = DB::table('workflow_steps as s')
    ->join('workflow_definitions as d', 'd.id', '=', 's.workflow_definition_id')
    ->where('d.module', 'acquisition_request')
    ->select('s.*')
    ->get();
if ($steps->isEmpty()) {
    echo "  (none — d.module != 'acquisition_request'?)\n";
}
foreach ($steps as $s) {
    echo "  step #{$s->id} name='{$s->step_name}' approver_value='{$s->approver_value}'\n";
    $ap = DB::table('workflow_step_approvers')->where('workflow_step_id', $s->id)->pluck('user_id');
    echo "     approvers: [" . $ap->implode(',') . "]\n";
}

echo "\n=== WorkflowActions on this acquisition ===\n";
try {
    $rows = DB::table('workflow_actions')
        ->where(function ($q) use ($acqId) {
            $q->where(function ($qq) use ($acqId) {
                $qq->where('workflowable_type', 'App\\Models\\AcquisitionRequest')
                   ->where('workflowable_id', $acqId);
            });
        })
        ->get();
    foreach ($rows as $r) {
        echo "  #{$r->id} step='{$r->step_name}' action='{$r->action}' acted_by={$r->acted_by}\n";
    }
    if ($rows->isEmpty()) echo "  (no polymorphic rows — try legacy)\n";
} catch (\Throwable $e) {
    echo "  polymorphic query failed: " . $e->getMessage() . "\n";
}

if ($userId) {
    echo "\n=== Would user #{$userId} pass canCurrentUserStartScoping? ===\n";
    $candidates = collect([
        $acq->user_id ?? null,
        $acq->project_accountant_user_id ?? null,
        $acq->approved_by ?? null,
    ]);

    try {
        $pmActors = DB::table('workflow_actions')
            ->where('workflowable_type', 'App\\Models\\AcquisitionRequest')
            ->where('workflowable_id', $acqId)
            ->where('action', 'approved')
            ->where(function ($q) {
                $q->where('step_name', 'like', '%Property%');
            })
            ->pluck('acted_by');
        $candidates = $candidates->merge($pmActors);
    } catch (\Throwable $e) {}

    try {
        $pmAssigned = DB::table('workflow_step_approvers as a')
            ->join('workflow_steps as s', 's.id', '=', 'a.workflow_step_id')
            ->join('workflow_definitions as d', 'd.id', '=', 's.workflow_definition_id')
            ->where('d.module', 'acquisition_request')
            ->where(function ($q) {
                $q->where('s.approver_value', 'like', '%property%')
                  ->orWhere('s.step_name', 'like', '%Property%');
            })
            ->pluck('a.user_id');
        $candidates = $candidates->merge($pmAssigned);
    } catch (\Throwable $e) {}

    $candidates = $candidates->filter()->map(fn($x)=>(int)$x)->unique()->values();
    echo "  candidate ids: [" . $candidates->implode(',') . "]\n";
    echo "  contains {$userId}? " . ($candidates->contains($userId) ? 'YES ✅' : 'NO ❌') . "\n";
}
