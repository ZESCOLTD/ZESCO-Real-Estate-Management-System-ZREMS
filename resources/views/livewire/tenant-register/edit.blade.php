<div>
    <x-slot name="title">Tenant Record</x-slot>
    <x-slot name="pageHeader">Tenant Record</x-slot>

    <div class="container-fluid">
        <div class="mb-3">
            <a href="{{ route('tenant-register.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i>Tenants Register</a>
            @if($canViewHistory)
                <a href="{{ route('tenant-register.history', ['tenant_register_id' => $tenant->id]) }}" class="btn btn-outline-secondary btn-sm ml-1"><i class="fas fa-history mr-1"></i>History</a>
            @endif
            <h5 class="font-weight-bold mt-3 mb-0">{{ $tenant->current_tenant ?: 'Vacant Unit' }}</h5>
            <small class="text-muted">{{ $tenant->station_location }} / {{ $tenant->category }} / {{ $tenant->physical_address }}</small>
        </div>

        @if(session()->has('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(!$canEdit)<div class="alert alert-info">You have read-only access to this tenant record.</div>@endif

        <form wire:submit="save">
            <section class="card elevation-2">
                <div class="card-header"><h6 class="mb-0 font-weight-bold">Tenant and Property Details</h6></div>
                <div class="card-body"><div class="row">
                    @foreach([
                        'serial_no' => 'Serial No.', 'station_location' => 'Station/Location', 'category' => 'Category',
                        'current_tenant' => 'Current Tenant', 'man_no' => 'Man No.', 'employer' => 'Employer',
                        'location' => 'Location', 'physical_address' => 'Physical Address/Hse No.',
                        'area_m2' => 'Area m²', 'bedrooms' => 'No. of B/Rooms',
                        'open_closed_verandah_m2' => 'Open/Closed Verandah m²', 'garage_m2' => 'Garage m²',
                        'servants_quarter_m2' => 'Servants Quarter m²', 'condition' => 'Condition',
                        'current_rental_charge' => 'Current Rental Charge (ZMW)',
                        'market_rental_charge_gvd' => 'Market Rental Charge GVD (ZMW)',
                        'proposed_rental_charge_50_percent' => 'Proposed Rental Charge @50% (ZMW)',
                        'proposed_rentals_after_verification' => 'Proposed Rentals after Verification (ZMW)',
                        'proposed_percentage_increment' => 'Proposed Percentage Increment (%)',
                        'water_charges' => 'Water Charges', 'sewer_charges' => 'Sewer Charges',
                        'proposed_total_amount' => 'Proposed Total Amount',
                    ] as $field => $label)
                        <div class="form-group col-md-6 col-xl-4">
                            <label for="tenant-{{ $field }}">{{ $label }}</label>
                            <input id="tenant-{{ $field }}" type="text" class="form-control" wire:model="form.{{ $field }}" @disabled(!$canEdit)>
                            @error('form.' . $field)<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    @endforeach
                </div></div>
                @if($canEdit)<div class="card-footer text-right"><button type="submit" class="btn btn-primary" wire:loading.attr="disabled"><i class="fas fa-save mr-1"></i>Save Changes</button></div>@endif
            </section>
        </form>

        @can('tenant_register.documents.view')
            <section class="card elevation-2 mt-3">
                <div class="card-header"><h6 class="font-weight-bold mb-0">Tenant Documents</h6></div>
                <div class="card-body">
                    @if($canManageDocuments)
                        <form wire:submit="uploadDocument" class="border-bottom pb-3 mb-3">
                            <div class="form-row align-items-end">
                                <div class="form-group col-md-4">
                                    <label for="tenant-document-type">Document type</label>
                                    <input id="tenant-document-type" type="text" class="form-control" wire:model="documentType" maxlength="255">
                                    @error('documentType')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="tenant-document-expiry">Expiry date (optional)</label>
                                    <input id="tenant-document-expiry" type="date" class="form-control" wire:model="documentExpiry">
                                    @error('documentExpiry')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="tenant-document-file">Private file (PDF/JPG/PNG, max 10 MB)</label>
                                    <input id="tenant-document-file" type="file" class="form-control-file" wire:model="documentFile" accept=".pdf,.jpg,.jpeg,.png">
                                    @error('documentFile')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="form-group col-md-2"><button class="btn btn-primary btn-block" type="submit" wire:loading.attr="disabled">Upload</button></div>
                            </div>
                        </form>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr><th>Document</th><th>Type</th><th>Uploaded</th><th>Uploaded by</th><th>Review status</th><th></th></tr></thead>
                            <tbody>
                                @forelse($documents as $document)
                                    <tr>
                                        <td>{{ $document->original_name }}</td>
                                        <td>{{ $document->document_type }}</td>
                                        <td>{{ $document->uploaded_at?->format('d M Y') ?: '—' }}</td>
                                        <td>{{ $document->uploader?->name ?: '—' }}</td>
                                        <td>{{ $document->review_status }}</td>
                                        <td><a href="{{ route('tenant-register.documents.download', $document) }}" class="btn btn-sm btn-outline-primary" aria-label="Download document"><i class="fas fa-download"></i></a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-muted text-center py-3">No documents on this register record.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endcan
    </div>
</div>