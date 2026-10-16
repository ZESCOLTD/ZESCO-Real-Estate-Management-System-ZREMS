<?php
/**
 * RE-SEED the Property Department "Mortgage & Securities Management" form.
 * Deletes all existing fields on template #25 and inserts the new spec.
 *
 * Run:  php scripts/reseed-property-mortgage-securities.php
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

$SERVICE_TYPE = [
    'New Mortgage', 'Variation', 'Assignment', 'Transfer',
    'Renewal', 'Discharge', 'Release', 'Enforcement',
];

$STATUS = ['Draft', 'Submitted', 'Under Review', 'Approved', 'Registered', 'Rejected', 'Completed'];

$APPLICANT_TYPE = ['Individual', 'Company', 'Institution', 'Government'];

$SECURITY_TYPE = [
    'Legal Mortgage', 'Equitable Mortgage', 'Charge',
    'Debenture', 'Assignment of Rights', 'Other',
];

$RANKING = ['First', 'Second', 'Third', 'Other'];

$LOAN_STATUS = ['Active', 'Paid', 'Defaulted', 'Restructured'];

$COMPLIANCE = ['Compliant', 'Non-Compliant', 'Under Review'];

$DOCUMENTS = [
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
    'Other Supporting Documents',
];

/* -------------------------------------------------------------------
 |  Legal Due Diligence checkbox
 |  ------------------------------------------------------------------- */
$LEGAL_DD = [
    'Ownership Verified',
    'Title Search Completed',
    'Existing Encumbrances Verified',
    'Caveats/Restrictions Checked',
    'Property Description Verified',
    "Parties' Identities Verified",
    'Corporate Authority Verified',
    'Mortgage Instrument Legally Reviewed',
    'Consent Requirements Checked',
    'Ranking Confirmed',
];

