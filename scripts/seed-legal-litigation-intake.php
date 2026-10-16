<?php
/**
 * Seed the Legal Department child service "Litigation Matter Intake"
 * + its form template with all 18 sections from the source spec.
 *
 * Parent already exists: service_parents.id = 22 (Legal Department / legal services / Legal Services)
 *
 * Safe to re-run: uses updateOrCreate for child + template; only inserts fields
 * if the template has none.
 *
 * Run:  php scripts/seed-legal-litigation-intake.php
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
$MATTER_TYPES = [
    'Letters','Compensation Claims','Letter of Demand','Writ of Summons',
    'Statement of Claim','Defence','Memorandum of Appearance','Reply',
    'Orders for Direction','Status','Scheduling Conference',
    'Interlocutory Applications','Summons','Affidavit','Skeleton Arguments',
    'Submissions','Bill of Costs','Appeal','Judgments','Other',
];

$PRIORITY = ['Urgent','Normal','Low'];

$DEADLINE_TYPES = ['Court Deadline','Claimant Response Deadline','Other'];

$INITIAL_REVIEW_CHECKS = [
    'Documents reviewed (letter)',
    'Court process reviewed',
    'Limitation/deadline identified',
    'Legal issues identified',
    'Facts established',
    'Relevant department identified',
    'Further instructions required',
    'Sufficient information available to respond',
];

$DEPARTMENTS = [
    'Property','Geomatics','Finance','Procurement','Human Resources',
    'Distribution','Transmission','Generation','Customer Services',
    'ICT','SHEQ','Valuation','Company Secretariat','Other',
];

$INFO_REQUIRED = [
    'Contract/Agreement','Correspondence','Invoices/Payment Records',
    'Project/Technical Records','Previous Legal Correspondence',
    'Witness Information','Company Instructions','Other',
];

$YES_NO = ['Yes','No'];

$INFO_SUFFICIENT = ['Yes — proceed','No — request further info'];

$LEGAL_ACTIONS = [
    'Prepare response letter','Prepare Defence','Prepare Affidavit',
    'Prepare Submission','Prepare Notice','Prepare other court process',
    'File court documents','Serve documents','Attend court',
    'Negotiate settlement','Ex-curia settlement','Seek further instructions','Other',
];

$WORK_COMPLETED = ['Not Started','In Progress','Completed','Blocked','On Hold'];

$INTERNAL_REVIEW = [
    'Reviewed by Senior/Responsible Officer',
    'Reviewed by Head Legal',
    'Final version approved',
];

$SETTLEMENT_TYPES = [
    'Ex-curia settlement','Negotiated settlement','Court-directed settlement',
    'Judgment','Other',
];

$FINAL_OUTCOMES = [
    'Judgment in favour of Company','Judgment against Company',
    'Settlement','Claim withdrawn','Matter discontinued',
    'Appeal filed','Appeal concluded','Other',
];

$CLOSURE_CHECKLIST = [
    'Final court documents filed','Judgment/order filed','Correspondence filed',
    'Departmental instructions filed','Hearing reports filed','Settlement documents filed',
    'Proof of service filed','Electronic records updated','Physical file complete',
    'Legal database/register updated','Matter closed',
];

/* -------------------------------------------------------------------
 |  Repeater column sets + default rows
 |  ------------------------------------------------------------------- */

// Section 7 — Departmental Follow-Up (one empty starter row; users add more)
$FOLLOWUP_TABLE = [
    ['name' => 'stage',    'label' => 'Follow-Up',   'type' => 'text'],
    ['name' => 'date',     'label' => 'Date',        'type' => 'date'],
    ['name' => 'officer',  'label' => 'Officer',     'type' => 'text'],
    ['name' => 'response', 'label' => 'Response',    'type' => 'text'],
    ['name' => 'remarks',  'label' => 'Remarks',     'type' => 'text'],
    'default_rows' => [
        ['stage' => '', 'date' => '', 'officer' => '', 'response' => '', 'remarks' => ''],
    ],
];

