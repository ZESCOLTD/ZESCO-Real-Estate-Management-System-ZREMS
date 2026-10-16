<?php
/**
 * Seed the Legal Department child service "Commercial Contracts"
 * + its full intake & workflow form template.
 *
 * Safe to re-run: uses updateOrCreate for parent/child/template.
 * Fields are only inserted when the template has none.
 *
 * Run:  php scripts/seed-legal-commercial-contracts.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceParent;
use App\Models\ServiceChild;
use App\Models\ServiceFormTemplate;
use App\Models\ServiceFormField;
use Illuminate\Support\Facades\DB;

/* -------------------------------------------------------------------
 |  Option lists
 |  ------------------------------------------------------------------- */
$CONTRACT_TYPES = [
    'Procurement Contract', 'MOU', 'NDA', 'PPA', 'PSA',
    'Grid Connection Agreement', 'Lease Agreement', 'Escrow Agreement',
    'Contract of Service', 'EPC Contract', 'Other',
];

$SOURCE_DEPTS = [
    'Planning & Projects', 'Procurement', 'Business Development',
    'Information Technology', 'Property Department', 'Other',
];

$RECEIVED_THROUGH = [
    'Head of Department', 'Head Legal Services',
    'Snr. Manager Commercial Contracts', 'Legal Representative/Self',
    'Company Secretary', 'Managing Director', 'Other',
];

$YES_NO = ['Yes', 'No'];

$CONSENT_STATUS = [
    'Granted', 'Granted subject to amendments',
    'Further information requested', 'Not granted', 'Pending',
];

$MD_ACTION = [
    'Executed', 'Returned for amendment',
    'Further information required', 'Not approved',
];

$MATTER_STATUS = [
    'New', 'Under Review', 'Awaiting Instructions', 'Awaiting AG Approval',
    'Awaiting Amendment', 'Awaiting Execution', 'Awaiting Other Party',
    'Completed', 'Closed',
];

$STAGE_STATUS = ['Pending', 'Done'];

$BOILERPLATE_CLAUSES = [
    'Parties Clause', 'Interpretation Clause', 'Obligation',
    'Amendments/Variation', 'Change Orders and Contract Amendments',
    'Severability', 'Notices', 'Governing Law', 'Contract Language',
    'Confidentiality', 'Force Majeure', 'Termination', 'Assignment',
    'Public Audit', 'Tax', 'Performance Security', 'Dispute Resolution',
    'Seat of Arbitration', 'Place of Arbitration', 'Corruption and Fraud',
    'Terms of Payment', 'Advance Payment (Contract of Service/EPC)',
    'Non Waiver', 'Joint Venture or Partnership',
    'Relationship of the Parties', 'Corporate Authority and Standing',
    'Sub Contracting', 'Entirety', 'Survival', 'Payments',
    'Defective', 'Insurance', 'Incoterms', 'Special Conditions',
    'Numbering of SCC',
];

$AG_DOCS = [
    'Agreement', 'Covering Letter', 'Supporting Documents',
    'Relevant Internal Memoranda', 'Other',
];

$LEGAL_ACTIONS_AFTER_CONSENT = [
    'Comments reviewed', 'Amendments incorporated',
    'Revised agreement sent to End User', 'Further clarification requested', 'Other',
];

$RECORD_MGMT_CHECKS = [
    'Fully executed agreement scanned', 'Electronic copy saved',
    'Hard copy filed', 'Contract register updated',
    'Attorney General consent filed (where applicable)',
    'Internal memoranda filed', 'Submission acknowledgement filed',
    'Relevant correspondence filed',
];

$CLOSURE_CHECKS = [
    'Contract fully executed', 'Legal Department received final copy',
    'Records updated', 'File complete', 'File closed',
];

/* -------------------------------------------------------------------
 |  Workflow tracker rows (Section 13)
 |  ------------------------------------------------------------------- */
$WORKFLOW_STAGES = [
    ['Contract received',              'Company Secretary'],
    ['Referred to Head Legal',         'Head Legal'],
    ['Referred to Commercial Contracts','Head Legal'],
    ['Assigned to Officer',            'Senior Manager'],
    ['Legal review',                   'Legal Officer'],
    ['AG assessment',                  'Legal Officer'],
    ['Internal memoranda',             'Legal Officer'],
    ['MD approval',                    'MD'],
    ['AG submission',                  'Legal Department'],
    ['AG consent',                     'AG Chambers'],
    ['Amendments',                     'Legal Officer'],
    ['Execution approval',             'Management'],
    ['MD execution',                   'MD'],
    ['End-user transmission',          'Commercial Contracts'],
    ['Full execution',                 'All Parties'],
    ['Legal filing',                   'Legal Department'],
];

