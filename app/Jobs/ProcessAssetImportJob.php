<?php

namespace App\Jobs;

use App\Models\ImportJob;
use App\Imports\AssetRegisterImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAssetImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $importJobId) {}

    public function handle()
    {
        $job = ImportJob::findOrFail($this->importJobId);

        $fullPath = storage_path('app/private/' . $job->file_path);

        // 🔥 STEP 1: PRE-CALCULATE TOTAL ROWS (CRITICAL FOR %)
        $rows = Excel::toCollection(null, $fullPath)->first();
        $totalRows = $rows ? $rows->count() : 0;

        $job->update([
            'status' => 'processing',
            'total_rows' => $totalRows,
            'processed_rows' => 0,
            'failed_rows' => 0,
            'current_row' => 0,
        ]);

        // 🔥 STEP 2: RUN IMPORT
        Excel::import(
            new AssetRegisterImport($job),
            $fullPath,
            null,
            \Maatwebsite\Excel\Excel::XLSX
        );

        $job->update(['status' => 'completed']);
    }
}
