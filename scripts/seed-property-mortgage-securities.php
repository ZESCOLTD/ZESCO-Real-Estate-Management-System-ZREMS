<?php
/**
 * Seed the Property Department child service "Mortgage & Securities Management"
 * + its full form template.
 *
 * Run:  php scripts/seed-property-mortgage-securities.php
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
$SERVICE_TYPE = [
    'New Mortgage', 'Variation', 'Assignment', 'Transfer',
    'Renewal', 'Discharge', 'Release', 'Enforcement',
];

$STATUS = [
    'Draft', 'Submitted', 'Under Review', 'Approved',
    'Registered', 'Rejected', 'Completed',
];

$APPLICANT_TYPE = ['Individual', 'Company', 'Institution', 'Government'];

$SECURITY_TYPE = [
    'Legal Mortgage', 'Equitable Mortgage', 'Charge',
    'Debenture', 'Assignment of Rights', 'Other',
];

$RANKING = ['First', 'Second', 'Third', 'Other'];

$LOAN_STATUS = ['Active', 'Paid', 'Defaulted', 'Restructured'];

$YES_NO = ['Yes', 'No'];

$CURRENCIES = ['ZMW', 'USD', 'EUR', 'GBP', 'ZAR', 'Other'];

$SUPPORTING_DOCS = [
    'Certificate of Title / Lease',
    'Mortgage Instrument',
    'Loan/Facility Agreement',
    'Valuation Report',
    'Company Registration Documents',
    'Board Resolution',
    'Identification Documents',
    'Power of Attorney',
    'Consent/Approval',
    'Search Report',
    'Discharge/Release Documents',
    'Proof of Payment',
    'Other',
];

$DD_CHECKS = [
    'Ownership verified',
    'Title search completed',
    'Existing encumbrances verified',
    'Caveats/restrictions checked',
    'Property description verified',
    'Parties\' identities verified',
    'Corporate authority verified',
    'Mortgage instrument legally reviewed',
    'Consent requirements checked',
    'Ranking confirmed',
];

$VARIATION_NATURE = [
    'Increase of Amount', 'Reduction of Amount', 'Change of Interest',
    'Extension of Term', 'Change of Mortgagee', 'Change of Ranking',
    'Additional Security', 'Release of Part of Security',
];

/* -------------------------------------------------------------------
 |  Fields
 |  ------------------------------------------------------------------- */
$S1  = '1. Service Information';
$S2  = '2. Applicant / Mortgagor Details';
$S3  = '3. Mortgagee / Secured Party Details';
$S4  = '4. Property / Asset Details';
$S5  = '5. Mortgage / Security Details';
$S6  = '6. Supporting Documents';
$S7  = '7. Legal Due Diligence';
$S8  = '8. Registration Details';
$S9  = '9. Monitoring & Compliance';
$S10 = '10. Variation / Amendment';
$S11 = '11. Discharge / Release';

