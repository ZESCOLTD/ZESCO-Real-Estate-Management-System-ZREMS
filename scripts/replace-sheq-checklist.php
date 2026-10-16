<?php

/**
 * Replace all fields on the SHEQ Checklist template (#17) with the new
 * "SHEQ Checklist for Land and Building Acquisition and Management" content.
 *
 * Idempotent: wipes existing fields on the template, then re-inserts.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceFormField;
use App\Models\ServiceFormTemplate;
use Illuminate\Support\Facades\DB;

$TEMPLATE_ID = 17;

$t   = fn($sec,$name,$label,$req=false) => ['section'=>$sec,'name'=>$name,'label'=>$label,'type'=>'text','required'=>$req];
$ta  = fn($sec,$name,$label,$req=false) => ['section'=>$sec,'name'=>$name,'label'=>$label,'type'=>'textarea','required'=>$req];
$date= fn($sec,$name,$label,$req=false) => ['section'=>$sec,'name'=>$name,'label'=>$label,'type'=>'date','required'=>$req];
$sig = fn($sec,$name,$label) => ['section'=>$sec,'name'=>$name,'label'=>$label,'type'=>'signature','required'=>false];
$file= fn($sec,$name,$label,$multi=false) => ['section'=>$sec,'name'=>$name,'label'=>$label,'type'=>'file','required'=>false,'allow_attachment'=>true,'allow_multiple_attachments'=>$multi];

/**
 * Checklist row builder — creates ONE repeater field per row so each has
 * its own Requirement label + free-text Comments/Findings input.
 * We render each item as a single textarea per row to keep the DB simple.
 */
$rowFields = function (string $section, array $items) use ($ta) {
    $out = [];
    foreach ($items as $key => $label) {
        $out[] = $ta($section, $key, $label);
    }
    return $out;
};

$fields = [];

// ===== Service 1 — Data Collection on Property Details =====
$S1 = '1. Data Collection on Property Details';
$fields[] = $ta ($S1, 's1_seller_details',      'Seller Details', true);
$fields[] = $ta ($S1, 's1_property_address',    'Property Address', true);
$fields[] = $t  ($S1, 's1_intended_use',        'Intended Use', true);
$fields[] = $t  ($S1, 's1_property_type',       'Property Type', true);
$fields[] = $t  ($S1, 's1_site_area',           'Site Area');
$fields[] = $ta ($S1, 's1_existing_structures', 'Existing Structures');
$fields[] = $date($S1,'s1_inspection_date',     'Date of Inspection', true);
$fields[] = $ta ($S1, 's1_due_diligence_team',  'Due Diligence Team (one member per line)');

// ===== Service 2 — Environmental Assessment =====
$S2 = '2. Environmental Assessment';
$fields = array_merge($fields, $rowFields($S2, [
    's2_esa'                    => 'Environmental Site Assessment (ESA) — Conduct Environmental Site Assessment',
    's2_recs'                   => 'Recognized Environmental Conditions (RECs) — Identify contamination risks from historical activities',
    's2_testing'                => 'Testing — Conduct soil, groundwater, or vapor testing if required',
    's2_storage_tanks'          => 'Storage Tanks Inspection — Check for underground/aboveground storage tanks',
    's2_hazardous_materials'    => 'Hazardous Materials Review — Review historical use/storage of hazardous substances',
    's2_waste_disposal'         => 'Waste Disposal History — Assess previous waste disposal practices',
    's2_nearby_contamination'   => 'Nearby Contamination Sources — Assess proximity to industrial sites or landfills',
    's2_water_rights_permits'   => 'Water Rights & Permits — Verify water use rights and permits',
    's2_water_quality'          => 'Water Quality Assessment — Evaluate water source quality and reliability',
    's2_wetlands_habitats'      => 'Wetlands & Protected Habitats — Check for environmental protection restrictions',
    's2_environmental_permits'  => 'Environmental Permits — Verify environmental licenses and approvals',
]));

