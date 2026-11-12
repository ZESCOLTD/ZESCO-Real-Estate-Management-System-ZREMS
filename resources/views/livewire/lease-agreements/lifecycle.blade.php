<div>
    <x-slot name="title">{{ $caseTitle }}</x-slot>
    <x-slot name="pageHeader">Lease Management</x-slot>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="font-weight-bold mb-1">{{ $caseTitle }}</h5>
                <small class="text-muted">Lifecycle records are linked to existing ZESCO-as-tenant leases.</small>
            </div>
            <a href="{{ route('lease_agreements.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i>Lease Dashboard</a>
        </div>

        @if(session()->has('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <ul class="nav nav-tabs mb-3" role="tablist" aria-label="Lease lifecycle processes">
            <li class="nav-item"><a class="nav-link {{ $caseType === 'renewal' ? 'active' : '' }}" href="{{ route('lease_agreements.renewals') }}">Renewals</a></li>
            <li class="nav-item"><a class="nav-link {{ $caseType === 'termination' ? 'active' : '' }}" href="{{ route('lease_agreements.terminations') }}">Terminations</a></li>
            <li class="nav-item"><a class="nav-link {{ $caseType === 'handover' ? 'active' : '' }}" href="{{ route('lease_agreements.handovers') }}">Property Handover</a></li>
        </ul>

        <div class="alert alert-info py-2">
            <i class="fas fa-info-circle mr-1"></i>
            These are requests and records only. They do not approve a renewal or termination, extend a lease, change rent, or change the Lease Register status.
        </div>

        <section class="card elevation-2 mb-3">
            <div class="card-header"><h6 class="font-weight-bold mb-0">{{ $caseType === 'renewal' ? 'Record renewal request' : ($caseType === 'termination' ? 'Record termination request' : 'Record property handover') }}</h6></div>
            <div class="card-body">
                @can('lease_tenant_workflow.manage')
                    <form wire:submit="submit">
                        <div class="form-row">
                            <div class="form-group col-lg-7">
                                <label for="lifecycle-lease">Existing lease</label>
                                <select id="lifecycle-lease" class="form-control" wire:model="leaseAgreementId">
                                    <option value="">Select a lease from the register</option>
                                    @foreach($leases as $lease)
                                        <option value="{{ $lease->id }}">{{ $lease->lease_number }} · {{ $lease->landlord_name }} · {{ \Illuminate\Support\Str::limit($lease->contract_description, 100) }}</option>
                                    @endforeach
                                </select>
                                @error('leaseAgreementId')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            @if($caseType === 'renewal')
                                <div class="form-group col-md-6">
                                    <label for="renewal-start">Proposed start date</label>
                                    <input id="renewal-start" type="date" class="form-control" wire:model="proposedStartDate">
                                    @error('proposedStartDate')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="renewal-end">Proposed end date</label>
                                    <input id="renewal-end" type="date" class="form-control" wire:model="proposedEndDate">
                                    @error('proposedEndDate')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="renewal-rent">Proposed monthly rent</label>
                                    <input id="renewal-rent" type="number" min="0" step="0.01" class="form-control" wire:model="proposedMonthlyPayment" placeholder="Leave blank if not yet proposed">
                                    @error('proposedMonthlyPayment')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="form-group col-12">
                                    <label for="renewal-reason">Reason / department confirmation</label>
                                    <textarea id="renewal-reason" class="form-control" rows="3" wire:model="reason"></textarea>
                                    @error('reason')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            @elseif($caseType === 'termination')
                                <div class="form-group col-md-6">
                                    <label for="termination-notice">Notice date</label>
                                    <input id="termination-notice" type="date" class="form-control" wire:model="noticeDate">
                                    @error('noticeDate')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="termination-effective">Proposed effective date</label>
                                    <input id="termination-effective" type="date" class="form-control" wire:model="proposedEffectiveDate">
                                    @error('proposedEffectiveDate')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="form-group col-12">
                                    <label for="termination-reason">Reason for termination request</label>
                                    <textarea id="termination-reason" class="form-control" rows="3" wire:model="reason"></textarea>
                                    @error('reason')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="form-group col-12">
                                    <label for="termination-finances">Outstanding rent, deposit, or other financial checks</label>
                                    <textarea id="termination-finances" class="form-control" rows="2" wire:model="details.financial_checks"></textarea>
                                </div>
                            @else
                                <div class="form-group col-md-6">
                                    <label for="handover-type">Handover type</label>
                                    <select id="handover-type" class="form-control" wire:model="details.handover_type">
                                        <option value="move_in">Initial occupation / move-in</option>
                                        <option value="move_out">Move-out</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="handover-inspection-date">Inspection date</label>
                                    <input id="handover-inspection-date" type="date" class="form-control" wire:model="details.inspection_date">
                                    @error('details.inspection_date')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="outgoing-occupant">Outgoing occupant</label>
                                    <input id="outgoing-occupant" type="text" class="form-control" wire:model="details.outgoing_occupant">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="incoming-occupant">Incoming occupant</label>
                                    <input id="incoming-occupant" type="text" class="form-control" wire:model="details.incoming_occupant">
                                </div>
                                <div class="form-group col-12">
                                    <label for="property-condition">Property condition</label>
                                    <textarea id="property-condition" class="form-control" rows="3" wire:model="details.property_condition"></textarea>
                                    @error('details.property_condition')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="meter-readings">Meter readings</label>
                                    <textarea id="meter-readings" class="form-control" rows="2" wire:model="details.meter_readings"></textarea>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="keys-items">Keys and items handed over</label>
                                    <textarea id="keys-items" class="form-control" rows="2" wire:model="details.keys_and_items"></textarea>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="defects-issues">Damage, defects, and outstanding issues</label>
                                    <textarea id="defects-issues" class="form-control" rows="2" wire:model="details.defects_issues"></textarea>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="handover-acknowledgements">Acknowledgements</label>
                                    <textarea id="handover-acknowledgements" class="form-control" rows="2" wire:model="details.acknowledgements"></textarea>
                                </div>
                                <div class="form-group col-12">
                                    <label for="handover-notes">Additional notes</label>
                                    <textarea id="handover-notes" class="form-control" rows="2" wire:model="details.notes"></textarea>
                                </div>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <i class="fas {{ $caseType === 'handover' ? 'fa-clipboard-check' : 'fa-paper-plane' }} mr-1"></i>
                            {{ $caseType === 'handover' ? 'Record handover' : 'Submit request for tracking' }}
                        </button>
                    </form>
                @else
                    <p class="text-muted mb-0">You can view lifecycle records but do not have permission to create them.</p>
                @endcan
            </div>
        </section>

        <div class="row">
            <div class="col-xl-7 mb-3">
                <section class="card elevation-2 h-100">
                    <div class="card-header"><h6 class="font-weight-bold mb-0">{{ $caseTitle }} records <span class="badge badge-light ml-1">{{ $cases->count() }}</span></h6></div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Lease</th><th>Request / inspection</th><th>Status</th><th>Created</th><th></th></tr></thead>
                            <tbody>
                                @forelse($cases as $lifecycleCase)
                                    <tr>
                                        <td><strong>{{ $lifecycleCase->leaseAgreement->lease_number }}</strong><small class="d-block text-muted">{{ $lifecycleCase->leaseAgreement->landlord_name }}</small></td>
                                        <td>
                                            @if($caseType === 'renewal')
                                                {{ $lifecycleCase->proposed_start_date?->format('d M Y') ?: '—' }} to {{ $lifecycleCase->proposed_end_date?->format('d M Y') ?: '—' }}
                                            @elseif($caseType === 'termination')
                                                {{ $lifecycleCase->proposed_effective_date?->format('d M Y') ?: '—' }}
                                            @else
                                                {{ $lifecycleCase->details['inspection_date'] ?? '—' }} · {{ ucfirst(str_replace('_', ' ', $lifecycleCase->details['handover_type'] ?? 'handover')) }}
                                            @endif
                                        </td>
                                        <td><span class="badge badge-{{ $lifecycleCase->status === 'recorded' ? 'success' : ($lifecycleCase->status === 'submitted' ? 'info' : 'secondary') }}">{{ ucfirst($lifecycleCase->status) }}</span></td>
                                        <td>{{ $lifecycleCase->created_at?->format('d M Y') ?: '—' }}</td>
                                        <td><button type="button" class="btn btn-sm btn-outline-primary" wire:click="openCase({{ $lifecycleCase->id }})">View</button></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No {{ strtolower($caseTitle) }} records yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div class="col-xl-5 mb-3">
                @if($selectedCase)
                    <section class="card elevation-2 h-100">
                        <div class="card-header">
                            <h6 class="font-weight-bold mb-1">{{ $selectedCase->leaseAgreement->lease_number }} · {{ ucfirst($selectedCase->status) }}</h6>
                            <small class="text-muted">{{ $selectedCase->leaseAgreement->contract_description }}</small>
                        </div>
                        <div class="card-body">
                            <h6 class="text-uppercase text-muted small font-weight-bold">Documents</h6>
                            @can('lease_tenant_workflow.manage')
                                <form wire:submit="uploadDocuments" class="border rounded p-3 mb-3">
                                    <div class="form-group">
                                        <label for="lifecycle-document-type">Document type</label>
                                        <select id="lifecycle-document-type" class="form-control form-control-sm" wire:model="documentType">
                                            @foreach($documentTypes as $type)<option value="{{ $type }}">{{ $type }}</option>@endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label for="lifecycle-files">Files</label>
                                        <input id="lifecycle-files" type="file" class="form-control-file" wire:model="supportingDocuments" multiple accept=".pdf,.jpg,.jpeg,.png">
                                        @error('supportingDocuments')<small class="text-danger d-block">{{ $message }}</small>@enderror
                                        @error('supportingDocuments.*')<small class="text-danger d-block">{{ $message }}</small>@enderror
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-outline-primary" wire:loading.attr="disabled" wire:target="supportingDocuments,uploadDocuments"><i class="fas fa-upload mr-1"></i>Upload privately</button>
                                </form>
                            @endcan
                            <div class="table-responsive mb-3">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        @forelse($selectedCase->documents as $document)
                                            <tr>
                                                <td>{{ $document->original_name }}<small class="d-block text-muted">{{ $document->document_type }} · {{ $document->uploaded_at?->format('d M Y') ?: '—' }}</small></td>
                                                <td><a class="btn btn-xs btn-outline-primary" href="{{ route('lease_agreements.lifecycle.documents.download', [$selectedCase, $document]) }}" aria-label="Download {{ $document->original_name }}" title="Download document"><i class="fas fa-download"></i></a></td>
                                            </tr>
                                        @empty
                                            <tr><td class="text-muted text-center py-3">No documents attached.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <h6 class="text-uppercase text-muted small font-weight-bold">History</h6>
                            @forelse($selectedCase->events as $event)
                                <div class="border-left pl-3 pb-2 mb-2">
                                    <strong class="d-block">{{ ucwords(str_replace('_', ' ', $event->action)) }}</strong>
                                    <small class="text-muted">{{ $event->actor?->name ?: '—' }} · {{ $event->occurred_at?->format('d M Y, H:i') ?: '—' }}</small>
                                    @if($event->comment)<p class="small mb-0 mt-1">{{ $event->comment }}</p>@endif
                                </div>
                            @empty
                                <p class="text-muted small mb-0">No history events.</p>
                            @endforelse
                        </div>
                    </section>
                @else
                    <section class="card elevation-2 h-100"><div class="card-body text-center text-muted py-5"><i class="fas fa-clipboard-list fa-2x mb-3"></i><p class="mb-0">Select a record to review its documents and history.</p></div></section>
                @endif
            </div>
        </div>
    </div>
</div>