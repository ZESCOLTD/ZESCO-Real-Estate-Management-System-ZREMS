<?php
/**
 * Seed the Property Department child service "Sublease Agreement Management"
 * + its full LAMS Sublease Agreement Management form template.
 *
 * Run:  php scripts/seed-property-sublease-management.php
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
$YES_NO         = ['Yes', 'No'];
$YES_NO_PENDING = ['Yes', 'No', 'Pending'];
$YES_NO_NA      = ['Yes', 'No', 'N/A'];

$APP_TYPE       = ['New', 'Renewal', 'Variation', 'Termination'];
$STATUS         = ['Draft', 'Submitted', 'Under Review', 'Approved', 'Rejected', 'Executed', 'Registered', 'Terminated'];
$PRIORITY       = ['Normal', 'Urgent'];

$SUPPORTING_AUTH   = ['Board Resolution', 'Power of Attorney', 'Other'];
$LEASE_STATUS      = ['Valid', 'Expired', 'Pending Renewal'];
$RENT_FREQUENCY    = ['Monthly', 'Quarterly', 'Annual'];
$LEGAL_RECO        = ['Proceed', 'Proceed subject to conditions', 'Do not proceed', 'Further information required'];
$COMPLIANCE_STATE  = ['Compliant', 'Non-Compliant'];
$REGISTRATION_STAT = ['Pending', 'Registered', 'Rejected'];
$TERM_TYPE         = ['Expiry', 'Mutual Surrender', 'Breach', 'Other'];

$CHECK_STATUS = ['Complete', 'Incomplete', 'N/A'];

$DOCUMENTS = [
    'Application for Sublease',
    'Parent Lease Agreement',
    'Certificate of Title / Property Record',
    'Consent to Sublease',
    'Site/Survey Plan',
    'Company Documents',
    'Identification/Authority Documents',
    'Board Resolution',
    'Planning Approval',
    'Environmental Approval',
    'Valuation Report',
    'Draft Sublease Agreement',
    'Executed Sublease Agreement',
    'Proof of Payment',
    'Registration Evidence',
    'Other Supporting Documents',
];

/* -------------------------------------------------------------------
 |  Legal Due Diligence table
 |  ------------------------------------------------------------------- */
$LEGAL_DD_TABLE = [
    ['name' => 'check',   'label' => 'Check',   'type' => 'text'],
    ['name' => 'status',  'label' => 'Status',  'type' => 'select', 'options' => $CHECK_STATUS],
    ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'text'],
    'default_rows' => array_map(fn($c) => ['check' => $c, 'status' => 'N/A', 'remarks' => ''], [
        'Parent Lease Verified',
        'Title/Ownership Verified',
        'Parent Lease Valid',
        'Rent/Lease Charges Checked',
        'Existing Encumbrances Checked',
        'Restrictions Checked',
        'Consent Requirement Checked',
        'Sublease Terms Reviewed',
        'Land Use Compliance',
        'Planning Compliance',
        'Environmental Requirements',
        'Rates/Charges Checked',
        'Litigation Search',
        'Other Interests Checked',
    ]),
];

/* -------------------------------------------------------------------
 |  Sections
 |  ------------------------------------------------------------------- */
$S1  = '1. Sublease Application / Case Details';
$S2  = '2. Property / Land Details';
$S3  = '3. Head Lessee / Sublessor Details';
$S4  = '4. Sublessee Details';
$S5  = '5. Sublease Property Details';
$S6  = '6. Proposed Sublease Terms';
$S7  = '7. Consent / Approval Details';
$S8  = '8. Legal Due Diligence';
$S9  = '9. Planning / Technical Review';
$S10 = '10. Financial Assessment';
$S11 = '11. Document Management';
$S12 = '12. Sublease Agreement Preparation';
$S13 = '13. Registration / Recording';
$S14 = '14. Financial & Rent Monitoring';
$S15 = '15. Inspection & Compliance';
$S16 = '16. Variation / Renewal';
$S17 = '17. Termination / Surrender';
$S18 = '18. Closure';

