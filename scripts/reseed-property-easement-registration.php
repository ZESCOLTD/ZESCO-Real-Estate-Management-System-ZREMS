<?php
/**
 * Replace the "Easement Verification and Registration" form template
 * with the simplified 3-step version.
 *
 * Run:  php scripts/reseed-property-easement-registration.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceParent;
use App\Models\ServiceChild;
use App\Models\ServiceFormTemplate;
use App\Models\ServiceFormField;
use Illuminate\Support\Facades\DB;

$YES_NO = ['Yes', 'No'];

$ISSUED_DOCS = [
    'Registered Easement Instrument',
    'Updated Certificate of Title/Endorsement',
    'Approved Easement Plan',
    'Registration Certificate/Confirmation',
    'Other',
];

$FINAL_STATUS = [
    'Registered', 'Pending', 'Returned for Correction', 'Rejected', 'Withdrawn',
];

$S1 = 'Step 1 — Easement Registration';
$S2 = 'Step 2 — Issue of Registered Documents';
$S3 = 'Step 3 — Official Use Only';

$fields = [
    // ---- Step 1 ----
    ['section' => $S1, 'name' => 'registration_no',   'label' => 'Registration No.',   'type' => 'text', 'required' => true],
    ['section' => $S1, 'name' => 'date_registered',   'label' => 'Date Registered',    'type' => 'date', 'required' => true],
    ['section' => $S1, 'name' => 'title_updated',     'label' => 'Title/Record Updated','type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S1, 'name' => 'instrument_no',     'label' => 'Instrument No.',     'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'register_entry_no', 'label' => 'Register Entry No.', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'registered_by',     'label' => 'Registered By',      'type' => 'text', 'required' => false],

    // ---- Step 2 ----
    ['section' => $S2, 'name' => 'issued_docs',           'label' => 'Documents Issued/Returned', 'type' => 'checkbox', 'options' => $ISSUED_DOCS, 'required' => false],
    ['section' => $S2, 'name' => 'issued_docs_other',     'label' => 'If Other, specify', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'date_issued',           'label' => 'Date Issued', 'type' => 'date', 'required' => false],
    ['section' => $S2, 'name' => 'received_by',           'label' => 'Received By', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'received_signature',    'label' => 'Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S2, 'name' => 'registered_site_plan',  'label' => 'Attach Registered Site Plan', 'type' => 'file', 'required' => false, 'allow_attachment' => true],

    // ---- Step 3 ----
    ['section' => $S3, 'name' => 'application_reference',    'label' => 'Application Reference', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'land_registry_file_no',    'label' => 'Land Registry File No.', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'survey_file_no',           'label' => 'Survey File No.', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'fees_payable',             'label' => 'Fees Payable (K)', 'type' => 'number', 'required' => false],
    ['section' => $S3, 'name' => 'fees_paid',                'label' => 'Fees Paid (K)', 'type' => 'number', 'required' => false],
    ['section' => $S3, 'name' => 'receipt_no',               'label' => 'Receipt No.', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'outstanding_requirements', 'label' => 'Outstanding Requirements', 'type' => 'textarea', 'required' => false],
    ['section' => $S3, 'name' => 'final_status',             'label' => 'Final Status', 'type' => 'select', 'options' => $FINAL_STATUS, 'required' => false],
    ['section' => $S3, 'name' => 'registered_easement_file', 'label' => 'Attach Registered Easement', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
];

DB::transaction(function () use ($fields) {

    $parent = ServiceParent::where('department_code', 'property_department')
        ->orWhere('department_name', 'Property Department')
        ->first();

    if (!$parent) {
        echo "  ERROR: Property Department parent not found.\n";
        return;
    }

    $child = ServiceChild::updateOrCreate(
        ['service_parent_id' => $parent->id, 'service_name' => 'Easement Verification and Registration'],
        ['model' => 'Easement Verification and Registration', 'type' => 'new_acquisition', 'is_active' => 1]
    );
    echo "  Child  : #{$child->id}  {$child->service_name}\n";

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'Easement Registration Process and Procedure Form',
            'description' => 'Property Department — Easement registration, issuance of registered documents, and official use record.',
            'is_active'   => true,
        ]
    );
    echo "  Template: #{$template->id}  {$template->form_name}\n";

    // Wipe existing fields for a clean replacement.
    $deleted = ServiceFormField::where('service_form_template_id', $template->id)->delete();
    echo "     ↳ Deleted {$deleted} existing fields.\n";

    $order = 10;
    foreach ($fields as $f) {
        ServiceFormField::create([
            'service_form_template_id'   => $template->id,
            'field_name'                 => $f['name'],
            'field_label'                => $f['label'],
            'field_type'                 => $f['type'],
            'options'                    => $f['options'] ?? null,
            'is_required'                => (bool) ($f['required'] ?? false),
            'sort_order'                 => $order,
            'section'                    => $f['section'],
            'allow_comments'             => false,
            'allow_attachment'           => (bool) ($f['allow_attachment'] ?? false),
            'allow_multiple_attachments' => (bool) ($f['allow_multiple_attachments'] ?? false),
            'attachment_label'           => $f['attachment_label'] ?? null,
            'validation_rules'           => null,
        ]);
        $order += 10;
    }
    echo "     ↳ Inserted " . count($fields) . " fields across "
        . count(array_unique(array_column($fields, 'section'))) . " sections.\n";
});

echo "\nDone.\n";
