<div>
    <x-slot name="title">Tenant Verification</x-slot>
    <x-slot name="pageHeader">Tenant Verification</x-slot>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-0 font-weight-bold">Tenant Verification</h5>
                <small class="text-muted">Applications assigned to you for review</small>
            </div>
            <span class="badge badge-warning p-2">{{ number_format($pendingCount) }} awaiting your review</span>
        </div>

        @if(session()->has('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->has('verification'))<div class="alert alert-danger">{{ $errors->first('verification') }}</div>@endif

        @forelse($workflows as $workflow)
            @php($application = $workflow->workflowable)
            @if($application)
                <section class="card elevation-2 mb-3">
                    <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <h6 class="font-weight-bold mb-1">{{ $application->applicant_name }}</h6>
                            <small class="text-muted">{{ $application->reference_no }} · {{ ucwords(str_replace('_', ' ', $application->tenant_type)) }}</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @can('tenant_register.history.view')
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('tenant-register.history', ['application_id' => $application->id]) }}"><i class="fas fa-history mr-1"></i>History</a>
                            @endcan
                            <span class="badge badge-warning">Pending Verification</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4 mb-2"><small class="text-muted d-block">Contact</small>{{ $application->phone }}@if($application->email) · {{ $application->email }}@endif</div>
                            <div class="col-md-4 mb-2"><small class="text-muted d-block">Requested station / category</small>{{ $application->station_location }} / {{ $application->category }}</div>
                            <div class="col-md-4 mb-2"><small class="text-muted d-block">Submitted</small>{{ $application->submitted_at?->format('d M Y H:i') ?: '—' }}</div>
                            <div class="col-md-4 mb-2"><small class="text-muted d-block">Identity reference</small>{{ $application->man_no ?: $application->identity_reference ?: $application->organization_registration_no ?: '—' }}</div>
                            <div class="col-md-4 mb-2"><small class="text-muted d-block">Physical address</small>{{ $application->physical_address }}</div>
                            <div class="col-md-4 mb-2"><small class="text-muted d-block">Current stage</small>{{ $workflow->currentStep?->step_name ?: 'Tenant Verification' }}</div>
                        </div>

                        <div class="mb-3">
                            <strong class="small d-block mb-1">Supporting documents</strong>
                            @forelse($application->documents as $document)
                                <a class="badge badge-light border mr-1 mb-1 p-2" href="{{ route('tenant-register.verification.documents.download', ['application' => $application->id, 'document' => $document->id]) }}">
                                    <i class="fas fa-paperclip mr-1"></i>{{ $document->original_name }}
                                </a>
                                <span class="badge badge-secondary">{{ $document->review_status }}</span>
                            @empty
                                <span class="text-muted small">No supporting files uploaded.</span>
                            @endforelse
                        </div>

                        <div class="form-group mb-2">
                            <label for="verification-comment-{{ $workflow->id }}">Review comment</label>
                            <textarea id="verification-comment-{{ $workflow->id }}" class="form-control" rows="2" wire:model="comments.{{ $workflow->id }}" placeholder="Required when returning for correction"></textarea>
                            @error('comments.' . $workflow->id)<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="d-flex flex-wrap">
                            <button type="button" class="btn btn-success btn-sm mr-2" wire:click="decide({{ $workflow->id }}, 'verify')" wire:loading.attr="disabled">Verify</button>
                            <button type="button" class="btn btn-outline-warning btn-sm" wire:click="decide({{ $workflow->id }}, 'return')" wire:loading.attr="disabled">Return for Correction</button>
                        </div>
                    </div>
                </section>
            @endif
        @empty
            <section class="card"><div class="card-body text-center text-muted py-5">No tenant applications are assigned to you.</div></section>
        @endforelse

        <div class="mt-3">{{ $workflows->links() }}</div>
    </div>
</div>