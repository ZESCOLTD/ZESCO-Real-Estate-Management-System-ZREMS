<?php

/**
 * Replace Valuation forms Sections 7 (Declaration) and 8 (Firm Contact)
 * with the Construction Materials + Building Condition Assessment block.
 *
 * Applies to templates #18 (Property Valuation) and #19 (Property Consultancy).
 * Idempotent: removes old sections 7/8 first, then re-inserts the new block.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceFormField;
use App\Models\ServiceFormTemplate;
use Illuminate\Support\Facades\DB;

// Discover template IDs by form_name so this works on any DB.
$TEMPLATES = ServiceFormTemplate::whereIn('form_name', [
        'Property Valuation Form',
        'Property Consultancy Form',
    ])->pluck('id')->all();

if (empty($TEMPLATES)) {
    echo "No Valuation templates found (looked for 'Property Valuation Form' / 'Property Consultancy Form').\n";
    exit(0);
}
echo "Target templates: " . implode(', ', $TEMPLATES) . "\n";

$YN       = ['Yes', 'No'];
$COND     = ['5 - New', '4 - Good', '3 - Fair', '2 - Poor', '1 - Critical'];
$BLDG_TYPE = ['Single', 'Double', 'Multi'];
$ROOF_DESIGN = ['Flat', 'Hipped', 'Gable', 'Dutch Gable', 'Other'];
$ROOF_MATERIAL = ['G.I. Sheets','Asbestos S','Asbestos T','IT4','IBR','Conc. Slates','Reinforced Concrete','Versatile Tiles','Translucent','Other'];
$STRUCTURE   = ['Timber','Steel','Other'];
$CEILING     = ['P. Board','Rhino/Gypsum','PVC Slates','T&G Boards','Other'];
$WALL_MAT    = ['Conc. Block','B. Brick','Prefabricated','Masonry','Steel Columns','Other'];
$FINISH_INT  = ['Plastered','Painted','Tiled','Rough Cast','Stone','Cladding','Other'];
$FINISH_EXT  = ['Plastered','Painted','Cladding','Rough Cast','Stone','Other'];
$WIN_FRAME   = ['Aluminium','Steel','Timber','Other'];
$WIN_GLAZING = ['Glazed','Louvers','Tinted','Burglar Bars','Other'];
$DOOR_FRAME  = ['Aluminium','Steel','Timber','Other'];
$DOOR_MAT    = ['Aluminium','Timber','Flush','Glass','Other'];
$FLOOR       = ['Cement Screed','Ceramic Tiles','Porcelain Tiles','PVC Tiles','Terrazzo','Parquet','Carpet','Other'];
$FENCE       = ['CBWF','Palisade','Floral Hedge','Precast','Brick','Stone','Chainlink Wire','Electric Fencing','Other'];
$ACCESS      = ['Double Opening','Single Opening','Sliding','Other'];
$SITE_SURF   = ['Brick Paved','Concrete Paved','Compacted Earth','Crushed Stone','Curb Stones','Road Frontage','Other'];
$SEWERAGE    = ['Mains','Septic Tank','Pit Latrine','Other'];

$t   = fn($sec,$name,$label,$req=false) => ['section'=>$sec,'name'=>$name,'label'=>$label,'type'=>'text','required'=>$req];
$num = fn($sec,$name,$label,$req=false) => ['section'=>$sec,'name'=>$name,'label'=>$label,'type'=>'number','required'=>$req];
$ta  = fn($sec,$name,$label,$req=false) => ['section'=>$sec,'name'=>$name,'label'=>$label,'type'=>'textarea','required'=>$req];
$sel = fn($sec,$name,$label,$opts,$req=false) => ['section'=>$sec,'name'=>$name,'label'=>$label,'type'=>'select','required'=>$req,'options'=>$opts];
$chk = fn($sec,$name,$label,$opts,$req=false) => ['section'=>$sec,'name'=>$name,'label'=>$label,'type'=>'checkbox_group','required'=>$req,'options'=>$opts];
$rad = fn($sec,$name,$label,$opts,$req=false) => ['section'=>$sec,'name'=>$name,'label'=>$label,'type'=>'radio','required'=>$req,'options'=>$opts];
$file= fn($sec,$name,$label,$multi=false) => ['section'=>$sec,'name'=>$name,'label'=>$label,'type'=>'file','required'=>false,'allow_attachment'=>true,'allow_multiple_attachments'=>$multi];
$sig = fn($sec,$name,$label) => ['section'=>$sec,'name'=>$name,'label'=>$label,'type'=>'signature','required'=>false];
$date= fn($sec,$name,$label,$req=false) => ['section'=>$sec,'name'=>$name,'label'=>$label,'type'=>'date','required'=>$req];

$fields = [];

// ===== Section 7: Building Type & Structure =====
$S7 = '7. Building Type & Structure';
$fields[] = $sel($S7, 'building_type',    'Building Type',      $BLDG_TYPE);
$fields[] = $num($S7, 'building_count',   'Number of Buildings');
$fields[] = $sel($S7, 'roof_design',      'Roof Design',        $ROOF_DESIGN);
$fields[] = $sel($S7, 'roof_material',    'Roof Material',      $ROOF_MATERIAL);
$fields[] = $sel($S7, 'structure',        'Structure',          $STRUCTURE);
$fields[] = $sel($S7, 'ceiling',          'Ceiling',            $CEILING);

// ===== Section 8: Construction Material & Finish =====
$S8 = '8. Construction Material & Finish';
$fields[] = $chk($S8, 'wall_material',      'Wall Material',              $WALL_MAT);
$fields[] = $chk($S8, 'internal_finish',    'Internal Finish',            $FINISH_INT);
$fields[] = $chk($S8, 'external_finish',    'External Finish',            $FINISH_EXT);
$fields[] = $ta ($S8, 'materials_notes',    'Additional Notes on Materials/Finishes');

// ===== Section 9: Windows, Doors & Floors =====
$S9 = '9. Windows, Doors & Floors';
$fields[] = $chk($S9, 'window_frame',    'Window Frame',      $WIN_FRAME);
$fields[] = $chk($S9, 'window_glazing',  'Window Glazing',    $WIN_GLAZING);
$fields[] = $chk($S9, 'door_frame',      'Door Frame',        $DOOR_FRAME);
$fields[] = $chk($S9, 'door_material',   'Door Material',     $DOOR_MAT);
$fields[] = $chk($S9, 'floor_material',  'Floor Material',    $FLOOR);

// ===== Section 10: Accommodation =====
$S10 = '10. Accommodation';
$fields[] = $num($S10, 'acc_bedrooms',   'Bedrooms');
$fields[] = $num($S10, 'acc_lounge',     'Lounge');
$fields[] = $num($S10, 'acc_dining',     'Dining');
$fields[] = $num($S10, 'acc_kitchen',    'Kitchen');
$fields[] = $num($S10, 'acc_bathroom',   'Bathrooms');
$fields[] = $num($S10, 'acc_toilet',     'Toilets');
$fields[] = $num($S10, 'acc_office',     'Offices');
$fields[] = $num($S10, 'acc_board_room', 'Board Rooms');
$fields[] = $num($S10, 'acc_warehouse',  'Warehouses');
$fields[] = $num($S10, 'acc_corridor',   'Corridors');
$fields[] = $num($S10, 'acc_msc',        'MSC');
$fields[] = $num($S10, 'acc_wrobes',     'Wardrobes');
$fields[] = $num($S10, 'acc_whb',        'WHB');
$fields[] = $num($S10, 'acc_kunits',     'Kitchen Units');
$fields[] = $num($S10, 'acc_btub',       'Bath Tubs');
$fields[] = $num($S10, 'acc_wc',         'WCs');
$fields[] = $ta ($S10, 'acc_other',      'Other Accommodation Notes');

// ===== Section 11: Outbuildings, Fence & Access =====
$S11 = '11. Outbuildings, Fence & Access';
$fields[] = $num($S11, 'ob_squarters',    "Servants' Quarters");
$fields[] = $num($S11, 'ob_cottage',      'Cottage');
$fields[] = $num($S11, 'ob_carport',      'Carport');
$fields[] = $num($S11, 'ob_store',        'Store Room');
$fields[] = $num($S11, 'ob_poultry',      'Poultry House');
$fields[] = $num($S11, 'ob_shed',         'Shed');
$fields[] = $num($S11, 'ob_garage',       'Garage');
$fields[] = $num($S11, 'ob_guardhouse',   'Guard House');
$fields[] = $num($S11, 'ob_rooms',        'Outbuilding Rooms');
$fields[] = $num($S11, 'ob_wc',           'Outbuilding WC');
$fields[] = $num($S11, 'ob_shower',       'Outbuilding Shower');
$fields[] = $num($S11, 'ob_whb',          'Outbuilding WHB');
$fields[] = $ta ($S11, 'ob_other',        'Other Outbuildings Notes');
$fields[] = $chk($S11, 'fence_type',      'Fence Type',        $FENCE);
$fields[] = $chk($S11, 'access_type',     'Access Type',       $ACCESS);
$fields[] = $ta ($S11, 'access_other',    'Other Access Notes');

// ===== Section 12: Services & Site =====
$S12 = '12. Services & Site';
$fields[] = $rad($S12, 'svc_water_supply', 'Water Supply',                 $YN);
$fields[] = $rad($S12, 'svc_water_tank',   'Water Tank',                   $YN);
$fields[] = $rad($S12, 'svc_electricity',  'Electricity',                  $YN);
$fields[] = $rad($S12, 'svc_standby_gen',  'Standby Diesel Generator Set', $YN);
$fields[] = $chk($S12, 'svc_sewerage',     'Sewerage',                     $SEWERAGE);
$fields[] = $chk($S12, 'site_surface',     'Site Surface',                 $SITE_SURF);
$fields[] = $ta ($S12, 'site_notes',       'Additional Site Notes');

// ===== Section 13: Building Condition Assessment =====
// Scale reference note as a textarea (read-only guidance).
$S13 = '13. Building Condition Assessment';
$scaleNote = "Rating Scale:\n"
    . "5 = NEW: like-new, no issues, plan 8–10 years\n"
    . "4 = GOOD: no reported issues, consider repairs/replacement 6–8 years\n"
    . "3 = FAIR: average wear for age, repairs/replacement 4–6 years\n"
    . "2 = POOR: end of expected lifespan, repair/replace 2–4 years\n"
    . "1 = CRITICAL: life expired, imminent failure risk\n"
    . "\nEvaluation considerations: (i) Age of component, (ii) Expected service life, "
    . "(iii) Maintenance records, (iv) Visual inspection condition.";
$fields[] = ['section'=>$S13,'name'=>'condition_scale_note','label'=>'Rating Scale Guidance',
    'type'=>'textarea','required'=>false, 'default'=>$scaleNote];

$elements = [
    'roofing'                 => 'Roofing',
    'gutters_downpipes'       => 'Gutters & Downpipes',
    'ceiling_cond'            => 'Ceiling',
    'external_wall_structure' => 'External Wall Structure',
    'internal_wall_structure' => 'Internal Wall Structure',
    'wall_finishes'           => 'Wall Finishes',
    'floor_finishes'          => 'Floor Finishes',
    'sanitary_fittings'       => 'Sanitary Fittings',
    'electrical_fittings'     => 'Electrical Fittings',
    'plumbing'                => 'Plumbing',
    'doors_cond'              => 'Doors',
    'windows_cond'            => 'Windows',
    'driveway_paving'         => 'Driveway / Paving (Asph./Conc.)',
    'drainages'               => 'Drainages',
    'curbing'                 => 'Curbing',
    'onsite_sidewalks'        => 'On-Site Sidewalks',
    'offsite_sidewalks'       => 'Off-Site Sidewalks',
    'carports_cond'           => 'Carports',
    'fencing_cond'            => 'Fencing',
    'foundation'              => 'Foundation',
];
foreach ($elements as $key => $label) {
    $fields[] = $rad($S13, 'cond_' . $key, $label, $COND);
}
$fields[] = $file($S13, 'condition_photos', 'Photographs', true);
$fields[] = $ta ($S13, 'condition_notes',  'Additional Notes / Observations');

// ===== Section 14: Inspection Sign-off =====
$S14 = '14. Inspection Sign-off';
$fields[] = $t   ($S14, 'inspected_by',       'Inspected By',         true);
$fields[] = $sig ($S14, 'inspector_signature_pad',  "Inspector Signature (draw)");
$fields[] = $file($S14, 'inspector_signature_file', 'Inspector Signature (upload scan/photo)');
$fields[] = $date($S14, 'inspection_signoff_date',  'Date', true);

// -------- persist --------
DB::transaction(function () use ($TEMPLATES, $fields) {
    foreach ($TEMPLATES as $tid) {
        // Wipe old sections 7 & 8 (Declaration + Firm Contact Details).
        $del = ServiceFormField::where('service_form_template_id', $tid)
            ->where(function ($q) {
                $q->where('section', 'like', '7.%')
                  ->orWhere('section', 'like', '8.%');
            })->delete();
        echo "tpl {$tid}: deleted {$del} old fields from Sections 7 & 8\n";

        // Find current max sort_order to append cleanly.
        $maxSort = (int) ServiceFormField::where('service_form_template_id', $tid)->max('sort_order');
        $order   = $maxSort + 10;

        $inserted = 0;
        foreach ($fields as $f) {
            // Skip if a field with this name already exists on this template.
            if (ServiceFormField::where('service_form_template_id', $tid)
                ->where('field_name', $f['name'])->exists()) {
                continue;
            }

            ServiceFormField::create([
                'service_form_template_id'   => $tid,
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
            $inserted++;
        }
        echo "tpl {$tid}: inserted {$inserted} new fields (Sections 7–14).\n";
    }
});

echo "\nDone.\n";
