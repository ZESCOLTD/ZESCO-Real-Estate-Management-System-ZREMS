<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Tables containing 'service' or 'department' ===\n";
$tables = DB::select("SHOW TABLES");
$key = 'Tables_in_' . DB::getDatabaseName();
foreach ($tables as $t) {
    $name = $t->$key;
    if (stripos($name, 'service') !== false || stripos($name, 'depart') !== false || stripos($name, 'parent') !== false) {
        echo "  $name\n";
    }
}

echo "\n=== service_parents (top level services) ===\n";
try {
    foreach (DB::table('service_parents')->orderBy('id')->get() as $p) {
        $cols = (array) $p;
        echo "  id={$p->id}  ";
        foreach ($cols as $k => $v) {
            if (in_array($k, ['id','created_at','updated_at','deleted_at'])) continue;
            if ($v === null || $v === '') continue;
            $vs = is_string($v) ? substr($v, 0, 40) : $v;
            echo "$k=$vs  ";
        }
        echo "\n";
    }
} catch (\Throwable $e) { echo "  ERR: ".$e->getMessage()."\n"; }

echo "\n=== service_childrens (all) ===\n";
try {
    $rows = DB::table('service_childrens')->orderBy('service_parent_id')->orderBy('id')->get();
    foreach ($rows as $c) {
        echo "  id={$c->id}  parent={$c->service_parent_id}  name={$c->service_name}  type=".($c->type??'-')."\n";
    }
} catch (\Throwable $e) { echo "  ERR: ".$e->getMessage()."\n"; }
