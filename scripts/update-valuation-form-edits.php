<?php
/**
 * Update Valuation forms (templates 18 & 19) per Part 2/4/7/13/end edits.
 *
 * Part 2:
 *   - Property Type "Other" should trigger a text field to specify.
 *     (Adds property_type_other; UI shows via existing conditional pattern.)
 *   - Rename "Plot / Lot No." -> "Property number" everywhere
 *     (field labels only, keep field_name for data continuity).
 *
 * Part 4:
 *   - Rename section "4. Property Inspection" -> "4. Property Description"
 *   - Rename multiple_properties label:
 *     "Multiple Properties Under One Title" -> "Multiple Building Under One Title"
 *
 * Part 7:
 *   - Rename "Structure" -> "Roof Structure"
 *
 * Part 13:
 *   - Add a photo upload after each condition rating (roofing, ceiling, walls, etc.)
 *
 * End of form:
 *   - Add a "Valuation Report" file upload as the last field.
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceFormField;
use App\Models\ServiceFormTemplate;

// Discover template IDs by form_name (portable across local MySQL and Oracle test/prod).
$TEMPLATES = ServiceFormTemplate::whereIn('form_name', [
        'Property Valuation Form',
        'Property Consultancy Form',
    ])
    ->pluck('id')
    ->all();

if (empty($TEMPLATES)) {
    echo "No Valuation templates found. Run seed-valuation-department.php first.\n";
    exit(0);
}
echo "Target templates: " . implode(', ', $TEMPLATES) . "\n";

$added = 0; $updated = 0;

foreach ($TEMPLATES as $tid) {
    echo "\n=== Template #{$tid} ===\n";

    /* -------------------- PART 2 -------------------- */

    // Add property_type_other if missing
    $pt = ServiceFormField::where('service_form_template_id', $tid)
        ->where('field_name', 'property_type')->first();
    if ($pt && !ServiceFormField::where('service_form_template_id', $tid)
        ->where('field_name', 'property_type_other')->exists()) {
        ServiceFormField::create([
            'service_form_template_id'   => $tid,
            'field_name'                 => 'property_type_other',
            'field_label'                => 'If Other, please specify',
            'field_type'                 => 'text',
            'options'                    => null,
            'is_required'                => false,
            'sort_order'                 => $pt->sort_order + 1,
            'section'                    => $pt->section,
            'allow_comments'             => false,
            'allow_attachment'           => false,
            'allow_multiple_attachments' => false,
            'attachment_label'           => null,
            'validation_rules'           => null,
        ]);
        echo "  + added property_type_other\n";
        $added++;
    }

    // Rename Plot / Lot No. labels to 'Property number' everywhere on this template.
    $plotFields = ServiceFormField::where('service_form_template_id', $tid)
        ->where(function ($q) {
            $q->where('field_label', 'like', '%Plot / Lot%')
              ->orWhere('field_label', 'like', '%Plot/Lot%')
              ->orWhere('field_label', 'like', '%Plot / Lot No.%');
        })->get();
    foreach ($plotFields as $f) {
        $f->field_label = 'Property number';
        $f->save();
        echo "  * label -> 'Property number' on field '{$f->field_name}'\n";
        $updated++;
    }

    // Also update the repeater column label inside multiple_properties options
    $mp = ServiceFormField::where('service_form_template_id', $tid)
        ->where('field_name', 'multiple_properties')->first();
    if ($mp) {
        $opts = $mp->options ?? [];
        $changed = false;
        foreach ($opts as $k => $col) {
            if (is_array($col) && ($col['name'] ?? '') === 'plot_lot_no' && ($col['label'] ?? '') !== 'Property number') {
                $opts[$k]['label'] = 'Property number';
                $changed = true;
            }
        }
        if ($changed) {
            $mp->options = $opts;
            $mp->save();
            echo "  * multiple_properties column 'plot_lot_no' label -> 'Property number'\n";
            $updated++;
        }
    }

    /* -------------------- PART 4 -------------------- */

    // Rename section 4
    $renamed = ServiceFormField::where('service_form_template_id', $tid)
        ->where('section', '4. Property Inspection')
        ->update(['section' => '4. Property Description']);
    if ($renamed > 0) {
        echo "  * renamed section on {$renamed} field(s) -> '4. Property Description'\n";
        $updated += $renamed;
    }

    // Rename multiple_properties label
    if ($mp) {
        $mp2 = ServiceFormField::find($mp->id);
        if ($mp2->field_label !== 'Multiple Building Under One Title') {
            $mp2->field_label = 'Multiple Building Under One Title';
            $mp2->save();
            echo "  * multiple_properties label -> 'Multiple Building Under One Title'\n";
            $updated++;
        }
    }

    /* -------------------- PART 7 -------------------- */

    $struct = ServiceFormField::where('service_form_template_id', $tid)
        ->where('field_name', 'structure')->first();
    if ($struct && $struct->field_label !== 'Roof Structure') {
        $struct->field_label = 'Roof Structure';
        $struct->save();
        echo "  * structure label -> 'Roof Structure'\n";
        $updated++;
    }

    /* -------------------- PART 13 -------------------- */

    // Add a photo upload after every cond_* radio field.
    $condFields = ServiceFormField::where('service_form_template_id', $tid)
        ->where('field_name', 'like', 'cond_%')
        ->orderBy('sort_order')
        ->get();

    foreach ($condFields as $cf) {
        $photoName = 'photo_' . $cf->field_name; // e.g. photo_cond_roofing
        if (ServiceFormField::where('service_form_template_id', $tid)
            ->where('field_name', $photoName)->exists()) {
            continue;
        }

        // Insert just after this cond field. Use sort_order + 1 (won't collide
        // in practice because seed uses steps of 10 or more).
        $baseLabel = str_replace('Cond ', '', $cf->field_label);
        ServiceFormField::create([
            'service_form_template_id'   => $tid,
            'field_name'                 => $photoName,
            'field_label'                => $baseLabel . ' - Photographs',
            'field_type'                 => 'file',
            'options'                    => null,
            'is_required'                => false,
            'sort_order'                 => $cf->sort_order + 1,
            'section'                    => $cf->section,
            'allow_comments'             => false,
            'allow_attachment'           => true,
            'allow_multiple_attachments' => true,
            'attachment_label'           => null,
            'validation_rules'           => null,
        ]);
        echo "  + added photo upload for '{$cf->field_name}'\n";
        $added++;
    }

    /* -------------------- END OF FORM -------------------- */

    // Add valuation_report file field as the very last field
    if (!ServiceFormField::where('service_form_template_id', $tid)
        ->where('field_name', 'valuation_report')->exists()) {

        $maxSort = (int) ServiceFormField::where('service_form_template_id', $tid)->max('sort_order');

        // Put it in the final section (Sign-off) or create dedicated one
        $lastField = ServiceFormField::where('service_form_template_id', $tid)
            ->orderByDesc('sort_order')->first();
        $section   = $lastField ? $lastField->section : '15. Report';

        ServiceFormField::create([
            'service_form_template_id'   => $tid,
            'field_name'                 => 'valuation_report',
            'field_label'                => 'Valuation Report',
            'field_type'                 => 'file',
            'options'                    => null,
            'is_required'                => false,
            'sort_order'                 => $maxSort + 100,
            'section'                    => $section,
            'allow_comments'             => false,
            'allow_attachment'           => true,
            'allow_multiple_attachments' => false,
            'attachment_label'           => null,
            'validation_rules'           => null,
        ]);
        echo "  + added final 'valuation_report' upload\n";
        $added++;
    }
}

echo "\n---\nDone. Added: {$added}, Updated: {$updated}\n";
