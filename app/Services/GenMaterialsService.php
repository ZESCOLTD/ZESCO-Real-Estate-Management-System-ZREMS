<?php

namespace App\Services;

use App\Models\ArticleView;
use App\Models\GenMaterialDetails;
use App\Models\GenMaterialHeaders;
use Illuminate\Support\Carbon;

class GenMaterialsService
{
    public function __construct() {}

    public static function createMaterials($reference, $formOrder)
    {

        $article = ArticleView::where('code_article', "401001-0224")->first();
        $stPur = null;
        $now = Carbon::now();

        GenMaterialHeaders::create([
            'proc_ref' => $stPur,
            'req_no' => $reference,
            'veh_reg_no' => null,
            'valid_date_from' => $now->toDateString(),
            'valid_date_to' => $now->copy()->addMonth()->toDateString(),
            'date_created' => $now->toDateString(),
            'created_by' => '2710',
            'created_at' => $now->toDateString(),
            'updated_at' => $now->toDateString(),
            'st_pur' => $stPur,
            'odometer' => null,
            'cost_centre' => null,
            'item_type' => 'NS',
            'workshop_no' => 1,
            'document_no' => null,
            'form_order' => $formOrder,
            'requested_by' => '72014',
            'authorised_by' => '71486',
            'town_from' => null,
            'town_to' => null,
            'comments' => 'TESTING TESTING TESTING TESTING TESTING TESTING TEST',
            'status' => 2,
            'requisition_type' => null,
            'cost_assigned_to' => 'CostCenter',
            'requested_by_id' => null,
            'store' => '1100',
            'purchase_office' => 'ZH',
            'supplier_code' => 'SNNR101684',
            'user_unit' => null,
            'issue_balance' => null,
            'is_fuel' => 'n',
            'project_name' => null,
            'extra_fuel' => null,
            'user_act' => null,
            'justification_rejection' => null,
        ]);

        GenMaterialDetails::create([
            'req_no' => $reference,
            'material_code' => $article->code_article,
            'quantity' => 1,
            'unit_of_measure' => '001',
            'specifications' => substr($article->technical_specifications, 0, 5),
            'project_code' => null,
            'supplier_code' => 'SNNR101684',
            'reg_no' => null,
            'amount' => '2000000',
            'price' => '2000000',
            'ref_no' => null,
            'date_created' => $now->toDateTimeString(),
            'created_by' => '72014',
            'created_at' => $now->toDateTimeString(),
            'updated_at' => $now->toDateTimeString(),
            'cost_centre' => null,
            'stores_code' => '1100',
            'movt_no' => null,
            'max_allowed' => null,
            'cost_centre_name' => null,
            'project_name' => null,
            'quantity_issued' => null,
            'description' => $article->technical_specifications,
            'claimed' => null,
            'defect_no' => null,
            'project_no' => null,
            'wshp_act_code' => null,
            'section_code' => null,
        ]);

    }

    public static function updateMaterials($reference,$prof_ref)
    {
        GenMaterialHeaders::where('req_no', $reference)->update([
            'proc_ref' => $prof_ref,
            'st_pur' => $prof_ref,
        ]);
    }
}