$fields = [
    // ---- 1. Service Information ----
    ['section' => $S1, 'name' => 'application_no',    'label' => 'Application No.', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'service_type',      'label' => 'Service Type', 'type' => 'select', 'options' => $SERVICE_TYPE, 'required' => true],
    ['section' => $S1, 'name' => 'application_date',  'label' => 'Date of Application', 'type' => 'date', 'required' => true],
    ['section' => $S1, 'name' => 'priority_no',       'label' => 'Priority No.', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'processing_officer','label' => 'Processing Officer', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'status',            'label' => 'Status', 'type' => 'select', 'options' => $STATUS, 'required' => true],

    // ---- 2. Applicant / Mortgagor Details ----
    ['section' => $S2, 'name' => 'applicant_name',        'label' => 'Full Name / Company Name', 'type' => 'text', 'required' => true],
    ['section' => $S2, 'name' => 'applicant_id_no',       'label' => 'NRC / Passport / Certificate of Incorporation No.', 'type' => 'text', 'required' => true],
    ['section' => $S2, 'name' => 'applicant_tpin',        'label' => 'TPIN', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'applicant_postal',      'label' => 'Postal Address', 'type' => 'textarea', 'required' => false],
    ['section' => $S2, 'name' => 'applicant_physical',    'label' => 'Physical Address', 'type' => 'textarea', 'required' => false],
    ['section' => $S2, 'name' => 'applicant_telephone',   'label' => 'Telephone', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'applicant_email',       'label' => 'Email', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'applicant_type',        'label' => 'Type', 'type' => 'select', 'options' => $APPLICANT_TYPE, 'required' => true],
    ['section' => $S2, 'name' => 'applicant_representative','label' => 'Authorised Representative', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'applicant_authority',   'label' => 'Supporting Authority / Board Resolution', 'type' => 'file', 'required' => false, 'allow_attachment' => true],

    // ---- 3. Mortgagee / Secured Party Details ----
    ['section' => $S3, 'name' => 'mortgagee_name',        'label' => 'Name of Bank/Financial Institution/Entity', 'type' => 'text', 'required' => true],
    ['section' => $S3, 'name' => 'mortgagee_reg_no',      'label' => 'Registration/Institution No.', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'mortgagee_tpin',        'label' => 'TPIN', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'mortgagee_address',     'label' => 'Address', 'type' => 'textarea', 'required' => false],
    ['section' => $S3, 'name' => 'mortgagee_contact',     'label' => 'Contact Person', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'mortgagee_telephone',   'label' => 'Telephone', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'mortgagee_email',       'label' => 'Email', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'mortgagee_representative','label' => 'Authorised Representative', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'mortgagee_legal_rep',   'label' => 'Legal Representative', 'type' => 'text', 'required' => false],

    // ---- 4. Property / Asset Details ----
    ['section' => $S4, 'name' => 'property_asset_id',     'label' => 'Property/Asset ID', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'title_lease_no',        'label' => 'Title/Lease No.', 'type' => 'text', 'required' => true],
    ['section' => $S4, 'name' => 'stand_plot_no',         'label' => 'Stand/Plot No.', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'property_address',      'label' => 'Property Address', 'type' => 'textarea', 'required' => false],
    ['section' => $S4, 'name' => 'property_district',     'label' => 'District', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'property_province',     'label' => 'Province', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'property_type',         'label' => 'Property Type', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'land_use',              'label' => 'Land Use', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'property_area',         'label' => 'Area/Size', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'registered_owner',      'label' => 'Registered Owner', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'current_encumbrances',  'label' => 'Current Encumbrances', 'type' => 'textarea', 'required' => false],
    ['section' => $S4, 'name' => 'existing_mortgages',    'label' => 'Existing Mortgage/Securities', 'type' => 'textarea', 'required' => false],
    ['section' => $S4, 'name' => 'valuation_reference',   'label' => 'Valuation Reference', 'type' => 'text', 'required' => false],

    // ---- 5. Mortgage / Security Details ----
    ['section' => $S5, 'name' => 'security_type',         'label' => 'Type of Security', 'type' => 'select', 'options' => $SECURITY_TYPE, 'required' => true],
    ['section' => $S5, 'name' => 'security_type_other',   'label' => 'If Other, specify', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'principal_loan_amount', 'label' => 'Principal Loan Amount', 'type' => 'number', 'required' => true],
    ['section' => $S5, 'name' => 'loan_currency',         'label' => 'Currency', 'type' => 'select', 'options' => $CURRENCIES, 'required' => true],
    ['section' => $S5, 'name' => 'interest_rate',         'label' => 'Interest Rate (%)', 'type' => 'number', 'required' => false],
    ['section' => $S5, 'name' => 'facility_agreement_no', 'label' => 'Loan/Facility Agreement No.', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'facility_agreement_date','label' => 'Date of Facility Agreement', 'type' => 'date', 'required' => false],
    ['section' => $S5, 'name' => 'mortgage_date',         'label' => 'Date of Mortgage', 'type' => 'date', 'required' => false],
    ['section' => $S5, 'name' => 'maturity_date',         'label' => 'Maturity Date', 'type' => 'date', 'required' => false],
    ['section' => $S5, 'name' => 'ranking',               'label' => 'Ranking', 'type' => 'select', 'options' => $RANKING, 'required' => false],
    ['section' => $S5, 'name' => 'ranking_other',         'label' => 'If Other ranking, specify', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'max_secured_amount',    'label' => 'Maximum Secured Amount', 'type' => 'number', 'required' => false],
    ['section' => $S5, 'name' => 'loan_purpose',          'label' => 'Purpose of Loan', 'type' => 'textarea', 'required' => false],
    ['section' => $S5, 'name' => 'security_conditions',   'label' => 'Conditions Attached to Security', 'type' => 'textarea', 'required' => false],
    ['section' => $S5, 'name' => 'cross_collateralisation','label' => 'Cross-Collateralisation (if applicable)', 'type' => 'textarea', 'required' => false],

    // ---- 6. Supporting Documents ----
    ['section' => $S6, 'name' => 'supporting_docs_checklist','label' => 'Documents Submitted (tick applicable)', 'type' => 'checkbox', 'options' => $SUPPORTING_DOCS, 'required' => false],
    ['section' => $S6, 'name' => 'supporting_docs_files',    'label' => 'Attach Supporting Documents', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 7. Legal Due Diligence ----
    ['section' => $S7, 'name' => 'dd_checks',             'label' => 'Due Diligence Checklist', 'type' => 'checkbox', 'options' => $DD_CHECKS, 'required' => false],
    ['section' => $S7, 'name' => 'legal_opinion',         'label' => 'Legal Opinion', 'type' => 'textarea', 'required' => false],
    ['section' => $S7, 'name' => 'legal_opinion_file',    'label' => 'Attach Legal Opinion', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S7, 'name' => 'dd_officer',            'label' => 'Due Diligence Officer', 'type' => 'text', 'required' => false],
    ['section' => $S7, 'name' => 'dd_date_completed',     'label' => 'Date Completed', 'type' => 'date', 'required' => false],
    ['section' => $S7, 'name' => 'dd_remarks',            'label' => 'Remarks', 'type' => 'textarea', 'required' => false],

    // ---- 8. Registration Details ----
    ['section' => $S8, 'name' => 'reg_registration_no',   'label' => 'Registration No.', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'reg_date_registered',   'label' => 'Date Registered', 'type' => 'date', 'required' => false],
    ['section' => $S8, 'name' => 'reg_instrument_no',     'label' => 'Instrument No.', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'reg_record_reference',  'label' => 'Register/Record Reference', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'reg_mortgage_rank',     'label' => 'Mortgage Rank', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'reg_secured_amount',    'label' => 'Registered Secured Amount', 'type' => 'number', 'required' => false],
    ['section' => $S8, 'name' => 'reg_certificate_file',  'label' => 'Registration Certificate / Endorsement', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S8, 'name' => 'reg_officer',           'label' => 'Registration Officer', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'reg_endorsement_date',  'label' => 'Date of Endorsement', 'type' => 'date', 'required' => false],

    // ---- 9. Monitoring & Compliance ----
    ['section' => $S9, 'name' => 'loan_status',           'label' => 'Loan Status', 'type' => 'select', 'options' => $LOAN_STATUS, 'required' => false],
    ['section' => $S9, 'name' => 'outstanding_balance',   'label' => 'Outstanding Balance', 'type' => 'number', 'required' => false],
    ['section' => $S9, 'name' => 'last_review_date',      'label' => 'Last Review Date', 'type' => 'date', 'required' => false],
    ['section' => $S9, 'name' => 'next_review_date',      'label' => 'Next Review Date', 'type' => 'date', 'required' => false],
    ['section' => $S9, 'name' => 'expiry_maturity_date',  'label' => 'Expiry/Maturity Date', 'type' => 'date', 'required' => false],
    ['section' => $S9, 'name' => 'compliance_status',     'label' => 'Compliance Status', 'type' => 'text', 'required' => false],
    ['section' => $S9, 'name' => 'default_notice_issued', 'label' => 'Default Notice Issued', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S9, 'name' => 'enforcement_initiated', 'label' => 'Enforcement Initiated', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S9, 'name' => 'court_proceedings',     'label' => 'Court Proceedings', 'type' => 'textarea', 'required' => false],
    ['section' => $S9, 'name' => 'receivership_status',   'label' => 'Property under Receivership/Realisation', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S9, 'name' => 'monitoring_remarks',    'label' => 'Remarks', 'type' => 'textarea', 'required' => false],

    // ---- 10. Variation / Amendment ----
    ['section' => $S10, 'name' => 'existing_mortgage_no',  'label' => 'Existing Mortgage No.', 'type' => 'text', 'required' => false],
    ['section' => $S10, 'name' => 'variation_nature',     'label' => 'Nature of Variation', 'type' => 'checkbox', 'options' => $VARIATION_NATURE, 'required' => false],
    ['section' => $S10, 'name' => 'variation_details',    'label' => 'Details / Description', 'type' => 'textarea', 'required' => false],
    ['section' => $S10, 'name' => 'supporting_instrument','label' => 'Supporting Instrument', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S10, 'name' => 'variation_approval',   'label' => 'Approval', 'type' => 'text', 'required' => false],
    ['section' => $S10, 'name' => 'new_registration_ref', 'label' => 'New Registration Reference', 'type' => 'text', 'required' => false],

    // ---- 11. Discharge / Release ----
    ['section' => $S11, 'name' => 'discharge_reg_no',     'label' => 'Mortgage Registration No.', 'type' => 'text', 'required' => false],
    ['section' => $S11, 'name' => 'settlement_date',      'label' => 'Date of Settlement', 'type' => 'date', 'required' => false],
    ['section' => $S11, 'name' => 'amount_settled',       'label' => 'Amount Settled', 'type' => 'number', 'required' => false],
    ['section' => $S11, 'name' => 'discharge_instrument', 'label' => 'Discharge/Release Instrument', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S11, 'name' => 'original_title_docs',  'label' => 'Original Title/Security Documents Returned', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'mortgagee_confirmation','label' => 'Confirmation from Mortgagee', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S11, 'name' => 'discharge_legal_verification','label' => 'Legal Verification', 'type' => 'text', 'required' => false],
    ['section' => $S11, 'name' => 'discharge_approval',   'label' => 'Approval', 'type' => 'text', 'required' => false],
    ['section' => $S11, 'name' => 'discharge_date',       'label' => 'Date of Discharge', 'type' => 'date', 'required' => false],
    ['section' => $S11, 'name' => 'register_updated',     'label' => 'Register Updated', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'encumbrance_removed',  'label' => 'Encumbrance Removed', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'title_released',       'label' => 'Title/Record Released', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
];

/* -------------------------------------------------------------------
 |  Persist
 |  ------------------------------------------------------------------- */
DB::transaction(function () use ($fields) {

    $parent = ServiceParent::where('department_code', 'property_department')
        ->orWhere('department_name', 'Property Department')
        ->first();

    if (!$parent) {
        $parent = ServiceParent::create([
            'department_code' => 'property_department',
            'department_name' => 'Property Department',
            'service_name'    => 'Conveyance Services',
            'is_active'       => 1,
        ]);
        echo "  Parent (created): #{$parent->id}\n";
    } else {
        echo "  Parent : #{$parent->id}  {$parent->department_name} / {$parent->service_name}\n";
    }

    $child = ServiceChild::updateOrCreate(
        ['service_parent_id' => $parent->id, 'service_name' => 'Mortgage & Securities Management'],
        ['model' => 'Mortgage & Securities Management', 'type' => 'new_acquisition', 'is_active' => 1]
    );
    echo "  Child  : #{$child->id}  {$child->service_name}\n";

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'Mortgage & Securities Management Form',
            'description' => 'Property Department — Mortgage/Security application, due diligence, registration, monitoring, variation and discharge.',
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
