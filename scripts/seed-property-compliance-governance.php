<?php
/**
 * Seed the Property Department child service "Compliance, Risk & Governance"
 * + its full LAMS Compliance, Risk & Governance form template.
 *
 * Run:  php scripts/seed-property-compliance-governance.php
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
$LOW_MED_HIGH = ['Low', 'Medium', 'High'];
$OVERALL_RATING = ['Low', 'Moderate', 'High', 'Critical'];

$TRANSACTION_TYPE = [
    'Acquisition', 'Conversion', 'Lease', 'Subdivision',
    'Transfer', 'Easement', 'Wayleave', 'Development', 'Other',
];

$STATUS = ['Open', 'Under Review', 'Compliant', 'Non-Compliant', 'Closed'];

$COMPLIANT_STATE = ['Compliant', 'Non-Compliant', 'N/A'];
$YES_NO_NA = ['Yes', 'No', 'N/A'];

$POLICY_PROCEDURAL = [
    'Correct application submitted',
    'Required supporting documents attached',
    'Correct approval authority identified',
    'Required consultations undertaken',
    'Required inspections conducted',
    'Required professional reports submitted',
    'Required public notices/consultations undertaken',
    'Correct fees paid',
    'Internal procedures followed',
    'Delegated authority verified',
];

$FRAUD_INTEGRITY = [
    'Identity of applicant verified',
    'Ownership documents authenticated',
    'Supporting documents verified',
    'Duplicate application checked',
    'Duplicate title/parcel checked',
    'Payment verified',
    'Officer involvement reviewed',
    'Suspicious activity identified',
    'Possible fraudulent documentation identified',
    'Matter referred for investigation',
];

$COI_ACTION = ['Officer recused', 'Matter reassigned', 'Management approval obtained', 'Other'];

$OUTCOME = [
    'Fully Compliant',
    'Compliant Subject to Conditions',
    'Partially Compliant',
    'Non-Compliant',
    'Further Investigation Required',
    'Escalation Required',
];

$APPROVE_REJECT = ['Approve', 'Reject'];
$CLEAR_NOT_CLEAR = ['Clear', 'Not Clear'];

$CORRECTIVE_STATE = ['Yes', 'No', 'Not Applicable'];

/* -------------------------------------------------------------------
 |  Table field definitions
 |  ------------------------------------------------------------------- */

// 2A. Legal Compliance table
$LEGAL_COMPLIANCE_TABLE = [
    ['name' => 'requirement', 'label' => 'Requirement', 'type' => 'text'],
    ['name' => 'status',      'label' => 'Status',      'type' => 'select', 'options' => $COMPLIANT_STATE],
    ['name' => 'remarks',     'label' => 'Remarks / Evidence', 'type' => 'text'],
    'default_rows' => [
        ['requirement' => 'Applicable land legislation verified',    'status' => 'N/A', 'remarks' => ''],
        ['requirement' => 'Planning legislation complied with',      'status' => 'N/A', 'remarks' => ''],
        ['requirement' => 'Environmental requirements complied with','status' => 'N/A', 'remarks' => ''],
        ['requirement' => 'Survey requirements complied with',       'status' => 'N/A', 'remarks' => ''],
        ['requirement' => 'Land ownership verified',                 'status' => 'N/A', 'remarks' => ''],
        ['requirement' => 'Consent requirements satisfied',          'status' => 'N/A', 'remarks' => ''],
        ['requirement' => 'Statutory approvals obtained',            'status' => 'N/A', 'remarks' => ''],
        ['requirement' => 'Fees/taxes/charges verified',             'status' => 'N/A', 'remarks' => ''],
        ['requirement' => 'Existing encumbrances checked',           'status' => 'N/A', 'remarks' => ''],
        ['requirement' => 'Court orders/disputes checked',           'status' => 'N/A', 'remarks' => ''],
        ['requirement' => 'Other legal requirements',                'status' => 'N/A', 'remarks' => ''],
    ],
];

