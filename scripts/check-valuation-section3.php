<?php
require "vendor/autoload.php";
$a=require "bootstrap/app.php";
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceFormField;

$rows = ServiceFormField::whereIn("service_form_template_id",[18,19])
    ->where("section","like","3.%")
    ->orderBy("service_form_template_id")->orderBy("sort_order")->get();

foreach ($rows as $r) {
    echo "tpl {$r->service_form_template_id}  | {$r->field_name}  | {$r->field_type}  | req=".($r->is_required?"y":"n")."\n";
    if ($r->options) echo "   options: ".implode(", ",$r->options)."\n";
}
