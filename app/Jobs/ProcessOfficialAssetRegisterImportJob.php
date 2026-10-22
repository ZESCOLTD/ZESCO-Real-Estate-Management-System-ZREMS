<?php

namespace App\Jobs;

use App\Imports\OfficialAssetRegisterImport;
use App\Models\ImportError;
use App\Models\ImportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Throwable;

class ProcessOfficialAssetRegisterImportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $importJobId)
    {
    }

    public function handle(): void
    {
        $job = ImportJob::findOrFail($this->importJobId);
        $fullPath = storage_path('app/private/' . $job->file_path);

        try {
            $sheets = Excel::toCollection(null, $fullPath);
            $sheetRows = $sheets->first();
            $totalRows = max(($sheetRows?->count() ?? 0) - 1, 0);

            $job->update([
                'status' => 'processing',
                'total_rows' => $totalRows,
                'processed_rows' => 0,
                'failed_rows' => 0,
            ]);

            if ($sheetRows === null || $sheetRows->count() <= 1) {
                throw new RuntimeException('No readable data rows were found on the first worksheet. Check the workbook sheet and header row; if it is a legacy .xls, save a copy as .xlsx and retry.');
            }

            Excel::import(new OfficialAssetRegisterImport($job), $fullPath);

            $job->refresh();
            if ($job->processed_rows === 0) {
                throw new RuntimeException('The workbook was read, but no asset rows were processed. Check that Asset Num is present in the header row.');
            }

            $job->update(['status' => 'completed']);
        } catch (Throwable $exception) {
            ImportError::create([
                'import_job_id' => $job->id,
                'error_message' => $exception->getMessage(),
                'row_data' => ['file' => basename($job->file_path)],
            ]);

            $job->update([
                'status' => 'failed',
                'failed_rows' => max(1, (int) $job->failed_rows),
            ]);
            report($exception);
        }
    }
}
