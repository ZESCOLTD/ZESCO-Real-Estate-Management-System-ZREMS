<div>
    <x-slot name="title">Fms Asset Register Import</x-slot>
    <x-slot name="pageHeader">Fms Asset Register Import</x-slot>

    <div class="card shadow-sm">
        <div class="card-header text-white" style="background: {{ config('theme.color_primary') }}">
            <h5 class="mb-0">Asset Register Import</h5>
        </div>

        <div class="card-body">

            {{-- 🔥 GLOBAL LOADER (STARTING IMPORT) --}}
            <div wire:loading wire:target="processImport" class="text-center my-4">
                <div class="spinner-border text-primary" style="width:3rem;height:3rem;"></div>
                <div class="mt-2 text-muted">Starting import... please wait</div>
            </div>

            {{-- FILE UPLOAD --}}
            <form wire:submit.prevent="processImport" class="mb-4">

                <div class="d-flex gap-2 align-items-center">
                    <input type="file" wire:model="file" class="form-control w-auto">

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="processImport"
                        class="btn btn-success"
                    >
                        START UPLOAD
                    </button>
                </div>

                {{-- Upload loader --}}
                <div wire:loading wire:target="file" class="text-muted mt-2">
                    Uploading file...
                </div>

            </form>

            {{-- 🔥 IMPORT STATE (POLLING WRAPPED PROPERLY) --}}
            @if($this->job)
                <div wire:poll.500ms>

                    {{-- STATUS --}}
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <span class="badge bg-info">
                            {{ ucfirst($this->job->status) }}
                        </span>
                    </div>

                    {{-- 🔥 PROCESSING LOADER --}}
                    @if(in_array($this->job->status, ['pending','processing']))

                        <div class="text-center my-3">

                            <div class="spinner-border text-primary" style="width:2.5rem;height:2.5rem;"></div>

                            <div class="mt-2 text-muted">
                                Processing Excel file...
                            </div>

                            <div class="text-primary mt-1">
                                <span class="spinner-grow spinner-grow-sm"></span>
                                Working...
                            </div>

                        </div>

                    @endif

                    {{-- 🔥 LIVE INFO --}}
                    <div class="mb-3">

                        <div class="text-muted small mb-2">
                            Last update: {{ now()->format('H:i:s') }}
                        </div>

                        <div><strong>Current Row:</strong> {{ $this->job->current_row ?? 0 }}</div>

                        <div><strong>Processing:</strong> {{ $this->job->current_item ?? 'N/A' }}</div>

                        <div>
                            <strong>Chunk:</strong>
                            {{ $this->job->current_chunk ?? 0 }} /
                            {{ $this->job->total_chunks ?? 0 }}
                        </div>

                    </div>

                    {{-- PROGRESS BAR --}}
                    @php
                        $percent = $this->job->total_rows > 0
                            ? ($this->job->processed_rows / $this->job->total_rows) * 100
                            : 0;
                    @endphp

                    <div class="progress mb-4" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             style="width: {{ $percent }}%">
                            {{ number_format($percent, 2) }}%
                        </div>
                    </div>

                    {{-- STATS --}}
                    <div class="row text-center">

                        <div class="col-md-4">
                            <div class="card p-3">
                                <h6>Total</h6>
                                <h4>{{ $this->job->total_rows }}</h4>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card p-3">
                                <h6>Processed</h6>
                                <h4 class="text-success">{{ $this->job->processed_rows }}</h4>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card p-3">
                                <h6>Failed</h6>
                                <h4 class="text-danger">{{ $this->job->failed_rows }}</h4>
                            </div>
                        </div>

                    </div>

                    {{-- ERROR DOWNLOAD --}}
                    @if($this->job->failed_rows > 0)
                        <a href="{{ url('/imports/'.$this->job->id.'/errors/download') }}"
                           class="btn btn-danger mt-3">
                            Download Error Report
                        </a>
                    @endif

                    {{-- COMPLETION --}}
                    @if($this->job->status === 'completed')
                        <div class="alert alert-success mt-3">
                            Import completed successfully.
                        </div>
                    @endif

                    @if($this->job->status === 'failed')
                        <div class="alert alert-danger mt-3">
                            Import failed. Check error logs.
                        </div>
                    @endif

                </div>
            @else
                <div class="alert alert-secondary">
                    No import running. Upload a file to begin.
                </div>
            @endif

        </div>
    </div>

    {{-- TOGGLE BUTTON --}}
    <button class="btn btn-outline-primary my-3" wire:click="toggleTable">
        {{ $showTable ? 'Hide Assets' : 'Show Assets' }}
    </button>

    {{-- ASSETS TABLE --}}
    @if($showTable)

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Imported Assets</h5>
            </div>

            <div class="card-body">

                {{-- FILTERS --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text"
                               class="form-control"
                               placeholder="Search asset..."
                               wire:model.debounce.500ms="search">
                    </div>

                    <div class="col-md-4">
                        <input type="text"
                               class="form-control"
                               placeholder="Filter category"
                               wire:model="filterCategory">
                    </div>
                </div>

                <div wire:loading wire:target="search, filterCategory">
                    <small class="text-muted">Filtering...</small>
                </div>

                {{-- TABLE --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">

                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Asset Number</th>
                            <th>Description</th>
                            <th>Tag</th>
                            <th>Category</th>
                            <th>Units</th>
                            <th>Town</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($this->assets as $index => $asset)
                            <tr>
                                <td>{{ $this->assets->firstItem() + $index }}</td>
                                <td>{{ $asset->asset_number }}</td>
                                <td>{{ $asset->description }}</td>
                                <td>{{ $asset->tag_number }}</td>
                                <td>{{ $asset->major_category }}</td>
                                <td>{{ $asset->units }}</td>
                                <td>{{ $asset->town }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    No assets found
                                </td>
                            </tr>
                        @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="mt-3">
                    {{ $this->assets->links() }}
                </div>

            </div>
        </div>

    @endif

</div>
