<?php
/**
 * Seed the Property Department child service "Land Disposal & Management"
 * + its full LAMS Land Disposal & Management form template.
 *
 * Run:  php scripts/seed-property-land-disposal.php
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

$TENURE = ['State Land', 'Leasehold', 'Other'];

$TITLE_STATUS = ['Registered Title', 'Lease', 'Offer Letter', 'Other'];

$ENCUMBRANCES = [
    'Mortgage', 'Charge', 'Caveat', 'Easement', 'Wayleave',
    'Lease/Sublease', 'Restriction', 'Litigation', 'None', 'Other',
];

$LEGAL_STATUS = [
    'Clear for Disposal',
    'Subject to Encumbrance',
    'Subject to Court Proceedings',
    'Subject to Third-Party Interest',
    'Other',
];

$REASONS = [
    'Surplus Land', 'No Longer Required', 'Underutilised Land',
    'Commercialisation', 'Financial Requirement', 'Strategic Reorganisation',
    'Expired/Redundant Asset', 'Government/Institutional Policy',
    'Donation', 'Other',
];

$PHYSICAL_CONDITION = [
    'Developed', 'Partially Developed', 'Vacant', 'Agricultural',
    'Commercial', 'Residential', 'Industrial', 'Other',
];

$INFRASTRUCTURE = ['Road', 'Water', 'Electricity', 'Sewerage', 'Drainage', 'Other'];

$VALUATION_TYPE = ['Market Value', 'Forced Sale Value', 'Investment Value', 'Replacement Value', 'Other'];

$DISPOSAL_METHOD = [
    'Public Auction', 'Tender', 'Public Advertisement', 'Private Treaty',
    'Sale to Existing Occupier', 'Transfer to Another Government/Institutional Entity',
    'Exchange', 'Lease/Long-Term Disposal Interest', 'Other Approved Method',
];

$PAYMENT_STATUS = ['Pending', 'Partially Paid', 'Fully Paid'];

$STAMP_DUTY = ['Paid', 'Pending'];

$DISPOSAL_STATUS = [
    'Completed', 'Completed with Conditions', 'Cancelled', 'Suspended', 'Pending',
];

$DECISION_RECO = ['Recommended', 'Not Recommended'];
$DECISION_CLEAR = ['Cleared', 'Not Cleared'];
$DECISION_APPROVE = ['Approved', 'Not Approved'];
$DECISION_VERIFY = ['Verified', 'Not Verified'];
$DECISION_FIN = ['Financially Cleared', 'Not Cleared'];

$DOCUMENTS = [
    'Disposal Request', 'Title Deed/Lease', 'Survey Diagram', 'Site Plan',
    'GIS/Cadastral Map', 'Site Inspection Report', 'Valuation Report',
    'Planning Report', 'Environmental Assessment', 'Legal Due-Diligence Report',
    'Legal Opinion', 'Approval/Minutes', 'Tender/Auction Documentation',
    'Advertisement', 'Bid Evaluation Report', 'Letter of Award',
    'Agreement of Sale', 'Proof of Payment', 'Tax/Stamp Duty Documents',
    'Consent Documents', 'Deed of Transfer', 'Handover Certificate',
    'Updated Title/Registration Documents',
];

/* -------------------------------------------------------------------
 |  Sections
 |  ------------------------------------------------------------------- */
$S1  = '1. Disposal Application / Initiation';
$S2  = '2. Ownership and Legal Status';
$S3  = '3. Reason for Disposal';
$S4  = '4. Physical and Technical Assessment';
$S5  = '5. Valuation';
$S6  = '6. Planning and Land-Use Assessment';
$S7  = '7. Proposed Disposal Method';
$S8  = '8. Disposal Approval';
$S9  = '9. Disposal Transaction Management';
$S10 = '10. Conveyancing and Transfer';
$S11 = '11. Handover and Closure';
$S12 = '12. Documents Checklist';

