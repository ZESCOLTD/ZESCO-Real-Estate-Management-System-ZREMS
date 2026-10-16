<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=====================================================================\n";
echo " Workflow Definitions\n";
echo "=====================================================================\n";
foreach (DB::table('workflow_definitions')->get() as $wd) {
    echo "  #{$wd->id}  module={$wd->module}  name={$wd->name}  default=" . ($wd->is_default?'Y':'N') . "\n";
}

echo "\n=====================================================================\n";
echo " Workflow Steps & Assigned Approvers (workflow_step_approvers)\n";
echo "=====================================================================\n";
$steps = DB::table('workflow_steps')->orderBy('workflow_definition_id')->orderBy('step_order')->get();
foreach ($steps as $s) {
    echo "\n  Step #{$s->id}  order={$s->step_order}  name={$s->step_name}\n";
    echo "          approver_type={$s->approver_type}  approver_value={$s->approver_value}\n";

    $approvers = DB::table('workflow_step_approvers as wsa')
        ->join('users as u', 'u.id', '=', 'wsa.user_id')
        ->where('wsa.workflow_step_id', $s->id)
        ->select('u.id', 'u.name', 'u.email')
        ->get();

    if ($approvers->isEmpty()) {
        echo "          (no approvers assigned)\n";
    } else {
        echo "          ".count($approvers)." approver(s):\n";
        foreach ($approvers as $a) {
            echo "            - #{$a->id}  {$a->name}  <{$a->email}>\n";
        }
    }
}

echo "\n=====================================================================\n";
echo " HOD / Subordinate assignments (service_department_people)\n";
echo "=====================================================================\n";
$hods = DB::table('service_department_people as sdp')
    ->leftJoin('users as u', 'u.id', '=', 'sdp.hod_user_id')
    ->select('sdp.department_code', 'sdp.hod_user_id', 'u.name', 'u.email')
    ->get();
foreach ($hods as $h) {
    echo "  dept={$h->department_code}  HOD_user_id={$h->hod_user_id}  name={$h->name}  <{$h->email}>\n";
}

echo "\n=====================================================================\n";
echo " Subordinates (service_department_subordinates)\n";
echo "=====================================================================\n";
$subs = DB::table('service_department_subordinates as sds')
    ->leftJoin('users as u', 'u.id', '=', 'sds.user_id')
    ->select('sds.department_code', 'sds.user_id', 'u.name', 'u.email')
    ->get();
foreach ($subs as $s) {
    echo "  dept={$s->department_code}  user_id={$s->user_id}  name={$s->name}  <{$s->email}>\n";
}