// ===== Service 3 — Health & Safety Assessment =====
$S3 = '3. Health & Safety Assessment';
$fields = array_merge($fields, $rowFields($S3, [
    's3_asbestos_survey'    => 'Asbestos Survey — Conduct asbestos inspection and management review',
    's3_lead_paint'         => 'Lead-Based Paint Inspection — Check for lead paint in older buildings',
    's3_mold_dampness'      => 'Mold & Dampness Assessment — Inspect indoor mold and moisture conditions',
    's3_indoor_air_quality' => 'Indoor Air Quality — Evaluate ventilation and air quality standards',
    's3_fire_safety'        => 'Fire Safety Compliance — Verify alarms, extinguishers, suppression systems, and exits',
    's3_fire_certificates'  => 'Fire Certificates — Confirm validity of fire approvals and inspections',
    's3_radon_gas'          => 'Radon / Gas Risks — Assess radon, carbon monoxide, or gas intrusion risks',
    's3_electrical_safety'  => 'Electrical Safety — Verify electrical safety compliance',
    's3_emergency_lighting' => 'Emergency Lighting — Confirm adequacy of emergency lighting systems',
    's3_slip_trip_fall'     => 'Slip, Trip & Fall Hazards — Identify physical safety hazards on-site',
]));

// ===== Service 4 — Operational & Miscellaneous Review =====
$S4 = '4. Operational & Miscellaneous Review';
$fields = array_merge($fields, $rowFields($S4, [
    's4_maintenance_records'    => 'Maintenance Records — Review maintenance logs and service history',
    's4_service_contracts'      => 'Service Contracts — Assess existing maintenance/service agreements',
    's4_equipment_warranties'   => 'Equipment Warranties — Verify active warranties on building systems',
    's4_site_security'          => 'Site Security — Evaluate fencing, CCTV, alarms, and access control',
    's4_accessibility'          => 'Accessibility Compliance — Confirm compliance with accessibility requirements',
    's4_parking_traffic'        => 'Parking & Traffic Flow — Assess operational movement and vehicle flow',
    's4_third_party_agreements' => 'Third-Party Agreements — Review easements, billboards, telecom towers, etc.',
    's4_day_night_inspection'   => 'Day & Night Site Inspection — Conduct inspections during different times/conditions',
    's4_photo_video_docs'       => 'Photo & Video Documentation — Maintain dated records of property condition',
]));
$fields[] = $file($S4, 's4_photo_video_uploads', 'Attach Photos / Videos', true);

// ===== Recommended Supporting Actions =====
$S5 = '5. Recommended Supporting Actions';
$fields = array_merge($fields, $rowFields($S5, [
    's5_engage_engineer'   => 'Engage Structural Engineer',
    's5_verify_docs'       => 'Verify Seller / Tenant Documents Independently',
    's5_prepare_report'    => 'Prepare Report',
]));
$fields[] = $file($S5, 's5_report_attachment', 'Attach Final Report', true);

// -------- persist --------
DB::transaction(function () use ($TEMPLATE_ID, $fields) {
    $template = ServiceFormTemplate::find($TEMPLATE_ID);
    if (!$template) {
        echo "ERROR: template #{$TEMPLATE_ID} not found.\n";
        return;
    }
    echo "Template: #{$template->id} {$template->form_name}\n";

    // Wipe all existing fields.
    $del = ServiceFormField::where('service_form_template_id', $TEMPLATE_ID)->delete();
    echo "  Deleted {$del} old fields.\n";

    $order = 10;
    foreach ($fields as $f) {
        ServiceFormField::create([
            'service_form_template_id'   => $TEMPLATE_ID,
            'field_name'                 => $f['name'],
            'field_label'                => $f['label'],
            'field_type'                 => $f['type'],
            'options'                    => $f['options'] ?? null,
            'is_required'                => (bool)($f['required'] ?? false),
            'sort_order'                 => $order,
            'section'                    => $f['section'],
            'allow_comments'             => false,
            'allow_attachment'           => (bool)($f['allow_attachment'] ?? false),
            'allow_multiple_attachments' => (bool)($f['allow_multiple_attachments'] ?? false),
            'attachment_label'           => null,
            'validation_rules'           => null,
        ]);
        $order += 10;
    }
    echo "  Inserted " . count($fields) . " new fields.\n";
});

echo "\nDone.\n";
