<div>
    <x-slot name="title">Tenant Documents</x-slot>
    <x-slot name="pageHeader">Tenant Documents</x-slot>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="font-weight-bold mb-0">Tenant Documents</h5>
                <small class="text-muted">Private supporting files linked to onboarding applications and register records</small>
            </div>
            <span class="badge badge-light border p-2">{{ number_format($totalDocuments) }} documents</span>
        </div>

        @if(session()->has('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <section class="card elevation-2 mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-8 mb-2">
                        <label class="sr-only" for="tenant-document-search">Search documents</label>
                        <input id="tenant-document-search" type="search" class="form-control" placeholder="Search file name, document type, or review comment" wire:model.live.debounce.300ms="search">
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label class="sr-only" for="tenant-document-status">Review status</label>
                        <select id="tenant-document-status" class="form-control" wire:model.live="reviewStatus">
                            <option value="">All review statuses</option>
                            <option value="Not Reviewed">Not Reviewed</option>
                            <option value="Accepted">Accepted</option>
                            <option value="Needs Replacement">Needs Replacement</option>
                        </select>
                    </div>
                </div>
            </div>
        </section>

        <section class="card elevation-2">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead style="background-color: {{ config('theme.color_primary') }}; color: {{ config('theme.color_primary_text_light') }}">
                        <tr><th>Document</th><th>Tenant / application</th><th>Type</th><th>Uploaded</th><th>Uploaded by</th><th>Expires</th><th>Review status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $document)
                            @php
                                $owner = $document->documentable;
                                $ownerName = $owner instanceof \App\Models\TenantOnboardingApplication
                                    ? ($owner->applicant_name ?: $owner->reference_no)
                                    : ($owner?->current_tenant ?: 'Tenant register record');
                            @endphp
                            <tr>
                                <td>{{ $document->original_name }}</td>
                                <td>{{ $ownerName }}</td>
                                <td>{{ $document->document_type }}</td>
                                <td>{{ $document->uploaded_at?->format('d M Y') ?: '—' }}</td>
                                <td>{{ $document->uploader?->name ?: '—' }}</td>
                                <td>{{ $document->expires_at?->format('d M Y') ?: '—' }}</td>
                                <td><span class="badge {{ $document->review_status === 'Accepted' ? 'badge-success' : ($document->review_status === 'Needs Replacement' ? 'badge-warning' : 'badge-secondary') }}">{{ $document->review_status }}</span></td>
                                <td class="text-nowrap">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant-register.documents.download', $document) }}" aria-label="Download {{ $document->original_name }}"><i class="fas fa-download"></i></a>
                                    @can('tenant_register.verify')
                                        @if($owner instanceof \App\Models\TenantOnboardingApplication && $owner->status === 'Pending Verification')
                                            <div class="mt-2">
                                                <input class="form-control form-control-sm mb-1" placeholder="Required if replacement requested" wire:model="reviewComments.{{ $document->id }}">
                                                @error('reviewComments.' . $document->id)<small class="text-danger d-block">{{ $message }}</small>@enderror
                                                <button type="button" class="btn btn-xs btn-outline-success" wire:click="review({{ $document->id }}, 'Accepted')">Accept</button>
                                                <button type="button" class="btn btn-xs btn-outline-warning" wire:click="review({{ $document->id }}, 'Needs Replacement')">Request replacement</button>
                                            </div>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-5">No tenant documents match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-body">{{ $documents->links() }}</div>
        </section>
    </div>
</div>