<?php

namespace App\Imports;

use App\Models\AssetRegisterConfiguration;
use App\Models\ImportError;
use App\Models\ImportJob;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class OfficialAssetRegisterImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private const LOOKUP_FIELDS = [
        'business_unit',
        'cost_centre',
        'area',
        'location',
        'sub_location',
        'major_category',
        'sub_major_category',
        'minor_category',
        'status',
        'reportline',
    ];

    public function __construct(public ImportJob $job)
    {
    }

    public function collection(Collection $rows): void
    {
        $upserts = [];
        $lookupValues = [];

        foreach ($rows as $index => $row) {
            $raw = $row->toArray();

            try {
                $assetNumber = $this->firstValue($raw, ['asset_num', 'asset_number', 'asset_no']);
                if ($assetNumber === null || trim((string) $assetNumber) === '') {
                    throw new \InvalidArgumentException('Missing Asset Num.');
                }

                $mapped = [
                    'business_unit' => $this->textValue($this->firstValue($raw, ['bu', 'business_unit'])),
                    'cost_centre' => $this->textValue($this->firstValue($raw, ['cc', 'cost_centre', 'cost_center'])),
                    'asset_number' => $this->textValue($assetNumber),
                    'description' => $this->textValue($this->firstValue($raw, ['asset_description', 'description'])),
                    'area' => $this->textValue($this->firstValue($raw, ['area'])),
                    'location' => $this->textValue($this->firstValue($raw, ['location'])),
                    'sub_location' => $this->textValue($this->firstValue($raw, ['sub_location', 'sublocation'])),
                    'major_category' => $this->textValue($this->firstValue($raw, ['major_category'])),
                    'sub_major_category' => $this->textValue($this->firstValue($raw, ['sub_major_category'])),
                    'minor_category' => $this->textValue($this->firstValue($raw, ['minor_category'])),
                    'date_placed_in_service' => $this->dateValue($this->firstValue($raw, ['date_placed_in_service', 'date_placed', 'date_place', 'date_placed_in'])),
                    'life_in_months' => $this->integerValue($this->firstValue($raw, ['life_in_months', 'life_in_month', 'life_in_mo', 'life_in_mor'])),
                    'asset_cost' => $this->decimalValue($this->firstValue($raw, ['cost', 'asset_cost'])),
                    'status' => $this->textValue($this->firstValue($raw, ['status'])),
                    'reportline' => $this->textValue($this->firstValue($raw, ['reportline', 'report_line'])),
                    'source_row' => json_encode($raw, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR),
                    'last_import_job_id' => $this->job->id,
                    'imported_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $upserts[$mapped['asset_number']] = $mapped;

                foreach (self::LOOKUP_FIELDS as $field) {
                    if ($mapped[$field] !== null && $mapped[$field] !== '') {
                        $lookupValues[$field][$mapped[$field]] = true;
                    }
                }

                $this->job->increment('processed_rows');
            } catch (Throwable $exception) {
                ImportError::create([
                    'import_job_id' => $this->job->id,
                    'row_number' => $index + 2,
                    'error_message' => $exception->getMessage(),
                    'row_data' => $raw,
                ]);
                $this->job->increment('failed_rows');
                $this->job->increment('processed_rows');
            }
        }

        if ($upserts !== []) {
            DB::table('asset_registers')->upsert(
                array_values($upserts),
                ['asset_number'],
                [
                    'business_unit',
                    'cost_centre',
                    'description',
                    'area',
                    'location',
                    'sub_location',
                    'major_category',
                    'sub_major_category',
                    'minor_category',
                    'date_placed_in_service',
                    'life_in_months',
                    'asset_cost',
                    'status',
                    'reportline',
                    'source_row',
                    'last_import_job_id',
                    'imported_at',
                    'updated_at',
                ]
            );
        }

        foreach ($lookupValues as $fieldKey => $values) {
            foreach (array_keys($values) as $value) {
                AssetRegisterConfiguration::firstOrCreate(
                    ['field_key' => $fieldKey, 'value' => $value],
                    ['sort_order' => 0, 'is_active' => true]
                );
            }
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }

    private function firstValue(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                return $row[$key];
            }
        }

        return null;
    }

    private function textValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return (string) $value;
    }

    private function decimalValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return number_format((float) $value, 2, '.', '');
        }

        $normalized = str_replace([',', '$', ' '], '', (string) $value);
        if (! is_numeric($normalized)) {
            throw new \InvalidArgumentException('Cost must be numeric.');
        }

        return number_format((float) $normalized, 2, '.', '');
    }

    private function integerValue(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value) || (float) $value < 0 || floor((float) $value) !== (float) $value) {
            throw new \InvalidArgumentException('Life in Months must be a whole number.');
        }

        return (int) $value;
    }

    private function dateValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value) && (float) $value > 20000) {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (Throwable) {
            throw new \InvalidArgumentException('Date Placed must be a valid date.');
        }
    }
}
