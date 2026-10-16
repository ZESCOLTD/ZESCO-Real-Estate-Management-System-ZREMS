<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (\Illuminate\Support\Facades\DB::table('workflow_step_approvers')->limit(20)->get() as $r) {
    print_r((array)$r);
}
