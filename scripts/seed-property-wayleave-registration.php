<?php
/**
 * Seed the Property Department child service "Wayleave Registration"
 * + its full application/registration form template.
 *
 * Run:  php scripts/seed-property-wayleave-registration.php
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
$PURPOSE = [
    'Electricity Transmission','Electricity Distribution','Water Pipeline',
    'Sewerage','Telecommunications','Road/Transport Infrastructure','Other',
];

$LAND_TENURE = [
    'State Land','Customary Land','Leasehold','Agricultural Land',
    'Institutional Land','Reserve/Protected Area','Other',
];

$EXISTING_USE = [
    'Residential','Commercial','Agricultural','Forest/Woodland','Grazing',
    'Road Reserve','Railway Reserve','Existing Utility Corridor',
    'Watercourse','Public Facility','Vacant Land','Other',
];

$YES_NO = ['Yes','No'];
$YES_NO_PENDING = ['Yes','No','Pending'];

$EXISTING_SERVICE_TYPES = ['Road Reserve','Rail Line','Other'];

$SUPPORTING_DOCS = [
    'Site/Route Plan','Survey Diagram/Survey Plan','GPS Coordinates',
    'Land Ownership Documents','Certificate of Title','Lease Agreement',
    'Consent from Landowner(s)','Traditional Authority/Chief\'s Consent',
    'Local Authority Approval/Consent','Environmental Approval/Documentation',
    'Project Design/Alignment Drawings','Land Acquisition/Wayleave Report',
    'List of Affected Properties','Valuation/Compensation Assessment','Other',
];

/* -------------------------------------------------------------------
 |  Repeater tables
 |  ------------------------------------------------------------------- */
$GPS_TABLE = [
    ['name' => 'point',       'label' => 'Point',        'type' => 'text'],
    ['name' => 'latitude',    'label' => 'Latitude',     'type' => 'text'],
    ['name' => 'longitude',   'label' => 'Longitude',    'type' => 'text'],
    ['name' => 'description', 'label' => 'Description',  'type' => 'text'],
    'default_rows' => [
        ['point' => 'Start',                'latitude' => '', 'longitude' => '', 'description' => ''],
        ['point' => 'End',                  'latitude' => '', 'longitude' => '', 'description' => ''],
        ['point' => 'Intermediate Point 1', 'latitude' => '', 'longitude' => '', 'description' => ''],
        ['point' => 'Intermediate Point 2', 'latitude' => '', 'longitude' => '', 'description' => ''],
    ],
];

$AFFECTED_PARCELS_TABLE = [
    ['name' => 'plot_no',        'label' => 'Property/Plot/Stand No.', 'type' => 'text'],
    ['name' => 'owner_occupier', 'label' => 'Owner/Occupier',          'type' => 'text'],
    ['name' => 'tenure',         'label' => 'Tenure',                  'type' => 'text'],
    ['name' => 'area_affected',  'label' => 'Area Affected',           'type' => 'text'],
    ['name' => 'remarks',        'label' => 'Remarks',                 'type' => 'text'],
    'default_rows' => [
        ['plot_no' => '', 'owner_occupier' => '', 'tenure' => '', 'area_affected' => '', 'remarks' => ''],
    ],
];

$EXISTING_SERVICES_TABLE = [
    ['name' => 'service_type',      'label' => 'Service/Infrastructure', 'type' => 'text'],
    ['name' => 'owner',             'label' => 'Owner',                  'type' => 'text'],
    ['name' => 'location',          'label' => 'Location',               'type' => 'text'],
    ['name' => 'crossing_parallel', 'label' => 'Crossing/Parallel',      'type' => 'text'],
    ['name' => 'remarks',           'label' => 'Remarks',                'type' => 'text'],
    'default_rows' => [
        ['service_type' => '', 'owner' => '', 'location' => '', 'crossing_parallel' => '', 'remarks' => ''],
    ],
];

$LANDOWNERS_TABLE = [
    ['name' => 'landowner',    'label' => 'Landowner/Occupier',     'type' => 'text'],
    ['name' => 'plot_no',      'label' => 'Plot/Stand No.',         'type' => 'text'],
    ['name' => 'contact',      'label' => 'Contact',                'type' => 'text'],
    ['name' => 'consent',      'label' => 'Consent/Notification',   'type' => 'text'],
    ['name' => 'remarks',      'label' => 'Remarks',                'type' => 'text'],
    'default_rows' => [
        ['landowner' => '', 'plot_no' => '', 'contact' => '', 'consent' => '', 'remarks' => ''],
    ],
];

