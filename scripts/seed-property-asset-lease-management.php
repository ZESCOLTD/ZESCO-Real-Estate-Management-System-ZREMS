<?php
/**
 * Seed the Property Department child service "Asset Lease Management"
 * + its full LAMS Asset Lease Management form template.
 *
 * Run:  php scripts/seed-property-asset-lease-management.php
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
$YES_NO    = ['Yes', 'No'];
$YES_NO_NA = ['Yes', 'No', 'N/A'];

$LEASE_TYPE     = ['New', 'Renewal', 'Variation', 'Assignment'];
$STATUS         = ['Draft', 'Submitted', 'Under Review', 'Approved', 'Active', 'Expired', 'Terminated'];
$ASSET_TYPE     = ['Land', 'Building', 'Vehicle', 'Equipment', 'Machinery', 'Other'];
$ASSET_STATUS   = ['Available', 'Leased', 'Partially Leased', 'Under Maintenance'];
$CONDITION      = ['Good', 'Fair', 'Poor'];
$MAINT_RESP     = ['Owner', 'Lessee', 'Shared'];
$PAY_FREQUENCY  = ['Monthly', 'Quarterly', 'Biannual', 'Annual'];
$AGREEMENT_TYPE = ['Lease', 'Tenancy', 'Licence', 'Hire Agreement'];
$DECISION       = ['Approved', 'Rejected', 'Pending'];
$RENT_STATE     = ['Up to Date', 'Arrears'];
$USE_COMPLIANCE = ['Compliant', 'Non-Compliant'];
$INSURANCE_ST   = ['Valid', 'Expired'];
$MAINT_STATE    = ['Up to Date', 'Outstanding'];
$LEASE_COMP     = ['Compliant', 'Breach'];
$ARREARS_STATE  = ['No Arrears', 'Arrears', 'Demand Notice Issued', 'Payment Plan', 'Legal Recovery'];
$RENEWAL_RECO   = ['Recommended', 'Not Recommended'];
$TERM_TYPE      = ['Expiry', 'Mutual Agreement', 'Breach', 'Other'];

$DOCUMENTS = [
    'Lease Application',
    'Title/Ownership Documents',
    'Asset Valuation Report',
    'Asset Inspection Report',
    'Lessee Identification/Registration Documents',
    'Lease Agreement',
    'Board/Management Approval',
    'Payment Receipts',
    'Insurance Documents',
    'Permits/Licences',
    'Renewal Documents',
    'Notices and Correspondence',
    'Breach/Default Records',
    'Termination Notice',
    'Handover Certificate',
    'Final Inspection Report',
];

/* -------------------------------------------------------------------
 |  Table field definitions
 |  ------------------------------------------------------------------- */
$APPROVAL_TABLE = [
    ['name' => 'stage',    'label' => 'Approval Stage',    'type' => 'text'],
    ['name' => 'officer',  'label' => 'Officer/Authority', 'type' => 'text'],
    ['name' => 'date',     'label' => 'Date',              'type' => 'date'],
    ['name' => 'decision', 'label' => 'Decision',          'type' => 'select', 'options' => $DECISION],
    ['name' => 'remarks',  'label' => 'Remarks',           'type' => 'text'],
    'default_rows' => array_map(fn($s) => ['stage' => $s, 'officer' => '', 'date' => '', 'decision' => 'Pending', 'remarks' => ''], [
        'Asset Management', 'Valuation', 'Finance', 'Legal', 'Management', 'Board/Committee',
    ]),
];

$MONITORING_TABLE = [
    ['name' => 'item',    'label' => 'Monitoring Item', 'type' => 'text'],
    ['name' => 'status',  'label' => 'Status',          'type' => 'text'],
    ['name' => 'remarks', 'label' => 'Remarks',         'type' => 'text'],
    'default_rows' => array_map(fn($it) => ['item' => $it, 'status' => '', 'remarks' => ''], [
        'Rent Payments', 'Asset Condition', 'Permitted Use Compliance', 'Insurance',
        'Licence/Permit', 'Maintenance', 'Lease Conditions', 'Inspection Conducted',
    ]),
];