$fields = [
    // ---- 1. Application details ----
    ['section' => $S1, 'name' => 'sublease_reference_no',    'label' => 'Sublease Reference No.', 'type' => 'text', 'required' => true],
    ['section' => $S1, 'name' => 'parent_lease_reference',   'label' => 'Parent Lease Reference No.', 'type' => 'text', 'required' => true],
    ['section' => $S1, 'name' => 'property_asset_id',        'label' => 'Property/Asset ID', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'title_lease_no',           'label' => 'Title/Lease No.', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'date_of_application',      'label' => 'Date of Application', 'type' => 'date', 'required' => true],
    ['section' => $S1, 'name' => 'application_type',         'label' => 'Application Type', 'type' => 'select', 'options' => $APP_TYPE, 'required' => true],
    ['section' => $S1, 'name' => 'status',                   'label' => 'Status', 'type' => 'select', 'options' => $STATUS, 'required' => true],
    ['section' => $S1, 'name' => 'responsible_officer',      'label' => 'Responsible Officer', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'managing_unit',            'label' => 'Managing Unit', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'date_received',            'label' => 'Date Received', 'type' => 'date', 'required' => false],
    ['section' => $S1, 'name' => 'priority',                 'label' => 'Priority', 'type' => 'select', 'options' => $PRIORITY, 'required' => false],

    // ---- 2. Property / Land ----
    ['section' => $S2, 'name' => 'property_name',           'label' => 'Property Name/Description', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'property_number',         'label' => 'Property Number', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'title_number',            'label' => 'Title Number', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'parent_lease_number',     'label' => 'Parent Lease Number', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'location',                'label' => 'Location', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'province',                'label' => 'Province', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'district',                'label' => 'District', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'local_authority',         'label' => 'Local Authority', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'plot_stand_number',       'label' => 'Plot/Stand Number', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'area_size',               'label' => 'Area/Size', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'land_use_zoning',         'label' => 'Land Use/Zoning', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'gis_cadastral_reference', 'label' => 'GIS/Cadastral Reference', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'coordinates',             'label' => 'Coordinates', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'current_land_use',        'label' => 'Current Land Use', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'development_on_property', 'label' => 'Development on Property', 'type' => 'textarea', 'required' => false],
    ['section' => $S2, 'name' => 'encumbrances',            'label' => 'Encumbrances/Restrictions', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S2, 'name' => 'existing_interests',      'label' => 'Existing Interests', 'type' => 'textarea', 'required' => false],
    ['section' => $S2, 'name' => 'parent_lease_expiry',     'label' => 'Parent Lease Expiry Date', 'type' => 'date', 'required' => false],

    // ---- 3. Head Lessee / Sublessor ----
    ['section' => $S3, 'name' => 'sublessor_name',          'label' => 'Name/Company', 'type' => 'text', 'required' => true],
    ['section' => $S3, 'name' => 'sublessor_nrc',           'label' => 'NRC/Registration No.', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'sublessor_tpin',          'label' => 'TPIN', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'sublessor_postal',        'label' => 'Postal Address', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'sublessor_physical',      'label' => 'Physical Address', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'sublessor_telephone',     'label' => 'Telephone', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'sublessor_email',         'label' => 'Email', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'sublessor_representative','label' => 'Authorised Representative', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'sublessor_capacity',      'label' => 'Capacity/Position', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'supporting_authority',    'label' => 'Supporting Authority', 'type' => 'select', 'options' => $SUPPORTING_AUTH, 'required' => false],
    ['section' => $S3, 'name' => 'existing_lease_status',   'label' => 'Existing Lease Status', 'type' => 'select', 'options' => $LEASE_STATUS, 'required' => false],

    // ---- 4. Sublessee ----
    ['section' => $S4, 'name' => 'sublessee_name',          'label' => 'Name/Company', 'type' => 'text', 'required' => true],
    ['section' => $S4, 'name' => 'sublessee_nrc',           'label' => 'NRC/Registration No.', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'sublessee_tpin',          'label' => 'TPIN', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'sublessee_company_reg',   'label' => 'Company Registration No.', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'sublessee_postal',        'label' => 'Postal Address', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'sublessee_physical',      'label' => 'Physical Address', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'sublessee_telephone',     'label' => 'Telephone', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'sublessee_email',         'label' => 'Email', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'sublessee_representative','label' => 'Authorised Representative', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'sublessee_nationality',   'label' => 'Nationality/Ownership Details', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'intended_activity',       'label' => 'Intended Activity', 'type' => 'textarea', 'required' => false],
    ['section' => $S4, 'name' => 'proposed_use',            'label' => 'Proposed Use', 'type' => 'textarea', 'required' => false],
    ['section' => $S4, 'name' => 'sublessee_compliance',    'label' => 'Compliance Status', 'type' => 'text', 'required' => false],

    // ---- 5. Sublease Property Details ----
    ['section' => $S5, 'name' => 'portion_subleased',       'label' => 'Portion/Area Subleased', 'type' => 'textarea', 'required' => false],
    ['section' => $S5, 'name' => 'size_of_portion',         'label' => 'Size of Subleased Portion', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'unit_shop_building',      'label' => 'Unit/Shop/Building/Floor', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'sublease_cadastral_ref',  'label' => 'Cadastral Reference', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'sublease_coordinates',    'label' => 'Coordinates', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'site_plan_attached',      'label' => 'Site Plan Attached', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S5, 'name' => 'survey_plan_no',          'label' => 'Survey Plan No.', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'site_plan_file',          'label' => 'Attach Site/Survey Plan', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],
    ['section' => $S5, 'name' => 'access_arrangements',     'label' => 'Access Arrangements', 'type' => 'textarea', 'required' => false],
    ['section' => $S5, 'name' => 'shared_facilities',       'label' => 'Shared Facilities', 'type' => 'textarea', 'required' => false],
    ['section' => $S5, 'name' => 'utilities',               'label' => 'Utilities', 'type' => 'textarea', 'required' => false],
    ['section' => $S5, 'name' => 'existing_structures',     'label' => 'Existing Structures', 'type' => 'textarea', 'required' => false],
    ['section' => $S5, 'name' => 'restrictions_on_use',     'label' => 'Restrictions on Use', 'type' => 'textarea', 'required' => false],
    ['section' => $S5, 'name' => 'development_conditions',  'label' => 'Development Conditions', 'type' => 'textarea', 'required' => false],

    // ---- 6. Proposed Sublease Terms ----
    ['section' => $S6, 'name' => 'sublease_commencement',   'label' => 'Sublease Commencement Date', 'type' => 'date', 'required' => false],
    ['section' => $S6, 'name' => 'sublease_expiry',         'label' => 'Sublease Expiry Date', 'type' => 'date', 'required' => false],
    ['section' => $S6, 'name' => 'term',                    'label' => 'Term', 'type' => 'text', 'required' => false],
    ['section' => $S6, 'name' => 'rent',                    'label' => 'Rent', 'type' => 'number', 'required' => false],
    ['section' => $S6, 'name' => 'rent_frequency',          'label' => 'Rent Frequency', 'type' => 'select', 'options' => $RENT_FREQUENCY, 'required' => false],
    ['section' => $S6, 'name' => 'rent_review',             'label' => 'Rent Review', 'type' => 'textarea', 'required' => false],
    ['section' => $S6, 'name' => 'security_deposit',        'label' => 'Security Deposit', 'type' => 'number', 'required' => false],
    ['section' => $S6, 'name' => 'premium',                 'label' => 'Premium/Consideration', 'type' => 'number', 'required' => false],
    ['section' => $S6, 'name' => 'service_charges',         'label' => 'Service Charges', 'type' => 'textarea', 'required' => false],
    ['section' => $S6, 'name' => 'utilities_terms',         'label' => 'Utilities', 'type' => 'textarea', 'required' => false],
    ['section' => $S6, 'name' => 'permitted_use',           'label' => 'Permitted Use', 'type' => 'textarea', 'required' => false],
    ['section' => $S6, 'name' => 'development_obligations', 'label' => 'Development Obligations', 'type' => 'textarea', 'required' => false],
    ['section' => $S6, 'name' => 'maintenance_responsibility','label' => 'Maintenance Responsibility', 'type' => 'textarea', 'required' => false],
    ['section' => $S6, 'name' => 'insurance_requirement',   'label' => 'Insurance Requirement', 'type' => 'textarea', 'required' => false],
    ['section' => $S6, 'name' => 'assignment_restrictions', 'label' => 'Assignment/Subletting Restrictions', 'type' => 'textarea', 'required' => false],
    ['section' => $S6, 'name' => 'renewal_option',          'label' => 'Renewal Option', 'type' => 'textarea', 'required' => false],
    ['section' => $S6, 'name' => 'termination_conditions',  'label' => 'Termination Conditions', 'type' => 'textarea', 'required' => false],
    ['section' => $S6, 'name' => 'special_conditions',      'label' => 'Other Special Conditions', 'type' => 'textarea', 'required' => false],

    // ---- 7. Consent / Approval ----
    ['section' => $S7, 'name' => 'consent_required',        'label' => 'Consent Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S7, 'name' => 'consent_authority',       'label' => 'Consent Authority', 'type' => 'text', 'required' => false],
    ['section' => $S7, 'name' => 'consent_app_date',        'label' => 'Consent Application Date', 'type' => 'date', 'required' => false],
    ['section' => $S7, 'name' => 'consent_reference',       'label' => 'Consent Reference', 'type' => 'text', 'required' => false],
    ['section' => $S7, 'name' => 'consent_granted',         'label' => 'Consent Granted', 'type' => 'select', 'options' => $YES_NO_PENDING, 'required' => false],
    ['section' => $S7, 'name' => 'consent_date',            'label' => 'Consent Date', 'type' => 'date', 'required' => false],
    ['section' => $S7, 'name' => 'consent_conditions',      'label' => 'Conditions of Consent', 'type' => 'textarea', 'required' => false],
    ['section' => $S7, 'name' => 'consent_document',        'label' => 'Upload Consent Document', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S7, 'name' => 'consent_expiry',          'label' => 'Consent Expiry', 'type' => 'date', 'required' => false],
    ['section' => $S7, 'name' => 'approval_authority',      'label' => 'Approval Authority', 'type' => 'text', 'required' => false],
    ['section' => $S7, 'name' => 'approval_date',           'label' => 'Approval Date', 'type' => 'date', 'required' => false],

    // ---- 8. Legal Due Diligence ----
    ['section' => $S8, 'name' => 'legal_dd_table',          'label' => 'Legal Due Diligence Checklist', 'type' => 'table', 'options' => $LEGAL_DD_TABLE, 'required' => false],
    ['section' => $S8, 'name' => 'legal_recommendation',    'label' => "Legal Officer's Recommendation", 'type' => 'select', 'options' => $LEGAL_RECO, 'required' => false],
    ['section' => $S8, 'name' => 'legal_comments',          'label' => 'Legal Comments', 'type' => 'textarea', 'required' => false],
    ['section' => $S8, 'name' => 'legal_signature',         'label' => 'Legal Officer Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S8, 'name' => 'legal_date',              'label' => 'Date', 'type' => 'date', 'required' => false],

    // ---- 9. Planning / Technical ----
    ['section' => $S9, 'name' => 'planning_approval_required','label' => 'Planning Approval Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S9, 'name' => 'current_zoning',          'label' => 'Current Zoning', 'type' => 'text', 'required' => false],
    ['section' => $S9, 'name' => 'planning_proposed_use',   'label' => 'Proposed Use', 'type' => 'text', 'required' => false],
    ['section' => $S9, 'name' => 'use_compatible',          'label' => 'Use Compatible with Zoning', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S9, 'name' => 'planning_permission_no',  'label' => 'Planning Permission No.', 'type' => 'text', 'required' => false],
    ['section' => $S9, 'name' => 'survey_required',         'label' => 'Survey Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S9, 'name' => 'survey_reference',        'label' => 'Survey Reference', 'type' => 'text', 'required' => false],
    ['section' => $S9, 'name' => 'site_plan_verified',      'label' => 'Site Plan Verified', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S9, 'name' => 'env_approval_required',   'label' => 'Environmental Approval Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S9, 'name' => 'technical_recommendation','label' => 'Technical Recommendation', 'type' => 'textarea', 'required' => false],

    // ---- 10. Financial Assessment ----
    ['section' => $S10, 'name' => 'proposed_rent',          'label' => 'Proposed Rent', 'type' => 'number', 'required' => false],
    ['section' => $S10, 'name' => 'fin_security_deposit',   'label' => 'Security Deposit', 'type' => 'number', 'required' => false],
    ['section' => $S10, 'name' => 'fin_premium',            'label' => 'Premium', 'type' => 'number', 'required' => false],
    ['section' => $S10, 'name' => 'fin_service_charges',    'label' => 'Service Charges', 'type' => 'number', 'required' => false],
    ['section' => $S10, 'name' => 'taxes_duties',           'label' => 'Taxes/Duties', 'type' => 'number', 'required' => false],
    ['section' => $S10, 'name' => 'admin_fees',             'label' => 'Administrative Fees', 'type' => 'number', 'required' => false],
    ['section' => $S10, 'name' => 'arrears_on_parent_lease','label' => 'Arrears on Parent Lease', 'type' => 'number', 'required' => false],
    ['section' => $S10, 'name' => 'other_charges',          'label' => 'Other Charges', 'type' => 'number', 'required' => false],
    ['section' => $S10, 'name' => 'payment_status',         'label' => 'Payment Status', 'type' => 'text', 'required' => false],
    ['section' => $S10, 'name' => 'receipt_reference',      'label' => 'Receipt Reference', 'type' => 'text', 'required' => false],

    // ---- 11. Document Management ----
    ['section' => $S11, 'name' => 'documents_checklist',    'label' => 'Documents Attached', 'type' => 'checkbox', 'options' => $DOCUMENTS, 'required' => false],
    ['section' => $S11, 'name' => 'documents_files',        'label' => 'Upload Supporting Documents', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 12. Sublease Agreement Preparation ----
    ['section' => $S12, 'name' => 'draft_prepared_by',      'label' => 'Draft Agreement Prepared By', 'type' => 'text', 'required' => false],
    ['section' => $S12, 'name' => 'date_drafted',           'label' => 'Date Drafted', 'type' => 'date', 'required' => false],
    ['section' => $S12, 'name' => 'legal_review_date',      'label' => 'Legal Review Date', 'type' => 'date', 'required' => false],
    ['section' => $S12, 'name' => 'amendments_required',    'label' => 'Amendments Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S12, 'name' => 'final_draft_approved',   'label' => 'Final Draft Approved', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S12, 'name' => 'execution_date',         'label' => 'Execution Date', 'type' => 'date', 'required' => false],
    ['section' => $S12, 'name' => 'sublicensor_signature',  'label' => 'Sublicensor Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S12, 'name' => 'sublessee_signature',    'label' => 'Sublessee Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S12, 'name' => 'witnesses',              'label' => 'Witnesses', 'type' => 'textarea', 'required' => false],
    ['section' => $S12, 'name' => 'notarisation',           'label' => 'Notarisation/Authentication (where applicable)', 'type' => 'text', 'required' => false],
    ['section' => $S12, 'name' => 'final_agreement_file',   'label' => 'Upload Final Agreement', 'type' => 'file', 'required' => false, 'allow_attachment' => true],

    // ---- 13. Registration / Recording ----
    ['section' => $S13, 'name' => 'registration_required',  'label' => 'Registration Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S13, 'name' => 'reg_app_date',           'label' => 'Registration Application Date', 'type' => 'date', 'required' => false],
    ['section' => $S13, 'name' => 'registration_reference', 'label' => 'Registration Reference', 'type' => 'text', 'required' => false],
    ['section' => $S13, 'name' => 'registration_authority', 'label' => 'Registration Authority', 'type' => 'text', 'required' => false],
    ['section' => $S13, 'name' => 'registration_date',      'label' => 'Registration Date', 'type' => 'date', 'required' => false],
    ['section' => $S13, 'name' => 'registered_instrument_no','label' => 'Registered Instrument No.', 'type' => 'text', 'required' => false],
    ['section' => $S13, 'name' => 'registration_status',    'label' => 'Registration Status', 'type' => 'select', 'options' => $REGISTRATION_STAT, 'required' => false],
    ['section' => $S13, 'name' => 'registration_certificate','label' => 'Upload Registration Certificate/Proof', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S13, 'name' => 'property_record_updated','label' => 'Updated Property Record', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S13, 'name' => 'gis_updated',            'label' => 'GIS Updated', 'type' => 'select', 'options' => $YES_NO, 'required' => false],

    // ---- 14. Financial & Rent Monitoring ----
    ['section' => $S14, 'name' => 'rent_payable',           'label' => 'Rent Payable', 'type' => 'number', 'required' => false],
    ['section' => $S14, 'name' => 'payment_frequency',      'label' => 'Payment Frequency', 'type' => 'select', 'options' => $RENT_FREQUENCY, 'required' => false],
    ['section' => $S14, 'name' => 'next_payment_date',      'label' => 'Next Payment Date', 'type' => 'date', 'required' => false],
    ['section' => $S14, 'name' => 'amount_paid',            'label' => 'Amount Paid', 'type' => 'number', 'required' => false],
    ['section' => $S14, 'name' => 'outstanding_balance',    'label' => 'Outstanding Balance', 'type' => 'number', 'required' => false],
    ['section' => $S14, 'name' => 'arrears',                'label' => 'Arrears', 'type' => 'number', 'required' => false],
    ['section' => $S14, 'name' => 'penalties_interest',     'label' => 'Penalties/Interest', 'type' => 'number', 'required' => false],
    ['section' => $S14, 'name' => 'monitoring_deposit',     'label' => 'Security Deposit', 'type' => 'number', 'required' => false],
    ['section' => $S14, 'name' => 'payment_history',        'label' => 'Payment History', 'type' => 'textarea', 'required' => false],
    ['section' => $S14, 'name' => 'receipts_file',          'label' => 'Upload Receipts', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 15. Inspection & Compliance ----
    ['section' => $S15, 'name' => 'inspection_required',    'label' => 'Inspection Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S15, 'name' => 'inspection_date',        'label' => 'Inspection Date', 'type' => 'date', 'required' => false],
    ['section' => $S15, 'name' => 'inspector',              'label' => 'Inspector', 'type' => 'text', 'required' => false],
    ['section' => $S15, 'name' => 'property_condition',     'label' => 'Property Condition', 'type' => 'textarea', 'required' => false],
    ['section' => $S15, 'name' => 'permitted_use_complied', 'label' => 'Permitted Use Complied With', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S15, 'name' => 'dev_conditions_complied','label' => 'Development Conditions Complied With', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S15, 'name' => 'env_compliance',         'label' => 'Environmental Compliance', 'type' => 'textarea', 'required' => false],
    ['section' => $S15, 'name' => 'planning_compliance',    'label' => 'Planning Compliance', 'type' => 'textarea', 'required' => false],
    ['section' => $S15, 'name' => 'health_safety',          'label' => 'Health & Safety', 'type' => 'textarea', 'required' => false],
    ['section' => $S15, 'name' => 'breach_identified',      'label' => 'Breach Identified', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S15, 'name' => 'corrective_action',      'label' => 'Corrective Action', 'type' => 'textarea', 'required' => false],
    ['section' => $S15, 'name' => 'compliance_deadline',    'label' => 'Compliance Deadline', 'type' => 'date', 'required' => false],
    ['section' => $S15, 'name' => 'followup_inspection',    'label' => 'Follow-up Inspection', 'type' => 'date', 'required' => false],
    ['section' => $S15, 'name' => 'compliance_status',      'label' => 'Compliance Status', 'type' => 'select', 'options' => $COMPLIANCE_STATE, 'required' => false],

    // ---- 16. Variation / Renewal ----
    ['section' => $S16, 'name' => 'variation_reference',    'label' => 'Variation Reference', 'type' => 'text', 'required' => false],
    ['section' => $S16, 'name' => 'variation_date',         'label' => 'Variation Date Requested', 'type' => 'date', 'required' => false],
    ['section' => $S16, 'name' => 'variation_reason',       'label' => 'Reason for Variation', 'type' => 'textarea', 'required' => false],
    ['section' => $S16, 'name' => 'proposed_amendment',     'label' => 'Proposed Amendment', 'type' => 'textarea', 'required' => false],
    ['section' => $S16, 'name' => 'variation_legal_review', 'label' => 'Legal Review', 'type' => 'textarea', 'required' => false],
    ['section' => $S16, 'name' => 'variation_consent',      'label' => 'Consent', 'type' => 'select', 'options' => $YES_NO_PENDING, 'required' => false],
    ['section' => $S16, 'name' => 'variation_approval',     'label' => 'Approval', 'type' => 'select', 'options' => $YES_NO_PENDING, 'required' => false],
    ['section' => $S16, 'name' => 'amended_agreement_file', 'label' => 'Upload Amended Agreement', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S16, 'name' => 'variation_effective_date','label' => 'Effective Date', 'type' => 'date', 'required' => false],
    ['section' => $S16, 'name' => 'current_expiry_date',    'label' => 'Current Expiry Date', 'type' => 'date', 'required' => false],
    ['section' => $S16, 'name' => 'proposed_renewal_period','label' => 'Proposed Renewal Period', 'type' => 'text', 'required' => false],
    ['section' => $S16, 'name' => 'renewal_terms',          'label' => 'Renewal Terms', 'type' => 'textarea', 'required' => false],
    ['section' => $S16, 'name' => 'renewal_consent',        'label' => 'Renewal Consent Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S16, 'name' => 'renewal_approval',       'label' => 'Renewal Approval', 'type' => 'select', 'options' => $YES_NO_PENDING, 'required' => false],
    ['section' => $S16, 'name' => 'new_expiry_date',        'label' => 'New Expiry Date', 'type' => 'date', 'required' => false],
    ['section' => $S16, 'name' => 'renewed_agreement_file', 'label' => 'Upload Renewed Agreement', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S16, 'name' => 'renewal_registration',   'label' => 'Registration/Update Reference', 'type' => 'text', 'required' => false],

    // ---- 17. Termination / Surrender ----
    ['section' => $S17, 'name' => 'termination_type',       'label' => 'Termination Type', 'type' => 'select', 'options' => $TERM_TYPE, 'required' => false],
    ['section' => $S17, 'name' => 'termination_notice_date','label' => 'Termination Notice Date', 'type' => 'date', 'required' => false],
    ['section' => $S17, 'name' => 'termination_effective',  'label' => 'Effective Termination Date', 'type' => 'date', 'required' => false],
    ['section' => $S17, 'name' => 'termination_reason',     'label' => 'Reason', 'type' => 'textarea', 'required' => false],
    ['section' => $S17, 'name' => 'outstanding_rent',       'label' => 'Outstanding Rent', 'type' => 'number', 'required' => false],
    ['section' => $S17, 'name' => 'termination_inspection', 'label' => 'Property Inspection Details', 'type' => 'textarea', 'required' => false],
    ['section' => $S17, 'name' => 'vacant_possession',      'label' => 'Vacant Possession', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S17, 'name' => 'restoration_required',   'label' => 'Restoration Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S17, 'name' => 'deposit_refund',         'label' => 'Deposit Refund', 'type' => 'number', 'required' => false],
    ['section' => $S17, 'name' => 'final_account',          'label' => 'Final Account', 'type' => 'textarea', 'required' => false],
    ['section' => $S17, 'name' => 'legal_clearance',        'label' => 'Legal Clearance', 'type' => 'text', 'required' => false],
    ['section' => $S17, 'name' => 'registration_cancel',    'label' => 'Registration Cancellation Reference', 'type' => 'text', 'required' => false],
    ['section' => $S17, 'name' => 'gis_asset_updated',      'label' => 'GIS/Asset Record Updated', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S17, 'name' => 'closure_date',           'label' => 'Closure Date', 'type' => 'date', 'required' => false],

    // ---- 18. Closure ----
    ['section' => $S18, 'name' => 'case_officer',           'label' => 'Case Officer', 'type' => 'text', 'required' => false],
    ['section' => $S18, 'name' => 'case_officer_signature', 'label' => 'Case Officer Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S18, 'name' => 'approved_by',            'label' => 'Approved By', 'type' => 'text', 'required' => false],
    ['section' => $S18, 'name' => 'approved_signature',     'label' => 'Approved By Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S18, 'name' => 'final_remarks',          'label' => 'Final Remarks', 'type' => 'textarea', 'required' => false],
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
        ['service_parent_id' => $parent->id, 'service_name' => 'Sublease Agreement Management'],
        ['model' => 'Sublease Agreement Management', 'type' => 'asset_management', 'is_active' => 1]
    );
    echo "  Child  : #{$child->id}  {$child->service_name}\n";

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'LAMS — Sublease Agreement Management Form',
            'description' => 'Property Department — End-to-end sublease lifecycle: application, due diligence, consent, drafting, execution, registration, monitoring, variation, renewal and termination.',
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