/* -------------------------------------------------------------------
 |  Fields
 |  ------------------------------------------------------------------- */
$A = 'A. Applicant Details';
$B = 'B. Wayleave Details';
$C = 'C. Location of Wayleave';
$D = 'D. Affected Land / Parcels';
$E = 'E. Land Tenure';
$F = 'F. Existing Land Use';
$G = 'G. Existing Services / Infrastructure';
$H = 'H. Affected Landowners / Occupiers';
$I = 'I. Supporting Documents';
$J = 'J. Declaration by Applicant';
$K = 'K. For Official Use Only';
$L = 'L. Wayleave Register Entry';

$fields = [
    // ---- A. Applicant Details ----
    ['section' => $A, 'name' => 'directorate_name',    'label' => 'Directorate Name', 'type' => 'text', 'required' => true],
    ['section' => $A, 'name' => 'postal_address',      'label' => 'Postal Address',   'type' => 'textarea', 'required' => false],
    ['section' => $A, 'name' => 'station',             'label' => 'Station',          'type' => 'text', 'required' => false],
    ['section' => $A, 'name' => 'district_applicant',  'label' => 'District',         'type' => 'text', 'required' => false],
    ['section' => $A, 'name' => 'contact_person',      'label' => 'Contact Person',   'type' => 'text', 'required' => true],
    ['section' => $A, 'name' => 'position_designation','label' => 'Position/Designation', 'type' => 'text', 'required' => false],

    // ---- B. Wayleave Details ----
    ['section' => $B, 'name' => 'purpose_of_wayleave',       'label' => 'Purpose of Wayleave', 'type' => 'checkbox', 'options' => $PURPOSE, 'required' => true],
    ['section' => $B, 'name' => 'purpose_other',             'label' => 'If Other, specify', 'type' => 'text', 'required' => false],
    ['section' => $B, 'name' => 'infrastructure_description','label' => 'Description of Proposed Infrastructure', 'type' => 'textarea', 'required' => true],
    ['section' => $B, 'name' => 'wayleave_length_km',        'label' => 'Wayleave Length (km)', 'type' => 'number', 'required' => false],
    ['section' => $B, 'name' => 'wayleave_width_m',          'label' => 'Proposed Wayleave Width (m)', 'type' => 'number', 'required' => false],
    ['section' => $B, 'name' => 'total_area_ha',             'label' => 'Total Area Required (ha)', 'type' => 'number', 'required' => false],
    ['section' => $B, 'name' => 'project_name',              'label' => 'Project Name', 'type' => 'text', 'required' => false],
    ['section' => $B, 'name' => 'project_reference_no',      'label' => 'Project Reference Number', 'type' => 'text', 'required' => false],
    ['section' => $B, 'name' => 'construction_period',       'label' => 'Expected Construction Period', 'type' => 'text', 'required' => false],

    // ---- C. Location of Wayleave ----
    ['section' => $C, 'name' => 'province',               'label' => 'Province',                'type' => 'text', 'required' => true],
    ['section' => $C, 'name' => 'district_location',      'label' => 'District',                'type' => 'text', 'required' => true],
    ['section' => $C, 'name' => 'chiefdom',               'label' => 'Chiefdom',                'type' => 'text', 'required' => false],
    ['section' => $C, 'name' => 'ward',                   'label' => 'Ward',                    'type' => 'text', 'required' => false],
    ['section' => $C, 'name' => 'local_authority',        'label' => 'Local Authority',         'type' => 'text', 'required' => false],
    ['section' => $C, 'name' => 'nearest_town',           'label' => 'Nearest Town/Settlement', 'type' => 'text', 'required' => false],
    ['section' => $C, 'name' => 'starting_point',         'label' => 'Starting Point',          'type' => 'text', 'required' => false],
    ['section' => $C, 'name' => 'ending_point',           'label' => 'Ending Point',            'type' => 'text', 'required' => false],
    ['section' => $C, 'name' => 'gps_coordinates',        'label' => 'GPS Coordinates', 'type' => 'table', 'required' => false, 'options' => $GPS_TABLE],
    ['section' => $C, 'name' => 'coordinate_system',      'label' => 'Coordinate System/Datum', 'type' => 'text', 'required' => false],

    // ---- D. Affected Land / Parcels ----
    ['section' => $D, 'name' => 'affected_parcels',       'label' => 'Affected Parcels', 'type' => 'table', 'required' => false, 'options' => $AFFECTED_PARCELS_TABLE],
    ['section' => $D, 'name' => 'total_affected_props',   'label' => 'Total Number of Affected Properties', 'type' => 'number', 'required' => false],
    ['section' => $D, 'name' => 'total_wayleave_area_ha', 'label' => 'Total Wayleave Area (ha)', 'type' => 'number', 'required' => false],

    // ---- E. Land Tenure ----
    ['section' => $E, 'name' => 'land_tenure',            'label' => 'Applicable Land Category', 'type' => 'checkbox', 'options' => $LAND_TENURE, 'required' => false],
    ['section' => $E, 'name' => 'land_tenure_other',      'label' => 'If Other, specify', 'type' => 'text', 'required' => false],
    ['section' => $E, 'name' => 'title_certificate_no',   'label' => 'Title/Lease/Occupancy Certificate No.', 'type' => 'text', 'required' => false],
    ['section' => $E, 'name' => 'traditional_authority',  'label' => 'Traditional Authority/Chiefdom', 'type' => 'text', 'required' => false],

    // ---- F. Existing Land Use ----
    ['section' => $F, 'name' => 'existing_land_use',      'label' => 'Existing Uses', 'type' => 'checkbox', 'options' => $EXISTING_USE, 'required' => false],
    ['section' => $F, 'name' => 'existing_land_use_other','label' => 'If Other, specify', 'type' => 'text', 'required' => false],
    ['section' => $F, 'name' => 'existing_developments',  'label' => 'Description of Existing Developments/Structures', 'type' => 'textarea', 'required' => false],

    // ---- G. Existing Services / Infrastructure ----
    ['section' => $G, 'name' => 'existing_services_yes_no','label' => 'Are there existing services within or crossing the proposed wayleave?', 'type' => 'select', 'options' => $YES_NO, 'required' => true],
    ['section' => $G, 'name' => 'existing_service_types', 'label' => 'If Yes, service types', 'type' => 'checkbox', 'options' => $EXISTING_SERVICE_TYPES, 'required' => false],
    ['section' => $G, 'name' => 'existing_services_other','label' => 'If Other type, specify', 'type' => 'text', 'required' => false],
    ['section' => $G, 'name' => 'existing_services_table','label' => 'Existing Services Details', 'type' => 'table', 'required' => false, 'options' => $EXISTING_SERVICES_TABLE],

    // ---- H. Affected Landowners / Occupiers ----
    ['section' => $H, 'name' => 'affected_landowners_table', 'label' => 'Affected Landowners/Occupiers', 'type' => 'table', 'required' => false, 'options' => $LANDOWNERS_TABLE],

    // ---- I. Supporting Documents ----
    ['section' => $I, 'name' => 'supporting_docs_checklist', 'label' => 'Applicable Documents (tick all that apply)', 'type' => 'checkbox', 'options' => $SUPPORTING_DOCS, 'required' => false],
    ['section' => $I, 'name' => 'supporting_docs_other',     'label' => 'If Other document, specify', 'type' => 'text', 'required' => false],
    ['section' => $I, 'name' => 'supporting_docs_files',     'label' => 'Attach Supporting Documents', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- J. Declaration by Applicant ----
    ['section' => $J, 'name' => 'declaration_ack',       'label' => 'I/We declare that the information is true and correct and undertake to comply with all applicable laws and conditions. I/We understand that submission does not constitute approval.', 'type' => 'checkbox', 'options' => ['I Confirm'], 'required' => true],
    ['section' => $J, 'name' => 'applicant_name',        'label' => 'Name of Applicant/Authorised Representative', 'type' => 'text', 'required' => true],
    ['section' => $J, 'name' => 'applicant_position',    'label' => 'Position', 'type' => 'text', 'required' => false],
    ['section' => $J, 'name' => 'applicant_signature',   'label' => 'Signature', 'type' => 'signature', 'required' => false],
    ['section' => $J, 'name' => 'applicant_sig_file',    'label' => 'Signature (upload)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $J, 'name' => 'applicant_date',        'label' => 'Date', 'type' => 'date', 'required' => true],
    ['section' => $J, 'name' => 'official_stamp',        'label' => 'Official Stamp (where applicable)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],

    // ---- K. For Official Use Only ----
    ['section' => $K, 'name' => 'app_registration_no',   'label' => 'Application/Registration No.', 'type' => 'text', 'required' => false],
    ['section' => $K, 'name' => 'date_received',         'label' => 'Date Received', 'type' => 'date', 'required' => false],
    ['section' => $K, 'name' => 'checked_by',            'label' => 'Application Checked By', 'type' => 'text', 'required' => false],
    ['section' => $K, 'name' => 'site_inspection_req',   'label' => 'Site Inspection Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $K, 'name' => 'survey_verification_req','label' => 'Survey Verification Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $K, 'name' => 'land_status_verified',  'label' => 'Land Status Verified', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $K, 'name' => 'affected_parcels_verified','label' => 'Affected Parcels Verified', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $K, 'name' => 'wayleave_approved',     'label' => 'Wayleave Approved', 'type' => 'select', 'options' => $YES_NO_PENDING, 'required' => false],
    ['section' => $K, 'name' => 'conditions_of_approval','label' => 'Conditions of Approval', 'type' => 'textarea', 'required' => false],
    ['section' => $K, 'name' => 'registration_date',     'label' => 'Registration/Approval Date', 'type' => 'date', 'required' => false],
    ['section' => $K, 'name' => 'authorised_officer',    'label' => 'Authorised Officer', 'type' => 'text', 'required' => false],
    ['section' => $K, 'name' => 'authorised_designation','label' => 'Designation', 'type' => 'text', 'required' => false],
    ['section' => $K, 'name' => 'authorised_signature',  'label' => 'Signature', 'type' => 'signature', 'required' => false],
    ['section' => $K, 'name' => 'authorised_sig_file',   'label' => 'Signature (upload)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $K, 'name' => 'official_stamp_officer','label' => 'Official Stamp', 'type' => 'file', 'required' => false, 'allow_attachment' => true],

    // ---- L. Wayleave Register Entry ----
    ['section' => $L, 'name' => 'reg_number',            'label' => 'Registration Number', 'type' => 'text', 'required' => false],
    ['section' => $L, 'name' => 'reg_project',           'label' => 'Project/Infrastructure', 'type' => 'text', 'required' => false],
    ['section' => $L, 'name' => 'reg_applicant_holder',  'label' => 'Applicant/Holder', 'type' => 'text', 'required' => false],
    ['section' => $L, 'name' => 'reg_location',          'label' => 'Location', 'type' => 'text', 'required' => false],
    ['section' => $L, 'name' => 'reg_district',          'label' => 'District', 'type' => 'text', 'required' => false],
    ['section' => $L, 'name' => 'reg_province',          'label' => 'Province', 'type' => 'text', 'required' => false],
    ['section' => $L, 'name' => 'reg_width_m',           'label' => 'Wayleave Width (m)', 'type' => 'number', 'required' => false],
    ['section' => $L, 'name' => 'reg_length_km',         'label' => 'Wayleave Length (km)', 'type' => 'number', 'required' => false],
    ['section' => $L, 'name' => 'reg_area_ha',           'label' => 'Wayleave Area (ha)', 'type' => 'number', 'required' => false],
    ['section' => $L, 'name' => 'reg_date_registered',   'label' => 'Date Registered', 'type' => 'date', 'required' => false],
    ['section' => $L, 'name' => 'reg_commencement_date', 'label' => 'Commencement Date', 'type' => 'date', 'required' => false],
    ['section' => $L, 'name' => 'reg_expiry_review_date','label' => 'Expiry/Review Date (if applicable)', 'type' => 'date', 'required' => false],
    ['section' => $L, 'name' => 'reg_parcel_reference',  'label' => 'Register/Parcel Reference', 'type' => 'text', 'required' => false],
    ['section' => $L, 'name' => 'reg_remarks',           'label' => 'Remarks', 'type' => 'textarea', 'required' => false],
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
        ['service_parent_id' => $parent->id, 'service_name' => 'Wayleave Registration'],
        ['model' => 'Wayleave Registration', 'type' => 'new_acquisition', 'is_active' => 1]
    );
    echo "  Child  : #{$child->id}  {$child->service_name}\n";

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'Wayleave Registration / Application Form',
            'description' => 'Property Department — Wayleave application, affected parcel capture, verification and register entry.',
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
