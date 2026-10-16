<?php

/**
 * Seed the Boundary/Encroachment Verification form (Geomatics > Cadastral Survey).
 * Idempotent: skips if fields already exist on the template.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceChild;
use App\Models\ServiceFormField;
use App\Models\ServiceFormTemplate;
use Illuminate\Support\Facades\DB;

$child = ServiceChild::where('service_name', 'Boundary/Encroachment verification')
    ->orWhere('service_name', 'like', '%Boundary%')
    ->orWhere('service_name', 'like', '%Encroachment%')
    ->first();
if (!$child) {
    echo "ERROR: Could not find child service matching 'Boundary' or 'Encroachment'\n";
    exit(1);
}
echo "Target child: #{$child->id} {$child->service_name}\n";

$YN = ['Yes', 'No'];
$SURVEY_TYPE = [
    'Boundary Re-establishment',
    'Subdivision Survey',
    'Consolidation Survey',
    'Beacon Repositioning/Replacement',
];
$DATA_SEARCH_SRC = ['MNR', 'Council', 'Planning Authority', 'Other'];
$PAID_STATUS = ['Paid', 'Pending'];

/** helper: yes/no radio */
$yn = fn(string $section, string $name, string $label, bool $req = false) => [
    'section' => $section, 'name' => $name, 'label' => $label,
    'type' => 'radio', 'required' => $req, 'options' => $YN,
];
/** helper: text field */
$t = fn(string $section, string $name, string $label, bool $req = false) => [
    'section' => $section, 'name' => $name, 'label' => $label,
    'type' => 'text', 'required' => $req,
];
/** helper: textarea */
$ta = fn(string $section, string $name, string $label, bool $req = false) => [
    'section' => $section, 'name' => $name, 'label' => $label,
    'type' => 'textarea', 'required' => $req,
];
/** helper: file */
$file = fn(string $section, string $name, string $label, bool $multi = false) => [
    'section' => $section, 'name' => $name, 'label' => $label,
    'type' => 'file', 'required' => false,
    'allow_attachment' => true, 'allow_multiple_attachments' => $multi,
];

$fields = [];

// ===== 1. Property Details =====
$S1 = '1. Property Details';
$fields[] = $t($S1, 'property_owner', 'Property Owner', true);
$fields[] = $t($S1, 'plot_no',       'Plot No.', true);
$fields[] = $t($S1, 'province',      'Province', true);
$fields[] = $t($S1, 'town',          'Town', true);
$fields[] = $t($S1, 'district',      'District', true);
$fields[] = $t($S1, 'village',       'Village');
$fields[] = $t($S1, 'local_authority', 'Local Authority', true);
$fields[] = $file($S1, 'site_plan_or_title', 'Numbered Site Plan / Title Deed');

// ===== 2. Type of Survey =====
$S2 = '2. Type of Survey (Cadastral/Boundary Survey)';
$fields[] = ['section' => $S2, 'name' => 'survey_type', 'label' => 'Survey Type',
    'type' => 'checkbox_group', 'required' => true, 'options' => $SURVEY_TYPE];

// ===== Step 1: Data Search =====
$S3 = '3. Step 1 — Data Search';
$fields[] = ['section' => $S3, 'name' => 'data_search_source', 'label' => 'Data Search Source',
    'type' => 'checkbox_group', 'required' => false, 'options' => $DATA_SEARCH_SRC];
$fields[] = $ta($S3, 'data_search_recommendation', 'Recommendation');
$fields[] = $file($S3, 'data_search_findings', 'Attach Findings', true);

// ===== Step 2: Field Visit =====
$S4 = '4. Step 2 — Field Visit';
$fields[] = $yn($S4, 'fv_accessibility',   'Property accessibility');
$fields[] = $yn($S4, 'fv_existing_marks',  'Existing boundary marks');
$fields[] = $yn($S4, 'fv_occupation',      'Occupation boundaries');
$fields[] = $yn($S4, 'fv_physical',        'Physical features');
$fields[] = $yn($S4, 'fv_encroachments',   'Encroachments');
$fields[] = $yn($S4, 'fv_id_neighbours',   'Neighbouring properties');
$fields[] = $yn($S4, 'fv_id_roads_rivers', 'Roads / Rivers / Utility corridors');
$fields[] = $file($S4, 'fv_findings', 'Attach Findings', true);

