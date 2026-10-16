<?php
require "vendor/autoload.php";
$a=require "bootstrap/app.php";
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceFormField;
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    // 1) Drop plant_machinery textarea from both templates.
    $del = ServiceFormField::whereIn("service_form_template_id",[18,19])
        ->where("field_name","plant_machinery")->delete();
    echo "Deleted plant_machinery rows: {$del}\n";

    // 2) Update Property Type options.
    $newOptions = ["Residential","Commercial","Industrial","Agricultural","Institutional","Mixed-Use","Bare Land","Plant and Machinery","Other"];
    foreach ([18,19] as $tid) {
        $pt = ServiceFormField::where("service_form_template_id",$tid)->where("field_name","property_type")->first();
        if ($pt) {
            $pt->options = $newOptions;
            $pt->save();
            echo "tpl {$tid}: property_type options updated -> ".implode(", ", $newOptions)."\n";
        }
    }
});
