<?php

/**
 * Seed the Valuation department, its parent service, two child services,
 * and their (identical) form templates + fields.
 *
 * Safe to re-run: uses updateOrCreate for parent/children/templates and
 * only inserts fields when the template has none.
 *
 * Run:  php scripts/seed-valuation-department.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceParent;
use App\Models\ServiceChild;
use App\Models\ServiceFormTemplate;
use App\Models\ServiceFormField;
use Illuminate\Support\Facades\DB;

/* ---------------------------------------------------------------------------
 |  Field definitions — 7 sections (Section 4 skipped in source; renumbered).
 |  field_type values used elsewhere in LAMS: text, textarea, email, number,
 |  date, select, radio, checkbox_group, file, signature, repeater.
 |  ---------------------------------------------------------------------------
 */

$PROPERTY_TYPES = ['Residential','Commercial','Industrial','Agricultural','Institutional','Mixed-Use','Bare Land','Plant and Machinery','Other'];
$PROVINCES = ['Central','Copperbelt','Eastern','Luapula','Lusaka','Muchinga','Northern','North-Western','Southern','Western'];
$PURPOSES = ['Sale / Purchase','Mortgage / Bank Security','Insurance','Rental Assessment','Financial Reporting','Compensation','PTT','Rating','Other'];
$UTILITIES = ['Water','Electricity','Sewer','Other'];
$CONDITION = ['Excellent','Good','Fair','Poor'];
$ACCESS_ROAD = ['Tarred','Gravel','Dirt'];

$REPEATER_ROW = [
    ['name' => 'plot_lot_no',   'label' => 'Plot / Lot No.',    'type' => 'text'],
    ['name' => 'land_size',     'label' => 'Land Size',         'type' => 'text'],
    ['name' => 'building_size', 'label' => 'Building Size',     'type' => 'text'],
    ['name' => 'use',           'label' => 'Use',               'type' => 'text'],
    ['name' => 'estimated_value','label' => 'Estimated Value (K)','type' => 'number'],
];

