<?php
require "vendor/autoload.php";
$a=require "bootstrap/app.php";
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\ServiceFormField;

foreach ([18,19] as $tid) {
    $f = ServiceFormField::where("service_form_template_id",$tid)
        ->where("field_name","gps_coordinates")->first();
    if (!$f) { echo "tpl {$tid}: gps_coordinates not found\n"; continue; }

    $f->field_type = "repeater";
    $f->field_label = "GPS Coordinates (optional — add one row per point)";
    $f->options = [
        ["name" => "easting",  "label" => "Easting",  "type" => "text"],
        ["name" => "northing", "label" => "Northing", "type" => "text"],
    ];
    $f->save();
    echo "tpl {$tid}: gps_coordinates converted to repeater (easting, northing)\n";
}
