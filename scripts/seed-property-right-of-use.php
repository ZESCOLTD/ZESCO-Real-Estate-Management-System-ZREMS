<?php
/**
 * Seed the Property Department child service "Right of Use"
 * + its full LAMS Right of Use form template.
 *
 * Run:  php scripts/seed-property-right-of-use.php
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
$YES_NO = ['Yes', 'No'];
$YES_NO_NA = ['Yes', 'No', 'N/A'];

$APP_TYPE = ['New Application', 'Renewal'];

$NATURE = [
    'Use of Excess/Spare Capacity',
    'Access to Fibre Infrastructure',
    'Installation of Fibre-Related Equipment',
    'Maintenance/Repair Access',
    'Other',
];

$CONSIDERATION = [
    'Annual Fee', 'One-Off Fee', 'Periodic Fee', 'Investment', 'Revenue/Profit Share', 'Other',
];

$VALUATION_REQ = ['New Valuation', 'Renewal / Review'];

$MGR_PROPERTY_RECO = ['Proceed to Draft Right of Use Agreement', 'Revert to Transmission for Further Clarification'];

$SUBMISSION_TYPE = ['New Agreement', 'Renewal'];

$MGR_PROPERTY_DECISION = ['Approved', 'Amend and Resubmit'];

$SM_CC_DECISION = ['Submit to Lessee for Review', 'Amend and Submit to Lessee for Review'];

$LESSEE_REVIEW_STATE = ['No Amendments Proposed', 'Amendments Proposed'];

$COMPSEC_DECISION = ['Recommended for Execution', 'Amend and Resubmit'];

$MD_DECISION = ['Executed', 'Further Action Required'];

$WITNESS_DECISION = ['Submit to the Lessee for Execution', 'Further Action Required'];

/* -------------------------------------------------------------------
 |  Table field definitions
 |  ------------------------------------------------------------------- */

// A2. Transmission technical assessment table
$TECH_ASSESSMENT_TABLE = [
    ['name' => 'criterion', 'label' => 'Assessment Criterion', 'type' => 'text'],
    ['name' => 'response',  'label' => 'Response',             'type' => 'select', 'options' => $YES_NO],
    ['name' => 'remarks',   'label' => 'Remarks',              'type' => 'text'],
    'default_rows' => [
        ['criterion' => 'Requested fibre/capacity is available',                  'response' => 'No', 'remarks' => ''],
        ['criterion' => 'Proposed use is technically feasible',                    'response' => 'No', 'remarks' => ''],
        ['criterion' => 'ZESCO network performance will be affected',              'response' => 'No', 'remarks' => ''],
        ['criterion' => 'Existing fibre infrastructure will be affected',          'response' => 'No', 'remarks' => ''],
        ['criterion' => 'Additional equipment is required',                        'response' => 'No', 'remarks' => ''],
        ['criterion' => 'Maintenance implications have been identified',           'response' => 'No', 'remarks' => ''],
        ['criterion' => 'Network security implications have been identified',      'response' => 'No', 'remarks' => ''],
        ['criterion' => 'Future ZESCO capacity requirements have been considered', 'response' => 'No', 'remarks' => ''],
    ],
];

// B1. Manager - Property application review checklist
$MGR_PROPERTY_REVIEW_TABLE = [
    ['name' => 'requirement', 'label' => 'Requirement', 'type' => 'text'],
    ['name' => 'response',    'label' => 'Response',    'type' => 'select', 'options' => $YES_NO],
    ['name' => 'remarks',     'label' => 'Remarks',     'type' => 'text'],
    'default_rows' => [
        ['requirement' => 'Parties identified',                          'response' => 'No', 'remarks' => ''],
        ['requirement' => 'Commencement date provided',                  'response' => 'No', 'remarks' => ''],
        ['requirement' => 'Term indicated',                              'response' => 'No', 'remarks' => ''],
        ['requirement' => 'Valuation / asset assessment conducted',      'response' => 'No', 'remarks' => ''],
        ['requirement' => 'Nature of right clearly defined',             'response' => 'No', 'remarks' => ''],
        ['requirement' => 'Capacity clearly defined',                    'response' => 'No', 'remarks' => ''],
        ['requirement' => 'Type of consideration defined',               'response' => 'No', 'remarks' => ''],
        ['requirement' => 'Consideration / applicable fee determined',   'response' => 'No', 'remarks' => ''],
        ['requirement' => 'Transmission technical assessment completed', 'response' => 'No', 'remarks' => ''],
    ],
];

