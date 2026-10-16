<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceFormField;
use App\Models\ServiceFormTemplate;
use App\Models\ServiceChild;

$child = ServiceChild::where('service_name', 'like', '%Litigation%')->first();
$template = ServiceFormTemplate::where('service_child_id', $child->id)->first();

// Show every field in section 15
$fields = ServiceFormField::where('service_form_template_id', $template->id)
    ->where('section', 'like', '15%')
    ->get();

foreach ($fields as $f) {
    echo "id={$f->id}  name={$f->field_name}  type={$f->field_type}  section={$f->section}\n";
    echo "  options=" . json_encode($f->options, JSON_PRETTY_PRINT) . "\n";
}

echo "\n\nAll form_values for workflow_tracker field (if any submissions exist):\n";
$wt = $fields->firstWhere('field_name', 'workflow_tracker');
if ($wt) {
    $vals = \App\Models\ServiceFormValue::where('service_form_field_id', $wt->id)->get();
    echo "count=" . $vals->count() . "\n";
    foreach ($vals as $v) {
        echo "  submission_id={$v->service_form_submission_id}  value=" . substr($v->value ?? '', 0, 300) . "\n";
    }
}
