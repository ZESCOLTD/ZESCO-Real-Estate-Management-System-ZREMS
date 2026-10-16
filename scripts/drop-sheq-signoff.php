<?php
require "vendor/autoload.php";
$a=require "bootstrap/app.php";
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$n = App\Models\ServiceFormField::where("service_form_template_id",17)
    ->where("section","like","6.%")->delete();
echo "Deleted: $n\n";