/* -------------------------------------------------------------------
 |  Sections
 |  ------------------------------------------------------------------- */
$SA1 = 'A1. Request for Right of Use by Transmission';
$SA2 = 'A2. Transmission Technical Assessment';
$SA3 = 'A3. Valuation / Asset Assessment';

$SB1 = 'B1. Assessment by Manager – Property';
$SB2 = 'B2. Preparation and Submission of Draft Agreement';
$SB3 = 'B3. Review of Draft Agreement by Manager – Property';
$SB4 = 'B4. Review of Draft Agreement by Senior Manager – Commercial Contracts';
$SB5 = 'B5. Submission of Reviewed Draft Agreement by Property Department';
$SB6 = 'B6. Submission of Reviewed Agreement to Company Secretary';
$SB7 = 'B7. Review of Agreement by Company Secretary';

$SC1 = 'C1. Execution of Agreement by Managing Director';
$SC2 = 'C2. Witnessing of Agreement by Company Secretary';

$SE1 = 'E1. Registration / Recording of Right of Use Agreement';

$fields = [
    // ===== SECTION A =====
    // A1. Request for Right of Use by Transmission
    ['section' => $SA1, 'name' => 'reference_no',           'label' => 'Reference No.', 'type' => 'text', 'required' => true],
    ['section' => $SA1, 'name' => 'application_date',       'label' => 'Date', 'type' => 'date', 'required' => true],
    ['section' => $SA1, 'name' => 'application_type',       'label' => 'Application Type', 'type' => 'select', 'options' => $APP_TYPE, 'required' => true],
    ['section' => $SA1, 'name' => 'lessee',                 'label' => 'Lessee', 'type' => 'text', 'required' => true],
    ['section' => $SA1, 'name' => 'request_description',    'label' => 'Brief Description of the Request', 'type' => 'textarea', 'required' => true],
    ['section' => $SA1, 'name' => 'capacity_requested',     'label' => 'Capacity Requested', 'type' => 'textarea', 'required' => false],
    ['section' => $SA1, 'name' => 'nature_of_right',        'label' => 'Nature of Right Requested', 'type' => 'checkbox', 'options' => $NATURE, 'required' => false],
    ['section' => $SA1, 'name' => 'nature_of_right_other',  'label' => 'If Other nature, specify', 'type' => 'text', 'required' => false],
    ['section' => $SA1, 'name' => 'commencement_date',      'label' => 'Proposed Commencement Date', 'type' => 'date', 'required' => false],
    ['section' => $SA1, 'name' => 'proposed_term',          'label' => 'Proposed Term', 'type' => 'text', 'required' => false],
    ['section' => $SA1, 'name' => 'consideration_type',     'label' => 'Proposed Type of Consideration', 'type' => 'checkbox', 'options' => $CONSIDERATION, 'required' => false],
    ['section' => $SA1, 'name' => 'consideration_other',    'label' => 'If Other consideration, specify', 'type' => 'text', 'required' => false],
    ['section' => $SA1, 'name' => 'application_attachment', 'label' => 'Attach Application Documents', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // A2. Technical Assessment
    ['section' => $SA2, 'name' => 'tech_assessment_table', 'label' => 'Transmission Technical Assessment', 'type' => 'table', 'options' => $TECH_ASSESSMENT_TABLE, 'required' => false],
    ['section' => $SA2, 'name' => 'tech_recommendation',   'label' => 'Recommendation', 'type' => 'textarea', 'required' => false],

    // A3. Valuation
    ['section' => $SA3, 'name' => 'fibre_asset',            'label' => 'Relevant Fibre Network Asset(s)', 'type' => 'textarea', 'required' => false],
    ['section' => $SA3, 'name' => 'valuation_required',     'label' => 'Valuation Required', 'type' => 'select', 'options' => $VALUATION_REQ, 'required' => false],
    ['section' => $SA3, 'name' => 'value_of_infrastructure','label' => 'Value of Underlying Fibre Infrastructure (ZMW)', 'type' => 'number', 'required' => false],
    ['section' => $SA3, 'name' => 'value_of_right_of_use',  'label' => 'Value of Right of Use (ZMW)', 'type' => 'number', 'required' => false],
    ['section' => $SA3, 'name' => 'rate_applied',           'label' => 'Percentage / Rate Applied (%)', 'type' => 'number', 'required' => false],
    ['section' => $SA3, 'name' => 'valuation_method',       'label' => 'Valuation / Assessment Method', 'type' => 'text', 'required' => false],
    ['section' => $SA3, 'name' => 'valuation_date',         'label' => 'Valuation / Assessment Date', 'type' => 'date', 'required' => false],
    ['section' => $SA3, 'name' => 'valuer_recommendation',  'label' => "Valuer's / Assessor's Recommendation", 'type' => 'textarea', 'required' => false],
    ['section' => $SA3, 'name' => 'valuation_comments',     'label' => 'Additional Comments', 'type' => 'textarea', 'required' => false],
    ['section' => $SA3, 'name' => 'valuation_report',       'label' => 'Attach Valuation Report', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ===== SECTION B =====
    // B1. Assessment by Manager – Property
    ['section' => $SB1, 'name' => 'mgr_property_review_table', 'label' => 'Application Review Checklist', 'type' => 'table', 'options' => $MGR_PROPERTY_REVIEW_TABLE, 'required' => false],
    ['section' => $SB1, 'name' => 'mgr_property_recommendation','label' => 'Recommendation', 'type' => 'select', 'options' => $MGR_PROPERTY_RECO, 'required' => false],
    ['section' => $SB1, 'name' => 'mgr_property_comments',     'label' => 'Comments', 'type' => 'textarea', 'required' => false],
    ['section' => $SB1, 'name' => 'assigned_property_officer', 'label' => 'Assigned Property Officer', 'type' => 'text', 'required' => false],
    ['section' => $SB1, 'name' => 'mgr_property_signature',    'label' => 'Manager – Property Signature', 'type' => 'signature', 'required' => false],
    ['section' => $SB1, 'name' => 'mgr_property_date',         'label' => 'Date', 'type' => 'date', 'required' => false],

    // B2. Preparation of Draft Agreement
    ['section' => $SB2, 'name' => 'draft_responsible_officer', 'label' => 'Responsible Officer (Property Officer)', 'type' => 'text', 'required' => false],
    ['section' => $SB2, 'name' => 'draft_submission_type',     'label' => 'Submission Type', 'type' => 'select', 'options' => $SUBMISSION_TYPE, 'required' => false],
    ['section' => $SB2, 'name' => 'draft_agreement_file',      'label' => 'Attach Draft Right of Use Agreement', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $SB2, 'name' => 'draft_comments',            'label' => 'Comments', 'type' => 'textarea', 'required' => false],
    ['section' => $SB2, 'name' => 'draft_signature',           'label' => 'Property Officer Signature', 'type' => 'signature', 'required' => false],
    ['section' => $SB2, 'name' => 'draft_date',                'label' => 'Date', 'type' => 'date', 'required' => false],

    // B3. Manager – Property review of draft
    ['section' => $SB3, 'name' => 'mgr_property_draft_decision', 'label' => 'Decision', 'type' => 'select', 'options' => $MGR_PROPERTY_DECISION, 'required' => false],
    ['section' => $SB3, 'name' => 'mgr_property_draft_comments', 'label' => 'Comments', 'type' => 'textarea', 'required' => false],
    ['section' => $SB3, 'name' => 'mgr_property_amended_draft',  'label' => 'Attach Amended Draft (if applicable)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $SB3, 'name' => 'mgr_property_draft_signature','label' => 'Manager – Property Signature', 'type' => 'signature', 'required' => false],
    ['section' => $SB3, 'name' => 'mgr_property_draft_date',     'label' => 'Date', 'type' => 'date', 'required' => false],

    // B4. Senior Manager – Commercial Contracts
    ['section' => $SB4, 'name' => 'sm_cc_decision',       'label' => 'Decision', 'type' => 'select', 'options' => $SM_CC_DECISION, 'required' => false],
    ['section' => $SB4, 'name' => 'sm_cc_comments',       'label' => 'Comments', 'type' => 'textarea', 'required' => false],
    ['section' => $SB4, 'name' => 'sm_cc_amended_draft',  'label' => 'Attach Amended Draft (if applicable)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $SB4, 'name' => 'sm_cc_signature',      'label' => 'Senior Manager – CC Signature', 'type' => 'signature', 'required' => false],
    ['section' => $SB4, 'name' => 'sm_cc_date',           'label' => 'Date', 'type' => 'date', 'required' => false],

    // B5. Lessee review outcome
    ['section' => $SB5, 'name' => 'lessee_review_state',      'label' => 'Lessee Review Outcome', 'type' => 'select', 'options' => $LESSEE_REVIEW_STATE, 'required' => false],
    ['section' => $SB5, 'name' => 'lessee_reviewed_draft',    'label' => 'Attach Draft Reviewed by Lessee', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $SB5, 'name' => 'lessee_review_comments',   'label' => 'Comments', 'type' => 'textarea', 'required' => false],

    // B6. Submission to Company Secretary
    ['section' => $SB6, 'name' => 'submission_to_compsec_comments', 'label' => 'Comments', 'type' => 'textarea', 'required' => false],
    ['section' => $SB6, 'name' => 'submission_to_compsec_file',     'label' => 'Attach Finalized Agreement', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $SB6, 'name' => 'submission_to_compsec_date',     'label' => 'Date of Submission', 'type' => 'date', 'required' => false],

    // B7. Company Secretary review
    ['section' => $SB7, 'name' => 'compsec_decision',   'label' => 'Decision', 'type' => 'select', 'options' => $COMPSEC_DECISION, 'required' => false],
    ['section' => $SB7, 'name' => 'compsec_comments',   'label' => 'Comments', 'type' => 'textarea', 'required' => false],
    ['section' => $SB7, 'name' => 'compsec_signature',  'label' => 'Company Secretary Signature', 'type' => 'signature', 'required' => false],
    ['section' => $SB7, 'name' => 'compsec_date',       'label' => 'Date', 'type' => 'date', 'required' => false],

    // ===== SECTION C =====
    // C1. MD Execution
    ['section' => $SC1, 'name' => 'md_decision',         'label' => 'Decision', 'type' => 'select', 'options' => $MD_DECISION, 'required' => false],
    ['section' => $SC1, 'name' => 'md_comments',         'label' => 'Comments', 'type' => 'textarea', 'required' => false],
    ['section' => $SC1, 'name' => 'md_execution_date',   'label' => 'Date of Execution', 'type' => 'date', 'required' => false],
    ['section' => $SC1, 'name' => 'md_signature',        'label' => 'Managing Director Signature', 'type' => 'signature', 'required' => false],

    // C2. Witnessing
    ['section' => $SC2, 'name' => 'witness_decision',    'label' => 'Decision', 'type' => 'select', 'options' => $WITNESS_DECISION, 'required' => false],
    ['section' => $SC2, 'name' => 'witness_comments',    'label' => 'Comments', 'type' => 'textarea', 'required' => false],
    ['section' => $SC2, 'name' => 'witness_signature',   'label' => 'Company Secretary Witness Signature', 'type' => 'signature', 'required' => false],
    ['section' => $SC2, 'name' => 'witness_date',        'label' => 'Date', 'type' => 'date', 'required' => false],

    // ===== SECTION E =====
    // E1. Registration & recording
    ['section' => $SE1, 'name' => 'executed_agreement_file', 'label' => 'Upload Executed Agreement', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],
    ['section' => $SE1, 'name' => 'registration_reference',  'label' => 'Registration Reference No.', 'type' => 'text', 'required' => false],
    ['section' => $SE1, 'name' => 'registration_date',       'label' => 'Date of Registration', 'type' => 'date', 'required' => false],
    ['section' => $SE1, 'name' => 'registered_by',           'label' => 'Registered By (Property Department)', 'type' => 'text', 'required' => false],
    ['section' => $SE1, 'name' => 'registration_remarks',    'label' => 'Remarks', 'type' => 'textarea', 'required' => false],
];

/* -------------------------------------------------------------------
 |  Persist
 |  ------------------------------------------------------------------- */
DB::transaction(function () use ($fields) {

    $parent = ServiceParent::where('department_code', 'property_department')
        ->orWhere('department_name', 'Property Department')
        ->first();

    if (!$parent) {
        echo "  ERROR: Property Department parent not found.\n";
        return;
    }
    echo "  Parent : #{$parent->id}  {$parent->department_name} / {$parent->service_name}\n";

    $child = ServiceChild::updateOrCreate(
        ['service_parent_id' => $parent->id, 'service_name' => 'Right of Use'],
        ['model' => 'Right of Use', 'type' => 'asset_management', 'is_active' => 1]
    );
    echo "  Child  : #{$child->id}  {$child->service_name}\n";

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'LAMS — Right of Use Form',
            'description' => 'Property Department — Application, technical & valuation assessment, draft agreement review, execution, witnessing and registration of Right of Use agreements.',
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
