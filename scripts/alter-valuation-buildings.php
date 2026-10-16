<?php
require "vendor/autoload.php";
$a=require "bootstrap/app.php";
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceFormField;
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    // 1) Delete number_of_buildings + building_size from both templates.
    $del = ServiceFormField::whereIn("service_form_template_id",[18,19])
        ->whereIn("field_name",["number_of_buildings","building_size"])
        ->delete();
    echo "Deleted: {$del}\n";

    // 2) Add a "buildings" repeater right after land_size (sort_order = land_size + 5).
    foreach ([18,19] as $tid) {
        // Skip if already exists.
        if (ServiceFormField::where("service_form_template_id",$tid)->where("field_name","buildings")->exists()) {
            echo "tpl {$tid}: buildings already exists, skipping\n";
            continue;
        }
        $landSize = ServiceFormField::where("service_form_template_id",$tid)->where("field_name","land_size")->first();
        $order = ($landSize?->sort_order ?? 100) + 5;

        ServiceFormField::create([
            "service_form_template_id" => $tid,
            "field_name"   => "buildings",
            "field_label"  => "Buildings on Parcel (add one row per building)",
            "field_type"   => "repeater",
            "options"      => [
                ["name" => "building_name", "label" => "Building Name", "type" => "text"],
                ["name" => "building_size", "label" => "Building Size", "type" => "text"],
            ],
            "is_required"  => false,
            "sort_order"   => $order,
            "section"      => "4. Property Inspection",
        ]);
        echo "tpl {$tid}: added buildings repeater at sort={$order}\n";
    }
});
