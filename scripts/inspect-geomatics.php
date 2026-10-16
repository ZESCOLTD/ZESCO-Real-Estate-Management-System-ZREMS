<?php
require "vendor/autoload.php";
$a=require "bootstrap/app.php";
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceParent;
use App\Models\ServiceChild;
use App\Models\ServiceFormTemplate;
use App\Models\ServiceFormField;

$parents = ServiceParent::where("department_code","like","%geomatic%")
    ->orWhere("department_name","like","%geomatic%")
    ->get();

foreach ($parents as $p) {
    echo "PARENT #{$p->id} code={$p->department_code} name={$p->department_name} service={$p->service_name}\n";
    foreach ($p->children as $c) {
        $tpl = ServiceFormTemplate::where("service_child_id",$c->id)->first();
        $cnt = $tpl ? ServiceFormField::where("service_form_template_id",$tpl->id)->count() : 0;
        echo "  Child #{$c->id} {$c->service_name}  ->  ".($tpl ? "template #{$tpl->id} ({$cnt} fields)" : "NO TEMPLATE")."\n";
    }
}
