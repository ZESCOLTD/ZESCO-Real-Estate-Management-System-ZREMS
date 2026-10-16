<?php
/**
 * cleanup-approvers.php
 *
 * Option B: reduce every workflow_step to EXACTLY ONE approver, mapped by role.
 * Also removes duplicate rows in workflow_step_approvers.
 *
 * Run:  php scripts/cleanup-approvers.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// role => user_id (Option B mapping)
$map = [
    'hod'                => 1,   // BENSON NGULUBE
    'chief_accountant'   => 3,   // CHRISTOPHER PHIRI
    'property'           => 5,   // SIITA MUSIALIKE
    'project_accountant' => 6,   // DAVID MUTEPUKA
    'procurement'        => 9,   // HAROLD CHOMBA
    'head_esd'           => 13,  // CHAPHUKA SIKAZWE
];

echo "==== Cleaning workflow_step_approvers ====\n";

$steps = DB::table('workflow_steps')->get();
foreach ($steps as $step) {
    $role = strtolower(trim($step->approver_value ?? ''));
    $userId = $map[$role] ?? null;

    // wipe existing rows for this step
    DB::table('workflow_step_approvers')->where('workflow_step_id', $step->id)->delete();

    if ($userId === null) {
        echo "  step #{$step->id} ({$step->step_name} / role={$role}) — no mapping, left empty\n";
        continue;
    }

    DB::table('workflow_step_approvers')->insert([
        'workflow_definition_id' => $step->workflow_definition_id,
        'workflow_step_id'       => $step->id,
        'user_id'                => $userId,
        'initiator_user_id'      => $userId, // sentinel; belongsToMany doesn't filter by it
        'is_primary'             => 1,
        'is_active'              => 1,
        'created_at'             => now(),
        'updated_at'             => now(),
    ]);
    echo "  step #{$step->id} ({$step->step_name} / role={$role}) -> user #{$userId}\n";
}

echo "\n==== De-duplicating service_department_people (keep first HOD per dept) ====\n";
$rows = DB::table('service_department_people')
    ->orderBy('id')
    ->get()
    ->groupBy('department_code');

foreach ($rows as $dept => $group) {
    $keep = $group->first();
    $extras = $group->slice(1);
    foreach ($extras as $r) {
        DB::table('service_department_people')->where('id', $r->id)->delete();
        echo "  dept={$dept}: dropped duplicate row #{$r->id}\n";
    }
    echo "  dept={$dept}: kept HOD user_id={$keep->hod_user_id}\n";
}

echo "\nDone.\n";