$WORKFLOW_TABLE_DEFAULT_ROWS = [];
foreach ($WORKFLOW_STAGES as [$stage, $officer]) {
    $WORKFLOW_TABLE_DEFAULT_ROWS[] = [
        'stage' => $stage,
        'officer' => $officer,
        'date_due' => '',
        'date_completed' => '',
        'status' => 'Pending',
    ];
}

$WORKFLOW_TABLE = [
    ['name' => 'stage',          'label' => 'Stage',              'type' => 'text'],
    ['name' => 'officer',        'label' => 'Responsible Officer','type' => 'text'],
    ['name' => 'date_due',       'label' => 'Date Due',           'type' => 'date'],
    ['name' => 'date_completed', 'label' => 'Date Completed',     'type' => 'date'],
    ['name' => 'status',         'label' => 'Status',             'type' => 'select', 'options' => $STAGE_STATUS],
    'default_rows' => $WORKFLOW_TABLE_DEFAULT_ROWS,
];

/* -------------------------------------------------------------------
 |  Field definitions
 |  ------------------------------------------------------------------- */
$S0  = '0. Matter Identification';
$S1  = '1. Matter Intake';
$S2  = '2. Assignment';
$S3  = '3. Contract Checklist / Boilerplate Clauses';
$S4  = '4. Attorney General Approval Assessment';
$S5  = '5. Contracts Requiring AG Approval — Internal Chain';
$S6  = '6. Attorney General Submission';
$S7  = '7. Attorney General\'s Consent';
$S8  = '8. Contracts NOT Requiring AG Approval — Execution';
$S9  = '9. MD Execution';
$S10 = '10. Forwarding to Originating Department';
$S11 = '11. Full Execution';
$S12 = '12. Contract Record Management';
$S13 = '13. Commercial Matter Workflow Tracker';
$S14 = '14. Matter Closure';
$S15 = '15. Matter Status';

