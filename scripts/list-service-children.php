<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (App\Models\ServiceChild::all() as $c) {
    echo $c->id . ' | ' . $c->service_name . PHP_EOL;
}
