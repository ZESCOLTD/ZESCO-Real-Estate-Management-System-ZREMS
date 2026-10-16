<?php
/**
 * Convert district/province fields on Valuation templates to dynamic dropdowns:
 *  - district: text -> select (options loaded live from `districts` table)
 *  - province: select with hardcoded labels -> select loaded from Province enum (int-valued)
 *
 * Safe to re-run.  Targets templates 18 (Property Valuation) and 19 (Property Consultancy).
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceFormField;
use App\Models\ServiceFormTemplate;

$templateIds = ServiceFormTemplate::whereIn('form_name', [
    'Property Valuation Form',
    'Property Consultancy Form',
])->pluck('id')->all();

if (empty($templateIds)) {
    echo "No Valuation templates found (looked for form_name = 'Property Valuation Form' / 'Property Consultancy Form').\n";
    // Fallback: try any template with a 'province' field
    $templateIds = ServiceFormField::where('field_name', 'province')
        ->distinct('service_form_template_id')
        ->pluck('service_form_template_id')->all();
    echo "Fallback: found " . count($templateIds) . " template(s) containing a 'province' field.\n";
}

$total = 0;
foreach ($templateIds as $tid) {
    $district = ServiceFormField::where('service_form_template_id', $tid)
        ->where('field_name', 'district')->first();
    $province = ServiceFormField::where('service_form_template_id', $tid)
        ->where('field_name', 'province')->first();

    if ($district) {
        $district->field_type = 'select';
        $district->options = [];  // resolved dynamically in blade
        $district->save();
        echo "  template=$tid district field #{$district->id} -> select (dynamic)\n";
        $total++;
    }

    if ($province) {
        $province->field_type = 'select';
        $province->options = [];  // resolved dynamically from Province enum in blade
        $province->save();
        echo "  template=$tid province field #{$province->id} -> select (dynamic)\n";
        $total++;
    }
}

echo "Updated $total field(s).\n";