$fields = [
    // ---------------- Section 1: Client Information ----------------
    ['section' => '1. Client Information', 'name' => 'client_name',      'label' => 'Client Name',              'type' => 'text',     'required' => true],
    ['section' => '1. Client Information', 'name' => 'nrc_company_no',   'label' => 'NRC / Company No.',        'type' => 'text',     'required' => true],
    ['section' => '1. Client Information', 'name' => 'phone',            'label' => 'Phone',                    'type' => 'text',     'required' => true],
    ['section' => '1. Client Information', 'name' => 'email',            'label' => 'Email',                    'type' => 'email',    'required' => true],
    ['section' => '1. Client Information', 'name' => 'address',          'label' => 'Postal / Physical Address','type' => 'textarea', 'required' => true],

    // ---------------- Section 2: Property Details ----------------
    ['section' => '2. Property Details', 'name' => 'property_type',   'label' => 'Property Type',        'type' => 'select',   'required' => true, 'options' => $PROPERTY_TYPES],
    ['section' => '2. Property Details', 'name' => 'plot_lot_no',     'label' => 'Plot / Lot No.',       'type' => 'text',     'required' => true],
    ['section' => '2. Property Details', 'name' => 'stand_no',        'label' => 'Stand No.',            'type' => 'text',     'required' => false],
    ['section' => '2. Property Details', 'name' => 'title_no',        'label' => 'Title No.',            'type' => 'text',     'required' => false],
    ['section' => '2. Property Details', 'name' => 'district',        'label' => 'District',             'type' => 'select',   'required' => true,  'options' => []],
    ['section' => '2. Property Details', 'name' => 'province',        'label' => 'Province',             'type' => 'select',   'required' => true, 'options' => []],
    ['section' => '2. Property Details', 'name' => 'property_location','label' => 'Property Location',   'type' => 'textarea', 'required' => true],
    ['section' => '2. Property Details', 'name' => 'gps_coordinates', 'label' => 'GPS Coordinates (optional — add one row per point)', 'type' => 'repeater', 'required' => false, 'options' => [
        ['name' => 'easting',  'label' => 'Easting',  'type' => 'text'],
        ['name' => 'northing', 'label' => 'Northing', 'type' => 'text'],
    ]],

    // ---------------- Section 3: Purpose of Valuation ----------------
    ['section' => '3. Purpose of Valuation', 'name' => 'purpose', 'label' => 'Purpose of Valuation', 'type' => 'checkbox_group', 'required' => true, 'options' => $PURPOSES],
    ['section' => '3. Purpose of Valuation', 'name' => 'purpose_other', 'label' => 'If Other, please specify', 'type' => 'text', 'required' => false],

    // ---------------- Section 4: Property Inspection (was Section 5) ----------------
    ['section' => '4. Property Inspection', 'name' => 'land_size',            'label' => 'Land Size',            'type' => 'text',     'required' => true],
    ['section' => '4. Property Inspection', 'name' => 'buildings',            'label' => 'Buildings on Parcel (add one row per building)', 'type' => 'repeater', 'required' => false, 'options' => [
        ['name' => 'building_name', 'label' => 'Building Name', 'type' => 'text'],
        ['name' => 'building_size', 'label' => 'Building Size', 'type' => 'text'],
    ]],
    ['section' => '4. Property Inspection', 'name' => 'condition',            'label' => 'Condition of Property','type' => 'radio',    'required' => true, 'options' => $CONDITION],
    ['section' => '4. Property Inspection', 'name' => 'improvements',        'label' => 'Improvements Observed', 'type' => 'textarea','required' => false],
    ['section' => '4. Property Inspection', 'name' => 'access_road',         'label' => 'Access Road',           'type' => 'radio',   'required' => false, 'options' => $ACCESS_ROAD],
    ['section' => '4. Property Inspection', 'name' => 'utilities',           'label' => 'Utilities Available',   'type' => 'checkbox_group', 'required' => false, 'options' => $UTILITIES],
    ['section' => '4. Property Inspection', 'name' => 'multiple_properties', 'label' => 'Multiple Properties Under One Title', 'type' => 'repeater', 'required' => false, 'options' => $REPEATER_ROW],

    // ---------------- Section 5: Required Documents (was Section 6) ----------------
    ['section' => '5. Required Documents', 'name' => 'doc_certificate_of_title', 'label' => 'Certificate of Title',     'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => '5. Required Documents', 'name' => 'doc_offer_letter',         'label' => 'Offer Letter',             'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => '5. Required Documents', 'name' => 'doc_lease_agreement',      'label' => 'Lease Agreement',          'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => '5. Required Documents', 'name' => 'doc_survey_diagram',       'label' => 'Survey Diagram',           'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => '5. Required Documents', 'name' => 'doc_site_plan',            'label' => 'Site Plan',                'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => '5. Required Documents', 'name' => 'doc_building_plan',        'label' => 'Building Plan',            'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => '5. Required Documents', 'name' => 'doc_nrc_registration',     'label' => 'NRC / Company Registration','type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => '5. Required Documents', 'name' => 'doc_contract_of_sale',     'label' => 'Contract of Sale',         'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => '5. Required Documents', 'name' => 'doc_chiefs_consent',       'label' => "Chief's Consent",          'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => '5. Required Documents', 'name' => 'doc_other',                'label' => 'Other Supporting Document','type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---------------- Section 6: Valuer's Assessment (was Section 7) ----------------
    ['section' => "6. Valuer's Assessment", 'name' => 'inspection_date',       'label' => 'Inspection Date',           'type' => 'date',     'required' => true],
    ['section' => "6. Valuer's Assessment", 'name' => 'valuation_reference_no','label' => 'Valuation Reference No.',   'type' => 'text',     'required' => true],
    ['section' => "6. Valuer's Assessment", 'name' => 'estimated_market_value','label' => 'Estimated Market Value (K)','type' => 'number',   'required' => true],
    ['section' => "6. Valuer's Assessment", 'name' => 'estimated_rental_value','label' => 'Estimated Rental Value (K)','type' => 'number',   'required' => false],
    ['section' => "6. Valuer's Assessment", 'name' => 'assessment_comments',   'label' => 'Comments',                  'type' => 'textarea', 'required' => false],

    // ---------------- Section 7: Declaration (was Section 8) ----------------
    ['section' => '7. Declaration', 'name' => 'client_signature_pad',   'label' => 'Client Signature (draw)',       'type' => 'signature', 'required' => false],
    ['section' => '7. Declaration', 'name' => 'client_signature_file',  'label' => 'Client Signature (upload scan/photo)', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => '7. Declaration', 'name' => 'declaration_date',       'label' => 'Date',                          'type' => 'date',     'required' => true],
    ['section' => '7. Declaration', 'name' => 'valuer_name',            'label' => "Valuer's Name",                 'type' => 'text',     'required' => true],
    ['section' => '7. Declaration', 'name' => 'valuer_signature_pad',   'label' => "Valuer's Signature (draw)",     'type' => 'signature', 'required' => false],
    ['section' => '7. Declaration', 'name' => 'valuer_signature_file',  'label' => "Valuer's Signature (upload scan/photo)", 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => '7. Declaration', 'name' => 'professional_reg_no',    'label' => 'Professional Registration No.', 'type' => 'text',     'required' => true],

    // ---------------- Valuation Firm Contact Details ----------------
    ['section' => '8. Valuation Firm Contact Details', 'name' => 'firm_company', 'label' => 'Company', 'type' => 'text',  'required' => true],
    ['section' => '8. Valuation Firm Contact Details', 'name' => 'firm_address', 'label' => 'Address', 'type' => 'textarea','required' => true],
    ['section' => '8. Valuation Firm Contact Details', 'name' => 'firm_phone',   'label' => 'Phone',   'type' => 'text',  'required' => true],
    ['section' => '8. Valuation Firm Contact Details', 'name' => 'firm_email',   'label' => 'Email',   'type' => 'email', 'required' => true],
];

/* ---------------------------------------------------------------------------
 |  Persistence
 |  ---------------------------------------------------------------------------
 */

echo "== Seeding Valuation department ==\n";

DB::transaction(function () use ($fields) {
    // 1) Department + parent service row.
    $parent = ServiceParent::updateOrCreate(
        ['department_code' => 'valuation'],
        [
            'department_name' => 'Valuation',
            'service_name'    => 'Valuation Services',
        ]
    );
    echo "  Parent : #{$parent->id}  {$parent->department_name} / {$parent->service_name}\n";

    // 2) Children.
    $children = [
        'Property Valuation'   => null,
        'Property Consultancy' => null,
    ];

    foreach ($children as $name => $_) {
        $child = ServiceChild::updateOrCreate(
            ['service_parent_id' => $parent->id, 'service_name' => $name],
            ['model' => $name, 'type' => 'new_acquisition']
        );
        echo "  Child  : #{$child->id}  {$child->service_name}\n";
        $children[$name] = $child;
    }

    // 3) Template + fields per child (identical structure).
    foreach ($children as $name => $child) {
        $template = ServiceFormTemplate::updateOrCreate(
            ['service_child_id' => $child->id],
            [
                'form_name'   => $name . ' Form',
                'description' => 'Standard ' . strtolower($name) . ' request form.',
                'is_active'   => true,
            ]
        );
        echo "  Template: #{$template->id}  {$template->form_name}\n";

        $existing = ServiceFormField::where('service_form_template_id', $template->id)->count();
        if ($existing > 0) {
            echo "     ↳ {$existing} fields already exist, skipping insert.\n";
            continue;
        }

        $order = 10;
        foreach ($fields as $f) {
            ServiceFormField::create([
                'service_form_template_id'   => $template->id,
                'field_name'                 => $f['name'],
                'field_label'                => $f['label'],
                'field_type'                 => $f['type'],
                'options'                    => isset($f['options']) ? $f['options'] : null,
                'is_required'                => (bool)($f['required'] ?? false),
                'sort_order'                 => $order,
                'section'                    => $f['section'],
                'allow_comments'             => false,
                'allow_attachment'           => (bool)($f['allow_attachment'] ?? false),
                'allow_multiple_attachments' => (bool)($f['allow_multiple_attachments'] ?? false),
                'attachment_label'           => $f['attachment_label'] ?? null,
                'validation_rules'           => null,
            ]);
            $order += 10;
        }
        echo "     ↳ Inserted " . count($fields) . " fields.\n";
    }
});

echo "\nDone.\n";