/* -------------------------------------------------------------------
 |  Sections
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
    ['section' => $S1, 'name' => 'application_no',           'label' => 'Application No.', 'type' => 'text', 'required' => true],
    ['section' => $S1, 'name' => 'service_type',             'label' => 'Service Type', 'type' => 'select', 'options' => $SERVICE_TYPE, 'required' => true],
    ['section' => $S1, 'name' => 'date_of_application',      'label' => 'Date of Application', 'type' => 'date', 'required' => true],
    ['section' => $S1, 'name' => 'priority_no',              'label' => 'Priority No.', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'processing_officer',       'label' => 'Processing Officer', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'status',                   'label' => 'Status', 'type' => 'select', 'options' => $STATUS, 'required' => true],

    // ---- 2. Applicant / Mortgagor ----
    ['section' => $S2, 'name' => 'mortgagor_name',           'label' => 'Full Name / Company Name', 'type' => 'text', 'required' => true],
    ['section' => $S2, 'name' => 'mortgagor_id_no',          'label' => 'NRC / Passport / Certificate of Incorporation No.', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'mortgagor_tpin',           'label' => 'TPIN', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'mortgagor_postal',         'label' => 'Postal Address', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'mortgagor_physical',       'label' => 'Physical Address', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'mortgagor_telephone',      'label' => 'Telephone', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'mortgagor_email',          'label' => 'Email', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'mortgagor_type',           'label' => 'Type', 'type' => 'select', 'options' => $APPLICANT_TYPE, 'required' => false],
    ['section' => $S2, 'name' => 'mortgagor_representative', 'label' => 'Authorised Representative', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'supporting_authority',     'label' => 'Supporting Authority / Board Resolution', 'type' => 'textarea', 'required' => false],
    ['section' => $S2, 'name' => 'mortgagor_documents',      'label' => 'Attach Mortgagor Documents', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 3. Mortgagee ----
    ['section' => $S3, 'name' => 'mortgagee_name',           'label' => 'Name of Bank/Financial Institution/Entity', 'type' => 'text', 'required' => true],
    ['section' => $S3, 'name' => 'mortgagee_reg_no',         'label' => 'Registration/Institution No.', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'mortgagee_tpin',           'label' => 'TPIN', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'mortgagee_address',        'label' => 'Address', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'mortgagee_contact_person', 'label' => 'Contact Person', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'mortgagee_telephone',      'label' => 'Telephone', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'mortgagee_email',          'label' => 'Email', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'mortgagee_representative', 'label' => 'Authorised Representative', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'mortgagee_legal_rep',      'label' => 'Legal Representative', 'type' => 'text', 'required' => false],

    // ---- 4. Property / Asset ----
    ['section' => $S4, 'name' => 'property_asset_id',        'label' => 'Property/Asset ID', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'title_lease_no',           'label' => 'Title/Lease No.', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'stand_plot_no',            'label' => 'Stand/Plot No.', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'property_address',         'label' => 'Property Address', 'type' => 'textarea', 'required' => false],
    ['section' => $S4, 'name' => 'district',                 'label' => 'District', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'province',                 'label' => 'Province', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'property_type',            'label' => 'Property Type', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'land_use',                 'label' => 'Land Use', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'area_size',                'label' => 'Area/Size', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'registered_owner',         'label' => 'Registered Owner', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'current_encumbrances',     'label' => 'Current Encumbrances', 'type' => 'textarea', 'required' => false],
    ['section' => $S4, 'name' => 'existing_securities',      'label' => 'Existing Mortgage/Securities', 'type' => 'textarea', 'required' => false],
    ['section' => $S4, 'name' => 'valuation_reference',      'label' => 'Valuation Reference', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'valuation_report_file',    'label' => 'Attach Valuation Report', 'type' => 'file', 'required' => false, 'allow_attachment' => true],

    // ---- 5. Mortgage / Security ----
    ['section' => $S5, 'name' => 'security_type',            'label' => 'Type of Security', 'type' => 'select', 'options' => $SECURITY_TYPE, 'required' => true],
    ['section' => $S5, 'name' => 'security_type_other',      'label' => 'If Other security type, specify', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'principal_loan_amount',    'label' => 'Principal Loan Amount', 'type' => 'number', 'required' => true],
    ['section' => $S5, 'name' => 'currency',                 'label' => 'Currency', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'interest_rate',            'label' => 'Interest Rate (%)', 'type' => 'number', 'required' => false],
    ['section' => $S5, 'name' => 'facility_agreement_no',    'label' => 'Loan/Facility Agreement No.', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'facility_agreement_date',  'label' => 'Date of Facility Agreement', 'type' => 'date', 'required' => false],
    ['section' => $S5, 'name' => 'date_of_mortgage',         'label' => 'Date of Mortgage', 'type' => 'date', 'required' => false],
    ['section' => $S5, 'name' => 'maturity_date',            'label' => 'Maturity Date', 'type' => 'date', 'required' => false],
    ['section' => $S5, 'name' => 'ranking',                  'label' => 'Ranking', 'type' => 'select', 'options' => $RANKING, 'required' => false],
    ['section' => $S5, 'name' => 'ranking_other',            'label' => 'If Other ranking, specify', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'maximum_secured_amount',   'label' => 'Maximum Secured Amount', 'type' => 'number', 'required' => false],
    ['section' => $S5, 'name' => 'purpose_of_loan',          'label' => 'Purpose of Loan', 'type' => 'textarea', 'required' => false],
    ['section' => $S5, 'name' => 'security_conditions',      'label' => 'Conditions Attached to Security', 'type' => 'textarea', 'required' => false],
    ['section' => $S5, 'name' => 'cross_collateralization',  'label' => 'Cross-Collateralization (if applicable)', 'type' => 'textarea', 'required' => false],

    // ---- 6. Supporting Documents ----
    ['section' => $S6, 'name' => 'documents_checklist',      'label' => 'Documents Attached', 'type' => 'checkbox', 'options' => $DOCUMENTS, 'required' => false],
    ['section' => $S6, 'name' => 'documents_files',          'label' => 'Upload Supporting Documents', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 7. Legal Due Diligence ----
    ['section' => $S7, 'name' => 'legal_dd_checks',          'label' => 'Due Diligence Checks', 'type' => 'checkbox', 'options' => $LEGAL_DD, 'required' => false],
    ['section' => $S7, 'name' => 'legal_opinion',            'label' => 'Legal Opinion', 'type' => 'textarea', 'required' => false],
    ['section' => $S7, 'name' => 'legal_opinion_file',       'label' => 'Attach Legal Opinion', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S7, 'name' => 'dd_officer',               'label' => 'Due Diligence Officer', 'type' => 'text', 'required' => false],
    ['section' => $S7, 'name' => 'dd_date_completed',        'label' => 'Date Completed', 'type' => 'date', 'required' => false],
    ['section' => $S7, 'name' => 'dd_signature',             'label' => 'Due Diligence Officer Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S7, 'name' => 'dd_remarks',               'label' => 'Remarks', 'type' => 'textarea', 'required' => false],

    // ---- 8. Registration ----
    ['section' => $S8, 'name' => 'registration_no',          'label' => 'Registration No.', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'date_registered',          'label' => 'Date Registered', 'type' => 'date', 'required' => false],
    ['section' => $S8, 'name' => 'instrument_no',            'label' => 'Instrument No.', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'register_reference',       'label' => 'Register/Record Reference', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'registered_rank',          'label' => 'Mortgage Rank', 'type' => 'select', 'options' => $RANKING, 'required' => false],
    ['section' => $S8, 'name' => 'registered_secured_amount','label' => 'Registered Secured Amount', 'type' => 'number', 'required' => false],
    ['section' => $S8, 'name' => 'registration_certificate', 'label' => 'Upload Registration Certificate/Endorsement', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S8, 'name' => 'registration_officer',     'label' => 'Registration Officer', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'endorsement_date',         'label' => 'Date of Endorsement', 'type' => 'date', 'required' => false],
    ['section' => $S8, 'name' => 'registration_signature',   'label' => 'Registration Officer Signature', 'type' => 'signature', 'required' => false],

    // ---- 9. Monitoring & Compliance ----
    ['section' => $S9, 'name' => 'loan_status',              'label' => 'Loan Status', 'type' => 'select', 'options' => $LOAN_STATUS, 'required' => false],
    ['section' => $S9, 'name' => 'outstanding_balance',      'label' => 'Outstanding Balance', 'type' => 'number', 'required' => false],
    ['section' => $S9, 'name' => 'last_review_date',         'label' => 'Last Review Date', 'type' => 'date', 'required' => false],
    ['section' => $S9, 'name' => 'next_review_date',         'label' => 'Next Review Date', 'type' => 'date', 'required' => false],
    ['section' => $S9, 'name' => 'expiry_maturity_date',     'label' => 'Expiry/Maturity Date', 'type' => 'date', 'required' => false],
    ['section' => $S9, 'name' => 'compliance_status',        'label' => 'Compliance Status', 'type' => 'select', 'options' => $COMPLIANCE, 'required' => false],
    ['section' => $S9, 'name' => 'default_notice_issued',    'label' => 'Default Notice Issued', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S9, 'name' => 'enforcement_initiated',    'label' => 'Enforcement Initiated', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S9, 'name' => 'court_proceedings',        'label' => 'Court Proceedings', 'type' => 'textarea', 'required' => false],
    ['section' => $S9, 'name' => 'receivership_realisation', 'label' => 'Property Under Receivership/Realisation', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S9, 'name' => 'monitoring_remarks',       'label' => 'Remarks', 'type' => 'textarea', 'required' => false],

    // ---- 10. Variation / Amendment ----
    ['section' => $S10, 'name' => 'existing_mortgage_no',    'label' => 'Existing Mortgage No.', 'type' => 'text', 'required' => false],
    ['section' => $S10, 'name' => 'nature_of_variation',     'label' => 'Nature of Variation', 'type' => 'textarea', 'required' => false],
    ['section' => $S10, 'name' => 'amount_change',           'label' => 'Increase/Reduction of Amount', 'type' => 'number', 'required' => false],
    ['section' => $S10, 'name' => 'change_of_interest',      'label' => 'Change of Interest', 'type' => 'text', 'required' => false],
    ['section' => $S10, 'name' => 'extension_of_term',       'label' => 'Extension of Term', 'type' => 'text', 'required' => false],
    ['section' => $S10, 'name' => 'change_of_mortgagee',     'label' => 'Change of Mortgagee', 'type' => 'text', 'required' => false],
    ['section' => $S10, 'name' => 'change_of_ranking',       'label' => 'Change of Ranking', 'type' => 'text', 'required' => false],
    ['section' => $S10, 'name' => 'additional_security',     'label' => 'Additional Security', 'type' => 'textarea', 'required' => false],
    ['section' => $S10, 'name' => 'release_part_security',   'label' => 'Release of Part of Security', 'type' => 'textarea', 'required' => false],
    ['section' => $S10, 'name' => 'supporting_instrument',   'label' => 'Attach Supporting Instrument', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],
    ['section' => $S10, 'name' => 'variation_approval',      'label' => 'Approval', 'type' => 'text', 'required' => false],
    ['section' => $S10, 'name' => 'variation_approval_date', 'label' => 'Approval Date', 'type' => 'date', 'required' => false],
    ['section' => $S10, 'name' => 'new_registration_ref',    'label' => 'New Registration Reference', 'type' => 'text', 'required' => false],

    // ---- 11. Discharge / Release ----
    ['section' => $S11, 'name' => 'discharge_mortgage_no',       'label' => 'Mortgage Registration No.', 'type' => 'text', 'required' => false],
    ['section' => $S11, 'name' => 'date_of_settlement',          'label' => 'Date of Settlement', 'type' => 'date', 'required' => false],
    ['section' => $S11, 'name' => 'amount_settled',              'label' => 'Amount Settled', 'type' => 'number', 'required' => false],
    ['section' => $S11, 'name' => 'discharge_instrument',        'label' => 'Upload Discharge/Release Instrument', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S11, 'name' => 'original_docs_returned',      'label' => 'Original Title/Security Documents Returned', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'mortgagee_confirmation',      'label' => 'Confirmation from Mortgagee', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S11, 'name' => 'discharge_legal_verification','label' => 'Legal Verification', 'type' => 'textarea', 'required' => false],
    ['section' => $S11, 'name' => 'discharge_approval',          'label' => 'Approval', 'type' => 'text', 'required' => false],
    ['section' => $S11, 'name' => 'date_of_discharge',           'label' => 'Date of Discharge', 'type' => 'date', 'required' => false],
    ['section' => $S11, 'name' => 'register_updated',            'label' => 'Register Updated', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'encumbrance_removed',         'label' => 'Encumbrance Removed', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'title_released',              'label' => 'Title/Record Released', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'discharge_signature',         'label' => 'Discharge Officer Signature', 'type' => 'signature', 'required' => false],
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

    $child = ServiceChild::where('service_parent_id', $parent->id)
        ->where('service_name', 'Mortgage & Securities Management')
        ->first();

    if (!$child) {
        echo "  ERROR: Child 'Mortgage & Securities Management' not found.\n";
        return;
    }

    $template = ServiceFormTemplate::where('service_child_id', $child->id)->first();
    if (!$template) {
        echo "  ERROR: Template for child #{$child->id} not found.\n";
        return;
    }

    echo "  Parent : #{$parent->id}  {$parent->department_name}\n";
    echo "  Child  : #{$child->id}  {$child->service_name}\n";
    echo "  Template: #{$template->id}  {$template->form_name}\n";

    // Update template metadata
    $template->update([
        'form_name'   => 'LAMS — Mortgage & Securities Management Form',
        'description' => 'Property Department — New mortgages, variations, assignments, transfers, renewals, discharges, releases and enforcement.',
    ]);

    // Delete existing fields
    $deleted = ServiceFormField::where('service_form_template_id', $template->id)->delete();
    echo "     ↳ Deleted {$deleted} existing field(s).\n";

    // Insert new fields
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
    echo "     ↳ Inserted " . count($fields) . " new fields across "
        . count(array_unique(array_column($fields, 'section'))) . " sections.\n";
});

echo "\nDone.\n";
