<?php

namespace App\Livewire\LeaseAgreements;

use App\Models\LeaseAgreement;
use App\Models\LeaseStatus;
use App\Models\Province;
use App\Models\User;
use DateTimeImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;
use App\Models\LeaseImportBatch;
use Illuminate\Support\Facades\Storage;

class Import extends Component
{
    use WithFileUploads;

    public $file;

    public ?int $batchId = null;
    public ?string $batchStatus = null;
    public string $dateOrder = 'DMY';

    public array $previewRows = [];
    public array $choices = [];
    public array $rowErrors = [];
    public array $importedNumbers = [];

    public int $created = 0;
    public int $updated = 0;
    public bool $finished = false;

    public function preview(): void
    {
        Gate::authorize('lease_agreements.manage');

        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $this->previewRows = [];
        $this->choices = [];
        $this->rowErrors = [];
        $this->importedNumbers = [];
        $this->finished = false;
        $this->batchId = null;
        $this->batchStatus = null;
        $batch = null;
        $this->dateOrder = 'DMY';

        try {
            $path = $this->file->store('lease-imports', 'local');

            if (! is_string($path)) {
                throw new DomainException('The uploaded file could not be saved.');
            }

            $batch = LeaseImportBatch::create([
                'original_filename' => $this->file->getClientOriginalName(),
                'file_path' => $path,
                'uploaded_by' => auth()->id(),
                'status' => 'uploaded',
                'date_order' => $this->dateOrder,
            ]);

            $sheet = Excel::toCollection(
                null,
                Storage::disk('local')->path($path)
            )->first();

            if (! $sheet || $sheet->count() < 2) {
                throw new DomainException('The spreadsheet has no data rows.');
            }

            $expected = [
                'no',
                'contract description',
                'landlord/contact person details',
                'location',
                'province',
                'lease duration',
                'lease tenure - years',
                'lease payment per month (zmw)',
                'lease payments per annum (zmw)',
                'start date',
                'end date',
                'status',
                'comment',
                'responsible officer',
            ];

            $headerRowIndex = null;

            foreach ($sheet->take(20) as $index => $row) {
                $columns = $row instanceof \Illuminate\Support\Collection
                    ? $row->toArray()
                    : (array) $row;

                $headings = array_map(
                    fn ($value) => $this->normaliseHeader((string) $value),
                    array_slice($columns, 0, 14)
                );

                if ($headings === $expected) {
                    $headerRowIndex = $index;
                    break;
                }
            }

            if ($headerRowIndex === null) {
                throw new DomainException(
                    'Could not find the lease register column headings in the first 20 rows.'
                );
            }

            foreach ($sheet->slice($headerRowIndex + 1) as $index => $excelRow) {
                $values = $excelRow instanceof \Illuminate\Support\Collection
                    ? $excelRow->toArray()
                    : (array) $excelRow;

                // Ignore any extra empty columns beyond A–N.
                $cells = array_pad(array_slice($values, 0, 14), 14, null);

                $cells = array_map(function ($value) {
                    if ($value instanceof \DateTimeInterface) {
                        return $value->format('Y-m-d');
                    }

                    return is_scalar($value) ? trim((string) $value) : '';
                }, $cells);

                if (collect($cells)->every(fn ($value) => $value === '')) {
                    continue;
                }

                if (count($this->previewRows) >= 200) {
                    throw new DomainException(
                        'This first version accepts at most 200 populated rows per file.'
                    );
                }

                $description = $cells[1];

                $matches = $description !== ''
                    ? LeaseAgreement::where('zesco_role', 'tenant')
                        ->where('contract_description', $description)
                        ->get(['lease_number'])
                    : collect();

                $warnings = [];

                if ($cells[2] !== '' && preg_match('/^\d{9}$/', $cells[2])) {
                    $warnings[] = 'Check phone number: Excel may have removed its leading zero.';
                }

                if ($cells[13] !== '') {
                    $officerCount = User::whereRaw(
                        'LOWER(name) = ?',
                        [strtolower($cells[13])]
                    )->count();

                    if ($officerCount !== 1) {
                        $warnings[] = 'Responsible officer name does not match exactly one user.';
                    }
                }

                if (stripos($cells[12], 'USD') !== false) {
                    $warnings[] = 'Comment mentions USD; verify the payment currency.';
                }

                $rowKey = count($this->previewRows);

                $this->previewRows[] = [
                    'excel_row' => $index + 1,
                    'cells' => $cells,
                    'warnings' => $warnings,
                ];

                // An exact description match is only a suggestion.
               $this->choices[$rowKey] = $matches->count() === 1
                    ? 'update:' . $matches->first()->lease_number
                    : 'new';
                            }

            if ($this->previewRows === []) {
                throw new DomainException('The spreadsheet has no populated lease rows.');
            }

            $batch->update([
                'status' => 'staged',
                'row_count' => count($this->previewRows),
                'rows' => $this->previewRows,
                'choices' => $this->choices,
                'errors' => null,
            ]);

            $this->batchId = $batch->id;
            $this->batchStatus = 'staged';

            $this->reset('file');
       } catch (DomainException $e) {
            if ($batch) {
                $batch->update([
                    'status' => 'failed',
                    'errors' => [$e->getMessage()],
                ]);
            }

            $this->addError('file', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            if ($batch) {
                $batch->update([
                    'status' => 'failed',
                    'errors' => ['The spreadsheet could not be read.'],
                ]);
            }

            $this->addError('file', 'The spreadsheet could not be read.');
        }
    }

    public function openBatch(int $id): void
{
    Gate::authorize('lease_agreements.manage');

    $batch = LeaseImportBatch::findOrFail($id);

    $this->resetValidation();
    $this->batchId = $batch->id;
    $this->batchStatus = $batch->status;
    $this->dateOrder = $batch->date_order ?? 'MDY';
    $this->previewRows = $batch->rows ?? [];
    $this->choices = $batch->choices ?? [];
    $this->rowErrors = $batch->errors ?? [];
    $this->importedNumbers = $batch->results ?? [];
    $this->finished = $batch->status === 'applied';

    $this->created = collect($this->importedNumbers)
        ->where('action', 'Created')
        ->count();

    $this->updated = collect($this->importedNumbers)
        ->where('action', 'Updated')
        ->count();
}

public function saveChoices(): void
{
    Gate::authorize('lease_agreements.manage');

    $batch = LeaseImportBatch::findOrFail($this->batchId);

    if ($batch->status === 'applied') {
        return;
    }

    $batch->update([
        'choices' => $this->choices,
        'date_order' => $this->dateOrder,
    ]);

    session()->flash('success', 'Review choices saved.');
}

    public function confirmImport(): void
    {
        Gate::authorize('lease_agreements.manage');

       $this->rowErrors = [];

        if ($this->batchId === null) {
            $this->rowErrors[] = 'Upload or open a register batch first.';
            $this->dispatch('lease-import-failed');
            return;
        }

        $batch = LeaseImportBatch::findOrFail($this->batchId);

        if ($batch->status === 'applied') {
            $this->rowErrors[] = 'This register batch has already been synced.';
            $this->dispatch('lease-import-failed');
            return;
        }

        if (! in_array($this->dateOrder, ['DMY', 'MDY'], true)) {
        $this->rowErrors[] = 'Choose a valid date format.';
        $this->dispatch('lease-import-failed');
        return;
    }

$batch->update(['date_order' => $this->dateOrder]);

        // Always use the saved source rows, not rows sent back by the browser.
        $this->previewRows = $batch->rows ?? [];

        if ($this->previewRows === []) {
            $this->rowErrors[] = 'This batch has no readable lease rows.';
            $this->dispatch('lease-import-failed');
            return;
        }

        $operations = [];
        $usedNumbers = [];
        $newDescriptions = [];

        foreach ($this->previewRows as $key => $previewRow) {
            $choice = $this->choices[$key] ?? '';
            $cells = $previewRow['cells'];
            $excelRow = $previewRow['excel_row'];

            if ($choice === 'skip') {
                continue;
            }

            try {
                $description = trim($cells[1]);
                $lease = null;

                if ($choice === 'new') {
                    if ($description === '') {
                        throw new DomainException('Contract description is required.');
                    }

                    if (
                        LeaseAgreement::where('contract_description', $description)
                            ->exists()
                    ) {
                        throw new DomainException(
                            'A lease with this description already exists. Choose its lease number.'
                        );
                    }

                    $descriptionKey = strtolower($description);

                    if (isset($newDescriptions[$descriptionKey])) {
                        throw new DomainException(
                            'Another row in this file is creating the same description.'
                        );
                    }

                    $newDescriptions[$descriptionKey] = true;
                } elseif (str_starts_with($choice, 'update:')) {
                    $number = substr($choice, strlen('update:'));

                    $lease = LeaseAgreement::where('lease_number', $number)
                        ->where('zesco_role', 'tenant')
                        ->first();

                    if (! $lease) {
                        throw new DomainException(
                            "Tenant lease {$number} was not found."
                        );
                    }

                    if (isset($usedNumbers[$number])) {
                        throw new DomainException(
                            "Lease {$number} is selected by more than one row."
                        );
                    }

                    $usedNumbers[$number] = true;
                } else {
                    throw new DomainException(
                        'Choose Create, Update, or Skip.'
                    );
                }

                $operations[] = [
                    'excel_row' => $excelRow,
                    'lease_number' => $lease?->lease_number,
                    'data' => $this->makeLeaseData($cells, $lease),
                ];
            } catch (DomainException $e) {
                $this->rowErrors[] = "Row {$excelRow}: {$e->getMessage()}";
            }
        }

        // Validate every selected row before writing any of them.
       if ($this->rowErrors !== []) {
        $batch->update([
            'status' => 'needs_review',
            'choices' => $this->choices,
            'errors' => $this->rowErrors,
        ]);

        $this->batchStatus = 'needs_review';
        $this->dispatch('lease-import-failed');
        return;
    }

        try {
            $created = 0;
            $updated = 0;
            $numbers = [];

            DB::transaction(function () use (
                $operations,
                $batch,
                &$created,
                &$updated,
                &$numbers
            ) {
                $batchToApply = LeaseImportBatch::whereKey($batch->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($batchToApply->status === 'applied') {
                    throw new DomainException('This batch has already been synced.');
                }
                foreach ($operations as $operation) {
                    if ($operation['lease_number']) {
                        $lease = LeaseAgreement::where(
                            'lease_number',
                            $operation['lease_number']
                        )
                            ->where('zesco_role', 'tenant')
                            ->firstOrFail();

                        $lease->update($operation['data']);
                        $updated++;
                        $action = 'Updated';
                    } else {
                        $lease = LeaseAgreement::create(
                            ['zesco_role' => 'tenant'] + $operation['data']
                        );

                        $created++;
                        $action = 'Created';
                    }

                    $numbers[] = [
                        'excel_row' => $operation['excel_row'],
                        'lease_number' => $lease->lease_number,
                        'action' => $action,
                    ];
                }

                $batchToApply->update([
                'status' => 'applied',
                'choices' => $this->choices,
                'errors' => null,
                'results' => $numbers,
                'applied_at' => now(),
            ]);
            });

            $this->created = $created;
            $this->updated = $updated;
            $this->importedNumbers = $numbers;
            $this->previewRows = [];
            $this->choices = [];
            $this->batchStatus = 'applied';
            $this->finished = true;
       } catch (DomainException $e) {
            $this->rowErrors[] = $e->getMessage();
            $this->dispatch('lease-import-failed');
        } catch (Throwable $e) {
            report($e);

            $this->rowErrors[] =
                'Sync failed. No leases were changed. Check the application log.';

            $batch->refresh();

            if ($batch->status !== 'applied') {
                $batch->update([
                    'status' => 'failed',
                    'choices' => $this->choices,
                    'errors' => $this->rowErrors,
                ]);
            }

            $this->batchStatus = $batch->status;
            $this->dispatch('lease-import-failed');
        }
    }

    private function makeLeaseData(array $cells, ?LeaseAgreement $lease): array
    {
        $description = trim($cells[1]);
        $contact = trim($cells[2]);
        $town = trim($cells[3]);
        $provinceText = trim($cells[4]);
        $monthlyText = str_replace([',', ' '], '', trim($cells[7]));
        if (
                $monthlyText === '#VALUE!'
                && strcasecmp(trim($cells[8]), 'N/A - Free') === 0
            ) {
                $monthlyText = '0';
            }
        $statusText = trim($cells[11]);
        $comment = trim($cells[12]);
        $officerText = trim($cells[13]);

        if ($description === '') {
            throw new DomainException('Contract description is required.');
        }

        if (strlen($contact) > 30) {
            throw new DomainException(
                'Contact details exceed the 30-character phone field.'
            );
        }

        $provinceId = $lease?->province_id;

        if ($provinceText !== '') {
            $wantedProvince = preg_replace(
                '/ province$/',
                '',
                $this->normalise($provinceText)
            );

            $province = Province::all()->first(function ($province) use (
                $wantedProvince
            ) {
                $name = preg_replace(
                    '/ province$/',
                    '',
                    $this->normalise($province->name)
                );

                return $name === $wantedProvince;
            });

            if (! $province) {
                throw new DomainException(
                    "Unknown province: {$provinceText}"
                );
            }

            $provinceId = $province->id;
        }

        $statusId = $lease?->lease_status_id;

        if ($statusText !== '') {
            $wantedStatus = $this->normalise($statusText);

            $status = LeaseStatus::where('is_active', true)
                ->get()
                ->first(fn ($status) =>
                    $this->normalise($status->name) === $wantedStatus
                    || $this->normalise($status->code) === $wantedStatus
                );

            if (! $status) {
                throw new DomainException(
                    "Unknown or inactive status: {$statusText}"
                );
            }

            $statusId = $status->id;
        }

        $start = $this->parseDate($cells[9])
            ?? ($lease?->start_date
                ? new DateTimeImmutable($lease->start_date->format('Y-m-d'))
                : null);

        $end = $this->parseDate($cells[10])
            ?? ($lease?->end_date
                ? new DateTimeImmutable($lease->end_date->format('Y-m-d'))
                : null);

        if (($start === null) !== ($end === null)) {
            throw new DomainException(
                'Provide both dates or leave both blank.'
            );
        }

        if ($start && $end && $end < $start) {
            throw new DomainException(
                'End date cannot be before start date.'
            );
        }

        $monthlyPayment = $lease?->monthly_payment;

        if ($monthlyText !== '') {
            if (! is_numeric($monthlyText) || (float) $monthlyText < 0) {
                throw new DomainException(
                    "Invalid monthly payment: {$cells[7]}"
                );
            }

            $monthlyPayment = $monthlyText;
        }

        $monthlyCents = $monthlyPayment !== null
            ? (int) round((float) $monthlyPayment * 100)
            : null;

        $officerId = $lease?->responsible_officer_id;

        if ($officerText !== '') {
            $officers = User::whereRaw(
                'LOWER(name) = ?',
                [strtolower($officerText)]
            )->get(['id']);

            if ($officers->count() === 1) {
                $officerId = $officers->first()->id;
            }
        }

        return [
            // The source document does not contain a reliable landlord name.
            'landlord_name' => $lease?->landlord_name,
            'contract_description' => $description,
            'landlord_contact_phone' => $contact !== ''
                ? $contact
                : $lease?->landlord_contact_phone,
            'town' => $town !== '' ? $town : $lease?->town,
            'province_id' => $provinceId,
            'start_date' => $start?->format('Y-m-d'),
            'end_date' => $end?->format('Y-m-d'),
            'lease_duration' => $start && $end
                ? $start->format('d F Y') . ' to ' . $end->format('d F Y')
                : null,
            'lease_tenure_years' => $start && $end
                ? round(
                    $start->diff($end->modify('+1 day'))->days / 365.25,
                    2
                )
                : null,
            'monthly_payment' => $monthlyCents !== null
                ? number_format($monthlyCents / 100, 2, '.', '')
                : null,
            'annual_payment' => $monthlyCents !== null
                ? number_format(($monthlyCents * 12) / 100, 2, '.', '')
                : null,
            'lease_status_id' => $statusId,
            'comment' => $comment !== '' ? $comment : $lease?->comment,
            'responsible_officer_id' => $officerId,
        ];
    }

   private function parseDate(string $value): ?DateTimeImmutable
{
    $value = trim($value);

    if ($value === '') {
        return null;
    }

    if (is_numeric($value)) {
        try {
            $value = ExcelDate::excelToDateTimeObject((float) $value)
                ->format('Y-m-d');
        } catch (Throwable) {
            throw new DomainException("Invalid Excel date: {$value}");
        }
    }

    if (preg_match('~^(\d{1,2})/(\d{1,2})/(\d{4})$~', $value, $parts)) {
        if ($this->dateOrder === 'DMY') {
            $day = (int) $parts[1];
            $month = (int) $parts[2];
        } else {
            $month = (int) $parts[1];
            $day = (int) $parts[2];
        }

        $year = (int) $parts[3];

        if (! checkdate($month, $day, $year)) {
            throw new DomainException(
                "Invalid date '{$value}' for the selected date format."
            );
        }

        return new DateTimeImmutable(sprintf(
            '%04d-%02d-%02d',
            $year,
            $month,
            $day
        ));
    }

    foreach (
        [
            'Y-m-d',
            'j-M-y',
            'j-M-Y',
            'j-F-Y',
            'd-M-y',
            'd-M-Y',
            'd-F-Y',
        ] as $format
    ) {
        $date = DateTimeImmutable::createFromFormat(
            '!' . $format,
            $value
        );

        if ($date && $date->format($format) === $value) {
            return $date;
        }
    }

    throw new DomainException(
        "Invalid date '{$value}'. Use an Excel date, YYYY-MM-DD, or the selected slash-date format."
    );
}

    private function normaliseHeader(string $value): string
    {
        $value = str_replace("\xEF\xBB\xBF", '', $value);

        return strtolower(trim(
            preg_replace('/\s+/', ' ', $value)
        ));
    }

    private function normalise(string $value): string
    {
        return trim(preg_replace(
            '/[^a-z0-9]+/',
            ' ',
            strtolower($value)
        ));
    }

    public function render()
    {
        
        return view('livewire.lease-agreements.import', [
            'existingLeases' => LeaseAgreement::where('zesco_role', 'tenant')
                ->orderBy('lease_number')
                ->get(['lease_number', 'contract_description']),
            'batches' => LeaseImportBatch::query()
            ->latest()
            ->limit(20)
            ->get([
                'id',
                'original_filename',
                'status',
                'row_count',
                'created_at',
                'applied_at',
            ]),
        ])->layout('layouts.master');
    }
}