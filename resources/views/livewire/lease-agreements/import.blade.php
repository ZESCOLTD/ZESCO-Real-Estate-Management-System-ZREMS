<div
    x-data
    x-on:lease-import-failed.window="$nextTick(() => document.getElementById('lease-import-errors')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))">
    <x-slot name="title">Import Lease Agreements</x-slot>
    <x-slot name="pageHeader">Import Lease Agreements</x-slot>

    <div class="container-fluid">
        <div class="card elevation-2 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Upload Current Lease Register</h5>
            </div>

            <div class="card-body">
                <p class="text-muted">
                    Upload the existing register with columns No through
                    Responsible Officer. Review every row before saving.
                </p>

                <form wire:submit.prevent="preview">
                    <div class="form-group">
                        <label for="lease-file">Excel or CSV file</label>
                        <input
                            id="lease-file"
                            type="file"
                            class="form-control"
                            accept=".xlsx,.xls,.csv"
                            wire:model="file">

                        @error('file')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="btn btn-success"
                        wire:loading.attr="disabled"
                        wire:target="file,preview">
                        Preview Import
                    </button>

                    <span
                        class="text-muted ml-2"
                        wire:loading
                        wire:target="file,preview">
                        Reading spreadsheet...
                    </span>
                </form>
            </div>
        </div>
        <div id="staged-batches" class="card elevation-2 mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Uploaded Registers</h5>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Batch</th>
                    <th>File</th>
                    <th>Uploaded</th>
                    <th>Rows</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($batches as $batch)
                    <tr>
                        <td>#{{ $batch->id }}</td>
                        <td>{{ $batch->original_filename }}</td>
                        <td>{{ $batch->created_at->format('d M Y H:i') }}</td>
                        <td>{{ $batch->row_count }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $batch->status)) }}</td>
                        <td>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary"
                                wire:click="openBatch({{ $batch->id }})">
                                Open
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No registers uploaded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

        @if ($rowErrors)
        <div
            id="lease-import-errors"
            class="alert alert-danger"
            style="scroll-margin-top: 5rem;"
            x-init="$nextTick(() => $el.scrollIntoView({ behavior: 'smooth', block: 'start' }))">
            <strong>Nothing was saved.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($rowErrors as $rowError)
                    <li>{{ $rowError }}</li>
                @endforeach
            </ul>
        </div>
    @endif

        @if ($previewRows)
            <div class="card elevation-2 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        Review {{ count($previewRows) }} Rows
                    </h5>
                </div>

                <div class="card-body">
                    <p class="text-muted">
                        An exact description match is a suggestion.
                        Check the lease number before choosing Update.
                    </p>
                    <div class="form-group">
    <label for="lease-date-order">Slash-date format in this register</label>
    <select
        id="lease-date-order"
        class="form-control"
        wire:model="dateOrder"
        @disabled($batchStatus === 'applied')>
        <option value="DMY">Day/month/year — 31/12/2028</option>
        <option value="MDY">Month/day/year — 12/31/2028</option>
    </select>
    <small class="form-text text-muted">
        Check this before syncing. For example, 01/04/2026 means
        1 April with day/month/year.
    </small>
</div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Excel Row</th>
                                    <th>Source No</th>
                                    <th style="min-width: 300px;">
                                        Contract Description
                                    </th>
                                    <th>Contact</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th style="min-width: 280px;">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($previewRows as $key => $previewRow)
                                    <tr wire:key="lease-import-{{ $key }}">
                                        <td>{{ $previewRow['excel_row'] }}</td>
                                        <td>{{ $previewRow['cells'][0] }}</td>
                                        <td>
                                            {{ $previewRow['cells'][1] }}
                                        </td>
                                        <td>{{ $previewRow['cells'][2] }}</td>
                                        <td>{{ $previewRow['cells'][9] }}</td>
                                        <td>{{ $previewRow['cells'][10] }}</td>
                                        <td>
                                            <select
                                                class="form-control"
                                                wire:model="choices.{{ $key }}"
                                                @disabled($batchStatus === 'applied')>
                                                <option value="">
                                                    Choose action...
                                                </option>
                                                <option value="new">
                                                    Create new lease
                                                </option>
                                                <option value="skip">
                                                    Skip this row
                                                </option>

                                                @foreach (
                                                    $existingLeases as $existingLease
                                                )
                                                    <option
                                                        value="update:{{ $existingLease->lease_number }}">
                                                        Update {{ $existingLease->lease_number }}
                                                        — {{ \Illuminate\Support\Str::limit(
                                                            $existingLease->contract_description,
                                                            45
                                                        ) }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            @foreach (
                                                $previewRow['warnings'] as $warning
                                            )
                                                <small class="text-warning d-block mt-1">
                                                    {{ $warning }}
                                                </small>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($batchStatus !== 'applied')
                        <button
                            type="button"
                            class="btn btn-outline-secondary mt-3 mr-2"
                            wire:click="saveChoices">
                            Save Review
                        </button>

                        <button
                            type="button"
                            class="btn btn-primary mt-3"
                            wire:click="confirmImport"
                            wire:loading.attr="disabled"
                            wire:target="confirmImport">
                            Sync Reviewed Register
                        </button>
                    @endif
                </div>
            </div>
        @endif

        @if ($finished)
            <div class="alert alert-success">
                Import complete. Created: {{ $created }}.
                Updated: {{ $updated }}.
            </div>

            <div class="card">
                <div class="card-header">
                    Lease Numbers by Spreadsheet Row
                </div>

                <div class="card-body table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Excel Row</th>
                                <th>Lease Number</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($importedNumbers as $item)
                                <tr>
                                    <td>{{ $item['excel_row'] }}</td>
                                    <td>{{ $item['lease_number'] }}</td>
                                    <td>{{ $item['action'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>