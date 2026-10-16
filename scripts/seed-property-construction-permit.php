<?php
/**
 * Seed the Property Department child service "Construction Permit Application"
 * + its full LAMS Construction Permit Application form template.
 *
 * Run:  php scripts/seed-property-construction-permit.php
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

$INDIVIDUAL_COMPANY = ['Individual', 'Company', 'Organisation'];

$LAND_INTEREST = ['Lease', 'Right of Use', 'Other'];

$LAMS_PARCEL_SEARCH = ['Verified', 'Not Verified', 'Discrepancy Identified'];

$CONSTRUCTION_TYPE = [
    'New Building', 'Extension', 'Renovation/Alteration', 'Reconstruction',
    'Demolition', 'Change of Use', 'Commercial Development',
    'Residential Development', 'Industrial Development',
    'Institutional Development', 'Infrastructure/Utility', 'Other',
];

$PLAN_DOCS = [
    'Site Plan', 'Location Plan', 'Architectural Drawings', 'Floor Plans',
    'Elevations', 'Sections', 'Structural Drawings', 'Drainage Plan',
    'Electrical Plan', 'Plumbing Plan', 'Fire Safety Plan',
    'Parking/Access Plan', 'Landscaping Plan', 'Other Technical Drawings',
];

$PAYMENT_STATUS = ['Paid', 'Part Paid', 'Outstanding'];

$LEGAL_CHECKS = [
    'Applicant has Legal Interest in Property',
    'Title/Lease Verified',
    'Right of Use Verified',
    'Existing Restrictions Checked',
    'Mortgage/Charge Checked',
    'Caveat Checked',
    'Easement Checked',
    'Wayleave Checked',
    'Litigation/Objection Checked',
    'Planning Restrictions Checked',
];

$DECISION = ['Approved', 'Approved Subject to Conditions', 'Refused', 'Referred Back for Further Information'];

/* -------------------------------------------------------------------
 |  Sections
 |  ------------------------------------------------------------------- */
$S0 = '0. Application Header';
$S1 = '1. Applicant Details';
$S2 = '2. Property & Land Details';
$S3 = '3. Proposed Development';
$S4 = '4. Building Plan Submission';
$S5 = '5. Fees & Payment';
$S6 = '6. Compliance / Legal Check';
$S7 = '7. Approving Authority';
$S8 = '8. Construction Permit';