// Section 15 — Litigation Workflow Tracker (17 pre-seeded rows)
$WORKFLOW_STAGES = [
    ['Matter received',                    'MD/CS'],
    ['Referred to Head Legal',             'Company Secretary'],
    ['Matter assigned',                    'Head Legal'],
    ['Initial review',                     'Officer'],
    ['Department identified',              'Officer'],
    ['Instructions requested',             'Officer'],
    ['Extension requested',                'Officer'],
    ['Department response',                'Relevant Department'],
    ['Head Legal updated',                 'Officer'],
    ['Response/court process drafted',     'Officer'],
    ['Approval/review',                    'Head Legal'],
    ['Filing/service',                     'Officer'],
    ['Court attendance',                   'Officer'],
    ['Hearing report',                     'Officer'],
    ['Further action',                     'Officer'],
    ['Settlement/Final determination',     'Officer'],
    ['File closure',                       'Legal Department'],
];
$workflowDefaults = [];
foreach ($WORKFLOW_STAGES as [$stage, $officer]) {
    $workflowDefaults[] = [
        'stage' => $stage, 'officer' => $officer,
        'date_due' => '', 'date_completed' => '', 'status' => 'Pending',
    ];
}
$WORKFLOW_TABLE = [
    ['name' => 'stage',          'label' => 'Stage',                 'type' => 'text'],
    ['name' => 'officer',        'label' => 'Responsible Officer',   'type' => 'text'],
    ['name' => 'date_due',       'label' => 'Date Due',              'type' => 'date'],
    ['name' => 'date_completed', 'label' => 'Date Completed',        'type' => 'date'],
    ['name' => 'status',         'label' => 'Status',                'type' => 'text'],
    'default_rows' => [
        ['stage' => '', 'officer' => '', 'date_due' => '', 'date_completed' => '', 'status' => ''],
    ],
];

// Section 16 — Deadline & Compliance Monitor (7 pre-seeded rows)
$DEADLINE_ROWS = [
    ['Response Letter deadline',   '1 day'],
    ['Memorandum',                 '1 day'],
    ['Filing deadline',            '1 day'],
    ['Service deadline (Arguments)','2 days'],
    ['Court hearing',              ''],
    ['Compensation Claims',        '1 day'],
    ['Other',                      ''],
];
$deadlineDefaults = [];
foreach ($DEADLINE_ROWS as [$event, $date]) {
    $deadlineDefaults[] = [
        'event' => $event, 'date' => $date,
        'action_required' => '', 'officer' => '', 'completed' => 'No',
    ];
}
$DEADLINE_TABLE = [
    ['name' => 'event',           'label' => 'Deadline/Event',     'type' => 'text'],
    ['name' => 'date',            'label' => 'Date',               'type' => 'text'],
    ['name' => 'action_required', 'label' => 'Action Required',    'type' => 'text'],
    ['name' => 'officer',         'label' => 'Responsible Officer','type' => 'text'],
    ['name' => 'completed',       'label' => 'Completed',          'type' => 'text'],
    'default_rows' => $deadlineDefaults,
];

/* -------------------------------------------------------------------
 |  Field definitions
 |  ------------------------------------------------------------------- */
