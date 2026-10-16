<?php
/**
 * Apply Part 2-7 UI edits to the "Litigation Services" form in the current DB.
 *
 * Part 2 (Classification & Priority): remove critical_date, days_available
 * Part 3 (Assignment by Head Legal): rename section to "Instructions by Head Legal"
 *                                    add file upload field below head_legal_instructions
 * Part 5: remove departmental_contact, memorandum_reference,
 *         info_required (+ its options list), info_required_other
 * Part 6 (Extension of Time): remove entire section (6 fields)
 * Part 7: remove date_info_received, info_sufficient
 *         reset departmental_followup repeater to a single empty row
 *
 * Idempotent. Safe to re-run.
 *
 * Run: php scripts/update-legal-form-part2to7.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceFormField;
use App\Models\ServiceFormTemplate;
use App\Models\ServiceChild;

$child = ServiceChild::where('service_name', 'like', '%Litigation%')->first();
if (!$child) {
    echo "Could not find Litigation service child.\n";
    exit(1);
}

$template = ServiceFormTemplate::where('service_child_id', $child->id)->first();
if (!$template) {
    echo "No form template for service child #{$child->id}.\n";
    exit(1);
}

$templateId = $template->id;
echo "Template #{$templateId} for '{$child->service_name}'\n";

$totalRemoved = 0;
$totalUpdated = 0;
$totalAdded   = 0;

/* --------- helper: delete a field by name if present ---------- */
$deleteByName = function (string $name) use ($templateId, &$totalRemoved) {
    $rows = ServiceFormField::where('service_form_template_id', $templateId)
        ->where('field_name', $name)->get();
    foreach ($rows as $row) {
        $row->delete();
        echo "  - removed field '{$name}'\n";
        $totalRemoved++;
    }
};

/* =========== PART 2 =========== */
echo "\n[Part 2] Matter Classification & Priority\n";
$deleteByName('critical_date');
$deleteByName('days_available');

/* =========== PART 3 =========== */
echo "\n[Part 3] Instructions by Head Legal\n";

// rename section for all fields in section 3
$renamed = ServiceFormField::where('service_form_template_id', $templateId)
    ->where('section', '3. Assignment by Head Legal')
    ->update(['section' => '3. Instructions by Head Legal']);
if ($renamed > 0) {
    echo "  * renamed section on {$renamed} field(s) to '3. Instructions by Head Legal'\n";
    $totalUpdated += $renamed;
} else {
    echo "  * section already named 'Instructions by Head Legal' (or missing)\n";
}

// insert file upload just after head_legal_instructions
$instr = ServiceFormField::where('service_form_template_id', $templateId)
    ->where('field_name', 'head_legal_instructions')->first();
$exists = ServiceFormField::where('service_form_template_id', $templateId)
    ->where('field_name', 'head_legal_instructions_file')->exists();
if ($instr && !$exists) {
    ServiceFormField::create([
        'service_form_template_id'   => $templateId,
        'field_name'                 => 'head_legal_instructions_file',
        'field_label'                => 'Upload Instructions Document',
        'field_type'                 => 'file',
        'options'                    => null,
        'is_required'                => false,
        'sort_order'                 => $instr->sort_order + 1,
        'section'                    => '3. Instructions by Head Legal',
        'allow_comments'             => false,
        'allow_attachment'           => true,
        'allow_multiple_attachments' => false,
        'attachment_label'           => null,
        'validation_rules'           => null,
    ]);
    echo "  + added file field 'head_legal_instructions_file' after 'head_legal_instructions'\n";
    $totalAdded++;
} else {
    echo "  * file field already present or head_legal_instructions missing\n";
}

/* =========== PART 5 =========== */
echo "\n[Part 5] Request for Departmental Information\n";
$deleteByName('departmental_contact');
$deleteByName('memorandum_reference');
$deleteByName('info_required');
$deleteByName('info_required_other');

/* =========== PART 6 =========== */
echo "\n[Part 6] Extension of Time (entire section)\n";
$sec6 = ServiceFormField::where('service_form_template_id', $templateId)
    ->where('section', '6. Extension of Time')->get();
foreach ($sec6 as $row) {
    $name = $row->field_name;
    $row->delete();
    echo "  - removed field '{$name}'\n";
    $totalRemoved++;
}
if ($sec6->isEmpty()) echo "  * section 6 already empty/removed\n";

/* =========== PART 7 =========== */
echo "\n[Part 7] Departmental Follow-Up\n";
$deleteByName('date_info_received');
$deleteByName('info_sufficient');

// reset repeater to one empty row
$followup = ServiceFormField::where('service_form_template_id', $templateId)
    ->where('field_name', 'departmental_followup')->first();
if ($followup) {
    $followup->options = [
        ['name' => 'stage',    'label' => 'Follow-Up', 'type' => 'text'],
        ['name' => 'date',     'label' => 'Date',      'type' => 'date'],
        ['name' => 'officer',  'label' => 'Officer',   'type' => 'text'],
        ['name' => 'response', 'label' => 'Response',  'type' => 'text'],
        ['name' => 'remarks',  'label' => 'Remarks',   'type' => 'text'],
        'default_rows' => [
            ['stage' => '', 'date' => '', 'officer' => '', 'response' => '', 'remarks' => ''],
        ],
    ];
    $followup->save();
    echo "  * departmental_followup: prefilled rows cleared, single empty row set\n";
    $totalUpdated++;
} else {
    echo "  * departmental_followup field missing\n";
}

echo "\n---\nDone. Removed: {$totalRemoved}, Updated: {$totalUpdated}, Added: {$totalAdded}\n";