// 3. Risk Identification table
$RISK_ID_TABLE = [
    ['name' => 'category',    'label' => 'Risk Category',   'type' => 'text'],
    ['name' => 'risk',        'label' => 'Risk Identified', 'type' => 'text'],
    ['name' => 'likelihood',  'label' => 'Likelihood',      'type' => 'select', 'options' => $LOW_MED_HIGH],
    ['name' => 'impact',      'label' => 'Impact',          'type' => 'select', 'options' => $LOW_MED_HIGH],
    ['name' => 'risk_level',  'label' => 'Risk Level',      'type' => 'select', 'options' => $OVERALL_RATING],
    'default_rows' => [
        ['category' => 'Legal',           'risk' => '', 'likelihood' => 'Low', 'impact' => 'Low', 'risk_level' => 'Low'],
        ['category' => 'Financial',       'risk' => '', 'likelihood' => 'Low', 'impact' => 'Low', 'risk_level' => 'Low'],
        ['category' => 'Operational',     'risk' => '', 'likelihood' => 'Low', 'impact' => 'Low', 'risk_level' => 'Low'],
        ['category' => 'Land/Property',   'risk' => '', 'likelihood' => 'Low', 'impact' => 'Low', 'risk_level' => 'Low'],
        ['category' => 'Environmental',   'risk' => '', 'likelihood' => 'Low', 'impact' => 'Low', 'risk_level' => 'Low'],
        ['category' => 'Planning',        'risk' => '', 'likelihood' => 'Low', 'impact' => 'Low', 'risk_level' => 'Low'],
        ['category' => 'Survey/Cadastral','risk' => '', 'likelihood' => 'Low', 'impact' => 'Low', 'risk_level' => 'Low'],
        ['category' => 'Fraud/Corruption','risk' => '', 'likelihood' => 'Low', 'impact' => 'Low', 'risk_level' => 'Low'],
        ['category' => 'Reputational',    'risk' => '', 'likelihood' => 'Low', 'impact' => 'Low', 'risk_level' => 'Low'],
        ['category' => 'Data/Information','risk' => '', 'likelihood' => 'Low', 'impact' => 'Low', 'risk_level' => 'Low'],
        ['category' => 'Governance',      'risk' => '', 'likelihood' => 'Low', 'impact' => 'Low', 'risk_level' => 'Low'],
        ['category' => 'Other',           'risk' => '', 'likelihood' => 'Low', 'impact' => 'Low', 'risk_level' => 'Low'],
    ],
];

// 5. Mitigation table
$MITIGATION_TABLE = [
    ['name' => 'risk',                'label' => 'Risk',                'type' => 'text'],
    ['name' => 'existing_control',    'label' => 'Existing Control',    'type' => 'text'],
    ['name' => 'proposed_mitigation', 'label' => 'Proposed Mitigation', 'type' => 'text'],
    ['name' => 'responsible_officer', 'label' => 'Responsible Officer', 'type' => 'text'],
    ['name' => 'due_date',            'label' => 'Due Date',            'type' => 'date'],
    ['name' => 'status',              'label' => 'Status',              'type' => 'select',
        'options' => ['Open', 'In Progress', 'Completed', 'Overdue']],
    'default_rows' => [
        ['risk' => '', 'existing_control' => '', 'proposed_mitigation' => '', 'responsible_officer' => '', 'due_date' => '', 'status' => 'Open'],
        ['risk' => '', 'existing_control' => '', 'proposed_mitigation' => '', 'responsible_officer' => '', 'due_date' => '', 'status' => 'Open'],
        ['risk' => '', 'existing_control' => '', 'proposed_mitigation' => '', 'responsible_officer' => '', 'due_date' => '', 'status' => 'Open'],
    ],
];

// 6. Governance check table
$GOVERNANCE_TABLE = [
    ['name' => 'requirement', 'label' => 'Governance Requirement', 'type' => 'text'],
    ['name' => 'status',      'label' => 'Status',                 'type' => 'select', 'options' => $YES_NO_NA],
    ['name' => 'remarks',     'label' => 'Remarks',                'type' => 'text'],
    'default_rows' => [
        ['requirement' => 'Correct authority identified',          'status' => 'N/A', 'remarks' => ''],
        ['requirement' => 'Officer has delegated authority',       'status' => 'N/A', 'remarks' => ''],
        ['requirement' => 'Conflict of interest declared',         'status' => 'N/A', 'remarks' => ''],
        ['requirement' => 'Separation of duties maintained',       'status' => 'N/A', 'remarks' => ''],
        ['requirement' => 'Required approvals obtained',           'status' => 'N/A', 'remarks' => ''],
        ['requirement' => 'Decision properly documented',          'status' => 'N/A', 'remarks' => ''],
        ['requirement' => 'Audit trail maintained in LAMS',        'status' => 'N/A', 'remarks' => ''],
        ['requirement' => 'Records properly secured',              'status' => 'N/A', 'remarks' => ''],
        ['requirement' => 'Decision communicated to stakeholders', 'status' => 'N/A', 'remarks' => ''],
    ],
];

