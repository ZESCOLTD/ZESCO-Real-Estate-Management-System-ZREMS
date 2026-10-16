<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== services (parents + children) ===\n";
foreach (DB::table('services')->orderBy('parent_id')->orderBy('id')->get() as $s) {
    $p = $s->parent_id ? "  child of #{$s->parent_id}" : "[PARENT]";
    echo "  #{$s->id}  {$p}  {$s->name}\n";
}

echo "\n=== service_form_templates ===\n";
foreach (DB::table('service_form_templates')->orderBy('id')->get() as $t) {
    $fields = DB::table('service_form_fields')->where('service_form_template_id', $t->id)->count();
    echo "  tpl #{$t->id}  service=#{$t->service_id}  {$t->name}  ({$fields} fields)\n";
}
