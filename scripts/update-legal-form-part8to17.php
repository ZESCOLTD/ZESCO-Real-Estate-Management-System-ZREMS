<?php
/**
 * Apply Parts 8-17 UI edits to the "Litigation Services" form.
 *
 * Part 8:  remove work_completed_status, person_responsible
 * Part 10: remove prepared_by; drop "Amendments made" from internal_review options
 * Part 11: document_filed_served -> not required
 * Part 12: remove entire section 12 (Court Hearing Record)
 * Part 13: remove entire section 13 (Hearing Report to Head Legal)
 * Part 15: workflow_tracker repeater -> single empty row
 * Part 17: add attachment + comment fields below final_outcome
 *
 * Idempotent. Safe to re-run.
 *
 * Run: php scripts/update-legal-form-part8to17.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceFormField;
use App\Models\ServiceFormTemplate;
use App\Models\ServiceChild;

$child = ServiceChild::where('service_name', 'like', '%Litigation%')->first();
if (!$child) { echo "No Litigation child\n"; exit(1); }
$template = ServiceFormTemplate::where('service_child_id', $child->id)->first();
if (!$template) { echo "No template\n"; exit(1); }
$templateId = $template->id;
echo "Template #{$templateId} for '{$child->service_name}'\n";

$removed = 0; $updated = 0; $added = 0;

$deleteByName = function (string $name) use ($templateId, &$removed) {
    foreach (ServiceFormField::where('service_form_template_id', $templateId)->where('field_name', $name)->get() as $r) {
        $r->delete();
        echo "  - removed '{$name}'\n";
        $removed++;
    }
};

$deleteSection = function (string $section) use ($templateId, &$removed) {
    foreach (ServiceFormField::where('service_form_template_id', $templateId)->where('section', $section)->get() as $r) {
        $name = $r->field_name;
        $r->delete();
        echo "  - removed '{$name}' (section {$section})\n";
        $removed++;
    }
};

/* Part 8 */
echo "\n[Part 8] Review of Departmental Instructions\n";
$deleteByName('work_completed_status');
$deleteByName('person_responsible');

/* Part 10 */
echo "\n[Part 10] Drafting & Approval\n";
$deleteByName('prepared_by');

$ir = ServiceFormField::where('service_form_template_id', $templateId)->where('field_name', 'internal_review')->first();
if ($ir) {
    $opts = $ir->options ?? [];
    $new = array_values(array_filter($opts, fn ($o) => $o !== 'Amendments made'));
    if ($new !== $opts) {
        $ir->options = $new;
        $ir->save();
        echo "  * internal_review: removed 'Amendments made'\n";
        $updated++;
    } else {
        echo "  * internal_review: 'Amendments made' already absent\n";
    }
}

/* Part 11 */
echo "\n[Part 11] Filing / Service\n";
$df = ServiceFormField::where('service_form_template_id', $templateId)->where('field_name', 'document_filed_served')->first();
if ($df && $df->is_required) {
    $df->is_required = false;
    $df->save();
    echo "  * document_filed_served: set to optional\n";
    $updated++;
} else {
    echo "  * document_filed_served: already optional or missing\n";
}

/* Part 12 */
echo "\n[Part 12] Court Hearing Record (entire section)\n";
$deleteSection('12. Court Hearing Record');

/* Part 13 */
echo "\n[Part 13] Hearing Report to Head Legal (entire section)\n";
$deleteSection('13. Hearing Report to Head Legal');

/* Part 15 */
echo "\n[Part 15] Workflow Tracker (reset to single empty row)\n";
$wt = ServiceFormField::where('service_form_template_id', $templateId)->where('field_name', 'workflow_tracker')->first();
if ($wt) {
    $wt->options = [
        ['name' => 'stage',          'label' => 'Stage',               'type' => 'text'],
        ['name' => 'officer',        'label' => 'Responsible Officer', 'type' => 'text'],
        ['name' => 'date_due',       'label' => 'Date Due',            'type' => 'date'],
        ['name' => 'date_completed', 'label' => 'Date Completed',      'type' => 'date'],
        ['name' => 'status',         'label' => 'Status',              'type' => 'text'],
        'default_rows' => [
            ['stage' => '', 'officer' => '', 'date_due' => '', 'date_completed' => '', 'status' => ''],
        ],
    ];
    $wt->save();
    echo "  * workflow_tracker: prefilled stages cleared, single empty row set\n";
    $updated++;
} else {
    echo "  * workflow_tracker missing\n";
}

/* Part 17 */
echo "\n[Part 17] Matter Closure — add attachment + comment below Final Outcome\n";
$fo = ServiceFormField::where('service_form_template_id', $templateId)->where('field_name', 'final_outcome')->first();
if ($fo) {
    $base = $fo->sort_order;
    $existsAtt = ServiceFormField::where('service_form_template_id', $templateId)->where('field_name', 'final_outcome_attachment')->exists();
    if (!$existsAtt) {
        ServiceFormField::create([
            'service_form_template_id'   => $templateId,
            'field_name'                 => 'final_outcome_attachment',
            'field_label'                => 'Attachment',
            'field_type'                 => 'file',
            'options'                    => null,
            'is_required'                => false,
            'sort_order'                 => $base + 1,
            'section'                    => '17. Matter Closure',
            'allow_comments'             => false,
            'allow_attachment'           => true,
            'allow_multiple_attachments' => false,
            'attachment_label'           => null,
            'validation_rules'           => null,
        ]);
        echo "  + added 'final_outcome_attachment'\n";
        $added++;
    } else {
        echo "  * 'final_outcome_attachment' already exists\n";
    }

    $existsCom = ServiceFormField::where('service_form_template_id', $templateId)->where('field_name', 'final_outcome_comment')->exists();
    if (!$existsCom) {
        ServiceFormField::create([
            'service_form_template_id'   => $templateId,
            'field_name'                 => 'final_outcome_comment',
            'field_label'                => 'Comment',
            'field_type'                 => 'textarea',
            'options'                    => null,
            'is_required'                => false,
            'sort_order'                 => $base + 2,
            'section'                    => '17. Matter Closure',
            'allow_comments'             => false,
            'allow_attachment'           => false,
            'allow_multiple_attachments' => false,
            'attachment_label'           => null,
            'validation_rules'           => null,
        ]);
        echo "  + added 'final_outcome_comment'\n";
        $added++;
    } else {
        echo "  * 'final_outcome_comment' already exists\n";
    }
} else {
    echo "  * final_outcome missing\n";
}

echo "\n---\nDone. Removed: {$removed}, Updated: {$updated}, Added: {$added}\n";
