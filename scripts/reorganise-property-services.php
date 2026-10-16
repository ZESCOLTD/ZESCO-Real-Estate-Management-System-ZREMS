<?php
/**
 * Reorganise Property Department children per new target structure.
 *
 * Idempotent: safe to re-run. Only moves children if not already in the
 * correct parent. Only deletes duplicate parents/children when they are
 * empty (no form template, or template with zero fields).
 *
 * Leaves untouched: any Conveyance #44 child not explicitly listed in the
 * target structure (per business decision).
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceParent;
use App\Models\ServiceChild;
use App\Models\ServiceFormTemplate;
use App\Models\ServiceFormField;
use Illuminate\Support\Facades\DB;

// -----------------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------------
function parentByName(string $service): ?ServiceParent {
    return ServiceParent::where('department_name', 'Property Department')
        ->where('service_name', $service)
        ->first();
}

function childCount(int $parentId): int {
    return ServiceChild::where('service_parent_id', $parentId)->count();
}

function templateFieldCount(?ServiceChild $c): int {
    if (!$c) return 0;
    $t = ServiceFormTemplate::where('service_child_id', $c->id)->first();
    if (!$t) return 0;
    return ServiceFormField::where('service_form_template_id', $t->id)->count();
}

// -----------------------------------------------------------------------------
// Target moves
// -----------------------------------------------------------------------------
$moves = [
    // [child_id, child_name, target parent service_name]
    [321, 'Application for Numbering',                'Conveyance Services'],
    [148, 'Application For Conversion - Council',     'Land Conversion'],
    [147, "Application for Chief's Consent",          'Land Conversion'],
    [150, 'Application for Lease',                    'Lease Management'],
    [267, 'Asset Lease Management',                   'Lease Management'],
    [ 52, 'Long term land leases (Drafting/Review)',  'Lease Management'],
    [ 53, 'Residential (landlord) Lease Drafting/Review', 'Lease Management'],
    [265, 'Right of Use',                             'Lease Management'],
    [266, 'Sublease Agreement Management',            'Lease Management'],
    [269, 'Change of Land Use',                       'Regularisation'],
    [155, 'Local Authority - Regularization',         'Regularisation'],
    [270, 'Construction Permit Application',          'Governance, Compliance & Risk'],
];

// Empty duplicate children (superseded by the child being moved above).
// These are deleted only if they still have no template + no fields.
$emptyDuplicateChildren = [
    322, // Application for Numbering dup (target keeps #321)
    261, // Application for Conversion - Council dup (target keeps #148)
    262, // Application for Chief's Consent dup (target keeps #147)
    272, // Local Authority - Regularisation dup (target keeps #155)
    281, // Sublease Agreement - Management dup (target keeps #266)
];

DB::transaction(function () use ($moves, $emptyDuplicateChildren) {
    $movedCount = $skippedCount = $deletedChildren = 0;

    echo "=== MOVE PHASE ===" . PHP_EOL;
    foreach ($moves as [$childId, $expectedName, $targetParentSvc]) {
        $child = ServiceChild::find($childId);
        if (!$child) {
            echo "  MISSING child #{$childId} '{$expectedName}' — skipping" . PHP_EOL;
            continue;
        }
        $target = parentByName($targetParentSvc);
        if (!$target) {
            echo "  MISSING parent '{$targetParentSvc}' — skipping child #{$childId}" . PHP_EOL;
            continue;
        }
        if ($child->service_parent_id === $target->id) {
            echo "  OK    child #{$childId} '{$child->service_name}' already under '{$targetParentSvc}' (#{$target->id})" . PHP_EOL;
            $skippedCount++;
            continue;
        }
        $from = $child->service_parent_id;
        $child->service_parent_id = $target->id;
        $child->save();
        echo "  MOVE  child #{$childId} '{$child->service_name}': #{$from} -> #{$target->id} ({$targetParentSvc})" . PHP_EOL;
        $movedCount++;
    }

    echo PHP_EOL . "=== DEDUPE EMPTY CHILDREN ===" . PHP_EOL;
    foreach ($emptyDuplicateChildren as $cid) {
        $c = ServiceChild::find($cid);
        if (!$c) {
            echo "  MISSING child #{$cid} — already gone" . PHP_EOL;
            continue;
        }
        $fields = templateFieldCount($c);
        if ($fields > 0) {
            echo "  KEEP  child #{$cid} '{$c->service_name}' — has {$fields} fields, refusing to delete" . PHP_EOL;
            continue;
        }
        // Delete any empty template attached to it
        ServiceFormTemplate::where('service_child_id', $c->id)->delete();
        echo "  DEL   child #{$cid} '{$c->service_name}' (empty duplicate)" . PHP_EOL;
        $c->delete();
        $deletedChildren++;
    }

    echo PHP_EOL . "=== SUMMARY ===" . PHP_EOL;
    echo "  moved children:   {$movedCount}" . PHP_EOL;
    echo "  already correct:  {$skippedCount}" . PHP_EOL;
    echo "  deleted dup children: {$deletedChildren}" . PHP_EOL;
});

// -----------------------------------------------------------------------------
// Post-report: everything in Property with no template / no fields
// -----------------------------------------------------------------------------
echo PHP_EOL . "=== POST-STATE: children needing forms ===" . PHP_EOL;
$parents = ServiceParent::where('department_name', 'Property Department')->orderBy('id')->get();
foreach ($parents as $p) {
    $missing = [];
    foreach (ServiceChild::where('service_parent_id', $p->id)->orderBy('service_name')->get() as $c) {
        $t = ServiceFormTemplate::where('service_child_id', $c->id)->first();
        $fields = $t ? ServiceFormField::where('service_form_template_id', $t->id)->count() : 0;
        if ($fields === 0) {
            $missing[] = "   #" . str_pad($c->id, 4) . ' ' . $c->service_name;
        }
    }
    if (!empty($missing)) {
        echo PHP_EOL . "PARENT #{$p->id} | {$p->service_name}" . PHP_EOL;
        foreach ($missing as $line) echo $line . PHP_EOL;
    }
}