$fields = [

    // ============ Section 1 — Matter Intake ============
    ['section' => '1. Matter Intake', 'name' => 'matter_type',        'label' => 'Type of Matter',                     'type' => 'select',     'required' => true,  'options' => $MATTER_TYPES],
    ['section' => '1. Matter Intake', 'name' => 'matter_type_other',  'label' => 'If Other, specify',                  'type' => 'text',       'required' => false],
    ['section' => '1. Matter Intake', 'name' => 'plaintiff',          'label' => 'Plaintiff / Claimant',               'type' => 'text',       'required' => true],
    ['section' => '1. Matter Intake', 'name' => 'defendant',          'label' => 'Defendant / Respondent',             'type' => 'repeater',   'required' => true,  'options' => [
        ['name' => 'name', 'label' => 'Defendant / Respondent', 'type' => 'text'],
        'default_rows' => [ ['name' => ''] ],
    ]],
    ['section' => '1. Matter Intake', 'name' => 'court',              'label' => 'Court',                              'type' => 'text',       'required' => true],
    ['section' => '1. Matter Intake', 'name' => 'cause_number',       'label' => 'Cause Number',                       'type' => 'text',       'required' => false],
    ['section' => '1. Matter Intake', 'name' => 'date_received',      'label' => 'Date Received',                      'type' => 'date',       'required' => true],
    ['section' => '1. Matter Intake', 'name' => 'response_deadline',  'label' => 'Response Deadline',                  'type' => 'date',       'required' => true],
    ['section' => '1. Matter Intake', 'name' => 'next_court_date',    'label' => 'Court Date',                         'type' => 'date',       'required' => false],
    ['section' => '1. Matter Intake', 'name' => 'amount_claimed',     'label' => 'Amount Claimed (K)',                 'type' => 'number',     'required' => false],

    // ============ Section 2 — Classification & Priority ============
    ['section' => '2. Matter Classification & Priority', 'name' => 'nature_of_claim', 'label' => 'Nature of Claim', 'type' => 'textarea',  'required' => true],
    ['section' => '2. Matter Classification & Priority', 'name' => 'priority',        'label' => 'Priority',        'type' => 'radio',     'required' => true, 'options' => $PRIORITY],
    ['section' => '2. Matter Classification & Priority', 'name' => 'deadline_type',   'label' => 'Deadline Type',   'type' => 'select',    'required' => true, 'options' => $DEADLINE_TYPES],
    ['section' => '2. Matter Classification & Priority', 'name' => 'deadline_other',  'label' => 'If Other, specify','type' => 'text',     'required' => false],

    // ============ Section 3 — Instructions by Head Legal ============
    ['section' => '3. Instructions by Head Legal', 'name' => 'date_assigned',       'label' => 'Date Assigned',            'type' => 'date',     'required' => true],
    ['section' => '3. Instructions by Head Legal', 'name' => 'assigned_officer',    'label' => 'Assigned Legal Officer',    'type' => 'text',     'required' => true],
    ['section' => '3. Instructions by Head Legal', 'name' => 'head_legal_instructions', 'label' => "Head Legal's Instructions", 'type' => 'textarea', 'required' => true],
    ['section' => '3. Instructions by Head Legal', 'name' => 'head_legal_instructions_file', 'label' => 'Upload Instructions Document', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => '3. Instructions by Head Legal', 'name' => 'immediate_action',    'label' => 'Immediate Action Required', 'type' => 'textarea','required' => false],

    // ============ Section 4 — Initial Legal Review ============
    ['section' => '4. Initial Legal Review', 'name' => 'initial_review_checks',   'label' => 'Confirm the following',    'type' => 'checkbox_group', 'required' => false, 'options' => $INITIAL_REVIEW_CHECKS],
    ['section' => '4. Initial Legal Review', 'name' => 'preliminary_assessment', 'label' => 'Preliminary Assessment',    'type' => 'textarea', 'required' => true],

    // ============ Section 5 — Departmental Info Request ============
    ['section' => '5. Request for Departmental Information / Instructions', 'name' => 'relevant_department',  'label' => 'Relevant Department', 'type' => 'select', 'required' => true, 'options' => $DEPARTMENTS],
    ['section' => '5. Request for Departmental Information / Instructions', 'name' => 'memorandum_attachment','label' => 'Attach Memorandum',   'type' => 'file',   'required' => false, 'allow_attachment' => true],
    ['section' => '5. Request for Departmental Information / Instructions', 'name' => 'date_request_sent',    'label' => 'Date Request Sent',   'type' => 'date',   'required' => true],
    ['section' => '5. Request for Departmental Information / Instructions', 'name' => 'detailed_info_required','label' => 'Detailed Information Required', 'type' => 'textarea', 'required' => false],
    ['section' => '5. Request for Departmental Information / Instructions', 'name' => 'departmental_deadline','label' => 'Deadline for Departmental Response (5-day rule)', 'type' => 'date', 'required' => true],
    ['section' => '5. Request for Departmental Information / Instructions', 'name' => 'time_is_of_essence',   'label' => 'Time is of the Essence', 'type' => 'radio', 'required' => true, 'options' => $YES_NO],

    // ============ Section 7 — Departmental Follow-Up (TABLE) ============
    ['section' => '7. Departmental Follow-Up', 'name' => 'departmental_followup', 'label' => 'Follow-Up Log', 'type' => 'repeater', 'required' => false, 'options' => $FOLLOWUP_TABLE],

    // ============ Section 8 — Review of Departmental Instructions ============
    ['section' => '8. Review of Departmental Instructions', 'name' => 'review_date',          'label' => 'Date Reviewed',                 'type' => 'date',           'required' => true],
    ['section' => '8. Review of Departmental Instructions', 'name' => 'officer_assessment',   'label' => "Legal Officer's Assessment",   'type' => 'textarea',       'required' => true],
    ['section' => '8. Review of Departmental Instructions', 'name' => 'legal_action_required','label' => 'Legal Action Required',         'type' => 'checkbox_group', 'required' => false, 'options' => $LEGAL_ACTIONS],
    ['section' => '8. Review of Departmental Instructions', 'name' => 'legal_action_other',   'label' => 'If Other, specify',             'type' => 'text',           'required' => false],

    // ============ Section 10 — Drafting & Approval ============
    ['section' => '10. Drafting & Approval', 'name' => 'document_prepared',  'label' => 'Document Prepared', 'type' => 'text',           'required' => true],
    ['section' => '10. Drafting & Approval', 'name' => 'date_prepared',      'label' => 'Date Prepared',     'type' => 'date',           'required' => true],
    ['section' => '10. Drafting & Approval', 'name' => 'internal_review',    'label' => 'Internal Review',   'type' => 'checkbox_group', 'required' => false, 'options' => $INTERNAL_REVIEW],
    ['section' => '10. Drafting & Approval', 'name' => 'date_approved',      'label' => 'Date Approved',     'type' => 'date',           'required' => false],

    // ============ Section 11 — Filing / Service of Court Documents ============
    ['section' => '11. Filing / Service of Court Documents', 'name' => 'document_filed_served', 'label' => 'Document Filed / Served', 'type' => 'text',  'required' => false],
    ['section' => '11. Filing / Service of Court Documents', 'name' => 'court_recipient',       'label' => 'Court / Recipient',       'type' => 'text',  'required' => true],
    ['section' => '11. Filing / Service of Court Documents', 'name' => 'date_filed_served',     'label' => 'Date Filed / Served',     'type' => 'date',  'required' => true],
    ['section' => '11. Filing / Service of Court Documents', 'name' => 'filing_deadline',       'label' => 'Deadline',                'type' => 'date',  'required' => false],
    ['section' => '11. Filing / Service of Court Documents', 'name' => 'proof_obtained',        'label' => 'Proof of Filing / Service Obtained', 'type' => 'radio', 'required' => false, 'options' => $YES_NO],
    ['section' => '11. Filing / Service of Court Documents', 'name' => 'proof_filed',           'label' => 'Proof Filed',             'type' => 'radio', 'required' => false, 'options' => $YES_NO],
    ['section' => '11. Filing / Service of Court Documents', 'name' => 'court_reference_no',    'label' => 'Court Reference / Receipt No.', 'type' => 'text', 'required' => false],
    ['section' => '11. Filing / Service of Court Documents', 'name' => 'proof_attachment',      'label' => 'Proof Attachment',        'type' => 'file',  'required' => false, 'allow_attachment' => true],

    // ============ Section 14 — Settlement / Resolution ============
    ['section' => '14. Settlement / Resolution', 'name' => 'settlement_considered', 'label' => 'Settlement Considered', 'type' => 'radio',    'required' => false, 'options' => $YES_NO],
    ['section' => '14. Settlement / Resolution', 'name' => 'settlement_type',       'label' => 'Type',                  'type' => 'select',   'required' => false, 'options' => $SETTLEMENT_TYPES],
    ['section' => '14. Settlement / Resolution', 'name' => 'settlement_type_other', 'label' => 'If Other, specify',     'type' => 'text',     'required' => false],
    ['section' => '14. Settlement / Resolution', 'name' => 'settlement_terms',      'label' => 'Settlement Terms / Proposal', 'type' => 'textarea', 'required' => false],
    ['section' => '14. Settlement / Resolution', 'name' => 'settlement_approval',   'label' => 'Approval Obtained',     'type' => 'radio',    'required' => false, 'options' => $YES_NO],
    ['section' => '14. Settlement / Resolution', 'name' => 'settlement_date',       'label' => 'Date',                  'type' => 'date',     'required' => false],
    ['section' => '14. Settlement / Resolution', 'name' => 'settlement_filed',      'label' => 'Settlement Agreement / Order Filed', 'type' => 'radio', 'required' => false, 'options' => $YES_NO],

    // ============ Section 15 — Litigation Workflow Tracker (TABLE, 17 pre-seeded rows) ============
    ['section' => '15. Litigation Workflow Tracker', 'name' => 'workflow_tracker', 'label' => 'Workflow Tracker', 'type' => 'repeater', 'required' => false, 'options' => $WORKFLOW_TABLE],

    // ============ Section 16 — Deadline & Compliance Monitor (TABLE, 7 pre-seeded rows) ============
    ['section' => '16. Deadline & Compliance Monitor', 'name' => 'deadline_monitor',        'label' => 'Deadline Monitor',                  'type' => 'repeater', 'required' => false, 'options' => $DEADLINE_TABLE],
    ['section' => '16. Deadline & Compliance Monitor', 'name' => 'all_deadlines_complied',  'label' => 'All Applicable Deadlines Complied With', 'type' => 'radio', 'required' => false, 'options' => $YES_NO],
    ['section' => '16. Deadline & Compliance Monitor', 'name' => 'non_compliance_reason',   'label' => 'If No, explain',                     'type' => 'textarea', 'required' => false],

    // ============ Section 17 — Matter Closure ============
    ['section' => '17. Matter Closure', 'name' => 'final_outcome',        'label' => 'Final Outcome',                    'type' => 'select', 'required' => true,  'options' => $FINAL_OUTCOMES],
    ['section' => '17. Matter Closure', 'name' => 'final_outcome_attachment', 'label' => 'Attachment',                   'type' => 'file',   'required' => false, 'allow_attachment' => true],
    ['section' => '17. Matter Closure', 'name' => 'final_outcome_comment',    'label' => 'Comment',                      'type' => 'textarea','required' => false],
    ['section' => '17. Matter Closure', 'name' => 'final_outcome_other',  'label' => 'If Other, specify',                'type' => 'text',   'required' => false],
    ['section' => '17. Matter Closure', 'name' => 'date_concluded',       'label' => 'Date Matter Concluded',            'type' => 'date',   'required' => true],
    ['section' => '17. Matter Closure', 'name' => 'final_order_obtained', 'label' => 'Final Order / Judgment Obtained',  'type' => 'radio',  'required' => false, 'options' => $YES_NO],
    ['section' => '17. Matter Closure', 'name' => 'final_order_filed',    'label' => 'Final Order / Judgment Filed',     'type' => 'radio',  'required' => false, 'options' => $YES_NO],
    ['section' => '17. Matter Closure', 'name' => 'client_notified',      'label' => 'Client / Relevant Department Notified', 'type' => 'radio', 'required' => false, 'options' => $YES_NO],
    ['section' => '17. Matter Closure', 'name' => 'date_notified',        'label' => 'Date Notified',                    'type' => 'date',   'required' => false],

    // ============ Section 18 — File Closure Checklist ============
    ['section' => '18. File Closure Checklist', 'name' => 'closure_checklist',   'label' => 'File Closure Checklist', 'type' => 'checkbox_group', 'required' => false, 'options' => $CLOSURE_CHECKLIST],
    ['section' => '18. File Closure Checklist', 'name' => 'closed_by',           'label' => 'Closed By',              'type' => 'text',       'required' => true],
    ['section' => '18. File Closure Checklist', 'name' => 'closure_date',        'label' => 'Date',                   'type' => 'date',       'required' => true],
    ['section' => '18. File Closure Checklist', 'name' => 'head_legal_approval', 'label' => 'Head Legal Services Approval (Name)', 'type' => 'text', 'required' => true],
    ['section' => '18. File Closure Checklist', 'name' => 'head_legal_signature','label' => 'Head Legal Signature (draw)', 'type' => 'signature', 'required' => false],
    ['section' => '18. File Closure Checklist', 'name' => 'head_legal_sig_file', 'label' => 'Head Legal Signature (upload)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => '18. File Closure Checklist', 'name' => 'signature_date',     'label' => 'Signature Date',         'type' => 'date',       'required' => false],
];

/* -------------------------------------------------------------------
 |  Persist
 |  ------------------------------------------------------------------- */
DB::transaction(function () use ($fields) {

    // Parent: Legal Department. Create it if missing so this works on a
    // freshly-seeded environment (test/prod) where it wasn't pre-populated.
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

    // Child
    $child = ServiceChild::updateOrCreate(
        ['service_parent_id' => $parent->id, 'service_name' => 'Litigation Matter Intake'],
        ['model' => 'Litigation Matter Intake', 'type' => 'new_acquisition']
    );
    echo "  Child  : #{$child->id}  {$child->service_name}\n";

    // Template
    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'Litigation Matter Intake Form',
            'description' => 'Legal Department — end-to-end litigation matter intake, tracking, and closure.',
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
    echo "     ↳ Inserted " . count($fields) . " fields across " . count(array_unique(array_column($fields, 'section'))) . " sections.\n";
});

echo "\nDone.\n";
