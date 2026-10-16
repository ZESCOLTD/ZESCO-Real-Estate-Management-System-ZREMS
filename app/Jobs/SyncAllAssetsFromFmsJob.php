<?php

namespace App\Jobs;

use App\Models\FmsAssetRegister;
use App\Models\User;
use App\Models\IpaPhrisView;
use App\Models\ZescoLamsFaV;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncAllAssetsFromFmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $assets = FmsAssetRegister::all();

        Cache::put('fms_sync_total', $assets->count());

        $count = 0;

        foreach ($assets as $asset) {

            $fms = ZescoLamsFaV::where('asset_number', $asset->asset_number)->first();

            if ($fms) {

                $asset->update([

                    'cost_centre' => $fms->cost_centre,
                    'tag_number' => $fms->tag_number,
                    'serial_number' => $fms->serial_number,
                    'asset_natural_account' => $fms->asset_natural_account,
                    'deprn_reserve_account' => $fms->deprn_reserve_account,
                    'deprn_charge_account' => $fms->deprn_charge_account,
                    'description' => $fms->description,
                    'link_id' => $fms->link_id,
                    'major_category' => $fms->major_category,
                    'sub_major_category' => $fms->sub_major_category,
                    'minor_category' => $fms->minor_category,
                    'sub_minor_category' => $fms->sub_minor_category,
                    'units' => $fms->units,
                    'life_in_months' => $fms->life_in_months,
                    'date_placed_in_service' => $fms->date_placed_in_service,
                    'town' => $fms->town,
                    'location_2' => $fms->location_2,
                    'dept' => $fms->dept

                ]);

            }

            $count++;

            Cache::put('fms_sync_count', $count);
        }

        Cache::put('fms_sync_running', false);
    }
}
