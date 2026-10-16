<?php
/**
 * Replace the Legal Department "Commercial Contracts" form template
 * with the simplified Services Intake & Workflow spec.
 *
 * Run:  php scripts/reseed-legal-commercial-contracts.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceParent;
use App\Models\ServiceChild;
use App\Models\ServiceFormTemplate;
use App\Models\ServiceFormField;
use Illuminate\Support\Facades\DB;

$CONTRACT_TYPES = [
    'Lease Agreement', 'Rental Agreement', 'Service Contract', 'Other',
];

$STAGES = [
    'Draft / Intake',
    'Internal Legal Review',
    'Managing Director Execution',
    'Attorney General Review',
    'Attorney General Consent Received',
    'Final Execution',
    'Submitted to End User',
    'Completed',
];

$SR = 'Reference Details';
$S1 = '1. Service Request / Intake Details';
$S2 = '2. Internal Execution Process by Managing Director';
$S3 = '3. Internal Process After Execution';
$S4 = '4. External Approval Process by the Attorney General';
$S5 = '5. Execution by Managing Director';
$S6 = '6. Post-Execution — End User / Property Department';
$S7 = '7. Process Status & Record';

$fields = [
    // ---- Reference Details ----
    ['section' => $SR, 'name' => 'reference_no', 'label' => 'Reference No.', 'type' => 'text', 'required' => true],
    ['section' => $SR, 'name' => 'case_no',      'label' => 'Case No.',      'type' => 'text', 'required' => false],

    // ---- 1. Service Request / Intake Details ----
    ['section' => $S1, 'name' => 'requesting_department',   'label' => 'Requesting Department', 'type' => 'text', 'required' => true],
    ['section' => $S1, 'name' => 'subject',                 'label' => 'Subject', 'type' => 'textarea', 'required' => true],
    ['section' => $S1, 'name' => 'contract_type',           'label' => 'Contract', 'type' => 'select', 'options' => $CONTRACT_TYPES, 'required' => true],
    ['section' => $S1, 'name' => 'contract_type_other',     'label' => 'If Other, specify', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'contract_term',           'label' => 'Contract Term', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'lease_rental_details',    'label' => 'Lease Agreement / Rental Details', 'type' => 'textarea', 'required' => false],
    ['section' => $S1, 'name' => 'correspondence_attach',   'label' => 'Attach Correspondence / Instructions', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],
    ['section' => $S1, 'name' => 'date_of_instructions',    'label' => 'Date of Instructions', 'type' => 'date', 'required' => true],
    ['section' => $S1, 'name' => 'required_completion_date','label' => 'Required Completion Date (Standard review: 2 days)', 'type' => 'date', 'required' => false],

    // ---- 2. Internal Execution Process by MD ----
    ['section' => $S2, 'name' => 'memo_1_sm_to_hl',         'label' => 'Memo 1 — Senior Manager Commercial Contracts → Head Legal Services (recommending execution of the Lease Agreement)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S2, 'name' => 'memo_2_hl_to_cs',         'label' => 'Memo 2 — Head Legal Services → Company Secretary (confirming legal clearance)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S2, 'name' => 'memo_3_cs_to_md',         'label' => 'Memo 3 — Company Secretary → Managing Director (requesting MD execution)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S2, 'name' => 'internal_exec_due_date',  'label' => 'Required Completion Date (Standard: 2 days)', 'type' => 'date', 'required' => false],

    // ---- 3. Internal Process After Execution ----
    ['section' => $S3, 'name' => 'executed_lease_agreement','label' => 'Executed Lease Agreement (after MD execution)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S3, 'name' => 'memo_to_property_dept',   'label' => 'Memorandum to Property Department (End User)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],

    // ---- 4. External Approval Process by AG ----
    ['section' => $S4, 'name' => 'memo_4_sm_to_hl_ag',      'label' => 'Memo 4 — Senior Manager Commercial Contracts → Head Legal Services (asking MD to transmit draft lease to AG)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S4, 'name' => 'memo_5_hl_to_cs_ag',      'label' => 'Memo 5 — Head Legal Services → Company Secretary (requesting MD to submit draft lease to AG)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S4, 'name' => 'memo_6_cs_to_md_ag',      'label' => 'Memo 6 — Company Secretary → Managing Director (asking MD to sign covering letter for AG review)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S4, 'name' => 'ag_submission_note',      'label' => 'Note: Lease is submitted to Attorney General for review and approval. Expected timeframe — 3 weeks.', 'type' => 'textarea', 'required' => false],
    ['section' => $S4, 'name' => 'ag_consent_attachment',   'label' => 'Attach Attorney General Consent', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S4, 'name' => 'user_dept_submission',    'label' => 'User Department Submission (Agreement + Consent + Memorandum)', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 5. Execution by Managing Director ----
    ['section' => $S5, 'name' => 'memo_7_sm_to_hl',         'label' => 'Memo 7 — Senior Manager Commercial Contracts → Head Legal Services (recommending execution)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S5, 'name' => 'memo_8_hl_to_cs',         'label' => 'Memo 8 — Head Legal Services → Company Secretary (confirming legal clearance)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S5, 'name' => 'memo_9_cs_to_md',         'label' => 'Memo 9 — Company Secretary → Managing Director (requesting MD execution)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S5, 'name' => 'md_execution_due_date',   'label' => 'Required Completion Date (Standard: 2 days)', 'type' => 'date', 'required' => false],

    // ---- 6. Post-Execution ----
    ['section' => $S6, 'name' => 'final_executed_lease',    'label' => 'Final Executed Lease Agreement', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S6, 'name' => 'final_memo_property_dept','label' => 'Final Memorandum to Property Department (End User)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],

    // ---- 7. Process Status & Record ----
    ['section' => $S7, 'name' => 'current_stage',        'label' => 'Current Stage', 'type' => 'select', 'options' => $STAGES, 'required' => true],
    ['section' => $S7, 'name' => 'responsible_officer',  'label' => 'Responsible Officer', 'type' => 'text', 'required' => false],
    ['section' => $S7, 'name' => 'date_completed',       'label' => 'Date Completed', 'type' => 'date', 'required' => false],
    ['section' => $S7, 'name' => 'comments_remarks',     'label' => 'Comments / Remarks', 'type' => 'textarea', 'required' => false],
];

DB::transaction(function () use ($fields) {

    $parent = ServiceParent::where('department_code', 'legal services')
        ->orWhere('department_name', 'Legal Department')
        ->first();

    if (!$parent) {
        echo "  ERROR: Legal Department parent not found.\n";
        return;
    }

    $child = ServiceChild::updateOrCreate(
        ['service_parent_id' => $parent->id, 'service_name' => 'Commercial Contracts'],
        ['model' => 'Commercial Contracts', 'type' => 'new_acquisition', 'is_active' => 1]
    );
    echo "  Child  : #{$child->id}  {$child->service_name}\n";

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'Commercial Contracts — Services Intake & Workflow Form',
            'description' => 'Legal Department — Commercial Contracts intake, internal legal review, AG approval workflow, MD execution and end-user submission.',
            'is_active'   => true,
        ]
    );
    echo "  Template: #{$template->id}  {$template->form_name}\n";

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
