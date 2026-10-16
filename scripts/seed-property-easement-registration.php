<?php
/**
 * Seed the Property Department child service "Easement Verification and Registration"
 * + its full form template.
 *
 * Run:  php scripts/seed-property-easement-registration.php
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
$APPLICATION_TYPE = ['New Easement', 'Renewal', 'Variation', 'Surrender'];

$APPLICANT_CAPACITY = [
    'Registered Proprietor', 'Lessee', 'Developer',
    'Utility/Service Provider', 'Local Authority', 'Other',
];

$LAND_TENURE = ['State Land', 'Other'];

$EASEMENT_PURPOSE = [
    'Electricity/Power Line Wayleave', 'Access Road/Right of Way',
    'Water Pipeline', 'Sewerage', 'Drainage', 'Telecommunications',
    'Gas/Petroleum Pipeline', 'Other Utility', 'Other',
];

$YES_NO = ['Yes', 'No'];

$DOC_CHECK = ['Complete', 'Incomplete'];

$VERIFICATION_OUTCOME = ['Satisfactory', 'Further information required', 'Rejected'];

$SURVEY_PLAN_ITEMS = [
    'Boundaries of the affected property',
    'Easement corridor',
    'Dimensions',
    'Coordinates',
    'Access points',
    'Existing developments',
    'Relevant adjoining properties',
];

$REVIEW_RECOMMENDATION = [
    'Approved for registration', 'Amendment required',
    'Further documentation required', 'Not recommended',
];

$APPROVAL_DECISION = [
    'Approved', 'Approved subject to conditions', 'Deferred', 'Rejected',
];

$FINAL_STATUS = [
    'Registered', 'Pending', 'Returned for Correction', 'Rejected', 'Withdrawn',
];

$REQUIRED_DOCS = [
    'Completed Easement Registration Application Form',
    'Copy of Certificate of Title/Lease',
    'Copy of National Registration Card or certificate of incorporation',
    'Consent of registered proprietor',
    'Survey plan/site plan showing the proposed easement',
    'Coordinates of the easement',
    'Approved subdivision plan',
    'Development/building approval',
    'Wayleave/utility corridor plan',
    'Valuation report',
    'Proof of payment of prescribed fees',
    'Environmental approval/assessment',
    'Resolution/authority to apply',
    'Other supporting documentation',
];

$ISSUED_DOCS = [
    'Registered Easement Instrument',
    'Updated Certificate of Title/Endorsement',
    'Approved Easement Plan',
    'Registration Certificate/Confirmation',
    'Other',
];

/* -------------------------------------------------------------------
 |  Fields
 |  ------------------------------------------------------------------- */
$S0 = '0. Reference & Application Type';
$S1 = '1. Applicant Details';
$S2 = '2. Land Parcel Details';
$S3 = '3. Easement Details';
$S4 = '4. Documents to Accompany Application';
$S5 = '5. Step 1 — Application Submission';
$S6 = '5. Step 2 — Document Verification';
$S7 = '5. Step 3 — Survey and Easement Plan';
$S8 = '5. Step 4 — Consent and Agreement';
$S9 = '5. Step 5 — Technical and Legal Review';
$S10 = '5. Step 6 — Approval';
$S11 = '5. Step 7 — Registration';
$S12 = '5. Step 8 — Issue of Registered Documents';
$S13 = '7. Official Use Only';
$S14 = '8. Applicant Declaration';

