<?php
/**
 * Cleanup test/prod after botched sync-service-parents run.
 *
 * 1. Move Litigation ADR + Litigation Services children from GEOMATIC (#22)
 *    to the REAL Litigation parent (Legal Department / Legal Services #201 on test).
 * 2. Delete empty duplicate parents created by sync (children=0).
 *
 * Safe to run repeatedly. Uses names to locate parents.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceParent;
use App\Models\ServiceChild;
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    // 1. Locate REAL Litigation parent (Legal Department / Legal Services)
    $realLitigation = ServiceParent::where('department_name', 'Legal Department')
        ->where('service_name', 'Legal Services')
        ->first();

    if (!$realLitigation) {
        echo "!! Could not find real Litigation parent (Legal Department / Legal Services). Aborting move step.\n";
    } else {
        echo "Real Litigation parent = #{$realLitigation->id}  {$realLitigation->department_name} / {$realLitigation->service_name}\n";

        // 2. Move the two litigation children (by name) to the real parent
        //    They may currently point at any wrong parent (e.g. #22 GEOMATIC on test, or #221-#225 duplicates)
        $moved = 0;
        foreach (['Alternative Dispute Resolution', 'Litigation Services'] as $childName) {
            $children = ServiceChild::where('service_name', $childName)->get();
            foreach ($children as $ch) {
                if ($ch->service_parent_id != $realLitigation->id) {
                    echo "MOVING child #{$ch->id} '{$childName}' from parent #{$ch->service_parent_id} -> #{$realLitigation->id}\n";
                    $ch->service_parent_id = $realLitigation->id;
                    $ch->save();
                    $moved++;
                }
            }
        }
        echo "Moved {$moved} litigation children.\n\n";
    }

    // 3. Delete EMPTY duplicate parents created by the botched sync.
    //    Only delete parents whose children count is 0 AND whose (department_name, service_name)
    //    exists as a duplicate on another older parent id.
    $duplicateTargets = [
        ['Geomatics',                    'wayleave'],
        ['Lines / Wayleaves',            'First Default Services'],
        ['Spatial Planning Department',  'First Default Services'],
        ['Security Department',          'Security Due Diligence Services'],
        ['Litigation Department',        'Legal Services'],
        ['Finance Department',           'Fixed Asset Register - Land & Buildings'],
        ['Procurement Department',       'Default Service'],
        ['Finance Department',           'Payments Services'],
    ];

    $deleted = 0;
    foreach ($duplicateTargets as [$dept, $svc]) {
        $rows = ServiceParent::where('department_name', $dept)
            ->where('service_name', $svc)
            ->orderByDesc('id')
            ->get();

        // Keep the OLDEST id (lowest) if it has children; delete newer empties.
        // But if all are empty and it's a fresh dept (no older row exists), leave the oldest alone.
        if ($rows->count() <= 1) {
            continue;
        }

        // Sort ascending: keep first with children (or first overall) and delete newer empties.
        $rowsAsc = $rows->sortBy('id')->values();
        $keeper = null;
        foreach ($rowsAsc as $r) {
            $cnt = ServiceChild::where('service_parent_id', $r->id)->count();
            if ($cnt > 0) { $keeper = $r; break; }
        }
        if (!$keeper) {
            $keeper = $rowsAsc->first();
        }

        foreach ($rowsAsc as $r) {
            if ($r->id === $keeper->id) continue;
            $cnt = ServiceChild::where('service_parent_id', $r->id)->count();
            if ($cnt === 0) {
                echo "DELETING duplicate parent #{$r->id}  {$dept} / {$svc}  (keeping #{$keeper->id})\n";
                $r->delete();
                $deleted++;
            } else {
                echo "SKIP duplicate parent #{$r->id}  has {$cnt} children — needs manual review\n";
            }
        }
    }

    echo "\nDeleted {$deleted} empty duplicate parents.\n";
    echo "Total parents now: " . ServiceParent::count() . PHP_EOL;
});