$fields = [
    // ---- 1. Initiation ----
    ['section' => $S1, 'name' => 'disposal_reference_no',    'label' => 'Disposal Reference No.', 'type' => 'text', 'required' => true],
    ['section' => $S1, 'name' => 'date_of_application',      'label' => 'Date of Application', 'type' => 'date', 'required' => true],
    ['section' => $S1, 'name' => 'disposing_institution',    'label' => 'Disposing Institution/Entity', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'department_unit',          'label' => 'Department/Unit', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'officer_responsible',      'label' => 'Officer Responsible', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'officer_designation',      'label' => 'Designation', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'officer_contact',          'label' => 'Contact', 'type' => 'text', 'required' => false],

    // 1A. Property Identification
    ['section' => $S1, 'name' => 'property_asset_id',        'label' => 'Property/Asset ID', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'title_lease_no',           'label' => 'Title/Lease No.', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'property_description',     'label' => 'Property Description', 'type' => 'textarea', 'required' => false],
    ['section' => $S1, 'name' => 'plot_stand_no',            'label' => 'Plot/Stand No.', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'scheme_plan_no',           'label' => 'Scheme/Plan No.', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'location',                 'label' => 'Location', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'district',                 'label' => 'District', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'province',                 'label' => 'Province', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'tenure',                   'label' => 'Tenure', 'type' => 'select', 'options' => $TENURE, 'required' => false],
    ['section' => $S1, 'name' => 'land_size',                'label' => 'Land Size', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'current_land_use',         'label' => 'Current Land Use', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'proposed_land_use',        'label' => 'Proposed Land Use', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'gps_coordinates',          'label' => 'GPS Coordinates', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'cadastral_reference',      'label' => 'Cadastral Reference', 'type' => 'text', 'required' => false],

    // ---- 2. Ownership & Legal ----
    ['section' => $S2, 'name' => 'registered_owner',         'label' => 'Registered Owner', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'title_status',             'label' => 'Title Status', 'type' => 'select', 'options' => $TITLE_STATUS, 'required' => false],
    ['section' => $S2, 'name' => 'encumbrances',             'label' => 'Encumbrances/Interests', 'type' => 'checkbox', 'options' => $ENCUMBRANCES, 'required' => false],
    ['section' => $S2, 'name' => 'encumbrances_other',       'label' => 'If Other encumbrance, specify', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'legal_status',             'label' => 'Legal Status', 'type' => 'select', 'options' => $LEGAL_STATUS, 'required' => false],
    ['section' => $S2, 'name' => 'legal_opinion_required',   'label' => 'Legal Opinion Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S2, 'name' => 'legal_comments',           'label' => 'Legal Comments', 'type' => 'textarea', 'required' => false],
    ['section' => $S2, 'name' => 'legal_documents',          'label' => 'Attach Legal/Title Documents', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 3. Reason ----
    ['section' => $S3, 'name' => 'reasons_for_disposal',     'label' => 'Reason for Disposal', 'type' => 'checkbox', 'options' => $REASONS, 'required' => true],
    ['section' => $S3, 'name' => 'reason_other',             'label' => 'If Other reason, specify', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'justification',            'label' => 'Detailed Justification', 'type' => 'textarea', 'required' => true],
    ['section' => $S3, 'name' => 'expected_benefits',        'label' => 'Benefits/Expected Outcome', 'type' => 'textarea', 'required' => false],

    // ---- 4. Physical / Technical ----
    ['section' => $S4, 'name' => 'inspection_date',          'label' => 'Inspection Date', 'type' => 'date', 'required' => false],
    ['section' => $S4, 'name' => 'inspection_team',          'label' => 'Inspection Team', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'physical_condition',       'label' => 'Physical Condition', 'type' => 'checkbox', 'options' => $PHYSICAL_CONDITION, 'required' => false],
    ['section' => $S4, 'name' => 'physical_other',           'label' => 'If Other condition, specify', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'existing_improvements',    'label' => 'Existing Improvements', 'type' => 'textarea', 'required' => false],
    ['section' => $S4, 'name' => 'buildings_structures',     'label' => 'Buildings/Structures', 'type' => 'textarea', 'required' => false],
    ['section' => $S4, 'name' => 'infrastructure_available', 'label' => 'Infrastructure Available', 'type' => 'checkbox', 'options' => $INFRASTRUCTURE, 'required' => false],
    ['section' => $S4, 'name' => 'encroachment',             'label' => 'Encroachment', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S4, 'name' => 'environmental_issues',     'label' => 'Environmental Issues', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S4, 'name' => 'planning_issues',          'label' => 'Planning Issues', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S4, 'name' => 'survey_issues',            'label' => 'Survey Issues', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S4, 'name' => 'technical_recommendation', 'label' => 'Technical Recommendation', 'type' => 'textarea', 'required' => false],
    ['section' => $S4, 'name' => 'inspection_report_file',   'label' => 'Attach Site Inspection Report', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 5. Valuation ----
    ['section' => $S5, 'name' => 'valuation_required',       'label' => 'Valuation Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S5, 'name' => 'valuation_type',           'label' => 'Valuation Type', 'type' => 'select', 'options' => $VALUATION_TYPE, 'required' => false],
    ['section' => $S5, 'name' => 'valuer',                   'label' => 'Valuer', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'valuation_date',           'label' => 'Valuation Date', 'type' => 'date', 'required' => false],
    ['section' => $S5, 'name' => 'market_value',             'label' => 'Market Value', 'type' => 'number', 'required' => false],
    ['section' => $S5, 'name' => 'recommended_disposal_price','label' => 'Recommended Disposal Price', 'type' => 'number', 'required' => false],
    ['section' => $S5, 'name' => 'valuation_report_ref',     'label' => 'Valuation Report Reference', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'valuation_report_file',    'label' => 'Attach Valuation Report', 'type' => 'file', 'required' => false, 'allow_attachment' => true],

    // ---- 6. Planning / Land Use ----
    ['section' => $S6, 'name' => 'current_zoning',           'label' => 'Current Zoning', 'type' => 'text', 'required' => false],
    ['section' => $S6, 'name' => 'permitted_use',            'label' => 'Permitted Use', 'type' => 'text', 'required' => false],
    ['section' => $S6, 'name' => 'planning_restrictions',    'label' => 'Planning Restrictions', 'type' => 'textarea', 'required' => false],
    ['section' => $S6, 'name' => 'subdivision_required',     'label' => 'Subdivision Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S6, 'name' => 'change_of_use_required',   'label' => 'Change of Land Use Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S6, 'name' => 'planning_approval_required','label' => 'Planning Approval Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S6, 'name' => 'planning_comments',        'label' => 'Planning Comments', 'type' => 'textarea', 'required' => false],

    // ---- 7. Proposed Method ----
    ['section' => $S7, 'name' => 'disposal_method',          'label' => 'Proposed Disposal Method', 'type' => 'checkbox', 'options' => $DISPOSAL_METHOD, 'required' => true],
    ['section' => $S7, 'name' => 'method_justification',     'label' => 'Method Justification', 'type' => 'textarea', 'required' => false],
    ['section' => $S7, 'name' => 'minimum_acceptable_price', 'label' => 'Minimum Acceptable Price', 'type' => 'number', 'required' => false],
    ['section' => $S7, 'name' => 'reserve_price',            'label' => 'Reserve Price', 'type' => 'number', 'required' => false],

    // ---- 8. Approvals ----
    // Asset/Property Management
    ['section' => $S8, 'name' => 'asset_mgmt_decision',      'label' => 'Asset/Property Management Decision', 'type' => 'select', 'options' => $DECISION_RECO, 'required' => false],
    ['section' => $S8, 'name' => 'asset_mgmt_name',          'label' => 'Asset/Property Management — Name', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'asset_mgmt_signature',     'label' => 'Asset/Property Management — Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S8, 'name' => 'asset_mgmt_date',          'label' => 'Asset/Property Management — Date', 'type' => 'date', 'required' => false],
    // Finance
    ['section' => $S8, 'name' => 'finance_decision',         'label' => 'Finance Decision', 'type' => 'select', 'options' => $DECISION_FIN, 'required' => false],
    ['section' => $S8, 'name' => 'finance_name',             'label' => 'Finance — Name', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'finance_signature',        'label' => 'Finance — Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S8, 'name' => 'finance_date',             'label' => 'Finance — Date', 'type' => 'date', 'required' => false],
    // Planning
    ['section' => $S8, 'name' => 'planning_decision',        'label' => 'Planning Decision', 'type' => 'select', 'options' => $DECISION_APPROVE, 'required' => false],
    ['section' => $S8, 'name' => 'planning_name',            'label' => 'Planning — Name', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'planning_signature',       'label' => 'Planning — Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S8, 'name' => 'planning_date',            'label' => 'Planning — Date', 'type' => 'date', 'required' => false],
    // Survey
    ['section' => $S8, 'name' => 'survey_decision',          'label' => 'Survey Decision', 'type' => 'select', 'options' => $DECISION_VERIFY, 'required' => false],
    ['section' => $S8, 'name' => 'survey_name',              'label' => 'Survey — Name', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'survey_signature',         'label' => 'Survey — Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S8, 'name' => 'survey_date',              'label' => 'Survey — Date', 'type' => 'date', 'required' => false],
    // Legal
    ['section' => $S8, 'name' => 'legal_decision',           'label' => 'Legal Decision', 'type' => 'select', 'options' => $DECISION_CLEAR, 'required' => false],
    ['section' => $S8, 'name' => 'legal_name',               'label' => 'Legal — Name', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'legal_signature',          'label' => 'Legal — Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S8, 'name' => 'legal_date',               'label' => 'Legal — Date', 'type' => 'date', 'required' => false],
    // Management Approval
    ['section' => $S8, 'name' => 'recommended_by',           'label' => 'Recommended By', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'approved_by',              'label' => 'Approved By', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'management_approval_date', 'label' => 'Approval Date', 'type' => 'date', 'required' => false],
    ['section' => $S8, 'name' => 'management_signature',     'label' => 'Management Approval Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S8, 'name' => 'approval_conditions',      'label' => 'Approval Conditions', 'type' => 'textarea', 'required' => false],

    // ---- 9. Transaction ----
    ['section' => $S9, 'name' => 'advert_reference',         'label' => 'Advertisement/Tender Reference', 'type' => 'text', 'required' => false],
    ['section' => $S9, 'name' => 'advertisement_date',       'label' => 'Advertisement Date', 'type' => 'date', 'required' => false],
    ['section' => $S9, 'name' => 'closing_date',             'label' => 'Closing Date', 'type' => 'date', 'required' => false],
    ['section' => $S9, 'name' => 'number_of_bidders',        'label' => 'Number of Bidders/Applicants', 'type' => 'number', 'required' => false],
    ['section' => $S9, 'name' => 'successful_purchaser',     'label' => 'Successful Purchaser', 'type' => 'text', 'required' => false],
    ['section' => $S9, 'name' => 'purchaser_registration_no','label' => 'National/Company Registration No.', 'type' => 'text', 'required' => false],
    ['section' => $S9, 'name' => 'purchase_price',           'label' => 'Purchase Price', 'type' => 'number', 'required' => false],
    ['section' => $S9, 'name' => 'deposit_paid',             'label' => 'Deposit Paid', 'type' => 'number', 'required' => false],
    ['section' => $S9, 'name' => 'balance',                  'label' => 'Balance', 'type' => 'number', 'required' => false],
    ['section' => $S9, 'name' => 'payment_status',           'label' => 'Payment Status', 'type' => 'select', 'options' => $PAYMENT_STATUS, 'required' => false],
    ['section' => $S9, 'name' => 'payment_completion_date',  'label' => 'Payment Completion Date', 'type' => 'date', 'required' => false],
    ['section' => $S9, 'name' => 'bid_evaluation_file',      'label' => 'Attach Bid Evaluation / Award Documents', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 10. Conveyancing ----
    ['section' => $S10, 'name' => 'sale_agreement_prepared', 'label' => 'Agreement of Sale Prepared', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S10, 'name' => 'sale_agreement_signed',   'label' => 'Agreement Signed', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S10, 'name' => 'stamp_duty_status',       'label' => 'Stamp Duty/Taxes', 'type' => 'select', 'options' => $STAMP_DUTY, 'required' => false],
    ['section' => $S10, 'name' => 'consent_required',        'label' => 'Consent/Approval Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S10, 'name' => 'consent_obtained',        'label' => 'Consent Obtained', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S10, 'name' => 'transfer_deed_prepared',  'label' => 'Deed of Transfer Prepared', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S10, 'name' => 'transfer_lodged',         'label' => 'Transfer Lodged', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S10, 'name' => 'transfer_registered',     'label' => 'Transfer Registered', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S10, 'name' => 'new_owner',               'label' => 'New Owner', 'type' => 'text', 'required' => false],
    ['section' => $S10, 'name' => 'new_title_no',            'label' => 'New Title/Registration No.', 'type' => 'text', 'required' => false],
    ['section' => $S10, 'name' => 'registration_date',       'label' => 'Date of Registration', 'type' => 'date', 'required' => false],
    ['section' => $S10, 'name' => 'transfer_documents_file', 'label' => 'Attach Transfer / Consent Documents', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 11. Handover / Closure ----
    ['section' => $S11, 'name' => 'handover_date',           'label' => 'Property Handover Date', 'type' => 'date', 'required' => false],
    ['section' => $S11, 'name' => 'handover_certificate',    'label' => 'Attach Handover Certificate', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S11, 'name' => 'keys_docs_handed_over',   'label' => 'Keys/Documents Handed Over', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'outstanding_obligations', 'label' => 'Outstanding Obligations', 'type' => 'textarea', 'required' => false],
    ['section' => $S11, 'name' => 'financial_recon_done',    'label' => 'Final Financial Reconciliation Completed', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'asset_register_updated',  'label' => 'Asset Register Updated', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'land_register_updated',   'label' => 'Land Register Updated', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'gis_updated',             'label' => 'GIS/Cadastral Database Updated', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'title_records_updated',   'label' => 'Title/Ownership Records Updated', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'disposal_status',         'label' => 'Disposal Status', 'type' => 'select', 'options' => $DISPOSAL_STATUS, 'required' => true],
    ['section' => $S11, 'name' => 'closure_officer',         'label' => 'Closure Officer', 'type' => 'text', 'required' => false],
    ['section' => $S11, 'name' => 'closure_date',            'label' => 'Closure Date', 'type' => 'date', 'required' => false],
    ['section' => $S11, 'name' => 'closure_signature',       'label' => 'Closure Officer Signature', 'type' => 'signature', 'required' => false],

    // ---- 12. Documents ----
    ['section' => $S12, 'name' => 'documents_checklist',     'label' => 'Documents Attached', 'type' => 'checkbox', 'options' => $DOCUMENTS, 'required' => false],
    ['section' => $S12, 'name' => 'documents_files',         'label' => 'Upload Supporting Documents', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],
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
        ['service_parent_id' => $parent->id, 'service_name' => 'Land Disposal & Management'],
        ['model' => 'Land Disposal & Management', 'type' => 'asset_management', 'is_active' => 1]
    );
    echo "  Child  : #{$child->id}  {$child->service_name}\n";

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'LAMS — Land Disposal & Management Form',
            'description' => 'Property Department — Full disposal lifecycle: initiation, legal & technical assessment, valuation, planning, approvals, disposal transaction, conveyancing, handover and closure.',
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
