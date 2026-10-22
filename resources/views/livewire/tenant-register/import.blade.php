<div>
    <x-slot name="title">Import ZESCO Tenants Register</x-slot>
    <x-slot name="pageHeader">Import ZESCO Tenants Register</x-slot>

    <div class="container-fluid">
        <div class="mb-3">
            <a href="{{ route('tenant-register.index') }}" class="btn btn-outline-secondary btn-sm mb-2"><i class="fas fa-arrow-left mr-1"></i>Tenants Register</a>
            <h5 class="font-weight-bold mb-1">Import Tenants Register</h5>
            <p class="text-muted mb-0">Upload a consolidated Excel sheet with the agreed 22 columns. Re-importing the same property updates its existing record.</p>
        </div>

        <section class="card elevation-2">
            <div class="card-header text-white" style="background: {{ config('theme.color_primary') }}"><h6 class="mb-0">Excel file</h6></div>
            <div class="card-body">
                <form wire:submit="processImport">
                    <div class="form-group">
                        <label for="tenant-import-file">Select Excel file (.xlsx or .xls)</label>
                        <input id="tenant-import-file" type="file" wire:model="file" class="form-control-file" accept=".xlsx,.xls">
                        <div wire:loading wire:target="file" class="text-muted mt-2">Uploading file...</div>
                        @error('file')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="file,processImport"><i class="fas fa-file-import mr-1"></i>Start Import</button>
                </form>
            </div>
        </section>

        @if($this->job)
            <section class="card mt-3" wire:poll.2s>
                <div class="card-header"><h6 class="mb-0">Import status: {{ ucfirst($this->job->status) }}</h6></div>
                <div class="card-body">
                    @php($progress = $this->job->total_rows > 0 ? min(100, ($this->job->processed_rows / $this->job->total_rows) * 100) : 0)
                    <div class="progress mb-3" style="height: 24px"><div class="progress-bar" role="progressbar" style="width: {{ $progress }}%">{{ number_format($progress, 1) }}%</div></div>
                    <div class="row text-center">
                        <div class="col-md-4"><strong>Total rows</strong><div>{{ number_format($this->job->total_rows) }}</div></div>
                        <div class="col-md-4"><strong>Processed</strong><div>{{ number_format($this->job->processed_rows) }}</div></div>
                        <div class="col-md-4"><strong>Failed</strong><div class="text-danger">{{ number_format($this->job->failed_rows) }}</div></div>
                    </div>
                    @if($this->job->failed_rows > 0)<a class="btn btn-outline-danger mt-3" href="{{ route('imports.errors.download', $this->job->id) }}"><i class="fas fa-download mr-1"></i>Download row errors</a>@endif
                    @foreach($this->errors as $error)
                        <div class="alert alert-danger mt-3 mb-0">@if($error->row_number)Row {{ $error->row_number }}: @else Workbook: @endif{{ $error->error_message }}</div>
                    @endforeach
                    @if($this->job->status === 'completed')<div class="alert alert-success mt-3 mb-0">Import completed. Review the records in the Tenants Register.</div>
                    @elseif($this->job->status === 'failed')<div class="alert alert-danger mt-3 mb-0">Import failed. Review the workbook error above or download row errors.</div>@endif
                </div>
            </section>
        @endif
    </div>
</div>