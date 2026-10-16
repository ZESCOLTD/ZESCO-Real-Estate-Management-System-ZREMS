<?php
/**
 * Seed the Security Department child service "Encroachment Inspection & Assessment"
 * + its full LAMS Encroachment Report & Assessment form template.
 *
 * Run:  php scripts/seed-security-encroachment.php
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
$CASE_TYPE = ['New Case', 'Follow-up', 'Complaint', 'Routine Inspection'];

$LAND_USE = ['Residential', 'Commercial', 'Industrial', 'Agricultural', 'Institutional', 'Other'];

$ENCROACH_NATURE = [
    'Building/Structure', 'Fence/Wall', 'Road Reserve', 'Wayleave',
    'Boundary Encroachment', 'Drainage/Watercourse', 'Access Road',
    'Utility Infrastructure', 'Neighbouring Property', 'Public/State Land', 'Other',
];

$YES_NO = ['Yes', 'No'];

$DEV_STATUS = ['Proposed', 'Foundation', 'Partially Constructed', 'Completed'];

$EFFECT = [
    'Affects Neighboring Property', 'Affects Road Reserve', 'Affects Wayleave',
    'Affects Public Land', 'Affects Access', 'Affects Utility Infrastructure', 'Other',
];

$COMPLIANCE_STATUS = ['Complied', 'Partially Complied', 'Not Complied', 'Disputed', 'Under Review'];

$RECOMMENDED_ACTIONS = [
    'No Encroachment Confirmed', 'Issue Notice to Remove Encroachment',
    'Issue Compliance Notice', 'Refer to Legal Department',
    'Refer to Surveyor General', 'Refer to Commissioner of Lands',
    'Refer to Survey Department', 'Refer to Planning Department',
    'Refer to Local Authority', 'Refer to Utility/Wayleave Authority',
    'Recommend Boundary Rectification', 'Recommend Regularisation',
    'Recommend Further Investigation', 'Other',
];

$REFERRED_TO = ['Legal', 'Survey', 'Planning', 'Valuation', 'Lands Administration', 'Local Authority', 'Other'];

$CASE_OUTCOME = [
    'Closed – No Encroachment', 'Closed – Encroachment Removed',
    'Regularised/Approved', 'Referred for Legal Action',
    'Enforcement Action Required', 'Further Investigation Required', 'Other',
];

$ATTACHMENTS = [
    'Survey Plan', 'Site Plan', 'Title Deed Copy', 'Photographs',
    'Inspection Report', 'Notice', 'Correspondence',
    'Approved Plans', 'GPS/Coordinate Report', 'Other Supporting Documents',
];

$LAMS_STATUS = [
    'Draft', 'Submitted', 'Under Inspection', 'Under Review',
    'Notice Issued', 'Referred', 'Enforcement', 'Closed',
];

/* -------------------------------------------------------------------
 |  Document verification repeater
 |  ------------------------------------------------------------------- */
$DOC_VERIFICATION_TABLE = [
    ['name' => 'document',  'label' => 'Document',  'type' => 'text'],
    ['name' => 'available', 'label' => 'Available', 'type' => 'select', 'options' => $YES_NO],
    ['name' => 'verified',  'label' => 'Verified',  'type' => 'select', 'options' => $YES_NO],
    ['name' => 'remarks',   'label' => 'Remarks',   'type' => 'text'],
    'default_rows' => [
        ['document' => 'Title Deed',              'available' => 'No', 'verified' => 'No', 'remarks' => ''],
        ['document' => 'Offer Letter',            'available' => 'No', 'verified' => 'No', 'remarks' => ''],
        ['document' => 'Site Plan',               'available' => 'No', 'verified' => 'No', 'remarks' => ''],
        ['document' => 'Survey Diagram',          'available' => 'No', 'verified' => 'No', 'remarks' => ''],
        ['document' => 'Approved Building Plan',  'available' => 'No', 'verified' => 'No', 'remarks' => ''],
        ['document' => 'Lease/Agreement',         'available' => 'No', 'verified' => 'No', 'remarks' => ''],
        ['document' => 'Wayleave/Consent',        'available' => 'No', 'verified' => 'No', 'remarks' => ''],
        ['document' => 'Other',                   'available' => 'No', 'verified' => 'No', 'remarks' => ''],
    ],
];