// ===== Phase 3: Establish Survey Control =====
$S5 = '5. Phase 3 — Establish Survey Control';
$fields[] = $yn($S5, 'sc_trig_stations',  'Trigonometrical stations');
$fields[] = $yn($S5, 'sc_beacons',        'Survey beacons');
$fields[] = $yn($S5, 'sc_pcm',            'Permanent Control Marks (PCMs)');
$fields[] = $yn($S5, 'sc_gps_stations',   'GPS control stations');
$fields[] = $ta($S5, 'sc_coord_verify',   'Verify coordinates and condition (notes)');

// ===== Step 4: Establish Secondary Control =====
$S6 = '6. Step 4 — Establish Secondary Control';
$fields[] = $yn($S6, 'ssc_use_gnss_ts',   'Using GNSS or Total Station');
$fields[] = $yn($S6, 'ssc_occupy_known',  'Occupy known control points');
$fields[] = $yn($S6, 'ssc_traverse',      'Observe and establish traverse stations around the property');
$fields[] = $yn($S6, 'ssc_closure',       'Perform closure checks');
$fields[] = $yn($S6, 'ssc_adjust',        'Adjust traverse if necessary');
$fields[] = $yn($S6, 'ssc_good_geometry', 'Good geometry');
$fields[] = $yn($S6, 'ssc_redundancy',    'Redundancy in observations');
$fields[] = $yn($S6, 'ssc_closure_ok',    'Acceptable closure limits');

// ===== Phase 5: Boundary Identification =====
$S7 = '7. Phase 5 — Boundary Identification';
$fields[] = $yn($S7, 'bi_iron_pins',       'Iron pins');
$fields[] = $yn($S7, 'bi_concrete_beacons','Concrete beacons');
$fields[] = $yn($S7, 'bi_boundary_stones', 'Boundary stones');
$fields[] = $yn($S7, 'bi_coord_markers',   'Coordinate markers');
$fields[] = $yn($S7, 'bi_verify_position', 'Verify position');
$fields[] = $yn($S7, 'bi_check_condition', 'Check condition');
$fields[] = $yn($S7, 'bi_compare_records', 'Compare with survey records');

// ===== Step 6: Resolve Boundary Discrepancies =====
$S8 = '8. Step 6 — Resolve Boundary Discrepancies';
$fields[] = $yn($S8, 'rd_consult_owners',  'Consult adjoining owners');
$fields[] = $yn($S8, 'rd_compare_occup',   'Compare occupation with records');
$fields[] = $yn($S8, 'rd_review_history',  'Review historical surveys');
$fields[] = $yn($S8, 'rd_apply_regs',      'Apply cadastral regulations');
$fields[] = $ta($S8, 'rd_document',        'Document all findings');

// ===== Phase 7: Boundary Survey =====
$S9 = '9. Phase 7 — Boundary Survey';
$fields[] = $yn($S9, 'bs_corner_points',   'Corner points');
$fields[] = $yn($S9, 'bs_boundary_lines',  'Boundary lines');
$fields[] = $yn($S9, 'bs_adjoining',       'Adjoining boundaries');
$fields[] = $t ($S9, 'bs_distances',       'Distances');
$fields[] = $t ($S9, 'bs_bearings',        'Bearings');
$fields[] = $t ($S9, 'bs_coordinates',     'Coordinates');
$fields[] = $ta($S9, 'bs_multi_observations','Multiple observations taken for accuracy (notes)');

// ===== Step 8: Set New Boundary Beacons =====
$S10 = '10. Step 8 — Set New Boundary Beacons';
$fields[] = $yn($S10, 'sb_calc_coords',   'Calculate beacon coordinates');
$fields[] = $yn($S10, 'sb_peg_corners',   'Peg corners');
$fields[] = $yn($S10, 'sb_install',       'Install permanent beacons');
$fields[] = $yn($S10, 'sb_type_rc',       'Reinforced concrete monuments');
$fields[] = $yn($S10, 'sb_type_iron',     'Iron pins');
$fields[] = $yn($S10, 'sb_type_pillars',  'Survey pillars');
$fields[] = $t ($S10, 'sb_coords',        'Coordinates');
$fields[] = $ta($S10, 'sb_description',   'Description');
$fields[] = $t ($S10, 'sb_condition',     'Condition');
$fields[] = $file($S10, 'sb_records',     'Attach Beacon Records', true);

