<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$template = DB::table('service_form_templates')->where('id', 21)->first();
if (!$template) { echo "Template #21 not found\n"; exit; }

echo "=======================================================================\n";
echo " TEMPLATE #{$template->id}: {$template->form_name}\n";
echo " Description: {$template->description}\n";
echo " Active: " . ($template->is_active ? 'yes' : 'no') . "\n";
echo "=======================================================================\n\n";

$fields = DB::table('service_form_fields')
    ->where('service_form_template_id', 21)
    ->orderBy('sort_order')
    ->get();

$currentSection = null;
foreach ($fields as $f) {
    if ($f->section !== $currentSection) {
        echo "\n─── {$f->section} ───\n";
        $currentSection = $f->section;
    }
    $req = $f->is_required ? '*' : ' ';
    $type = str_pad($f->field_type, 15);
    $opts = '';
    if ($f->options) {
        $decoded = json_decode($f->options, true);
        if (is_array($decoded)) {
            if (isset($decoded['default_rows'])) {
                $cols = array_filter($decoded, fn($v,$k) => $k !== 'default_rows', ARRAY_FILTER_USE_BOTH);
                $rows = count($decoded['default_rows']);
                $opts = "  [table: ".count($cols)." cols, {$rows} pre-seeded rows]";
            } else {
                $opts = "  [".count($decoded)." options]";
            }
        }
    }
    echo "  {$req} {$type} {$f->field_label}{$opts}\n";
}

echo "\n=======================================================================\n";
echo " TOTAL: " . count($fields) . " fields, "
     . count(array_unique(array_column(json_decode(json_encode($fields), true), 'section')))
     . " sections\n";
echo "=======================================================================\n";