/* -------------------------------------------------------------------
 |  Fields
 |  ------------------------------------------------------------------- */
$S1  = '1. General Information';
$S2  = '2. Property Information';
$S3  = '3. Encroachment Information';
$S4  = '4. Survey & Technical Assessment';
$S5  = '5. Document Verification';
$S6  = '6. Site Inspection Findings';
$S7  = '7. Legal / Compliance Assessment';
$S8  = '8. Recommended Action';
$S9  = '9. Notice & Compliance Tracking';
$S10 = '10. Referral / Escalation';
$S11 = '11. Final Decision';
$S12 = '12. Attachments';
$S13 = '13. Approval & Sign-Off';
$S14 = 'LAMS Status';

$fields = [
    // ---- 1. General Information ----
    ['section' => $S1, 'name' => 'encroachment_reference_no', 'label' => 'Encroachment Reference No.', 'type' => 'text', 'required' => true],
    ['section' => $S1, 'name' => 'date_reported',             'label' => 'Date Reported', 'type' => 'date', 'required' => true],
    ['section' => $S1, 'name' => 'date_of_inspection',        'label' => 'Date of Inspection', 'type' => 'date', 'required' => false],
    ['section' => $S1, 'name' => 'responsible_unit',          'label' => 'Responsible Unit/Department', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'officer_inspector',         'label' => 'Officer/Inspector', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'case_type',                 'label' => 'Case Type', 'type' => 'select', 'options' => $CASE_TYPE, 'required' => true],

    // ---- 2. Property Information ----
    ['section' => $S2, 'name' => 'property_plot_no',    'label' => 'Property/Plot No.', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'title_deed_no',       'label' => 'Title Deed No.', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'parcel_survey_no',    'label' => 'Parcel/Survey No.', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'property_address',    'label' => 'Property Address/Location', 'type' => 'textarea', 'required' => false],
    ['section' => $S2, 'name' => 'property_area',       'label' => 'Area of Property', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'land_use',            'label' => 'Land Use', 'type' => 'checkbox', 'options' => $LAND_USE, 'required' => false],
    ['section' => $S2, 'name' => 'land_use_other',      'label' => 'If Other, specify', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'registered_owner',    'label' => 'Registered Owner', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'occupier_developer', 'label' => 'Occupier/Developer', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'contact_details',     'label' => 'Contact Details', 'type' => 'textarea', 'required' => false],

    // ---- 3. Encroachment Information ----
    ['section' => $S3, 'name' => 'encroachment_nature',       'label' => 'Nature of Encroachment', 'type' => 'checkbox', 'options' => $ENCROACH_NATURE, 'required' => true],
    ['section' => $S3, 'name' => 'encroachment_nature_other', 'label' => 'If Other, specify', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'encroachment_description',  'label' => 'Description of Encroachment', 'type' => 'textarea', 'required' => true],
    ['section' => $S3, 'name' => 'encroached_area',           'label' => 'Estimated Encroached Area', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'encroached_area_unit',      'label' => 'Area Unit', 'type' => 'select', 'options' => ['m²', 'ha'], 'required' => false],
    ['section' => $S3, 'name' => 'encroachment_extent_m',     'label' => 'Estimated Distance/Extent (metres)', 'type' => 'number', 'required' => false],
    ['section' => $S3, 'name' => 'encroachment_start_date',   'label' => 'Date Encroachment Commenced (if known)', 'type' => 'date', 'required' => false],

    // ---- 4. Survey & Technical Assessment ----
    ['section' => $S4, 'name' => 'survey_conducted',      'label' => 'Survey Conducted', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S4, 'name' => 'survey_reference',      'label' => 'Survey Reference/Job No.', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'northing',              'label' => 'Northing', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'easting',               'label' => 'Easting', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'boundary_confirmed',    'label' => 'Approved Boundary Confirmed', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S4, 'name' => 'survey_plan_attached',  'label' => 'Survey Plan Attached', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S4, 'name' => 'survey_plan_file',      'label' => 'Attach Survey Plan', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S4, 'name' => 'extent_confirmed',      'label' => 'Extent of Encroachment Confirmed', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S4, 'name' => 'measured_encroachment', 'label' => 'Measured Encroachment', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'affected_boundary_file','label' => 'Affected Boundary/Feature (attach)', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],
    ['section' => $S4, 'name' => 'surveyor_findings',     'label' => 'Surveyor\'s Findings', 'type' => 'textarea', 'required' => false],

    // ---- 5. Document Verification ----
    ['section' => $S5, 'name' => 'doc_verification_table', 'label' => 'Document Verification Checklist', 'type' => 'table', 'required' => false, 'options' => $DOC_VERIFICATION_TABLE],

    // ---- 6. Site Inspection Findings ----
    ['section' => $S6, 'name' => 'existing_development',       'label' => 'Existing Development', 'type' => 'textarea', 'required' => false],
    ['section' => $S6, 'name' => 'development_status',         'label' => 'Development Status', 'type' => 'select', 'options' => $DEV_STATUS, 'required' => false],
    ['section' => $S6, 'name' => 'responsible_entity',         'label' => 'Person/Entity Responsible for Encroachment', 'type' => 'text', 'required' => false],
    ['section' => $S6, 'name' => 'effect_of_encroachment',     'label' => 'Effect of Encroachment', 'type' => 'checkbox', 'options' => $EFFECT, 'required' => false],
    ['section' => $S6, 'name' => 'effect_other',               'label' => 'If Other effect, specify', 'type' => 'text', 'required' => false],
    ['section' => $S6, 'name' => 'photographs_taken',          'label' => 'Photographs Taken', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S6, 'name' => 'number_of_photographs',      'label' => 'Number of Photographs', 'type' => 'number', 'required' => false],
    ['section' => $S6, 'name' => 'inspection_photos',          'label' => 'Attach Photographs', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 7. Legal / Compliance Assessment ----
    ['section' => $S7, 'name' => 'legal_review_required',   'label' => 'Legal/Regulatory Review Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S7, 'name' => 'applicable_law',          'label' => 'Applicable Law/Regulation/Condition', 'type' => 'textarea', 'required' => false],
    ['section' => $S7, 'name' => 'nature_of_noncompliance', 'label' => 'Nature of Non-compliance', 'type' => 'textarea', 'required' => false],
    ['section' => $S7, 'name' => 'previous_notice_issued',  'label' => 'Previous Notice Issued', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S7, 'name' => 'notice_reference_no',     'label' => 'Notice Reference No.', 'type' => 'text', 'required' => false],
    ['section' => $S7, 'name' => 'notice_date_issued',      'label' => 'Notice Date Issued', 'type' => 'date', 'required' => false],

    // ---- 8. Recommended Action ----
    ['section' => $S8, 'name' => 'recommended_actions',   'label' => 'Recommended Actions', 'type' => 'checkbox', 'options' => $RECOMMENDED_ACTIONS, 'required' => false],
    ['section' => $S8, 'name' => 'recommended_action_other','label' => 'If Other, specify', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'recommendation_remarks','label' => 'Recommended Action / Remarks', 'type' => 'textarea', 'required' => false],

    // ---- 9. Notice & Compliance Tracking ----
    ['section' => $S9, 'name' => 'notice_type',            'label' => 'Notice Type', 'type' => 'text', 'required' => false],
    ['section' => $S9, 'name' => 'notice_no',              'label' => 'Notice No.', 'type' => 'text', 'required' => false],
    ['section' => $S9, 'name' => 'notice_date_served',     'label' => 'Date Served', 'type' => 'date', 'required' => false],
    ['section' => $S9, 'name' => 'method_of_service',      'label' => 'Method of Service', 'type' => 'text', 'required' => false],
    ['section' => $S9, 'name' => 'compliance_deadline',    'label' => 'Compliance Deadline', 'type' => 'date', 'required' => false],
    ['section' => $S9, 'name' => 'compliance_status',      'label' => 'Compliance Status', 'type' => 'select', 'options' => $COMPLIANCE_STATUS, 'required' => false],
    ['section' => $S9, 'name' => 'followup_inspection_date','label' => 'Follow-up Inspection Date', 'type' => 'date', 'required' => false],
    ['section' => $S9, 'name' => 'followup_findings',      'label' => 'Follow-up Findings', 'type' => 'textarea', 'required' => false],

    // ---- 10. Referral / Escalation ----
    ['section' => $S10, 'name' => 'referred_to',         'label' => 'Referred To', 'type' => 'checkbox', 'options' => $REFERRED_TO, 'required' => false],
    ['section' => $S10, 'name' => 'referred_to_other',   'label' => 'If Other, specify', 'type' => 'text', 'required' => false],
    ['section' => $S10, 'name' => 'referral_date',       'label' => 'Referral Date', 'type' => 'date', 'required' => false],
    ['section' => $S10, 'name' => 'reason_for_referral', 'label' => 'Reason for Referral', 'type' => 'textarea', 'required' => false],
    ['section' => $S10, 'name' => 'receiving_unit_action','label' => 'Action/Decision by Receiving Unit', 'type' => 'textarea', 'required' => false],

    // ---- 11. Final Decision ----
    ['section' => $S11, 'name' => 'case_outcome',       'label' => 'Case Outcome', 'type' => 'select', 'options' => $CASE_OUTCOME, 'required' => false],
    ['section' => $S11, 'name' => 'case_outcome_other', 'label' => 'If Other outcome, specify', 'type' => 'text', 'required' => false],
    ['section' => $S11, 'name' => 'final_remarks',      'label' => 'Final Remarks', 'type' => 'textarea', 'required' => false],

    // ---- 12. Attachments ----
    ['section' => $S12, 'name' => 'attachments_checklist','label' => 'Attached Documents', 'type' => 'checkbox', 'options' => $ATTACHMENTS, 'required' => false],
    ['section' => $S12, 'name' => 'attachments_files',    'label' => 'Attach Supporting Documents', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 13. Approval & Sign-Off ----
    ['section' => $S13, 'name' => 'prepared_by',           'label' => 'Prepared By', 'type' => 'text', 'required' => false],
    ['section' => $S13, 'name' => 'prepared_designation',  'label' => 'Prepared By — Designation', 'type' => 'text', 'required' => false],
    ['section' => $S13, 'name' => 'prepared_signature',    'label' => 'Prepared By — Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S13, 'name' => 'prepared_date',         'label' => 'Prepared By — Date', 'type' => 'date', 'required' => false],
    ['section' => $S13, 'name' => 'checked_by',            'label' => 'Checked/Verified By', 'type' => 'text', 'required' => false],
    ['section' => $S13, 'name' => 'checked_designation',   'label' => 'Checked By — Designation', 'type' => 'text', 'required' => false],
    ['section' => $S13, 'name' => 'checked_signature',     'label' => 'Checked By — Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S13, 'name' => 'checked_date',          'label' => 'Checked By — Date', 'type' => 'date', 'required' => false],
    ['section' => $S13, 'name' => 'approved_by',           'label' => 'Approved By', 'type' => 'text', 'required' => false],
    ['section' => $S13, 'name' => 'approved_designation',  'label' => 'Approved By — Designation', 'type' => 'text', 'required' => false],
    ['section' => $S13, 'name' => 'approved_signature',    'label' => 'Approved By — Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S13, 'name' => 'approved_date',         'label' => 'Approved By — Date', 'type' => 'date', 'required' => false],

    // ---- LAMS Status ----
    ['section' => $S14, 'name' => 'case_status',   'label' => 'Case Status', 'type' => 'select', 'options' => $LAMS_STATUS, 'required' => true],
    ['section' => $S14, 'name' => 'lams_case_id',  'label' => 'LAMS Case ID', 'type' => 'text', 'required' => false],
];

/* -------------------------------------------------------------------
 |  Persist
 |  ------------------------------------------------------------------- */
DB::transaction(function () use ($fields) {

    $parent = ServiceParent::where('department_code', 'security_services')
        ->orWhere('department_name', 'Security Department')
        ->first();

    if (!$parent) {
        echo "  ERROR: Security Department parent not found.\n";
        return;
    }
    echo "  Parent : #{$parent->id}  {$parent->department_name} / {$parent->service_name}\n";

    $child = ServiceChild::updateOrCreate(
        ['service_parent_id' => $parent->id, 'service_name' => 'Encroachment Inspection & Assessment'],
        ['model' => 'Encroachment Inspection & Assessment', 'type' => 'asset_management', 'is_active' => 1]
    );
    echo "  Child  : #{$child->id}  {$child->service_name}\n";

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'LAMS — Encroachment Report & Assessment Form',
            'description' => 'Security Department — Encroachment inspection, technical/legal assessment, notice tracking, referral and closure.',
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