// 11. Approval & escalation table
$APPROVAL_TABLE = [
    ['name' => 'level',    'label' => 'Level',             'type' => 'text'],
    ['name' => 'officer',  'label' => 'Officer/Authority', 'type' => 'text'],
    ['name' => 'decision', 'label' => 'Decision',          'type' => 'text'],
    ['name' => 'date',     'label' => 'Date',              'type' => 'date'],
    ['name' => 'signature','label' => 'Signature',         'type' => 'signature'],
    'default_rows' => [
        ['level' => 'Compliance Officer',    'officer' => '', 'decision' => '', 'date' => '', 'signature' => ''],
        ['level' => 'Risk Officer',          'officer' => '', 'decision' => '', 'date' => '', 'signature' => ''],
        ['level' => 'Legal',                 'officer' => '', 'decision' => '', 'date' => '', 'signature' => ''],
        ['level' => 'Technical/Planning',    'officer' => '', 'decision' => '', 'date' => '', 'signature' => ''],
        ['level' => 'Management',            'officer' => '', 'decision' => '', 'date' => '', 'signature' => ''],
        ['level' => 'Final Authority',       'officer' => '', 'decision' => '', 'date' => '', 'signature' => ''],
    ],
];

/* -------------------------------------------------------------------
 |  Sections
 |  ------------------------------------------------------------------- */
$S1  = '1. General Information';
$S2  = '2. Compliance Assessment';
$S3  = '3. Risk Identification';
$S4  = '4. Risk Rating';
$S5  = '5. Risk Mitigation / Control Measures';
$S6  = '6. Governance Check';
$S7  = '7. Conflict of Interest Declaration';
$S8  = '8. Fraud & Integrity Check';
$S9  = '9. Audit & Document Control';
$S10 = '10. Compliance Decision';
$S11 = '11. Approval & Escalation';
$S12 = '12. Closure';

