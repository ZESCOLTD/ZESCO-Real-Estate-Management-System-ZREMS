<?php

namespace App\Jobs;

use App\Imports\TenantRegisterImport;
use App\Models\ImportError;
use App\Models\ImportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;
use Throwable;

class ProcessTenantRegisterImportJob implements ShouldQueue
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
            $job->update(['status' => 'processing', 'total_rows' => 0, 'processed_rows' => 0, 'failed_rows' => 0]);
            $headerRow = $this->findHeaderRow($fullPath);
            if ($headerRow === null) {
                throw new RuntimeException('Could not find a header row containing Serial No. and Current Tenant.');
            }

            Excel::import(new TenantRegisterImport($job, $headerRow), $fullPath);
            $job->refresh();

            if ($job->processed_rows === 0) {
                throw new RuntimeException('No tenant rows were found. Use the consolidated template with a header row and property records.');
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

    private function findHeaderRow(string $fullPath): ?int
    {
        $reader = IOFactory::createReaderForFile($fullPath);
        $reader->setReadDataOnly(true);
        $workbook = $reader->load($fullPath);
        $worksheet = $workbook->getSheet(0);
        $lastRow = min(30, $worksheet->getHighestDataRow());

        for ($row = 1; $row <= $lastRow; $row++) {
            $values = $worksheet->rangeToArray("A{$row}:V{$row}", null, true, false)[0];
            $contents = strtolower(implode(' ', array_map(fn ($value) => (string) $value, $values)));

            if (str_contains($contents, 'serial no') && str_contains($contents, 'current tenant')) {
                $workbook->disconnectWorksheets();

                return $row;
            }
        }

        $workbook->disconnectWorksheets();

        return null;
    }
}