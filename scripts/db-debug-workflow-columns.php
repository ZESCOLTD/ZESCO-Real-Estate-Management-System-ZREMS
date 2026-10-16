<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$instanceCols = DB::select("SHOW COLUMNS FROM workflow_instances");
$actionCols = DB::select("SHOW COLUMNS FROM workflow_actions");

function findCol(array $cols, string $name): ?object {
    foreach ($cols as $col) {
        if (($col->Field ?? null) === $name) {
            return $col;
        }
    }
    return null;
}

$wi = findCol($instanceCols, 'acquisition_request_id');
$wa = findCol($actionCols, 'acquisition_request_id');

echo "workflow_instances.acquisition_request_id: " . json_encode($wi, JSON_PRETTY_PRINT) . PHP_EOL;
echo "workflow_actions.acquisition_request_id: " . json_encode($wa, JSON_PRETTY_PRINT) . PHP_EOL;
