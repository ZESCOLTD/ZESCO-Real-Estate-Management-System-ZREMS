<?php

namespace App\Imports;

use App\Models\ImportJob;
use App\Models\ImportError;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;

class AssetRegisterImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public function __construct(public ImportJob $job) {}

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {

            $insert = [];

            foreach ($rows as $index => $row) {

                try {

                    if (empty($row['asset_number'])) {
                        throw new \Exception("Missing asset number");
                    }

                    // ✅ Use provided LAMS number OR generate
                    $lamsNumber = !empty($row['lams_asset_number'])
                        ? $row['lams_asset_number']
                        : $this->generateLamsNumber();

                    $insert[] = [
                        'asset_number' => $row['asset_number'],
                        'lams_asset_number' => $lamsNumber,
                        'business_unit' => $row['business_unit'] ?? null,
                        'cost_centre' => $row['cost_centre'] ?? null,
                        'tag_number' => $row['tag_number'] ?? null,
                        'serial_number' => $row['serial_number'] ?? null,
                        'asset_natural_account' => $row['asset_natural_account'] ?? null,
                        'deprn_reserve_account' => $row['deprn_reserve_account'] ?? null,
                        'deprn_charge_account' => $row['deprn_charge_account'] ?? null,
                        'description' => $row['description'] ?? null,
                        'link_id' => $row['link_id'] ?? null,
                        'major_category' => $row['major_category'] ?? null,
                        'sub_major_category' => $row['sub_major_category'] ?? null,
                        'minor_category' => $row['minor_category'] ?? null,
                        'sub_minor_category' => $row['sub_minor_category'] ?? null,
                        'units' => $row['units'] ?? null,
                        'life_in_months' => $row['life_in_months'] ?? null,
                        'date_placed_in_service' => $row['date_placed_in_service'] ?? null,
                        'town' => $row['town'] ?? null,
                        'location_2' => $row['location_2'] ?? null,
                        'dept' => $row['dept'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // 🔥 REAL-TIME ROW UPDATE
                    $this->job->increment('processed_rows');

                    $this->job->update([
                        'current_row' => $this->job->processed_rows,
                        'current_item' => $row['asset_number'] ?? 'N/A',
                    ]);

                } catch (\Exception $e) {

                    ImportError::create([
                        'import_job_id' => $this->job->id,
                        'row_number' => $this->job->processed_rows + $index + 1,
                        'error_message' => $e->getMessage(),
                        'row_data' => json_encode($row),
                    ]);

                    $this->job->increment('failed_rows');
                }
            }

            // 🚀 UPSERT
            DB::table('fms_asset_register')->upsert(
                $insert,
                ['asset_number'],
                [
                    'lams_asset_number',
                    'business_unit',
                    'cost_centre',
                    'tag_number',
                    'serial_number',
                    'updated_at'
                ]
            );

        });
    }

    /**
     * ✅ Generate sequential LAMS number (SAFE + GLOBAL)
     */
    private function generateLamsNumber(): string
    {
        // Lock the sequence row to prevent race conditions
        $sequence = DB::table('fms_asset_sequence_register')
            ->lockForUpdate()
            ->first();

        if (!$sequence) {
            // First-time setup fallback
            DB::table('fms_asset_sequence_register')->insert([
                'last_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $nextNumber = 1;
        } else {
            $nextNumber = $sequence->last_number + 1;

            DB::table('fms_asset_sequence_register')
                ->where('id', $sequence->id)
                ->update([
                    'last_number' => $nextNumber,
                    'updated_at' => now(),
                ]);
        }

        // ✅ Format: LAMS-2026-000001
        return 'LAMS-' . date('Y') . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
