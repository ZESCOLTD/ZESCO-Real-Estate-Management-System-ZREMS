<?php
/**
 * HARD DELETE the "Commercial Contracts" service from LAMS entirely.
 *
 * Removes: submissions/values, fields, template, and the service child row.
 * Force-deletes to bypass SoftDeletes.
 *
 * Run:  php scripts/delete-commercial-contracts.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceChild;
use App\Models\ServiceFormTemplate;
use App\Models\ServiceFormField;
use App\Models\ServiceFormSubmission;
use App\Models\ServiceFormValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

DB::transaction(function () {
    $children = DB::table('service_childrens')
        ->where('service_name', 'Commercial Contracts')
        ->get();

    if ($children->isEmpty()) {
        echo "  No 'Commercial Contracts' service found.\n";
        return;
    }

    foreach ($children as $child) {
        echo "  Child #{$child->id}  parent_id={$child->service_parent_id}  {$child->service_name}\n";

        $templateIds = DB::table('service_form_templates')
            ->where('service_child_id', $child->id)
            ->pluck('id');

        foreach ($templateIds as $tplId) {
            $subIds = DB::table('service_form_submissions')
                ->where('service_form_template_id', $tplId)
                ->pluck('id');

            if ($subIds->isNotEmpty()) {
                $vDel = DB::table('service_form_values')
                    ->whereIn('service_form_submission_id', $subIds)->delete();
                echo "     ↳ Deleted {$vDel} form values.\n";
                $sDel = DB::table('service_form_submissions')
                    ->whereIn('id', $subIds)->delete();
                echo "     ↳ Deleted {$sDel} submissions.\n";
            }

            $fDel = DB::table('service_form_fields')
                ->where('service_form_template_id', $tplId)->delete();
            echo "     ↳ Deleted {$fDel} fields.\n";

            DB::table('service_form_templates')->where('id', $tplId)->delete();
            echo "     ↳ Deleted template #{$tplId}.\n";
        }

        if (Schema::hasTable('service_request_services')) {
            $srsDel = DB::table('service_request_services')
                ->where('service_child_id', $child->id)
                ->delete();
            if ($srsDel) echo "     ↳ Deleted {$srsDel} service_request_services rows.\n";
        }

        DB::table('service_childrens')->where('id', $child->id)->delete();
        echo "     ↳ Deleted child #{$child->id}.\n";
    }
});

echo "\nDone.\n";
