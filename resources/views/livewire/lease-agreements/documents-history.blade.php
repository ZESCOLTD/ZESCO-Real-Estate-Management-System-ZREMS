<div>
    <x-slot name="title">Lease Documents &amp; History</x-slot>
    <x-slot name="pageHeader">Lease Management</x-slot>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="font-weight-bold mb-1">Lease Documents &amp; History</h5>
                <small class="text-muted">Private files and recorded activity across lease processes</small>
            </div>
            <a href="{{ route('lease_agreements.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i>Lease Dashboard</a>
        </div>

        <ul class="nav nav-tabs mb-3" role="tablist" aria-label="Lease documents and history">
            <li class="nav-item"><button class="nav-link {{ $activeView === 'documents' ? 'active' : '' }}" type="button" role="tab" aria-selected="{{ $activeView === 'documents' ? 'true' : 'false' }}" wire:click="setView('documents')">Documents</button></li>
            <li class="nav-item"><button class="nav-link {{ $activeView === 'history' ? 'active' : '' }}" type="button" role="tab" aria-selected="{{ $activeView === 'history' ? 'true' : 'false' }}" wire:click="setView('history')">History</button></li>
        </ul>

        <section class="card elevation-2 mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-8 mb-2">
                        <label class="sr-only" for="lease-record-search">Search lease records</label>
                        <input id="lease-record-search" type="search" class="form-control" placeholder="Search file, lease reference, tenant, event, or actor" wire:model.live.debounce.300ms="search">
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label class="sr-only" for="lease-record-source">Filter by record type</label>
                        <select id="lease-record-source" class="form-control" wire:model.live="source">
                            <option value="">All record types</option>
                            <option value="tenant_workflow">Tenant lease process</option>
                            <option value="renewal">Renewal</option>
                            <option value="termination">Termination</option>
                            <option value="handover">Property handover</option>
                            <option value="landlord_application">Landlord application</option>
                            @if($activeView === 'documents')<option value="lease_record">Lease agreement</option>@endif
                        </select>
                    </div>
                </div>
            </div>
        </section>

        @if($activeView === 'documents')
            <section class="card elevation-2">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold mb-0">Lease documents</h6>
                    <span class="badge badge-light">{{ number_format($documents->total()) }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>File</th><th>Record type</th><th>Linked record</th><th>Uploaded</th><th>Uploaded by</th><th></th></tr></thead>
                        <tbody>
                            @forelse($documents as $document)
                                <tr>
                                    <td><strong>{{ $document->original_name }}</strong><small class="d-block text-muted">{{ $document->document_type }}</small></td>
                                    <td>{{ $this->documentSourceLabel($document) }}</td>
                                    <td>{{ $this->documentRecordLabel($document) }}</td>
                                    <td>{{ $document->uploaded_at?->format('d M Y, H:i') ?: '—' }}</td>
                                    <td>{{ $document->uploader?->name ?: '—' }}</td>
                                    <td><a class="btn btn-sm btn-outline-primary" href="{{ route('lease_agreements.documents.download', $document) }}" title="Download document" aria-label="Download {{ $document->original_name }}"><i class="fas fa-download"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-5">No lease documents match these filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-body">{{ $documents->links() }}</div>
            </section>
        @else
            <section class="card elevation-2">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold mb-0">Lease history</h6>
                    <span class="badge badge-light">{{ number_format($history->total()) }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Date</th><th>Record type</th><th>Reference</th><th>Activity</th><th>Comment</th><th>Actor</th></tr></thead>
                        <tbody>
                            @forelse($history as $event)
                                <tr>
                                    <td class="text-nowrap">{{ \Illuminate\Support\Carbon::parse($event->occurred_at)->format('d M Y, H:i') }}</td>
                                    <td>{{ $event->source }}</td>
                                    <td><strong>{{ $event->record_reference ?: '—' }}</strong><small class="d-block text-muted">{{ $event->record_name ?: '—' }}</small></td>
                                    <td>{{ ucwords(str_replace('_', ' ', $event->action)) }}</td>
                                    <td>{{ $event->comment ?: '—' }}</td>
                                    <td>{{ $event->actor_name ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-5">No history events match these filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-body">{{ $history->links() }}</div>
            </section>
        @endif
    </div>
</div>