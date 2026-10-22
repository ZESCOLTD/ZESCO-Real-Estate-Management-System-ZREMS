<?php

namespace App\Imports;

use App\Models\ImportError;
use App\Models\ImportJob;
use App\Models\TenantRegister;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class TenantRegisterImport implements ToCollection, WithHeadingRow, WithChunkReading, WithCalculatedFormulas
{
    private const FIELDS = [
        'serial_no' => ['serial_no', 'serial_number'],
        'station_location' => ['station_location', 'stationlocation', 'station', 'site', 'station_location_name'],
        'category' => ['category', 'housing_category', 'property_category'],
        'current_tenant' => ['current_tenant', 'tenant'],
        'man_no' => ['man_no', 'man_number'],
        'employer' => ['employer'],
        'location' => ['location', 'location_code'],
        'physical_address' => ['physical_address_hs_e_no', 'physical_addresshs_e_no', 'physical_address_hse_no', 'physical_address', 'hse_no', 'house_no'],
        'area_m2' => ['area_in_m2_dwelling_house', 'area_in_m2', 'area_in_m', 'area_m2', 'area'],
        'bedrooms' => ['no_of_b_rooms', 'no_of_brooms', 'number_of_b_rooms', 'bedrooms'],
        'open_closed_verandah_m2' => ['open_closed_verandah_m2', 'openclosed_verandah_m2', 'open_closed_verandah', 'verandah_m2'],
        'garage_m2' => ['garage_m2', 'garage'],
        'servants_quarter_m2' => ['servants_quarter_m2', 'servants_quarters_m2', 'servants_quarter'],
        'condition' => ['condition'],
        'current_rental_charge' => ['current_rental_charge_zmw', 'current_rental_charge'],
        'market_rental_charge_gvd' => ['market_rental_charge_gvd_zmw', 'market_rental_charge_gvd'],
        'proposed_rental_charge_50_percent' => ['proposed_rental_charge_50_zmw', 'proposed_rental_charge_at_50', 'proposed_rental_charge_zmw', 'proposed_rental_charge_50_percent'],
        'proposed_rentals_after_verification' => ['proposed_rentals_after_verification_zmw', 'proposed_rentals_after_verification'],
        'proposed_percentage_increment' => ['proposed_percentage_increment', 'proposed_percentage_increament'],
        'water_charges' => ['water_charges'],
        'sewer_charges' => ['sewer_charges'],
        'proposed_total_amount' => ['proposed_total_amount'],
    ];

    private const NUMERIC_FIELDS = [
        'area_m2', 'open_closed_verandah_m2', 'garage_m2', 'servants_quarter_m2',
        'current_rental_charge', 'market_rental_charge_gvd', 'proposed_rental_charge_50_percent',
        'proposed_rentals_after_verification', 'proposed_percentage_increment', 'water_charges',
        'sewer_charges', 'proposed_total_amount',
    ];

    public function __construct(public ImportJob $job, private int $headerRow = 1)
    {
    }

    public function headingRow(): int
    {
        return $this->headerRow;
    }

    public function collection(Collection $rows): void
    {
        $upserts = [];
        $processedRows = 0;
        $failedRows = 0;

        foreach ($rows as $index => $row) {
            $raw = $row->toArray();
            $normalized = $this->normalizeKeys($raw);
            if ($this->isNonRecordRow($normalized)) {
                continue;
            }

            $processedRows++;

            try {
                $mapped = [];
                foreach (self::FIELDS as $field => $aliases) {
                    $value = $this->firstValue($normalized, $aliases);
                    $mapped[$field] = in_array($field, self::NUMERIC_FIELDS, true)
                        ? $this->decimalValue($value)
                        : $this->textValue($value);
                }
                $mapped['bedrooms'] = $this->integerValue($this->firstValue($normalized, self::FIELDS['bedrooms']));

                $unitKey = TenantRegister::unitKeyFor($mapped);
                if ($unitKey === null) {
                    throw new \InvalidArgumentException('Station/Location, Category, and either Physical Address/Hse No. or Serial No. are required to identify the property.');
                }

                $mapped['unit_key'] = $unitKey;
                $mapped['source_row'] = json_encode($raw, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
                $mapped['last_import_job_id'] = $this->job->id;
                $mapped['imported_at'] = now();
                $mapped['created_at'] = now();
                $mapped['updated_at'] = now();
                $mapped['deleted_at'] = null;
                $upserts[$unitKey] = $mapped;
            } catch (Throwable $exception) {
                ImportError::create([
                    'import_job_id' => $this->job->id,
                    'row_number' => (int) $index + $this->headerRow + 1,
                    'error_message' => $exception->getMessage(),
                    'row_data' => $raw,
                ]);
                $failedRows++;
            }
        }

        if ($upserts !== []) {
            DB::table('tenant_registers')->upsert(
                array_values($upserts),
                ['unit_key'],
                [
                    'serial_no', 'station_location', 'category', 'current_tenant', 'man_no', 'employer',
                    'location', 'physical_address', 'area_m2', 'bedrooms', 'open_closed_verandah_m2',
                    'garage_m2', 'servants_quarter_m2', 'condition', 'current_rental_charge',
                    'market_rental_charge_gvd', 'proposed_rental_charge_50_percent',
                    'proposed_rentals_after_verification', 'proposed_percentage_increment',
                    'water_charges', 'sewer_charges', 'proposed_total_amount', 'source_row',
                    'last_import_job_id', 'imported_at', 'deleted_at', 'updated_at',
                ]
            );
        }

        if ($processedRows > 0) {
            $this->job->increment('total_rows', $processedRows);
            $this->job->increment('processed_rows', $processedRows);
        }
        if ($failedRows > 0) {
            $this->job->increment('failed_rows', $failedRows);
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }

    private function normalizeKeys(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $key = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '_', (string) $key), '_'));
            $normalized[$key] = $value;
        }

        return $normalized;
    }

    private function isNonRecordRow(array $row): bool
    {
        $tenant = strtolower(trim((string) $this->firstValue($row, self::FIELDS['current_tenant'])));
        $serial = strtolower(trim((string) $this->firstValue($row, self::FIELDS['serial_no'])));
        if (in_array($tenant, ['current tenant', 'tenant', 'total', 'subtotal', 'grand total', 'overall total'], true)
            || in_array($serial, ['serial no', 'serial number'], true)) {
            return true;
        }

        foreach (['current_tenant', 'physical_address_hs_e_no', 'physical_address_hse_no', 'physical_address', 'hse_no', 'house_no', 'man_no'] as $key) {
            if (isset($row[$key]) && trim((string) $row[$key]) !== '') {
                return false;
            }
        }

        return $serial === '' || ! is_numeric(rtrim($serial, '.'));
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
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return trim((string) $value);
    }

    private function decimalValue(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $normalized = preg_replace('/[^0-9,.\-]/', '', (string) $value);
        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $normalized = str_replace(',', '', $normalized);
        } elseif (str_contains($normalized, ',')) {
            $normalized = preg_match('/,\d{1,2}$/', $normalized)
                ? str_replace(',', '.', $normalized)
                : str_replace(',', '', $normalized);
        }

        if (! is_numeric($normalized)) {
            throw new \InvalidArgumentException('A numeric value could not be read.');
        }

        return number_format((float) $normalized, 2, '.', '');
    }

    private function integerValue(mixed $value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }
        if (! is_numeric($value) || (float) $value < 0 || floor((float) $value) !== (float) $value) {
            throw new \InvalidArgumentException('No. of B/Rooms must be a whole number.');
        }

        return (int) $value;
    }
}