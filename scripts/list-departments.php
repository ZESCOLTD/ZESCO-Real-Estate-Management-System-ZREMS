<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = App\Models\ServiceParent::query()
    ->select("department_code", "department_name")
    ->distinct()
    ->orderBy("department_code")
    ->get();

echo "Departments in LAMS: " . $rows->count() . "\n";
echo str_repeat("-", 60) . "\n";
foreach ($rows as $r) {
    echo str_pad($r->department_code, 30) . " | " . $r->department_name . "\n";
}