$RENT_LEDGER_TABLE = [
    ['name' => 'period',       'label' => 'Period',       'type' => 'text'],
    ['name' => 'amount_due',   'label' => 'Amount Due',   'type' => 'number'],
    ['name' => 'amount_paid',  'label' => 'Amount Paid',  'type' => 'number'],
    ['name' => 'balance',      'label' => 'Balance',      'type' => 'number'],
    ['name' => 'payment_date', 'label' => 'Payment Date', 'type' => 'date'],
    ['name' => 'receipt_no',   'label' => 'Receipt No.',  'type' => 'text'],
    'default_rows' => [
        ['period' => '', 'amount_due' => 0, 'amount_paid' => 0, 'balance' => 0, 'payment_date' => '', 'receipt_no' => ''],
        ['period' => '', 'amount_due' => 0, 'amount_paid' => 0, 'balance' => 0, 'payment_date' => '', 'receipt_no' => ''],
        ['period' => '', 'amount_due' => 0, 'amount_paid' => 0, 'balance' => 0, 'payment_date' => '', 'receipt_no' => ''],
    ],
];

/* -------------------------------------------------------------------
 |  Sections
 |  ------------------------------------------------------------------- */
$S1  = '1. Lease Application / Transaction Details';
$S2  = '2. Asset Details';
$S3  = '3. Lessee Details';
$S4  = '4. Lease Terms';
$S5  = '5. Financial Management';
$S6  = '6. Lease Agreement';
$S7  = '7. Approvals';
$S8  = '8. Lease Monitoring';
$S9  = '9. Rent & Arrears Management';
$S10 = '10. Renewal / Expiry Management';
$S11 = '11. Breach / Default Management';
$S12 = '12. Termination / Closure';
$S13 = '13. Documents & Records';

