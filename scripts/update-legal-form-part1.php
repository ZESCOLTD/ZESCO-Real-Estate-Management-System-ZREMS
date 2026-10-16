<?php
/**
 * Apply Part 1 UI edits to the "Litigation Matter Intake" form in the current DB.
 *
 *   1. Matter type dropdown: rename option "Submission" -> "Submissions"
 *   2. Cause Number: no longer required
 *   3. Defendant / Respondent: switch to repeater (multiple defendants)
 *   4. Next Court Date -> Court Date
 *
 * Idempotent. Safe to re-run.
 *
 * Run: php scripts/update-legal-form-part1.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceFormField;
use App\Models\ServiceFormTemplate;
use App\Models\ServiceChild;

$child = ServiceChild::where('service_name', 'like', '%Litigation%')->first();
if (!$child) {
    echo "Could not find 'Litigation Matter Intake' service child.\n";
    exit(1);
}

$template = ServiceFormTemplate::where('service_child_id', $child->id)->first();
if (!$template) {
    echo "No form template found for service child #{$child->id}.\n";
    exit(1);
}

echo "Using template #{$template->id} for '{$child->service_name}'\n";

$updates = 0;

// 1. Matter type dropdown: Submission -> Submissions
$matterType = ServiceFormField::where('service_form_template_id', $template->id)
    ->where('field_name', 'matter_type')->first();
if ($matterType) {
    $opts = $matterType->options ?? [];
    $before = $opts;
    $opts = array_map(fn ($o) => $o === 'Submission' ? 'Submissions' : $o, $opts);
    if ($opts !== $before) {
        $matterType->options = $opts;
        $matterType->save();
        echo "  [1] matter_type options updated: Submission -> Submissions\n";
        $updates++;
    } else {
        echo "  [1] matter_type already correct\n";
    }
}

// 2. Cause Number no longer required
$causeNumber = ServiceFormField::where('service_form_template_id', $template->id)
    ->where('field_name', 'cause_number')->first();
if ($causeNumber && $causeNumber->is_required) {
    $causeNumber->is_required = false;
    $causeNumber->save();
    echo "  [2] cause_number set to optional\n";
    $updates++;
} else {
    echo "  [2] cause_number already optional or missing\n";
}

// 3. Defendant / Respondent -> repeater
$defendant = ServiceFormField::where('service_form_template_id', $template->id)
    ->where('field_name', 'defendant')->first();
if ($defendant && $defendant->field_type !== 'repeater') {
    $defendant->field_type = 'repeater';
    $defendant->options = [
        ['name' => 'name', 'label' => 'Defendant / Respondent', 'type' => 'text'],
        'default_rows' => [['name' => '']],
    ];
    $defendant->save();
    echo "  [3] defendant converted to repeater\n";
    $updates++;
} else {
    echo "  [3] defendant already repeater or missing\n";
}

// 4. Next Court Date -> Court Date
$nextCourt = ServiceFormField::where('service_form_template_id', $template->id)
    ->where('field_name', 'next_court_date')->first();
if ($nextCourt && $nextCourt->field_label !== 'Court Date') {
    $nextCourt->field_label = 'Court Date';
    $nextCourt->save();
    echo "  [4] next_court_date label -> 'Court Date'\n";
    $updates++;
} else {
    echo "  [4] next_court_date already labelled 'Court Date' or missing\n";
}

echo "\nDone. {$updates} field(s) updated.\n";