$fields = [
    // ---- 0. Header ----
    ['section' => $S0, 'name' => 'application_reference_no', 'label' => 'Application/Reference No.', 'type' => 'text', 'required' => true],
    ['section' => $S0, 'name' => 'permit_no',                'label' => 'Permit No.', 'type' => 'text', 'required' => false],
    ['section' => $S0, 'name' => 'date_received',            'label' => 'Date Received', 'type' => 'date', 'required' => true],
    ['section' => $S0, 'name' => 'case_officer',             'label' => 'Case Officer', 'type' => 'text', 'required' => false],

    // ---- 1. Applicant ----
    ['section' => $S1, 'name' => 'applicant_developer',      'label' => 'Applicant/Developer', 'type' => 'text', 'required' => true],
    ['section' => $S1, 'name' => 'individual_company',       'label' => 'Individual / Company / Organisation', 'type' => 'select', 'options' => $INDIVIDUAL_COMPANY, 'required' => false],
    ['section' => $S1, 'name' => 'nrc_registration_no',      'label' => 'NRC/Registration No.', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'contact_person',           'label' => 'Contact Person', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'postal_address',           'label' => 'Postal Address', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'physical_address',         'label' => 'Physical Address', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'telephone',                'label' => 'Telephone', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'email',                    'label' => 'Email', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'authorised_agent',         'label' => 'Authorised Agent/Consultant', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'agent_contact',            'label' => 'Agent Contact Details', 'type' => 'text', 'required' => false],

    // ---- 2. Property ----
    ['section' => $S2, 'name' => 'property_plot_no',         'label' => 'Property/Plot No.', 'type' => 'text', 'required' => true],
    ['section' => $S2, 'name' => 'title_lease_no',           'label' => 'Title/Lease No.', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'parcel_lams_no',           'label' => 'Parcel ID/LAMS No.', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'district',                 'label' => 'District', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'province',                 'label' => 'Province', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'local_authority',          'label' => 'Local Authority', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'area_size',                'label' => 'Area/Size', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'land_use',                 'label' => 'Land Use', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'zoning',                   'label' => 'Zoning', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'current_land_interest',    'label' => 'Current Land Interest', 'type' => 'select', 'options' => $LAND_INTEREST, 'required' => false],
    ['section' => $S2, 'name' => 'land_interest_other',      'label' => 'If Other interest, specify', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'registered_owner',         'label' => 'Registered Owner/Holder', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'cadastral_plan_no',        'label' => 'Cadastral Plan No.', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'gis_coordinates',          'label' => 'GIS Coordinates', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'lams_parcel_search',       'label' => 'LAMS Parcel Search', 'type' => 'select', 'options' => $LAMS_PARCEL_SEARCH, 'required' => false],

    // ---- 3. Proposed Development ----
    ['section' => $S3, 'name' => 'construction_type',        'label' => 'Type of Construction', 'type' => 'checkbox', 'options' => $CONSTRUCTION_TYPE, 'required' => true],
    ['section' => $S3, 'name' => 'construction_type_other',  'label' => 'If Other construction type, specify', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'proposed_use',             'label' => 'Proposed Use', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'number_of_buildings',      'label' => 'Number of Buildings', 'type' => 'number', 'required' => false],
    ['section' => $S3, 'name' => 'number_of_floors',         'label' => 'Number of Floors', 'type' => 'number', 'required' => false],
    ['section' => $S3, 'name' => 'estimated_floor_area',     'label' => 'Estimated Floor Area', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'construction_value',       'label' => 'Estimated Construction Value', 'type' => 'number', 'required' => false],
    ['section' => $S3, 'name' => 'proposed_commencement',    'label' => 'Proposed Commencement Date', 'type' => 'date', 'required' => false],
    ['section' => $S3, 'name' => 'expected_completion',      'label' => 'Expected Completion Date', 'type' => 'date', 'required' => false],
    ['section' => $S3, 'name' => 'contractor',               'label' => 'Contractor', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'architect',                'label' => 'Architect', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'quantity_surveyor',        'label' => 'Quantity Surveyor', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'structural_engineer',      'label' => 'Structural Engineer', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'other_consultants',        'label' => 'Other Consultants', 'type' => 'textarea', 'required' => false],
    ['section' => $S3, 'name' => 'planning_officer_comments','label' => "Planning Officer's Comments", 'type' => 'textarea', 'required' => false],

    // ---- 4. Building Plan Submission ----
    ['section' => $S4, 'name' => 'plan_documents_checklist', 'label' => 'Documents/Plans Attached', 'type' => 'checkbox', 'options' => $PLAN_DOCS, 'required' => false],
    ['section' => $S4, 'name' => 'drawing_reference_no',     'label' => 'Drawing/Document Reference No.', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'drawing_version',          'label' => 'Version', 'type' => 'text', 'required' => false],
    ['section' => $S4, 'name' => 'drawing_date',             'label' => 'Date', 'type' => 'date', 'required' => false],
    ['section' => $S4, 'name' => 'plan_files',               'label' => 'Upload Drawings/Plans', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 5. Fees & Payment ----
    ['section' => $S5, 'name' => 'application_fee',          'label' => 'Application Fee', 'type' => 'number', 'required' => false],
    ['section' => $S5, 'name' => 'plan_assessment_fee',      'label' => 'Plan Assessment Fee', 'type' => 'number', 'required' => false],
    ['section' => $S5, 'name' => 'inspection_fee',           'label' => 'Inspection Fee', 'type' => 'number', 'required' => false],
    ['section' => $S5, 'name' => 'permit_fee',               'label' => 'Permit Fee', 'type' => 'number', 'required' => false],
    ['section' => $S5, 'name' => 'other_fee',                'label' => 'Other Fee', 'type' => 'number', 'required' => false],
    ['section' => $S5, 'name' => 'total_fee',                'label' => 'Total', 'type' => 'number', 'required' => false],
    ['section' => $S5, 'name' => 'payment_reference',        'label' => 'Payment Reference', 'type' => 'text', 'required' => false],
    ['section' => $S5, 'name' => 'payment_date',             'label' => 'Payment Date', 'type' => 'date', 'required' => false],
    ['section' => $S5, 'name' => 'payment_status',           'label' => 'Payment Status', 'type' => 'select', 'options' => $PAYMENT_STATUS, 'required' => false],
    ['section' => $S5, 'name' => 'payment_receipt_file',     'label' => 'Upload Payment Receipt', 'type' => 'file', 'required' => false, 'allow_attachment' => true],

    // ---- 6. Compliance / Legal ----
    ['section' => $S6, 'name' => 'legal_checks',             'label' => 'Legal & Land Interest Verification', 'type' => 'checkbox', 'options' => $LEGAL_CHECKS, 'required' => false],
    ['section' => $S6, 'name' => 'legal_comments',           'label' => 'Legal Comments', 'type' => 'textarea', 'required' => false],
    ['section' => $S6, 'name' => 'legal_officer',            'label' => 'Legal Officer', 'type' => 'text', 'required' => false],
    ['section' => $S6, 'name' => 'legal_signature',          'label' => 'Legal Officer Signature', 'type' => 'signature', 'required' => false],
    ['section' => $S6, 'name' => 'legal_date',               'label' => 'Legal Review Date', 'type' => 'date', 'required' => false],

    // ---- 7. Approving Authority ----
    ['section' => $S7, 'name' => 'decision',                 'label' => 'Decision', 'type' => 'select', 'options' => $DECISION, 'required' => false],
    ['section' => $S7, 'name' => 'permit_conditions',        'label' => 'Permit Conditions', 'type' => 'textarea', 'required' => false],
    ['section' => $S7, 'name' => 'approval_authority',       'label' => 'Approval Authority', 'type' => 'text', 'required' => false],
    ['section' => $S7, 'name' => 'approval_reference',       'label' => 'Approval Reference', 'type' => 'text', 'required' => false],
    ['section' => $S7, 'name' => 'approval_date',            'label' => 'Approval Date', 'type' => 'date', 'required' => false],
    ['section' => $S7, 'name' => 'approving_signature',      'label' => 'Approving Authority Signature', 'type' => 'signature', 'required' => false],

    // ---- 8. Construction Permit ----
    ['section' => $S8, 'name' => 'permit_number',            'label' => 'Permit Number', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'approved_applicant',       'label' => 'Approved Applicant', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'approved_property_plot',   'label' => 'Property/Plot', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'approved_development',     'label' => 'Approved Development', 'type' => 'textarea', 'required' => false],
    ['section' => $S8, 'name' => 'approved_floor_area',      'label' => 'Approved Floor Area', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'approved_number_of_floors','label' => 'Number of Floors', 'type' => 'number', 'required' => false],
    ['section' => $S8, 'name' => 'permit_commencement_date', 'label' => 'Permit Commencement Date', 'type' => 'date', 'required' => false],
    ['section' => $S8, 'name' => 'permit_expiry_date',       'label' => 'Permit Expiry Date', 'type' => 'date', 'required' => false],
    ['section' => $S8, 'name' => 'special_conditions',       'label' => 'Special Conditions', 'type' => 'textarea', 'required' => false],
    ['section' => $S8, 'name' => 'digital_permit_generated', 'label' => 'Digital Permit Generated', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S8, 'name' => 'qr_verification_code',     'label' => 'QR/Verification Code', 'type' => 'text', 'required' => false],
    ['section' => $S8, 'name' => 'permit_document_file',     'label' => 'Upload Permit Document', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S8, 'name' => 'final_inspection_report',  'label' => 'Final Inspection Report', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],
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
        ['service_parent_id' => $parent->id, 'service_name' => 'Construction Permit Application'],
        ['model' => 'Construction Permit Application', 'type' => 'asset_management', 'is_active' => 1]
    );
    echo "  Child  : #{$child->id}  {$child->service_name}\n";

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'LAMS — Construction Permit Application Form',
            'description' => 'Property Department — Applicant, property, proposed development, plan submission, fees, legal compliance, approval and permit issuance.',
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
