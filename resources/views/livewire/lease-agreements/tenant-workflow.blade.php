<div>
    <x-slot name="title">New Lease</x-slot>
    <x-slot name="pageHeader">New Lease</x-slot>

    <div class="container-fluid">
        <div class="mb-3">
            <h5 class="font-weight-bold mb-1">New Lease</h5>
            <small class="text-muted">Start and track lease actions for ZESCO properties and agreements.</small>
        </div>

        @if(session()->has('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <p class="text-muted mb-3">Direction: <strong>ZESCO as Tenant</strong></p>

        <div class="alert alert-info py-2">
            <i class="fas fa-info-circle mr-1"></i>
            This process is linked to an existing Lease Register entry. Recording progress here does not change its lease status.
        </div>

        <section class="card elevation-2 mb-3">
            <div class="card-header"><h6 class="font-weight-bold mb-0">Start tenant-side lease process</h6></div>
            <div class="card-body">
                @can('lease_tenant_workflow.manage')
                    <form wire:submit="startProcess">
                        <div class="form-row align-items-end">
                            <div class="form-group col-lg-7">
                                <label for="lease-agreement-select">Existing lease</label>
                                <select id="lease-agreement-select" class="form-control" wire:model="leaseAgreementId">
                                    <option value="">Select a lease from the register</option>
                                    @foreach($leases as $lease)
                                        <option value="{{ $lease->id }}">{{ $lease->lease_number }} · {{ $lease->landlord_name }} · {{ \Illuminate\Support\Str::limit($lease->contract_description, 90) }}</option>
                                    @endforeach
                                </select>
                                @error('leaseAgreementId')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-lg-3">
                                <label for="lease-department-name">ZESCO user department</label>
                                <input id="lease-department-name" type="text" class="form-control" wire:model="departmentName" placeholder="e.g. Customer Service">
                                @error('departmentName')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-lg-2">
                                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-play mr-1"></i>Start process</button>
                            </div>
                        </div>
                    </form>
                @else
                    <p class="text-muted mb-0">You can view lease processes but do not have permission to start or update them.</p>
                @endcan
            </div>
        </section>

        <div class="row">
            <div class="col-xl-7 mb-3">
                <section class="card elevation-2 h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="font-weight-bold mb-0">Tenant-side processes</h6>
                        <span class="badge badge-light">{{ $workflows->count() }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Lease</th><th>Department</th><th>Current stage</th><th></th></tr></thead>
                            <tbody>
                                @forelse($workflows as $workflow)
                                    <tr>
                                        <td><strong>{{ $workflow->leaseAgreement->lease_number }}</strong><small class="d-block text-muted">{{ $workflow->leaseAgreement->landlord_name }}</small></td>
                                        <td>{{ $workflow->department_name }}</td>
                                        <td>{{ app(\App\Services\LeaseTenantWorkflowService::class)->statusLabel($workflow->status) }}</td>
                                        <td><button type="button" class="btn btn-sm btn-outline-primary" wire:click="openWorkflow({{ $workflow->id }})">View</button></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No tenant-side lease processes yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div class="col-xl-5 mb-3">
                @if($selectedWorkflow)
                    <section class="card elevation-2 h-100">
                        <div class="card-header">
                            <h6 class="font-weight-bold mb-1">{{ $selectedWorkflow->leaseAgreement->lease_number }} · {{ $selectedWorkflow->department_name }}</h6>
                            <small class="text-muted">{{ app(\App\Services\LeaseTenantWorkflowService::class)->statusLabel($selectedWorkflow->status) }}</small>
                        </div>
                        @can('lease_tenant_workflow.manage')
                            @if($availableActions !== [])
                                <div class="card-body border-bottom">
                                    <label for="lease-process-comment">Note <small class="text-muted">(required when department declines)</small></label>
                                    <textarea id="lease-process-comment" class="form-control mb-2" rows="2" wire:model="comment" placeholder="Record relevant confirmation or handoff details"></textarea>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($availableActions as $availableAction)
                                            <button type="button" class="btn btn-sm {{ $availableAction['action'] === 'department_declined' ? 'btn-outline-danger' : 'btn-outline-primary' }}" wire:click="recordAction('{{ $availableAction['action'] }}')">{{ $availableAction['label'] }}</button>
                                        @endforeach
                                    </div>
                                    @error('action')<small class="text-danger d-block mt-2">{{ $message }}</small>@enderror
                                    @error('comment')<small class="text-danger d-block mt-2">{{ $message }}</small>@enderror
                                </div>
                            @endif
                        @endcan
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="text-uppercase text-muted small font-weight-bold mb-0">Lease documents</h6>
                                <span class="badge badge-light">{{ $selectedWorkflow->documents->count() }}</span>
                            </div>
                            @can('lease_tenant_workflow.manage')
                                <form wire:submit="uploadDocuments" class="border rounded p-3 mb-3">
                                    <div class="form-row align-items-end">
                                        <div class="form-group col-md-4">
                                            <label for="lease-document-type">Document type</label>
                                            <select id="lease-document-type" class="form-control form-control-sm" wire:model="documentType">
                                                @foreach($documentTypes as $type)<option value="{{ $type }}">{{ $type }}</option>@endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label for="lease-document-files">Files</label>
                                            <input id="lease-document-files" type="file" class="form-control-file" wire:model="supportingDocuments" multiple accept=".pdf,.jpg,.jpeg,.png">
                                            @error('supportingDocuments')<small class="text-danger d-block">{{ $message }}</small>@enderror
                                            @error('supportingDocuments.*')<small class="text-danger d-block">{{ $message }}</small>@enderror
                                        </div>
                                        <div class="form-group col-md-3 mb-3">
                                            <button type="submit" class="btn btn-sm btn-outline-primary btn-block" wire:loading.attr="disabled" wire:target="supportingDocuments,uploadDocuments">
                                                <i class="fas fa-upload mr-1"></i>Upload privately
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @endcan
                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr><th>File</th><th>Type</th><th>Uploaded</th><th></th></tr></thead>
                                    <tbody>
                                        @forelse($selectedWorkflow->documents as $document)
                                            <tr>
                                                <td>{{ $document->original_name }}</td>
                                                <td>{{ $document->document_type }}</td>
                                                <td>{{ $document->uploaded_at?->format('d M Y') ?: '—' }}<small class="d-block text-muted">{{ $document->uploader?->name ?: '—' }}</small></td>
                                                <td><a class="btn btn-xs btn-outline-primary" href="{{ route('lease_agreements.tenant_workflows.documents.download', [$selectedWorkflow, $document]) }}" title="Download document" aria-label="Download {{ $document->original_name }}"><i class="fas fa-download"></i></a></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted py-3">No lease documents uploaded.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <h6 class="text-uppercase text-muted small font-weight-bold">Process history</h6>
                            @forelse($selectedWorkflow->events as $event)
                                <div class="border-left pl-3 pb-3 mb-2">
                                    <strong class="d-block">{{ ucwords(str_replace('_', ' ', $event->action)) }}</strong>
                                    <small class="text-muted">{{ $event->acted_by ? ($event->actor?->name ?: 'Former user') : 'System' }} · {{ $event->acted_at->format('d M Y, H:i') }}</small>
                                    @if($event->comment)<p class="small mb-0 mt-1">{{ $event->comment }}</p>@endif
                                </div>
                            @empty
                                <p class="text-muted small mb-0">No process history recorded.</p>
                            @endforelse
                        </div>
                    </section>
                @else
                    <section class="card elevation-2 h-100"><div class="card-body text-center text-muted py-5"><i class="fas fa-clipboard-list fa-2x mb-3"></i><p class="mb-0">Select a process to review its history and available actions.</p></div></section>
                @endif
            </div>
        </div>
    </div>
</div>