$fields = [
    // ---- 0. Reference & Application Type ----
    ['section' => $S0, 'name' => 'reference_no',      'label' => 'Reference No.', 'type' => 'text', 'required' => true],
    ['section' => $S0, 'name' => 'date_received',     'label' => 'Date Received', 'type' => 'date', 'required' => false],
    ['section' => $S0, 'name' => 'application_type',  'label' => 'Application Type', 'type' => 'select', 'options' => $APPLICATION_TYPE, 'required' => true],
    ['section' => $S0, 'name' => 'property_title_no', 'label' => 'Property/Title No.', 'type' => 'text', 'required' => false],

    // ---- 1. Applicant Details ----
    ['section' => $S1, 'name' => 'applicant_name',         'label' => 'Name of Applicant/Institution', 'type' => 'text', 'required' => true],
    ['section' => $S1, 'name' => 'applicant_postal',       'label' => 'Postal Address', 'type' => 'textarea', 'required' => false],
    ['section' => $S1, 'name' => 'applicant_physical',     'label' => 'Physical Address', 'type' => 'textarea', 'required' => false],
    ['section' => $S1, 'name' => 'applicant_telephone',    'label' => 'Telephone', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'applicant_email',        'label' => 'Email', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'applicant_contact_person','label' => 'Contact Person', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'applicant_capacity',     'label' => 'Capacity', 'type' => 'select', 'options' => $APPLICANT_CAPACITY, 'required' => true],
    ['section' => $S1, 'name' => 'applicant_capacity_other','label' => 'If Other, specify', 'type' => 'text', 'required' => false],

    // ---- 2. Land Parcel Details ----
    ['section' => $S2, 'name' => 'parcel_title_no',   'label' => 'Property/Title Number', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'parcel_plot_no',    'label' => 'Plot/Stand/Lot Number', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'parcel_location',   'label' => 'Location', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'parcel_district',   'label' => 'District', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'parcel_province',   'label' => 'Province', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'parcel_area',       'label' => 'Area of Property', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'parcel_area_unit',  'label' => 'Area Unit', 'type' => 'select', 'options' => ['hectares','square metres'], 'required' => false],
    ['section' => $S2, 'name' => 'parcel_registered_owner', 'label' => 'Registered Owner', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'parcel_tenure',     'label' => 'Nature of Tenure', 'type' => 'select', 'options' => $LAND_TENURE, 'required' => false],
    ['section' => $S2, 'name' => 'parcel_tenure_other','label' => 'If Other tenure, specify', 'type' => 'text', 'required' => false],

    // ---- 3. Easement Details ----
    ['section' => $S3, 'name' => 'easement_purpose',       'label' => 'Type/Purpose of Easement', 'type' => 'checkbox', 'options' => $EASEMENT_PURPOSE, 'required' => true],
    ['section' => $S3, 'name' => 'easement_purpose_other', 'label' => 'If Other, specify', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'easement_description',   'label' => 'Description of Proposed Easement', 'type' => 'textarea', 'required' => true],
    ['section' => $S3, 'name' => 'easement_length_m',      'label' => 'Approximate Length (metres)', 'type' => 'number', 'required' => false],
    ['section' => $S3, 'name' => 'easement_width_m',       'label' => 'Proposed Width (metres)', 'type' => 'number', 'required' => false],
    ['section' => $S3, 'name' => 'easement_area',          'label' => 'Approximate Area Affected', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'easement_area_unit',     'label' => 'Area Unit', 'type' => 'select', 'options' => ['hectares','square metres'], 'required' => false],
    ['section' => $S3, 'name' => 'easement_beneficiary',   'label' => 'Beneficiary/Holder of Easement', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'servient_landowner',     'label' => 'Servient Landowner', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'dominant_landowner',     'label' => 'Dominant Landowner/Beneficiary', 'type' => 'text', 'required' => false],

    // ---- 4. Documents to Accompany Application ----
    ['section' => $S4, 'name' => 'required_docs_checklist', 'label' => 'Documents Submitted (tick applicable)', 'type' => 'checkbox', 'options' => $REQUIRED_DOCS, 'required' => false],
    ['section' => $S4, 'name' => 'supporting_docs_files',   'label' => 'Attach Supporting Documents', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 5. Step 1 — Application Submission ----
    ['section' => $S5, 'name' => 'received_by',              'label' => 'Received By', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'received_date',            'label' => 'Date', 'type' => 'date', 'required' => false],
    ['section' => $S5, 'name' => 'document_check',           'label' => 'Document Check', 'type' => 'select', 'options' => $DOC_CHECK, 'required' => false],
    ['section' => $S5, 'name' => 'incomplete_docs_required', 'label' => 'If Incomplete, documents required', 'type' => 'textarea', 'required' => false],

    // ---- 5. Step 2 — Document Verification ----
    ['section' => $S6, 'name' => 'verification_checks', 'label' => 'Verification Checklist', 'type' => 'checkbox', 'options' => [
        'Ownership of the affected property',
        'Validity of title/lease',
        'Identity of the parties',
        'Proposed purpose of the easement',
        'Existing encumbrances',
        'Compatibility with existing registered interests',
        'Survey and plan requirements',
    ], 'required' => false],
    ['section' => $S6, 'name' => 'verification_officer', 'label' => 'Verification Officer', 'type' => 'text', 'required' => false],
    ['section' => $S6, 'name' => 'verification_outcome', 'label' => 'Outcome', 'type' => 'select', 'options' => $VERIFICATION_OUTCOME, 'required' => false],
    ['section' => $S6, 'name' => 'verification_comments','label' => 'Comments', 'type' => 'textarea', 'required' => false],

    // ---- 5. Step 3 — Survey and Easement Plan ----
    ['section' => $S7, 'name' => 'survey_plan_items',    'label' => 'Survey Plan Shows', 'type' => 'checkbox', 'options' => $SURVEY_PLAN_ITEMS, 'required' => false],
    ['section' => $S7, 'name' => 'survey_plan_no',       'label' => 'Survey Plan No.', 'type' => 'text', 'required' => false],
    ['section' => $S7, 'name' => 'surveyor_name',        'label' => 'Surveyor', 'type' => 'text', 'required' => false],
    ['section' => $S7, 'name' => 'survey_date',          'label' => 'Date', 'type' => 'date', 'required' => false],
    ['section' => $S7, 'name' => 'survey_approved',      'label' => 'Survey Approved/Verified', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S7, 'name' => 'survey_plan_file',     'label' => 'Attach Survey Plan', 'type' => 'file', 'required' => false, 'allow_attachment' => true],

    // ---- 5. Step 4 — Consent and Agreement ----
    ['section' => $S8, 'name' => 'servient_owner_name',     'label' => 'Servient Owner', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'servient_signature',      'label' => 'Servient Owner Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S8, 'name' => 'servient_signature_date', 'label' => 'Servient Owner Signature Date', 'type' => 'date', 'required' => false],
    ['section' => $S8, 'name' => 'dominant_owner_name',     'label' => 'Dominant Owner/Beneficiary', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'dominant_signature',      'label' => 'Dominant Owner Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S8, 'name' => 'dominant_signature_date', 'label' => 'Dominant Owner Signature Date', 'type' => 'date', 'required' => false],
    ['section' => $S8, 'name' => 'witness_name',            'label' => 'Witness', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'witness_signature',       'label' => 'Witness Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S8, 'name' => 'compensation_agreed',     'label' => 'Compensation Agreed (K)', 'type' => 'number', 'required' => false],
    ['section' => $S8, 'name' => 'compensation_paid',       'label' => 'Compensation Paid (K)', 'type' => 'number', 'required' => false],
    ['section' => $S8, 'name' => 'rent_charge_paid',        'label' => 'Rent Charge Paid (K)', 'type' => 'number', 'required' => false],
    ['section' => $S8, 'name' => 'easement_agreement_file', 'label' => 'Attach Signed Easement Agreement', 'type' => 'file', 'required' => false, 'allow_attachment' => true],

    // ---- 5. Step 5 — Technical and Legal Review ----
    ['section' => $S9, 'name' => 'reviewed_by',        'label' => 'Reviewed By', 'type' => 'text', 'required' => false],
    ['section' => $S9, 'name' => 'reviewer_designation','label' => 'Designation', 'type' => 'text', 'required' => false],
    ['section' => $S9, 'name' => 'review_date',        'label' => 'Date', 'type' => 'date', 'required' => false],
    ['section' => $S9, 'name' => 'review_recommendation','label' => 'Recommendation', 'type' => 'select', 'options' => $REVIEW_RECOMMENDATION, 'required' => false],
    ['section' => $S9, 'name' => 'review_remarks',     'label' => 'Remarks', 'type' => 'textarea', 'required' => false],

    // ---- 5. Step 6 — Approval ----
    ['section' => $S10, 'name' => 'approving_authority','label' => 'Approving Authority', 'type' => 'text', 'required' => false],
    ['section' => $S10, 'name' => 'approval_decision', 'label' => 'Decision', 'type' => 'select', 'options' => $APPROVAL_DECISION, 'required' => false],
    ['section' => $S10, 'name' => 'approval_conditions','label' => 'Conditions (if any)', 'type' => 'textarea', 'required' => false],
    ['section' => $S10, 'name' => 'approval_signature','label' => 'Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S10, 'name' => 'approval_date',     'label' => 'Date', 'type' => 'date', 'required' => false],

    // ---- 5. Step 7 — Registration ----
    ['section' => $S11, 'name' => 'reg_registration_no', 'label' => 'Registration No.', 'type' => 'text', 'required' => false],
    ['section' => $S11, 'name' => 'reg_date_registered', 'label' => 'Date Registered', 'type' => 'date', 'required' => false],
    ['section' => $S11, 'name' => 'reg_title_updated',   'label' => 'Title/Record Updated', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S11, 'name' => 'reg_instrument_no',   'label' => 'Instrument No.', 'type' => 'text', 'required' => false],
    ['section' => $S11, 'name' => 'reg_entry_no',        'label' => 'Register Entry No.', 'type' => 'text', 'required' => false],
    ['section' => $S11, 'name' => 'reg_registered_by',   'label' => 'Registered By', 'type' => 'text', 'required' => false],

    // ---- 5. Step 8 — Issue of Registered Documents ----
    ['section' => $S12, 'name' => 'issued_docs',        'label' => 'Documents Issued/Returned', 'type' => 'checkbox', 'options' => $ISSUED_DOCS, 'required' => false],
    ['section' => $S12, 'name' => 'issued_docs_other',  'label' => 'If Other, specify', 'type' => 'text', 'required' => false],
    ['section' => $S12, 'name' => 'issued_date',        'label' => 'Date Issued', 'type' => 'date', 'required' => false],
    ['section' => $S12, 'name' => 'issued_received_by', 'label' => 'Received By', 'type' => 'text', 'required' => false],
    ['section' => $S12, 'name' => 'issued_signature',   'label' => 'Signature', 'type' => 'signature', 'required' => false],

    // ---- 7. Official Use Only ----
    ['section' => $S13, 'name' => 'app_reference',           'label' => 'Application Reference', 'type' => 'text', 'required' => false],
    ['section' => $S13, 'name' => 'land_registry_file_no',   'label' => 'Land Registry File No.', 'type' => 'text', 'required' => false],
    ['section' => $S13, 'name' => 'survey_file_no',          'label' => 'Survey File No.', 'type' => 'text', 'required' => false],
    ['section' => $S13, 'name' => 'fees_payable',            'label' => 'Fees Payable (K)', 'type' => 'number', 'required' => false],
    ['section' => $S13, 'name' => 'fees_paid',               'label' => 'Fees Paid (K)', 'type' => 'number', 'required' => false],
    ['section' => $S13, 'name' => 'receipt_no',              'label' => 'Receipt No.', 'type' => 'text', 'required' => false],
    ['section' => $S13, 'name' => 'outstanding_requirements','label' => 'Outstanding Requirements', 'type' => 'textarea', 'required' => false],
    ['section' => $S13, 'name' => 'final_status',            'label' => 'Final Status', 'type' => 'select', 'options' => $FINAL_STATUS, 'required' => false],
    ['section' => $S13, 'name' => 'officer_name',            'label' => 'Officer\'s Name', 'type' => 'text', 'required' => false],
    ['section' => $S13, 'name' => 'officer_designation',     'label' => 'Designation', 'type' => 'text', 'required' => false],
    ['section' => $S13, 'name' => 'officer_signature',       'label' => 'Officer Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S13, 'name' => 'officer_date',            'label' => 'Date', 'type' => 'date', 'required' => false],

    // ---- 8. Applicant Declaration ----
    ['section' => $S14, 'name' => 'declaration_ack',        'label' => 'I/We declare that the information and accompanying documents are true and correct and undertake to comply with all applicable legal, surveying, planning and land registration requirements.', 'type' => 'checkbox', 'options' => ['I Confirm'], 'required' => true],
    ['section' => $S14, 'name' => 'declaration_name',       'label' => 'Applicant Name', 'type' => 'text', 'required' => true],
    ['section' => $S14, 'name' => 'declaration_signature',  'label' => 'Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S14, 'name' => 'declaration_sig_file',   'label' => 'Signature (upload)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S14, 'name' => 'declaration_capacity',   'label' => 'Capacity', 'type' => 'text', 'required' => false],
    ['section' => $S14, 'name' => 'declaration_date',       'label' => 'Date', 'type' => 'date', 'required' => true],
    ['section' => $S14, 'name' => 'declaration_stamp',      'label' => 'Official Stamp (where applicable)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
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
        ['service_parent_id' => $parent->id, 'service_name' => 'Easement Verification and Registration'],
        ['model' => 'Easement Verification and Registration', 'type' => 'new_acquisition', 'is_active' => 1]
    );
    echo "  Child  : #{$child->id}  {$child->service_name}\n";

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'Easement Registration Process and Procedure Form',
            'description' => 'Property Department — Easement application, verification, survey, consent, review, approval, registration and issuance.',
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
