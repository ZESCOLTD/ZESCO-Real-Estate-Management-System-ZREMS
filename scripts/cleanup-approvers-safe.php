<?php
/**
 * scripts/cleanup-approvers-safe.php
 *
 * Reduces every workflow_step to ONE approver — safely across environments:
 *   - Looks up target users by EMAIL (not hardcoded IDs)
 *   - If target email not found, keeps only the FIRST existing approver on
 *     that step and deletes the rest (never leaves the step empty).
 *   - Prints a summary so you can eyeball before/after.
 *
 * Run:  php scripts/cleanup-approvers-safe.php
 *
 * Dry-run (show what WOULD happen, change nothing):
 *       php scripts/cleanup-approvers-safe.php --dry
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$dry = in_array('--dry', $argv, true);
echo $dry ? "== DRY RUN — nothing will be changed ==\n\n" : "== APPLYING changes ==\n\n";

// role => preferred email (edit these to match your test-server users)
$emailMap = [
    'hod'                => 'BENSONNGULUBE@ZESCO.CO.ZM',
    'chief_accountant'   => 'MCPHIRI@ZESCO.CO.ZM',
    'property'           => 'SMUSIALIKE@ZESCO.CO.ZM',
    'project_accountant' => 'DMUTEPUKA@ZESCO.CO.ZM',
    'procurement'        => 'HKCHOMBA@ZESCO.CO.ZM',
    'head_esd'           => 'CSIKAZWE@ZESCO.CO.ZM',
];

// Resolve emails → user_ids (case-insensitive)
$resolved = [];
foreach ($emailMap as $role => $email) {
    $u = DB::table('users')->whereRaw('UPPER(email) = ?', [strtoupper($email)])->first();
    $resolved[$role] = $u?->id;
    echo sprintf("  role=%s  email=%s  ->  user_id=%s\n",
        str_pad($role, 20),
        $email,
        $u?->id ?? 'NOT FOUND'
    );
}
echo "\n";

$steps = DB::table('workflow_steps')->get();
foreach ($steps as $step) {
    $role = strtolower(trim($step->approver_value ?? ''));
    $targetUserId = $resolved[$role] ?? null;

    $currentApprovers = DB::table('workflow_step_approvers')
        ->where('workflow_step_id', $step->id)
        ->get();

    // Decide which user to KEEP for this step
    $keepUserId = null;
    if ($targetUserId && $currentApprovers->contains('user_id', $targetUserId)) {
        $keepUserId = $targetUserId;  // best case: target user already assigned
    } elseif ($targetUserId) {
        // target not assigned; will add them below
        $keepUserId = $targetUserId;
    } elseif ($currentApprovers->isNotEmpty()) {
        // no target mapping → keep first existing approver
        $keepUserId = (int) $currentApprovers->first()->user_id;
    }

    echo sprintf("Step #%d '%s' (role=%s)  currently: %d approver(s) -> keep user_id=%s\n",
        $step->id,
        $step->step_name,
        $role ?: '(none)',
        $currentApprovers->count(),
        $keepUserId ?? 'NONE'
    );

    if ($dry) continue;
    if ($keepUserId === null) continue;   // no approvers and no target → skip

    // Delete all rows for this step, then re-insert one row for the keeper
    DB::table('workflow_step_approvers')->where('workflow_step_id', $step->id)->delete();
    DB::table('workflow_step_approvers')->insert([
        'workflow_definition_id' => $step->workflow_definition_id,
        'workflow_step_id'       => $step->id,
        'user_id'                => $keepUserId,
        'initiator_user_id'      => $keepUserId,
        'is_primary'             => 1,
        'is_active'              => 1,
        'created_at'             => now(),
        'updated_at'             => now(),
    ]);
}

echo "\nDone.\n";