$fields = [
    // ---- 1. Application ----
    ['section' => $S1, 'name' => 'lease_reference_no',      'label' => 'Lease Reference No.', 'type' => 'text', 'required' => true],
    ['section' => $S1, 'name' => 'application_no',          'label' => 'Application/Transaction No.', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'date_of_application',     'label' => 'Date of Application', 'type' => 'date', 'required' => true],
    ['section' => $S1, 'name' => 'lease_type',              'label' => 'Type of Lease', 'type' => 'select', 'options' => $LEASE_TYPE, 'required' => true],
    ['section' => $S1, 'name' => 'lease_purpose',           'label' => 'Lease Purpose', 'type' => 'textarea', 'required' => false],
    ['section' => $S1, 'name' => 'responsible_department',  'label' => 'Responsible Department/Unit', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'officer_handling',        'label' => 'Officer Handling', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'status',                  'label' => 'Status', 'type' => 'select', 'options' => $STATUS, 'required' => true],

    // ---- 2. Asset ----
    ['section' => $S2, 'name' => 'asset_id',                'label' => 'Asset ID/Number', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'asset_type',              'label' => 'Asset Type', 'type' => 'select', 'options' => $ASSET_TYPE, 'required' => false],
    ['section' => $S2, 'name' => 'asset_type_other',        'label' => 'If Other asset type, specify', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'asset_description',       'label' => 'Asset Description', 'type' => 'textarea', 'required' => false],
    ['section' => $S2, 'name' => 'asset_location',          'label' => 'Asset Location', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'title_registration_no',   'label' => 'Title/Registration No.', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'plot_stand_lot',          'label' => 'Plot/Stand/Lot No.', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'asset_owner',             'label' => 'Asset Owner', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'current_asset_status',    'label' => 'Current Asset Status', 'type' => 'select', 'options' => $ASSET_STATUS, 'required' => false],
    ['section' => $S2, 'name' => 'asset_condition',         'label' => 'Asset Condition', 'type' => 'select', 'options' => $CONDITION, 'required' => false],
    ['section' => $S2, 'name' => 'current_asset_value',     'label' => 'Current Asset Value', 'type' => 'number', 'required' => false],
    ['section' => $S2, 'name' => 'valuation_date',          'label' => 'Valuation Date', 'type' => 'date', 'required' => false],
    ['section' => $S2, 'name' => 'valuation_reference',     'label' => 'Valuation Report Reference', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'asset_supporting_docs',   'label' => 'Attach Supporting Documents', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 3. Lessee ----
    ['section' => $S3, 'name' => 'lessee_name',             'label' => 'Lessee Name', 'type' => 'text', 'required' => true],
    ['section' => $S3, 'name' => 'lessee_nrc',              'label' => 'NRC/Registration No.', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'lessee_tpin',             'label' => 'TPIN', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'lessee_postal',           'label' => 'Postal Address', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'lessee_physical',         'label' => 'Physical Address', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'lessee_telephone',        'label' => 'Telephone', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'lessee_email',            'label' => 'Email', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'lessee_contact_person',   'label' => 'Contact Person', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'lessee_representative',   'label' => 'Authorised Representative', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'lessee_id_docs',          'label' => 'Attach Supporting Identification/Registration Documents', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 4. Lease Terms ----
    ['section' => $S4, 'name' => 'lease_commencement_date', 'label' => 'Lease Commencement Date', 'type' => 'date', 'required' => false],
    ['section' => $S4, 'name' => 'lease_expiry_date',       'label' => 'Lease Expiry Date', 'type' => 'date', 'required' => false],
    ['section' => $S4, 'name' => 'lease_period',            'label' => 'Lease Period', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'renewal_option',          'label' => 'Renewal Option', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S4, 'name' => 'number_of_renewals',      'label' => 'Number of Renewal Periods', 'type' => 'number', 'required' => false],
    ['section' => $S4, 'name' => 'permitted_use',           'label' => 'Permitted Use', 'type' => 'textarea', 'required' => false],
    ['section' => $S4, 'name' => 'restrictions_on_use',     'label' => 'Restrictions on Use', 'type' => 'textarea', 'required' => false],
    ['section' => $S4, 'name' => 'subleasing_allowed',      'label' => 'Subleasing Allowed', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S4, 'name' => 'assignment_allowed',      'label' => 'Assignment Allowed', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S4, 'name' => 'alterations_allowed',     'label' => 'Alterations/Improvements Allowed', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S4, 'name' => 'maintenance_responsibility','label' => 'Maintenance Responsibility', 'type' => 'select', 'options' => $MAINT_RESP, 'required' => false],
    ['section' => $S4, 'name' => 'insurance_responsibility','label' => 'Insurance Responsibility', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'utilities_responsibility','label' => 'Utilities Responsibility', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'security_responsibility', 'label' => 'Security/Caretaking Responsibility', 'type' => 'text', 'required' => false],

    // ---- 5. Financial ----
    ['section' => $S5, 'name' => 'rent_amount',             'label' => 'Monthly/Annual Rent', 'type' => 'number', 'required' => false],
    ['section' => $S5, 'name' => 'currency',                'label' => 'Currency', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'security_deposit',        'label' => 'Security Deposit', 'type' => 'number', 'required' => false],
    ['section' => $S5, 'name' => 'payment_frequency',       'label' => 'Payment Frequency', 'type' => 'select', 'options' => $PAY_FREQUENCY, 'required' => false],
    ['section' => $S5, 'name' => 'payment_due_date',        'label' => 'Payment Due Date', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'rent_escalation',         'label' => 'Rent Escalation', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S5, 'name' => 'escalation_rate',         'label' => 'Escalation Rate (%)', 'type' => 'number', 'required' => false],
    ['section' => $S5, 'name' => 'escalation_frequency',    'label' => 'Escalation Frequency', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'service_charges',         'label' => 'Service Charges', 'type' => 'number', 'required' => false],
    ['section' => $S5, 'name' => 'taxes_levies',            'label' => 'Taxes/Levies', 'type' => 'number', 'required' => false],
    ['section' => $S5, 'name' => 'other_charges',           'label' => 'Other Charges', 'type' => 'number', 'required' => false],
    ['section' => $S5, 'name' => 'bank_payment_reference',  'label' => 'Bank/Payment Reference', 'type' => 'text', 'required' => false],

    // ---- 6. Agreement ----
    ['section' => $S6, 'name' => 'agreement_reference_no',  'label' => 'Agreement Reference No.', 'type' => 'text', 'required' => false],
    ['section' => $S6, 'name' => 'agreement_date',          'label' => 'Agreement Date', 'type' => 'date', 'required' => false],
    ['section' => $S6, 'name' => 'agreement_type',          'label' => 'Agreement Type', 'type' => 'select', 'options' => $AGREEMENT_TYPE, 'required' => false],
    ['section' => $S6, 'name' => 'executed_by_owner',       'label' => 'Executed By Owner', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S6, 'name' => 'executed_by_lessee',      'label' => 'Executed By Lessee', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S6, 'name' => 'witnessed',               'label' => 'Witnessed', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S6, 'name' => 'legal_review_completed',  'label' => 'Legal Review Completed', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S6, 'name' => 'legal_review_reference',  'label' => 'Legal Review Reference', 'type' => 'text', 'required' => false],
    ['section' => $S6, 'name' => 'registration_required',   'label' => 'Registration Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S6, 'name' => 'registration_no',         'label' => 'Registration No.', 'type' => 'text', 'required' => false],
    ['section' => $S6, 'name' => 'owner_signature',         'label' => 'Owner Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S6, 'name' => 'lessee_signature',        'label' => 'Lessee Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S6, 'name' => 'agreement_document',      'label' => 'Upload Agreement Document', 'type' => 'file', 'required' => false, 'allow_attachment' => true],

    // ---- 7. Approvals ----
    ['section' => $S7, 'name' => 'approval_table',          'label' => 'Approval Register', 'type' => 'table', 'options' => $APPROVAL_TABLE, 'required' => false],

    // ---- 8. Lease Monitoring ----
    ['section' => $S8, 'name' => 'monitoring_table',        'label' => 'Lease Monitoring Register', 'type' => 'table', 'options' => $MONITORING_TABLE, 'required' => false],
    ['section' => $S8, 'name' => 'last_inspection_date',    'label' => 'Last Inspection Date', 'type' => 'date', 'required' => false],
    ['section' => $S8, 'name' => 'next_inspection_date',    'label' => 'Next Inspection Date', 'type' => 'date', 'required' => false],

    // ---- 9. Rent & Arrears ----
    ['section' => $S9, 'name' => 'rent_ledger_table',       'label' => 'Rent Ledger', 'type' => 'table', 'options' => $RENT_LEDGER_TABLE, 'required' => false],
    ['section' => $S9, 'name' => 'arrears_status',          'label' => 'Arrears Status', 'type' => 'select', 'options' => $ARREARS_STATE, 'required' => false],
    ['section' => $S9, 'name' => 'arrears_notes',           'label' => 'Arrears Notes / Action', 'type' => 'textarea', 'required' => false],

    // ---- 10. Renewal / Expiry ----
    ['section' => $S10, 'name' => 'expiry_date',            'label' => 'Lease Expiry Date', 'type' => 'date', 'required' => false],
    ['section' => $S10, 'name' => 'renewal_notice_date',    'label' => 'Renewal Notice Date', 'type' => 'date', 'required' => false],
    ['section' => $S10, 'name' => 'renewal_requested',      'label' => 'Renewal Requested', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S10, 'name' => 'renewal_assessment',     'label' => 'Renewal Assessment', 'type' => 'select', 'options' => $RENEWAL_RECO, 'required' => false],
    ['section' => $S10, 'name' => 'revised_rent',           'label' => 'Revised Rent', 'type' => 'number', 'required' => false],
    ['section' => $S10, 'name' => 'new_lease_period',       'label' => 'New Lease Period', 'type' => 'text', 'required' => false],
    ['section' => $S10, 'name' => 'renewal_approval',       'label' => 'Renewal Approval', 'type' => 'text', 'required' => false],
    ['section' => $S10, 'name' => 'new_agreement_reference','label' => 'New Agreement Reference', 'type' => 'text', 'required' => false],
    ['section' => $S10, 'name' => 'renewal_date',           'label' => 'Renewal Date', 'type' => 'date', 'required' => false],

    // ---- 11. Breach / Default ----
    ['section' => $S11, 'name' => 'breach_identified',      'label' => 'Breach Identified', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'breach_type',            'label' => 'Type of Breach', 'type' => 'text', 'required' => false],
    ['section' => $S11, 'name' => 'breach_date_identified', 'label' => 'Date Identified', 'type' => 'date', 'required' => false],
    ['section' => $S11, 'name' => 'notice_issued',          'label' => 'Notice Issued', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'notice_date',            'label' => 'Notice Date', 'type' => 'date', 'required' => false],
    ['section' => $S11, 'name' => 'rectification_period',   'label' => 'Rectification Period', 'type' => 'text', 'required' => false],
    ['section' => $S11, 'name' => 'lessee_response',        'label' => 'Lessee Response', 'type' => 'textarea', 'required' => false],
    ['section' => $S11, 'name' => 'action_taken',           'label' => 'Action Taken', 'type' => 'textarea', 'required' => false],
    ['section' => $S11, 'name' => 'legal_action_required',  'label' => 'Legal Action Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'legal_reference',        'label' => 'Legal Reference', 'type' => 'text', 'required' => false],

    // ---- 12. Termination ----
    ['section' => $S12, 'name' => 'termination_type',       'label' => 'Termination Type', 'type' => 'select', 'options' => $TERM_TYPE, 'required' => false],
    ['section' => $S12, 'name' => 'termination_date',       'label' => 'Termination Date', 'type' => 'date', 'required' => false],
    ['section' => $S12, 'name' => 'termination_notice_date','label' => 'Notice Date', 'type' => 'date', 'required' => false],
    ['section' => $S12, 'name' => 'outstanding_rent',       'label' => 'Outstanding Rent', 'type' => 'number', 'required' => false],
    ['section' => $S12, 'name' => 'other_outstanding',      'label' => 'Other Outstanding Charges', 'type' => 'number', 'required' => false],
    ['section' => $S12, 'name' => 'asset_inspection_done',  'label' => 'Asset Inspection Completed', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S12, 'name' => 'asset_returned',         'label' => 'Asset Returned', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S12, 'name' => 'asset_condition_return', 'label' => 'Asset Condition on Return', 'type' => 'text', 'required' => false],
    ['section' => $S12, 'name' => 'deposit_refund',         'label' => 'Deposit Refund', 'type' => 'number', 'required' => false],
    ['section' => $S12, 'name' => 'handover_certificate',   'label' => 'Upload Handover Certificate', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S12, 'name' => 'final_account_recon',    'label' => 'Final Account Reconciled', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S12, 'name' => 'lease_closed_by',        'label' => 'Lease Closed By', 'type' => 'text', 'required' => false],
    ['section' => $S12, 'name' => 'closure_date',           'label' => 'Closure Date', 'type' => 'date', 'required' => false],
    ['section' => $S12, 'name' => 'closure_signature',      'label' => 'Closure Signature', 'type' => 'signature', 'required' => false],

    // ---- 13. Documents ----
    ['section' => $S13, 'name' => 'documents_checklist',    'label' => 'Documents Attached', 'type' => 'checkbox', 'options' => $DOCUMENTS, 'required' => false],
    ['section' => $S13, 'name' => 'documents_files',        'label' => 'Upload Supporting Documents', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],
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
        ['service_parent_id' => $parent->id, 'service_name' => 'Asset Lease Management'],
        ['model' => 'Asset Lease Management', 'type' => 'asset_management', 'is_active' => 1]
    );
    echo "  Child  : #{$child->id}  {$child->service_name}\n";

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'LAMS — Asset Lease Management Form',
            'description' => 'Property Department — Full asset lease lifecycle: application, valuation, approvals, agreement, monitoring, rent management, renewal, breach, termination and closure.',
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