// ===== Step 9: Witness Boundary Placement =====
$S11 = '11. Step 9 — Witness Boundary Placement';
$fields[] = $yn($S11, 'wp_landowner_present', 'Landowner present');
$fields[] = $yn($S11, 'wp_neighbours_informed','Neighbours informed');
$fields[] = $yn($S11, 'wp_boundaries_accepted','Boundaries pointed out and accepted');
$fields[] = $file($S11, 'wp_attendance', 'Attach Attendance Register / Minutes', true);

// ===== Phase 10: Detail Survey =====
$S12 = '12. Phase 10 — Detail Survey';
$fields[] = $yn($S12, 'ds_buildings',  'Buildings');
$fields[] = $yn($S12, 'ds_fences',     'Fences');
$fields[] = $yn($S12, 'ds_roads',      'Roads');
$fields[] = $yn($S12, 'ds_drainage',   'Drainage lines');
$fields[] = $yn($S12, 'ds_power_lines','Power lines');
$fields[] = $yn($S12, 'ds_watercourses','Watercourses');

// ===== Phase 11: Computations =====
$S13 = '13. Phase 11 — Computations';
$fields[] = $yn($S13, 'cp_gnss',       'GNSS observations processed');
$fields[] = $yn($S13, 'cp_traverse',   'Traverse data processed');
$fields[] = $yn($S13, 'cp_total_stn',  'Total Station observations processed');
$fields[] = $yn($S13, 'cp_coord_acc',  'Coordinate accuracy verified');
$fields[] = $yn($S13, 'cp_closure',    'Traverse closure verified');
$fields[] = $yn($S13, 'cp_residuals',  'Observation residuals verified');

// ===== Step 12: Compute Parcel Area =====
$S14 = '14. Step 12 — Compute Parcel Area';
$fields[] = $yn($S14, 'pa_coords',    'Boundary coordinates computed');
$fields[] = $yn($S14, 'pa_bearings',  'Bearings computed');
$fields[] = $yn($S14, 'pa_distances', 'Distances computed');
$fields[] = $yn($S14, 'pa_area',      'Parcel area computed');

// ===== Step 13: Quality Assurance =====
$S15 = '15. Step 13 — Quality Assurance';
$fields[] = $yn($S15, 'qa_corners',        'All corners surveyed');
$fields[] = $yn($S15, 'qa_beacon_pos',     'Beacons correctly positioned');
$fields[] = $yn($S15, 'qa_area_calc',      'Area calculations correct');
$fields[] = $yn($S15, 'qa_national_grid',  'Survey ties to national grid');
$fields[] = $yn($S15, 'qa_legal_req',      'Legal requirements satisfied');

// ===== Phase 14: Preparation of Survey Documents =====
$S16 = '16. Phase 14 — Preparation of Survey Documents';
$fields[] = $yn($S16, 'sd_parcel_number',  'Parcel number');
$fields[] = $yn($S16, 'sd_dimensions',     'Boundary dimensions');
$fields[] = $yn($S16, 'sd_coordinates',    'Coordinates');
$fields[] = $yn($S16, 'sd_area',           'Area');
$fields[] = $yn($S16, 'sd_north_arrow',    'North arrow');
$fields[] = $yn($S16, 'sd_adjoining',      'Adjoining properties');
$fields[] = $yn($S16, 'sd_road_reserves',  'Road reserves');
$fields[] = $yn($S16, 'sd_certification',  'Surveyor certification');

