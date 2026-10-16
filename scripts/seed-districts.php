<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$count = DB::table('districts')->count();
echo "districts count before: $count\n";

if ($count === 0) {
    $seeder = new Database\Seeders\DistrictSeeder();
    $seeder->run();
    echo "districts count after: " . DB::table('districts')->count() . "\n";
}

echo "sample rows:\n";
foreach (DB::table('districts')->orderBy('province_id')->orderBy('name')->limit(5)->get() as $r) {
    echo "  province_id={$r->province_id}  name={$r->name}\n";
}