$fields = [
    // ---- 0. Header identification ----
    ['section' => $S0, 'name' => 'reference_no',     'label' => 'Reference No.',  'type' => 'text', 'required' => true],
    ['section' => $S0, 'name' => 'case_no',          'label' => 'Case No.',       'type' => 'text', 'required' => false],
    ['section' => $S0, 'name' => 'requesting_directorate', 'label' => 'Requesting Directorate', 'type' => 'text', 'required' => true],
    ['section' => $S0, 'name' => 'legal_representative_self', 'label' => 'Legal Representative / Self', 'type' => 'text', 'required' => false],
    ['section' => $S0, 'name' => 'head_of_department','label' => 'Head of Department', 'type' => 'text', 'required' => false],
    ['section' => $S0, 'name' => 'subject',          'label' => 'Subject', 'type' => 'text', 'required' => true],
    ['section' => $S0, 'name' => 'contract_name',    'label' => 'Contract', 'type' => 'text', 'required' => true],
    ['section' => $S0, 'name' => 'contract_type',    'label' => 'Nature of the Contract', 'type' => 'select', 'options' => $CONTRACT_TYPES, 'required' => true],
    ['section' => $S0, 'name' => 'contract_type_other', 'label' => 'If Other, specify', 'type' => 'text', 'required' => false],
    ['section' => $S0, 'name' => 'contract_term',    'label' => 'Contract Term', 'type' => 'text', 'required' => false],
    ['section' => $S0, 'name' => 'contract_price',   'label' => 'Contract Price', 'type' => 'text', 'required' => false],
    ['section' => $S0, 'name' => 'correspondence_attach', 'label' => 'Attach Correspondence / Instructions', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],
    ['section' => $S0, 'name' => 'received_through', 'label' => 'Received Through', 'type' => 'select', 'options' => $RECEIVED_THROUGH, 'required' => true],
    ['section' => $S0, 'name' => 'date_of_instructions', 'label' => 'Date of Instructions', 'type' => 'date', 'required' => true],
    ['section' => $S0, 'name' => 'head_legal_services', 'label' => 'Head Legal Services', 'type' => 'text', 'required' => false],
    ['section' => $S0, 'name' => 'snr_manager_commercial','label' => 'Snr. Manager Commercial Contracts', 'type' => 'text', 'required' => false],

    // ---- 1. Matter Intake ----
    ['section' => $S1, 'name' => 'source_of_contract',       'label' => 'Source of Contract (Department)', 'type' => 'select', 'options' => $SOURCE_DEPTS, 'required' => true],
    ['section' => $S1, 'name' => 'source_of_contract_other', 'label' => 'If Other, specify', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'assigned_legal_officer',   'label' => 'Assigned Legal Officer', 'type' => 'text', 'required' => true],
    ['section' => $S1, 'name' => 'required_completion_date', 'label' => 'Required Completion (Review standard: 2 days)', 'type' => 'date', 'required' => true],
    ['section' => $S1, 'name' => 'execution_notes',          'label' => 'Execution Notes (MD → Company Secretary → Head Legal → AG if required — timeframe 2-3 weeks)', 'type' => 'textarea', 'required' => false],

    // ---- 2. Assignment ----
    ['section' => $S2, 'name' => 'outstanding_documents',     'label' => 'Outstanding Documents / Information', 'type' => 'textarea', 'required' => false],
    ['section' => $S2, 'name' => 'date_assigned_commercial',  'label' => 'Date Assigned to Commercial Contracts', 'type' => 'date', 'required' => true],
    ['section' => $S2, 'name' => 'assigned_officer_commercial','label' => 'Assigned Legal Officer', 'type' => 'text', 'required' => true],

    // ---- 3. Contract Checklist / Boilerplate ----
    ['section' => $S3, 'name' => 'boilerplate_clauses', 'label' => 'Applicable Clauses (tick all that apply)', 'type' => 'checkbox', 'options' => $BOILERPLATE_CLAUSES, 'required' => false],
    ['section' => $S3, 'name' => 'clauses_notes',       'label' => 'Notes on clauses / Not-applicable rationale', 'type' => 'textarea', 'required' => false],

    // ---- 4. AG Approval Assessment ----
    ['section' => $S4, 'name' => 'ag_approval_required',  'label' => 'Does the Agreement Require Attorney General Approval / Consent?', 'type' => 'select', 'options' => $YES_NO, 'required' => true],
    ['section' => $S4, 'name' => 'ag_basis_determination','label' => 'Basis for Determination (AG Threshold)', 'type' => 'textarea', 'required' => true],
    ['section' => $S4, 'name' => 'ag_determined_by',      'label' => 'Determined By', 'type' => 'text', 'required' => true],
    ['section' => $S4, 'name' => 'ag_determination_date', 'label' => 'Date', 'type' => 'date', 'required' => true],

    // ---- 5. Contracts Requiring AG Approval — Internal Chain ----
    // A. Senior Manager → Head Legal Services
    ['section' => $S5, 'name' => 'chain_a_checks',         'label' => 'A. Senior Manager → Head Legal Services', 'type' => 'checkbox', 'options' => ['Legal review completed','Memorandum prepared','Submitted to Head Legal'], 'required' => false],
    ['section' => $S5, 'name' => 'chain_a_date',           'label' => 'A. Date', 'type' => 'date', 'required' => false],
    ['section' => $S5, 'name' => 'chain_a_recommendation', 'label' => 'A. Recommendation', 'type' => 'textarea', 'required' => false],
    // B. Head Legal → Company Secretary
    ['section' => $S5, 'name' => 'chain_b_checks',         'label' => 'B. Head Legal → Company Secretary', 'type' => 'checkbox', 'options' => ['Memorandum prepared','Contract reviewed','Submitted to Company Secretary'], 'required' => false],
    ['section' => $S5, 'name' => 'chain_b_date',           'label' => 'B. Date', 'type' => 'date', 'required' => false],
    // C. Company Secretary → Managing Director
    ['section' => $S5, 'name' => 'chain_c_checks',         'label' => 'C. Company Secretary → Managing Director', 'type' => 'checkbox', 'options' => ['Memorandum prepared','Covering letter prepared for AG approval','Approval / signature requested','Submitted to Managing Director'], 'required' => false],
    ['section' => $S5, 'name' => 'chain_c_date',           'label' => 'C. Date', 'type' => 'date', 'required' => false],

    // ---- 6. Attorney General Submission ----
    ['section' => $S6, 'name' => 'md_covering_letter_signed', 'label' => 'Covering Letter Signed by MD', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S6, 'name' => 'md_covering_letter_file',   'label' => 'Attach Covering Letter', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S6, 'name' => 'md_covering_letter_date',   'label' => 'Date Signed', 'type' => 'date', 'required' => false],
    ['section' => $S6, 'name' => 'ag_submission_date',        'label' => 'Date Submitted to AG Chambers', 'type' => 'date', 'required' => false],
    ['section' => $S6, 'name' => 'ag_submission_ref_no',      'label' => 'Submission Reference No.', 'type' => 'text', 'required' => false],
    ['section' => $S6, 'name' => 'ag_documents_submitted',    'label' => 'Documents Submitted', 'type' => 'checkbox', 'options' => $AG_DOCS, 'required' => false],
    ['section' => $S6, 'name' => 'ag_documents_other',        'label' => 'If Other document, specify', 'type' => 'text', 'required' => false],
    ['section' => $S6, 'name' => 'ag_ack_received',           'label' => 'Acknowledgement Received', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S6, 'name' => 'ag_ack_date',               'label' => 'Acknowledgement Date', 'type' => 'date', 'required' => false],
    ['section' => $S6, 'name' => 'ag_ack_scanned',            'label' => 'Acknowledgement Scanned', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S6, 'name' => 'ag_ack_filed',              'label' => 'Acknowledgement Filed', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S6, 'name' => 'ag_ack_file',               'label' => 'Attach Acknowledgement', 'type' => 'file', 'required' => false, 'allow_attachment' => true],

    // ---- 7. AG's Consent ----
    ['section' => $S7, 'name' => 'ag_consent_date',       'label' => 'Date Consent Received', 'type' => 'date', 'required' => false],
    ['section' => $S7, 'name' => 'ag_consent_status',     'label' => 'Consent Status', 'type' => 'select', 'options' => $CONSENT_STATUS, 'required' => false],
    ['section' => $S7, 'name' => 'ag_consent_file',       'label' => 'Attach AG Consent Letter', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S7, 'name' => 'legal_action_taken',    'label' => 'Action Taken by Legal Department', 'type' => 'checkbox', 'options' => $LEGAL_ACTIONS_AFTER_CONSENT, 'required' => false],
    ['section' => $S7, 'name' => 'legal_action_other',    'label' => 'If Other action, specify', 'type' => 'text', 'required' => false],
    ['section' => $S7, 'name' => 'legal_action_date',     'label' => 'Date', 'type' => 'date', 'required' => false],

    // ---- 8. Contracts NOT Requiring AG Approval — Execution ----
    ['section' => $S8, 'name' => 'non_ag_checks',        'label' => 'Execution Checklist', 'type' => 'checkbox', 'options' => ['Legal review completed','Senior Manager recommendation prepared','Head Legal memorandum prepared','Company Secretary memorandum prepared','MD execution requested'], 'required' => false],
    ['section' => $S8, 'name' => 'non_ag_reason',        'label' => 'Reason AG Approval Is Not Required', 'type' => 'textarea', 'required' => false],
    ['section' => $S8, 'name' => 'sm_recommendation',    'label' => 'Senior Manager, Commercial Contracts — Recommendation', 'type' => 'textarea', 'required' => false],
    ['section' => $S8, 'name' => 'sm_signature',         'label' => 'Senior Manager Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S8, 'name' => 'sm_signature_date',    'label' => 'Senior Manager Signature Date', 'type' => 'date', 'required' => false],
    ['section' => $S8, 'name' => 'hl_recommendation',    'label' => 'Head Legal Services — Recommendation', 'type' => 'textarea', 'required' => false],
    ['section' => $S8, 'name' => 'hl_signature',         'label' => 'Head Legal Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S8, 'name' => 'hl_signature_date',    'label' => 'Head Legal Signature Date', 'type' => 'date', 'required' => false],
    ['section' => $S8, 'name' => 'cs_recommendation',    'label' => 'Company Secretary — Recommendation', 'type' => 'textarea', 'required' => false],
    ['section' => $S8, 'name' => 'cs_signature',         'label' => 'Company Secretary Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S8, 'name' => 'cs_signature_date',    'label' => 'Company Secretary Signature Date', 'type' => 'date', 'required' => false],

    // ---- 9. MD Execution ----
    ['section' => $S9, 'name' => 'md_submission_date', 'label' => 'Agreement Submitted to MD', 'type' => 'date', 'required' => false],
    ['section' => $S9, 'name' => 'md_action',          'label' => 'MD Action', 'type' => 'select', 'options' => $MD_ACTION, 'required' => false],
    ['section' => $S9, 'name' => 'md_signature_date',  'label' => 'MD Signature Date', 'type' => 'date', 'required' => false],

    // ---- 10. Forwarding to Originating Department ----
    ['section' => $S10, 'name' => 'agreement_received_from_md', 'label' => 'Agreement Received from MD', 'type' => 'date', 'required' => false],
    ['section' => $S10, 'name' => 'forwarded_to_end_user',      'label' => 'Forwarded to End User', 'type' => 'date', 'required' => false],
    ['section' => $S10, 'name' => 'forwarded_by',               'label' => 'Forwarded By', 'type' => 'text', 'required' => false],

    // ---- 11. Full Execution ----
    ['section' => $S11, 'name' => 'date_fully_executed',    'label' => 'Date Fully Executed', 'type' => 'date', 'required' => false],
    ['section' => $S11, 'name' => 'all_parties_signed',     'label' => 'All Parties Signed', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'copy_received_by_legal', 'label' => 'Copy Received by Legal Department', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'copy_received_date',     'label' => 'Date Received', 'type' => 'date', 'required' => false],
    ['section' => $S11, 'name' => 'fully_executed_file',    'label' => 'Attach Fully-Executed Agreement', 'type' => 'file', 'required' => false, 'allow_attachment' => true],

    // ---- 12. Contract Record Management ----
    ['section' => $S12, 'name' => 'record_mgmt_checks',    'label' => 'Record Management Checklist', 'type' => 'checkbox', 'options' => $RECORD_MGMT_CHECKS, 'required' => false],
    ['section' => $S12, 'name' => 'electronic_file_location','label' => 'Electronic File Location', 'type' => 'text', 'required' => false],
    ['section' => $S12, 'name' => 'physical_file_location','label' => 'Physical File Location', 'type' => 'text', 'required' => false],
    ['section' => $S12, 'name' => 'filed_by',              'label' => 'Filed By', 'type' => 'text', 'required' => false],
    ['section' => $S12, 'name' => 'filed_date',            'label' => 'Date Filed', 'type' => 'date', 'required' => false],

    // ---- 13. Workflow Tracker ----
    ['section' => $S13, 'name' => 'workflow_tracker', 'label' => 'Commercial Matter Workflow Tracker', 'type' => 'table', 'required' => false, 'options' => $WORKFLOW_TABLE],

    // ---- 14. Matter Closure ----
    ['section' => $S14, 'name' => 'final_outcome',       'label' => 'Final Outcome', 'type' => 'textarea', 'required' => false],
    ['section' => $S14, 'name' => 'outstanding_issues',  'label' => 'Outstanding Issues', 'type' => 'textarea', 'required' => false],
    ['section' => $S14, 'name' => 'closure_checks',      'label' => 'Closure Checklist', 'type' => 'checkbox', 'options' => $CLOSURE_CHECKS, 'required' => false],
    ['section' => $S14, 'name' => 'closed_by',           'label' => 'Closed By', 'type' => 'text', 'required' => false],
    ['section' => $S14, 'name' => 'closed_date',         'label' => 'Closure Date', 'type' => 'date', 'required' => false],
    ['section' => $S14, 'name' => 'head_legal_approval', 'label' => 'Head Legal Services Approval (Name)', 'type' => 'text', 'required' => false],
    ['section' => $S14, 'name' => 'head_legal_signature','label' => 'Head Legal Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S14, 'name' => 'head_legal_sig_date', 'label' => 'Signature Date', 'type' => 'date', 'required' => false],

    // ---- 15. Matter Status ----
    ['section' => $S15, 'name' => 'matter_status', 'label' => 'Matter Status', 'type' => 'select', 'options' => $MATTER_STATUS, 'required' => true],
];

/* -------------------------------------------------------------------
 |  Persist
 |  ------------------------------------------------------------------- */
DB::transaction(function () use ($fields) {

    $parent = ServiceParent::where('department_code', 'legal services')
        ->orWhere('department_name', 'Legal Department')
        ->orWhere('service_name', 'like', '%Legal%')
        ->first();

    if (!$parent) {
        $parent = ServiceParent::create([
            'department_code' => 'legal services',
            'department_name' => 'Legal Department',
            'service_name'    => 'Legal Services',
        ]);
        echo "  Parent (created): #{$parent->id}  {$parent->department_name} / {$parent->service_name}\n";
    } else {
        echo "  Parent : #{$parent->id}  {$parent->department_name} / {$parent->service_name}\n";
    }

    $child = ServiceChild::updateOrCreate(
        ['service_parent_id' => $parent->id, 'service_name' => 'Commercial Contracts'],
        ['model' => 'Commercial Contracts', 'type' => 'new_acquisition', 'is_active' => 1]
    );
    echo "  Child  : #{$child->id}  {$child->service_name}\n";

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'Commercial Contracts Services Intake & Workflow Form',
            'description' => 'Legal Department — Commercial Contracts intake, review, AG-approval workflow, execution and closure.',
            'is_active'   => true,
        ]
    );
    echo "  Template: #{$template->id}  {$template->form_name}\n";

    $existing = ServiceFormField::where('service_form_template_id', $template->id)->count();
    if ($existing > 0) {
        echo "     ↳ {$existing} fields already exist, skipping insert.\n";
        return;
    }

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
