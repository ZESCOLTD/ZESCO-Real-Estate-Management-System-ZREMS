<?php
/**
 * Clear the saved workflow_tracker value on existing legal-form submissions
 * so the repeater re-seeds from the (now single-empty-row) default_rows.
 *
 * Safe to re-run.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceFormField;
use App\Models\ServiceFormTemplate;
use App\Models\ServiceChild;
use App\Models\ServiceFormValue;

$child = ServiceChild::where('service_name', 'like', '%Litigation%')->first();
if (!$child) { echo "No Litigation child (nothing to reset).\n"; exit(0); }
$template = ServiceFormTemplate::where('service_child_id', $child->id)->first();
if (!$template) { echo "No template for Litigation child (nothing to reset).\n"; exit(0); }

$targets = ['workflow_tracker', 'departmental_followup'];
$reset = 0;

foreach ($targets as $name) {
    $field = ServiceFormField::where('service_form_template_id', $template->id)
        ->where('field_name', $name)->first();
    if (!$field) { echo "  * '{$name}' field not found\n"; continue; }

    $values = ServiceFormValue::where('service_form_field_id', $field->id)->get();
    foreach ($values as $v) {
        $v->value = '[]';
        $v->save();
        echo "  * cleared submission #{$v->service_form_submission_id} value for '{$name}'\n";
        $reset++;
    }
}

echo "\nDone. {$reset} saved value(s) cleared.\n";
