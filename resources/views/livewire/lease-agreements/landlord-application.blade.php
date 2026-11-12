<div>
    <x-slot name="title">New Lease</x-slot>
    <x-slot name="pageHeader">New Lease</x-slot>

    <div class="container-fluid">
        <div class="mb-3">
            <h5 class="font-weight-bold mb-1">New Lease</h5>
            <small class="text-muted">Capture and track a proposed lease where ZESCO is the landlord.</small>
        </div>

        @if(session()->has('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <p class="text-muted mb-3">Direction: <strong>ZESCO as Landlord</strong></p>

        <div class="alert alert-info py-2">
            <i class="fas fa-info-circle mr-1"></i>
            This is an intake record only. Submission does not approve the applicant, create a Lease Register agreement, authorize occupation, or set final rent.
        </div>

        <section class="card elevation-2 mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="font-weight-bold mb-0">{{ $selectedApplication?->status === 'draft' ? 'Update landlord-side draft' : 'Landlord-side proposal' }}</h6>
                @if($selectedApplication)
                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="startNew"><i class="fas fa-plus mr-1"></i>New application</button>
                @endif
            </div>
            <div class="card-body">
                @if($selectedApplication && $selectedApplication->status !== 'draft')
                    <p class="text-muted mb-0">This application has been submitted and is read-only. Record review milestones from its stage panel.</p>
                @else
                @can('lease_tenant_workflow.manage')
                    <form>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="landlord-origin">How did this proposal start?</label>
                                <select id="landlord-origin" class="form-control" wire:model="origin">
                                    <option value="">Select origin</option>
                                    <option value="external_request">External tenant request</option>
                                    <option value="zesco_offer">ZESCO offer</option>
                                </select>
                                @error('origin')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="external-tenant-name">External tenant / organization</label>
                                <input id="external-tenant-name" type="text" class="form-control" wire:model="externalTenantName">
                                @error('externalTenantName')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="external-contact-name">Contact person</label>
                                <input id="external-contact-name" type="text" class="form-control" wire:model="contactName">
                                @error('contactName')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="external-contact-phone">Contact phone</label>
                                <input id="external-contact-phone" type="text" class="form-control" wire:model="contactPhone">
                                @error('contactPhone')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="external-contact-email">Contact email</label>
                                <input id="external-contact-email" type="email" class="form-control" wire:model="contactEmail">
                                @error('contactEmail')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="landlord-property-reference">Property / asset reference</label>
                                <input id="landlord-property-reference" type="text" class="form-control" wire:model="propertyReference" placeholder="Asset number, if known">
                                @error('propertyReference')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-8">
                                <label for="landlord-property-description">Property description and location</label>
                                <input id="landlord-property-description" type="text" class="form-control" wire:model="propertyDescription">
                                @error('propertyDescription')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="landlord-proposed-start">Proposed start date</label>
                                <input id="landlord-proposed-start" type="date" class="form-control" wire:model="proposedStartDate">
                                @error('proposedStartDate')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="landlord-proposed-end">Proposed end date</label>
                                <input id="landlord-proposed-end" type="date" class="form-control" wire:model="proposedEndDate">
                                @error('proposedEndDate')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="landlord-rent-period">Rent payment term</label>
                                <select id="landlord-rent-period" class="form-control" wire:model="proposedRentPeriod">
                                    <option value="monthly">Monthly</option>
                                    <option value="3_months">Every 3 months</option>
                                    <option value="6_months">Every 6 months</option>
                                </select>
                                @error('proposedRentPeriod')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="landlord-proposed-rent">Rent amount per term (ZMW)</label>
                                <input id="landlord-proposed-rent" type="number" min="0" step="0.01" class="form-control" wire:model="proposedRentAmount">
                                @error('proposedRentAmount')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-12">
                                <label for="landlord-proposal-notes">Proposal notes</label>
                                <textarea id="landlord-proposal-notes" class="form-control" rows="3" wire:model="proposalNotes"></textarea>
                                @error('proposalNotes')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-secondary" wire:click="saveDraft" wire:loading.attr="disabled"><i class="fas fa-save mr-1"></i>Save draft</button>
                            <button type="button" class="btn btn-primary" wire:click="submitForTracking" wire:loading.attr="disabled"><i class="fas fa-paper-plane mr-1"></i>Submit for tracking</button>
                        </div>
                    </form>
                @else
                    <p class="text-muted mb-0">You can view landlord-side applications but do not have permission to create them.</p>
                @endcan
                @endif
            </div>
        </section>

        <div class="row">
            <div class="col-xl-7 mb-3">
                <section class="card elevation-2 h-100">
                    <div class="card-header d-flex justify-content-between align-items-center"><h6 class="font-weight-bold mb-0">Landlord-side applications</h6><span class="badge badge-light">{{ $applications->count() }}</span></div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Application</th><th>Origin</th><th>Proposed tenant</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                                @forelse($applications as $application)
                                    <tr>
                                        <td><strong>{{ $application->reference_no }}</strong><small class="d-block text-muted">{{ $application->property_reference ?: $application->property_description }}</small></td>
                                        <td>{{ $application->origin === 'external_request' ? 'External request' : 'ZESCO offer' }}</td>
                                        <td>{{ $application->external_tenant_name }}</td>
                                        <td><span class="badge badge-{{ in_array($application->status, ['tenant_review', 'submitted', 'legal_review', 'ag_review'], true) ? 'info' : ($application->status === 'tenant_feedback' ? 'warning' : (in_array($application->status, ['legal_finalized', 'ag_finalized'], true) ? 'success' : 'secondary')) }}">{{ $this->statusLabel($application->status) }}</span></td>
                                        <td><button type="button" class="btn btn-sm btn-outline-primary" wire:click="openApplication({{ $application->id }})">View</button></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No landlord-side applications yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div class="col-xl-5 mb-3">
                @if($selectedApplication)
                    <section class="card elevation-2 h-100">
                        <div class="card-header"><h6 class="font-weight-bold mb-1">{{ $selectedApplication->reference_no }} · {{ $selectedApplication->external_tenant_name }}</h6><small class="text-muted">{{ $selectedApplication->property_description }} · {{ $this->statusLabel($selectedApplication->status) }}</small></div>
                        <div class="card-body">
                            @if($selectedApplication->status === 'tenant_review' || $selectedApplication->status === 'submitted')
                                <div class="alert alert-info py-2">The draft is with the prospective tenant for review. Record their feedback when it arrives.</div>
                                @can('lease_tenant_workflow.manage')
                                    <label for="tenant-feedback-note">Tenant feedback</label>
                                    <textarea id="tenant-feedback-note" class="form-control mb-2" rows="3" wire:model="actionComment" placeholder="Record the tenant's comments or requested changes"></textarea>
                                    @error('actionComment')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                    <button type="button" class="btn btn-sm btn-primary mb-3" wire:click="recordAction('tenant_feedback_received')">Record feedback</button>
                                @endcan
                            @elseif($selectedApplication->status === 'tenant_feedback')
                                <div class="alert alert-warning py-2">Tenant feedback is recorded. Upload any revised draft before choosing the next step.</div>
                                @can('lease_tenant_workflow.manage')
                                    <label for="tenant-feedback-action-note">Revision / confirmation note</label>
                                    <textarea id="tenant-feedback-action-note" class="form-control mb-2" rows="3" wire:model="actionComment" placeholder="Describe the revision sent or tenant confirmation"></textarea>
                                    @error('actionComment')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="recordAction('revision_sent_to_tenant')">Send revised draft to tenant</button>
                                        <button type="button" class="btn btn-sm btn-primary" wire:click="recordAction('tenant_confirmed_draft')">Tenant confirmed, send to Legal</button>
                                    </div>
                                @endcan
                            @elseif($selectedApplication->status === 'legal_review')
                                @php($sendToAttorneyGeneral = (float) $selectedApplication->proposed_rent_amount > 600000)
                                <div class="alert alert-info py-2">
                                    Tenant confirmation is recorded. Rent term: <strong>{{ match($selectedApplication->proposed_rent_period) { 'monthly' => 'Monthly', '3_months' => 'Every 3 months', '6_months' => 'Every 6 months', default => 'Not set' } }}</strong>;
                                    amount per term: <strong>K{{ number_format((float) $selectedApplication->proposed_rent_amount, 2) }}</strong>.
                                    Above K600,000 routes to the Attorney General; K600,000 or less is finalized by ZESCO Legal.
                                </div>
                                @can('lease_tenant_workflow.manage')
                                    <label for="legal-review-note">Legal review / routing note</label>
                                    <textarea id="legal-review-note" class="form-control mb-2" rows="2" wire:model="actionComment" placeholder="Record Legal's review and handoff"></textarea>
                                    @error('actionComment')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                    @error('action')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                    <button type="button" class="btn btn-sm btn-primary mb-3" wire:click="recordAction('complete_legal_review')">{{ $sendToAttorneyGeneral ? 'Complete Legal review and send to AG' : 'Complete Legal review and finalize' }}</button>
                                @endcan
                            @elseif($selectedApplication->status === 'ag_review')
                                <div class="alert alert-warning py-2">This lease exceeds K600,000 for its stated payment term and is awaiting Attorney General review/finalization.</div>
                                @can('lease_tenant_workflow.manage')
                                    <label for="ag-finalization-note">Attorney General return note</label>
                                    <textarea id="ag-finalization-note" class="form-control mb-2" rows="2" wire:model="actionComment" placeholder="Record receipt of the finalized copy"></textarea>
                                    @error('actionComment')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                    <button type="button" class="btn btn-sm btn-primary mb-3" wire:click="recordAction('ag_finalization_received')">Record finalized copy returned to ZESCO</button>
                                @endcan
                            @elseif($selectedApplication->status === 'legal_finalized')
                                <div class="alert alert-success py-2">ZESCO Legal finalized the lease. Upload and retain the finalized copy for Real Estate.</div>
                            @elseif($selectedApplication->status === 'ag_finalized')
                                <div class="alert alert-success py-2">The Attorney General's Office finalized the lease and the copy was recorded as returned to ZESCO.</div>
                            @endif

                            <h6 class="text-uppercase text-muted small font-weight-bold">Documents</h6>
                            @can('lease_tenant_workflow.manage')
                                <form wire:submit="uploadDocuments" class="border rounded p-3 mb-3">
                                    <div class="form-group">
                                        <label for="landlord-document-type">Document type</label>
                                        <select id="landlord-document-type" class="form-control form-control-sm" wire:model="documentType">
                                            @foreach($documentTypes as $type)<option value="{{ $type }}">{{ $type }}</option>@endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label for="landlord-documents">Files</label>
                                        <input id="landlord-documents" type="file" class="form-control-file" wire:model="supportingDocuments" multiple accept=".pdf,.jpg,.jpeg,.png">
                                        @error('supportingDocuments')<small class="text-danger d-block">{{ $message }}</small>@enderror
                                        @error('supportingDocuments.*')<small class="text-danger d-block">{{ $message }}</small>@enderror
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-outline-primary" wire:loading.attr="disabled" wire:target="supportingDocuments,uploadDocuments"><i class="fas fa-upload mr-1"></i>Upload privately</button>
                                </form>
                            @endcan
                            <div class="table-responsive mb-3">
                                <table class="table table-sm mb-0"><tbody>
                                    @forelse($selectedApplication->documents as $document)
                                        <tr><td>{{ $document->original_name }}<small class="d-block text-muted">{{ $document->document_type }} · {{ $document->uploaded_at?->format('d M Y') ?: '—' }}</small></td><td><a class="btn btn-xs btn-outline-primary" href="{{ route('lease_agreements.landlord_applications.documents.download', [$selectedApplication, $document]) }}" aria-label="Download {{ $document->original_name }}"><i class="fas fa-download"></i></a></td></tr>
                                    @empty
                                        <tr><td class="text-center text-muted py-3">No documents attached.</td></tr>
                                    @endforelse
                                </tbody></table>
                            </div>
                            <h6 class="text-uppercase text-muted small font-weight-bold">History</h6>
                            @forelse($selectedApplication->events as $event)
                                <div class="border-left pl-3 pb-2 mb-2"><strong class="d-block">{{ ucwords(str_replace('_', ' ', $event->action)) }}</strong><small class="text-muted">{{ $event->actor?->name ?: '—' }} · {{ $event->occurred_at?->format('d M Y, H:i') ?: '—' }}</small>@if($event->comment)<p class="small mb-0 mt-1">{{ $event->comment }}</p>@endif</div>
                            @empty
                                <p class="text-muted small mb-0">No history events.</p>
                            @endforelse
                        </div>
                    </section>
                @else
                    <section class="card elevation-2 h-100"><div class="card-body text-center text-muted py-5"><i class="fas fa-clipboard-list fa-2x mb-3"></i><p class="mb-0">Select an application to review its documents and history.</p></div></section>
                @endif
            </div>
        </div>
    </div>
</div>