// ===== Step 15: Beacon Description Report =====
$S17 = '17. Step 15 — Beacon Description Report';
$fields[] = ['section' => $S17, 'name' => 'beacons', 'label' => 'Beacons (add a row per beacon)',
    'type' => 'repeater', 'required' => false, 'options' => [
        ['name' => 'beacon_no',     'label' => 'Beacon Number',   'type' => 'text'],
        ['name' => 'coordinates',   'label' => 'Coordinates',     'type' => 'text'],
        ['name' => 'material',      'label' => 'Material Type',   'type' => 'text'],
        ['name' => 'condition',     'label' => 'Condition',       'type' => 'text'],
        ['name' => 'reference_ties','label' => 'Reference Ties',  'type' => 'text'],
    ]];

// ===== Step 16: Compile Survey Report =====
$S18 = '18. Step 16 — Compile Survey Report';
$fields[] = $yn($S18, 'sr_purpose',      'Purpose of survey');
$fields[] = $yn($S18, 'sr_method',       'Method used');
$fields[] = $yn($S18, 'sr_control_stns', 'Control stations');
$fields[] = $yn($S18, 'sr_instruments',  'Instruments used');
$fields[] = $yn($S18, 'sr_accuracy',     'Accuracy achieved');
$fields[] = $yn($S18, 'sr_computations', 'Computations');
$fields[] = $yn($S18, 'sr_observations', 'Observations');

// ===== Phase 17: Approval Process =====
$S19 = '19. Phase 17 — Approval Process';
$fields[] = $t($S19, 'ap_case_number', 'Case Number');
$fields[] = $t($S19, 'ap_cost_zmw',    'Cost (ZMW)');
$fields[] = ['section' => $S19, 'name' => 'ap_status', 'label' => 'Payment Status',
    'type' => 'radio', 'required' => false, 'options' => $PAID_STATUS];
$fields[] = $yn($S19, 'ap_submit_diagram',     'Submit Survey diagram');
$fields[] = $yn($S19, 'ap_submit_comp_sheets', 'Submit Computation sheets');
$fields[] = $yn($S19, 'ap_submit_coord_lists', 'Submit Coordinate lists');
$fields[] = $yn($S19, 'ap_submit_report',      'Submit Survey report');
$fields[] = $yn($S19, 'ap_submit_beacon_recs', 'Submit Beacon records');
$fields[] = $yn($S19, 'ap_to_surveyor_general','Submitted to Surveyor General');
$fields[] = $yn($S19, 'ap_to_dept_surveys',    'Submitted to Department of Surveys');
$fields[] = $yn($S19, 'ap_to_cadastral_auth',  'Submitted to relevant cadastral authority');

// ===== Step 18: Examination =====
$S20 = '20. Step 18 — Examination of Survey';
$fields[] = $yn($S20, 'ex_accuracy',     'Accuracy');
$fields[] = $yn($S20, 'ex_compliance',   'Compliance');
$fields[] = $yn($S20, 'ex_dimensions',   'Parcel dimensions');
$fields[] = $yn($S20, 'ex_boundary_ok',  'Boundary consistency');
$fields[] = $yn($S20, 'ex_regulations',  'Survey regulations');
$fields[] = $ta($S20, 'ex_corrections',  'Corrections requested (if any)');

// ===== Step 19: Approval & Authentication =====
$S21 = '21. Step 19 — Approval and Authentication';
$fields[] = $yn($S21, 'aa_authenticated',    'Survey diagram authenticated');
$fields[] = $yn($S21, 'aa_parcel_confirmed', 'Parcel number confirmed');
$fields[] = $yn($S21, 'aa_archived',         'Survey records archived');
$fields[] = $file($S21, 'aa_approved_survey', 'Attach Approved Survey', true);
$fields[] = $ta($S21, 'aa_comments',          'Comments');

// -------- persist --------
DB::transaction(function () use ($child, $fields) {
    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'Boundary/Encroachment Verification Form',
            'description' => 'Cadastral boundary verification workflow (phases 1–19).',
            'is_active'   => true,
        ]
    );
    echo "Template: #{$template->id} {$template->form_name}\n";

    $existing = ServiceFormField::where('service_form_template_id', $template->id)->count();
    if ($existing > 0) {
        echo "  Skipping: template already has {$existing} fields.\n";
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
    echo "  Inserted " . count($fields) . " fields across 21 sections.\n";
});

echo "\nDone.\n";
