<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceParent;
use App\Models\ServiceChild;
use App\Models\ServiceFormTemplate;
use App\Models\ServiceFormField;

$parents = ServiceParent::where('department_name', 'Property Department')->orderBy('id')->get();
foreach ($parents as $p) {
    echo "PARENT #{$p->id} | {$p->service_name}" . PHP_EOL;
    foreach (ServiceChild::where('service_parent_id', $p->id)->orderBy('service_name')->get() as $c) {
        $t = ServiceFormTemplate::where('service_child_id', $c->id)->first();
        $f = $t ? ServiceFormField::where('service_form_template_id', $t->id)->count() : 0;
        $tpl = $t ? '#' . $t->id : 'NONE';
        echo '   #' . str_pad($c->id, 4) . ' ' . str_pad(substr($c->service_name, 0, 55), 55) . " tpl={$tpl}  fields={$f}" . PHP_EOL;
    }
    echo PHP_EOL;
}