$fields = [
    // ---- 1. General Information ----
    ['section' => $S1, 'name' => 'compliance_reference_no', 'label' => 'Compliance/Risk Reference No.', 'type' => 'text', 'required' => true],
    ['section' => $S1, 'name' => 'lams_transaction_no',     'label' => 'LAMS Application/Transaction No.', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'date_submitted',          'label' => 'Date Submitted', 'type' => 'date', 'required' => true],
    ['section' => $S1, 'name' => 'department_unit',         'label' => 'Department/Unit', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'responsible_officer',     'label' => 'Responsible Officer', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'applicant_institution',   'label' => 'Applicant/Institution', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'property_stand_no',       'label' => 'Property/Stand No.', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'location',                'label' => 'Location', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'transaction_type',        'label' => 'Transaction Type', 'type' => 'checkbox', 'options' => $TRANSACTION_TYPE, 'required' => false],
    ['section' => $S1, 'name' => 'transaction_type_other',  'label' => 'If Other transaction, specify', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'risk_assessment_date',    'label' => 'Risk Assessment Date', 'type' => 'date', 'required' => false],
    ['section' => $S1, 'name' => 'status',                  'label' => 'Status', 'type' => 'select', 'options' => $STATUS, 'required' => true],

    // ---- 2. Compliance Assessment ----
    ['section' => $S2, 'name' => 'legal_compliance_table',  'label' => 'A. Legal Compliance', 'type' => 'table', 'options' => $LEGAL_COMPLIANCE_TABLE, 'required' => false],
    ['section' => $S2, 'name' => 'policy_procedural',       'label' => 'B. Policy & Procedural Compliance', 'type' => 'checkbox', 'options' => $POLICY_PROCEDURAL, 'required' => false],

    // ---- 3. Risk Identification ----
    ['section' => $S3, 'name' => 'risk_identification_table', 'label' => 'Risk Identification Register', 'type' => 'table', 'options' => $RISK_ID_TABLE, 'required' => false],

    // ---- 4. Risk Rating ----
    ['section' => $S4, 'name' => 'risk_score',           'label' => 'Risk Score (Likelihood × Impact)', 'type' => 'number', 'required' => false],
    ['section' => $S4, 'name' => 'overall_risk_rating',  'label' => 'Overall Risk Rating', 'type' => 'select', 'options' => $OVERALL_RATING, 'required' => false],
    ['section' => $S4, 'name' => 'risk_description',     'label' => 'Risk Description', 'type' => 'textarea', 'required' => false],

    // ---- 5. Mitigation ----
    ['section' => $S5, 'name' => 'mitigation_table', 'label' => 'Risk Mitigation Register', 'type' => 'table', 'options' => $MITIGATION_TABLE, 'required' => false],

    // ---- 6. Governance Check ----
    ['section' => $S6, 'name' => 'governance_table', 'label' => 'Decision-Making Controls', 'type' => 'table', 'options' => $GOVERNANCE_TABLE, 'required' => false],

    // ---- 7. Conflict of Interest ----
    ['section' => $S7, 'name' => 'coi_present',       'label' => 'Does any officer involved have a potential or actual conflict of interest?', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S7, 'name' => 'coi_details',       'label' => 'If yes, provide details', 'type' => 'textarea', 'required' => false],
    ['section' => $S7, 'name' => 'coi_action',        'label' => 'Action Taken', 'type' => 'checkbox', 'options' => $COI_ACTION, 'required' => false],
    ['section' => $S7, 'name' => 'coi_action_other',  'label' => 'If Other action, specify', 'type' => 'text', 'required' => false],

    // ---- 8. Fraud & Integrity ----
    ['section' => $S8, 'name' => 'fraud_integrity_checks', 'label' => 'Fraud & Integrity Checks', 'type' => 'checkbox', 'options' => $FRAUD_INTEGRITY, 'required' => false],
    ['section' => $S8, 'name' => 'fraud_concern',          'label' => 'Fraud/Integrity Concern', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S8, 'name' => 'fraud_concern_details',  'label' => 'Concern Details', 'type' => 'textarea', 'required' => false],

    // ---- 9. Audit & Document Control ----
    ['section' => $S9, 'name' => 'documents_verified',        'label' => 'Documents Verified', 'type' => 'textarea', 'required' => false],
    ['section' => $S9, 'name' => 'verification_officer',      'label' => 'Verification Officer', 'type' => 'text', 'required' => false],
    ['section' => $S9, 'name' => 'verification_date',         'label' => 'Verification Date', 'type' => 'date', 'required' => false],
    ['section' => $S9, 'name' => 'digital_records_available', 'label' => 'Digital Records Available', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S9, 'name' => 'originals_sighted',         'label' => 'Original Documents Sighted', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S9, 'name' => 'gis_record_checked',        'label' => 'GIS/Cadastral Record Checked', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S9, 'name' => 'audit_trail_complete',      'label' => 'Audit Trail Complete', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S9, 'name' => 'previous_tx_checked',       'label' => 'Previous Transactions Checked', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S9, 'name' => 'audit_attachments',         'label' => 'Attach Supporting Evidence', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 10. Compliance Decision ----
    ['section' => $S10, 'name' => 'assessment_outcome',     'label' => 'Assessment Outcome', 'type' => 'select', 'options' => $OUTCOME, 'required' => true],
    ['section' => $S10, 'name' => 'conditions_corrective',  'label' => 'Conditions / Corrective Actions', 'type' => 'textarea', 'required' => false],

    // ---- 11. Approval & Escalation ----
    ['section' => $S11, 'name' => 'approval_table', 'label' => 'Approval & Escalation Register', 'type' => 'table', 'options' => $APPROVAL_TABLE, 'required' => false],

    // ---- 12. Closure ----
    ['section' => $S12, 'name' => 'corrective_actions_completed', 'label' => 'Corrective actions completed', 'type' => 'select', 'options' => $CORRECTIVE_STATE, 'required' => false],
    ['section' => $S12, 'name' => 'outstanding_issues',           'label' => 'Outstanding Issues', 'type' => 'textarea', 'required' => false],
    ['section' => $S12, 'name' => 'final_risk_rating',            'label' => 'Final Risk Rating', 'type' => 'select', 'options' => $OVERALL_RATING, 'required' => false],
    ['section' => $S12, 'name' => 'case_closed',                  'label' => 'Case/Transaction Closed', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S12, 'name' => 'closure_date',                 'label' => 'Closure Date', 'type' => 'date', 'required' => false],
    ['section' => $S12, 'name' => 'authorised_by',                'label' => 'Authorised By', 'type' => 'text', 'required' => false],
    ['section' => $S12, 'name' => 'authorised_signature',         'label' => 'Authorised By — Signature', 'type' => 'signature', 'required' => false],
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
        ['service_parent_id' => $parent->id, 'service_name' => 'Compliance, Risk & Governance'],
        ['model' => 'Compliance, Risk & Governance', 'type' => 'asset_management', 'is_active' => 1]
    );
    echo "  Child  : #{$child->id}  {$child->service_name}\n";

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'LAMS — Compliance, Risk & Governance Form',
            'description' => 'Property Department — Compliance assessment, risk identification, governance, conflict of interest, fraud/integrity, audit and closure.',
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
