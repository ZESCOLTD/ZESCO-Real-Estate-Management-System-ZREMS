<?php
require "vendor/autoload.php";
$a=require "bootstrap/app.php";
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$n = App\Models\ServiceFormField::whereIn("service_form_template_id",[18,19])
    ->where("field_name","construction_material")->delete();
echo "Deleted rows: $n\n";
