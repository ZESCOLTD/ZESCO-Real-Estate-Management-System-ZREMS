<div>
    <x-slot name="title">Import Asset Register</x-slot>
    <x-slot name="pageHeader">Import Asset Register</x-slot>

    <div class="container-fluid px-0">
        <div class="mb-3">
            <a href="{{ route('asset-register.index') }}" class="btn btn-outline-secondary btn-sm mb-2"><i class="fas fa-arrow-left mr-1"></i> Asset Register</a>
            <h3 class="mb-1">Import Asset Register</h3>
            <p class="text-muted mb-0">Import the official Excel register. Existing Asset Numbers are updated; new Asset Numbers are added.</p>
        </div>

        @if(session()->has('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <section class="card">
            <div class="card-header text-white" style="background: {{ config('theme.color_primary') }}"><h5 class="mb-0">Excel file</h5></div>
            <div class="card-body">
                <form wire:submit.prevent="processImport">
                    <div class="form-group">
                        <label for="asset-import-file">Select Excel file (.xlsx or .xls)</label>
                        <input id="asset-import-file" type="file" wire:model="file" class="form-control-file" accept=".xlsx,.xls">
                        <div wire:loading wire:target="file" class="text-muted mt-2">Uploading file...</div>
                        @error('file') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="file,processImport">
                        <span wire:loading.remove wire:target="processImport"><i class="fas fa-file-import mr-1"></i>Start Import</span>
                        <span wire:loading wire:target="processImport"><span class="spinner-border spinner-border-sm mr-1"></span>Starting...</span>
                    </button>
                </form>
            </div>
        </section>

        @if($this->job)
            <section class="card mt-3" wire:poll.2s>
                <div class="card-header"><h5 class="mb-0">Import status: {{ ucfirst($this->job->status) }}</h5></div>
                <div class="card-body">
                    @php($progress = $this->job->total_rows > 0 ? min(100, ($this->job->processed_rows / $this->job->total_rows) * 100) : 0)
                    <div class="progress mb-3" style="height: 24px">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%">{{ number_format($progress, 1) }}%</div>
                    </div>
                    <div class="row text-center">
                        <div class="col-md-4"><strong>Total rows</strong><div>{{ number_format($this->job->total_rows) }}</div></div>
                        <div class="col-md-4"><strong>Processed</strong><div>{{ number_format($this->job->processed_rows) }}</div></div>
                        <div class="col-md-4"><strong>Failed</strong><div class="text-danger">{{ number_format($this->job->failed_rows) }}</div></div>
                    </div>
                    @if($this->job->failed_rows > 0)
                        <a class="btn btn-outline-danger mt-3" href="{{ route('imports.errors.download', $this->job->id) }}"><i class="fas fa-download mr-1"></i>Download row errors</a>
                    @endif
                    @if($this->errors->isNotEmpty())
                        <div class="mt-3">
                            @foreach($this->errors as $error)
                                <div class="alert alert-danger mb-2">
                                    @if($error->row_number) Row {{ $error->row_number }}: @else Workbook: @endif{{ $error->error_message }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if($this->job->status === 'completed')
                        <div class="alert alert-success mt-3 mb-0">Import completed. Return to the Asset Register to review the imported records.</div>
                    @elseif($this->job->status === 'failed')
                        <div class="alert alert-danger mt-3 mb-0">Import failed. Download the row errors or check the application log.</div>
                    @endif
                </div>
            </section>
        @endif
    </div>
</div>
