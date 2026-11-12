<div>
    <x-slot name="title">Tenant Onboarding</x-slot>
    <x-slot name="pageHeader">Tenant Onboarding</x-slot>

    <style>
        .tenant-onboarding { --tenant-ink: #253745; --tenant-muted: #71808a; --tenant-line: #e3e8e7; --tenant-amber: #e69732; }
        .tenant-onboarding .onboarding-heading { align-items: flex-start; display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 1.15rem; }
        .tenant-onboarding h3 { color: var(--tenant-ink); font-size: 1.25rem; font-weight: 700; margin: 0 0 .25rem; }
        .tenant-onboarding .intro { color: var(--tenant-muted); font-size: .85rem; margin: 0; }
        .tenant-onboarding .step-strip { border-bottom: 1px solid var(--tenant-line); display: grid; grid-template-columns: repeat(5, minmax(100px, 1fr)); margin-bottom: 1rem; overflow-x: auto; }
        .tenant-onboarding .step-item { border-bottom: 3px solid transparent; color: var(--tenant-muted); min-width: 100px; padding: .7rem .45rem .65rem; text-align: center; }
        .tenant-onboarding .step-item.is-active { border-color: var(--tenant-amber); color: var(--tenant-ink); }
        .tenant-onboarding .step-number { align-items: center; background: #edf0ef; border-radius: 50%; display: inline-flex; font-size: .73rem; font-weight: 700; height: 25px; justify-content: center; margin-right: .35rem; width: 25px; }
        .tenant-onboarding .step-item.is-active .step-number { background: var(--tenant-amber); color: #fff; }
        .tenant-onboarding .step-label { font-size: .76rem; font-weight: 600; white-space: nowrap; }
        .tenant-onboarding .section-title { border-bottom: 1px solid var(--tenant-line); color: var(--tenant-ink); font-size: .95rem; font-weight: 700; margin: 0 0 1rem; padding-bottom: .65rem; }
        .tenant-onboarding .form-group label { color: #4b5a62; font-size: .8rem; font-weight: 600; }
        .tenant-onboarding .form-control { border-color: #dce3e1; min-height: 39px; }
        .tenant-onboarding .form-control:focus { border-color: #d49a4a; box-shadow: 0 0 0 .15rem rgba(230,151,50,.14); }
        .tenant-onboarding .help-text { color: var(--tenant-muted); font-size: .75rem; margin-top: .25rem; }
        .tenant-onboarding .review-value { color: var(--tenant-ink); font-size: .88rem; font-weight: 600; overflow-wrap: anywhere; }
        .tenant-onboarding .review-label { color: var(--tenant-muted); font-size: .72rem; margin-bottom: .15rem; text-transform: uppercase; }
        .tenant-onboarding .review-item { border-bottom: 1px solid #edf0ef; min-height: 57px; padding: .55rem 0; }
        .tenant-onboarding .actions { align-items: center; border-top: 1px solid var(--tenant-line); display: flex; flex-wrap: wrap; gap: .5rem; justify-content: space-between; margin-top: 1rem; padding-top: 1rem; }
        .tenant-onboarding .action-group { display: flex; flex-wrap: wrap; gap: .5rem; }
        @media (max-width: 575.98px) {
            .tenant-onboarding .onboarding-heading { flex-direction: column; }
            .tenant-onboarding .step-label { display: block; margin-top: .25rem; }
        }
    </style>

    <div class="container-fluid tenant-onboarding">
        <div class="onboarding-heading">
            <div>
                <button type="button" class="btn btn-outline-secondary btn-sm mb-2" wire:click="backToApplications"><i class="fas fa-arrow-left mr-1"></i>My Applications</button>
                @if($application && auth()->user()->can('tenant_register.history.view'))
                    <a class="btn btn-outline-secondary btn-sm mb-2" href="{{ route('tenant-register.history', ['application_id' => $application->id]) }}"><i class="fas fa-history mr-1"></i>History</a>
                @endif
                <h3>{{ $application ? 'Continue Tenant Application' : 'New Tenant Application' }}</h3>
                <p class="intro">Capture the proposed tenant and housing request. Document requirements are provisional until policy is confirmed.</p>
            </div>
            @if($application)<span class="badge badge-light border p-2">{{ $application->reference_no }} · {{ $application->status }}</span>@endif
        </div>

        @if(session()->has('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->has('submission') || $errors->has('workflow'))
            <div class="alert alert-danger" role="alert">
                @if($errors->has('submission'))<div>{{ $errors->first('submission') }}</div>@endif
                @if($errors->has('workflow'))<div>{{ $errors->first('workflow') }}</div>@endif
            </div>
        @endif

        <section class="card elevation-2">
            <div class="card-body">
                <div class="step-strip" aria-label="Application steps">
                    @foreach(['Tenant', 'Contact', 'Housing', 'Documents', 'Review'] as $index => $label)
                        <div class="step-item {{ $currentStep === $index + 1 ? 'is-active' : '' }}">
                            <span class="step-number">{{ $index + 1 }}</span><span class="step-label">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>

                @if($currentStep === 1)
                    <h4 class="section-title">Tenant information</h4>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="onboarding-type">Applicant type <span class="text-danger">*</span></label>
                            <select id="onboarding-type" class="form-control" wire:model="form.tenant_type">
                                <option value="">Select applicant type</option>
                                <option value="zesco_employee">ZESCO employee</option>
                                <option value="organization">Organization</option>
                                <option value="individual">Individual</option>
                            </select>
                            @error('form.tenant_type')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group col-md-8">
                            <label for="applicant-name">{{ $form['tenant_type'] === 'organization' ? 'Organization legal name' : 'Applicant full name' }} <span class="text-danger">*</span></label>
                            <input id="applicant-name" type="text" class="form-control" wire:model="form.applicant_name" autocomplete="name">
                            @error('form.applicant_name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        @if($form['tenant_type'] === 'zesco_employee')
                            <div class="form-group col-md-6">
                                <label for="man-no">Man No. <span class="text-danger">*</span></label>
                                <input id="man-no" type="text" class="form-control" wire:model="form.man_no">
                                @error('form.man_no')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="employer">Employer / business unit</label>
                                <input id="employer" type="text" class="form-control" wire:model="form.employer">
                                @error('form.employer')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        @elseif($form['tenant_type'] === 'organization')
                            <div class="form-group col-md-6">
                                <label for="organization-registration">Organization registration number</label>
                                <input id="organization-registration" type="text" class="form-control" wire:model="form.organization_registration_no">
                                @error('form.organization_registration_no')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="contact-person">Contact person <span class="text-danger">*</span></label>
                                <input id="contact-person" type="text" class="form-control" wire:model="form.contact_person">
                                @error('form.contact_person')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        @else
                            <div class="form-group col-md-6">
                                <label for="identity-reference">NRC / Passport number</label>
                                <input id="identity-reference" type="text" class="form-control" wire:model="form.identity_reference">
                                @error('form.identity_reference')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        @endif
                    </div>
                @elseif($currentStep === 2)
                    <h4 class="section-title">Contact and address</h4>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="contact-phone">Phone number <span class="text-danger">*</span></label>
                            <input id="contact-phone" type="tel" class="form-control" wire:model="form.phone" autocomplete="tel">
                            @error('form.phone')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="contact-email">Email address</label>
                            <input id="contact-email" type="email" class="form-control" wire:model="form.email" autocomplete="email">
                            @error('form.email')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="physical-address">Physical address <span class="text-danger">*</span></label>
                            <textarea id="physical-address" class="form-control" rows="3" wire:model="form.physical_address"></textarea>
                            @error('form.physical_address')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="postal-address">Postal address</label>
                            <textarea id="postal-address" class="form-control" rows="3" wire:model="form.postal_address"></textarea>
                            @error('form.postal_address')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                @elseif($currentStep === 3)
                    <h4 class="section-title">Housing request</h4>
                    <p class="help-text mb-3">This records the requested location. It does not allocate a unit or create a lease.</p>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="station-location">Station / location <span class="text-danger">*</span></label>
                            <input id="station-location" list="tenant-stations" class="form-control" wire:model="form.station_location">
                            <datalist id="tenant-stations">@foreach($stationOptions as $station)<option value="{{ $station }}">@endforeach</datalist>
                            @error('form.station_location')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="housing-category">Housing category <span class="text-danger">*</span></label>
                            <input id="housing-category" list="tenant-categories" class="form-control" wire:model="form.category">
                            <datalist id="tenant-categories">@foreach($categoryOptions as $category)<option value="{{ $category }}">@endforeach</datalist>
                            @error('form.category')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="location-code">Location code</label>
                            <input id="location-code" type="text" class="form-control" wire:model="form.location_code" placeholder="e.g. LS, H/C">
                            @error('form.location_code')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="unit-reference">Requested unit / house reference</label>
                            <input id="unit-reference" type="text" class="form-control" wire:model="form.requested_unit_reference">
                            @error('form.requested_unit_reference')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="occupants-count">Number of occupants</label>
                            <input id="occupants-count" type="number" min="1" class="form-control" wire:model="form.occupants_count">
                            @error('form.occupants_count')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="move-in-date">Requested move-in date</label>
                            <input id="move-in-date" type="date" class="form-control" wire:model="form.requested_move_in_date">
                            @error('form.requested_move_in_date')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                @elseif($currentStep === 4)
                    <h4 class="section-title">Supporting documents</h4>
                    <div class="alert alert-info py-2">Uploads are private. The required document checklist is provisional until ZESCO’s official requirements are confirmed.</div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="supporting-document-type">Document type</label>
                            <input id="supporting-document-type" type="text" class="form-control" wire:model="supportingDocumentType" placeholder="e.g. Identification, application form">
                            @error('supportingDocumentType')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="supporting-document-expiry">Expiry date (if applicable)</label>
                            <input id="supporting-document-expiry" type="date" class="form-control" wire:model="supportingDocumentExpiry">
                            @error('supportingDocumentExpiry')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="supporting-documents">Attach supporting files (PDF, JPG or PNG; up to 10 files, 10 MB each)</label>
                        <input id="supporting-documents" type="file" class="form-control-file" wire:model="supportingDocuments" accept=".pdf,.jpg,.jpeg,.png" multiple>
                        <div wire:loading wire:target="supportingDocuments" class="help-text">Uploading files…</div>
                        @error('supportingDocuments')<small class="text-danger d-block">{{ $message }}</small>@enderror
                        @error('supportingDocuments.*')<small class="text-danger d-block">{{ $message }}</small>@enderror
                    </div>
                    @if(count($supportingDocuments))
                        <ul class="mb-0">@foreach($supportingDocuments as $file)<li>{{ $file->getClientOriginalName() }}</li>@endforeach</ul>
                    @endif
                    @if($application?->documents->isNotEmpty())
                        <div class="mt-3"><strong>Previously uploaded</strong><ul class="mb-0">@foreach($application->documents as $document)<li>{{ $document->original_name }}</li>@endforeach</ul></div>
                    @endif
                @else
                    <h4 class="section-title">Review and submit</h4>
                    <div class="row">
                        @foreach([
                            'tenant_type' => 'Applicant type', 'applicant_name' => 'Tenant / organization name',
                            'man_no' => 'Man No.', 'employer' => 'Employer', 'organization_registration_no' => 'Registration number',
                            'contact_person' => 'Contact person', 'phone' => 'Phone', 'email' => 'Email',
                            'physical_address' => 'Physical address', 'postal_address' => 'Postal address',
                            'station_location' => 'Station / location', 'category' => 'Housing category',
                            'location_code' => 'Location code', 'requested_unit_reference' => 'Requested unit',
                            'occupants_count' => 'Occupants', 'requested_move_in_date' => 'Requested move-in date',
                        ] as $field => $label)
                            @if(filled($form[$field] ?? null))
                                <div class="col-md-6"><div class="review-item"><div class="review-label">{{ $label }}</div><div class="review-value">{{ $form[$field] }}</div></div></div>
                            @endif
                        @endforeach
                    </div>
                    <div class="custom-control custom-checkbox mt-3">
                        <input id="tenant-declaration" type="checkbox" class="custom-control-input" wire:model="form.declaration_confirmed">
                        <label class="custom-control-label" for="tenant-declaration">I confirm the information provided is accurate to the best of my knowledge.</label>
                    </div>
                    @error('form.declaration_confirmed')<small class="text-danger">{{ $message }}</small>@enderror
                @endif

                <div class="actions">
                    <span class="help-text">Step {{ $currentStep }} of 5</span>
                    <div class="action-group">
                        @if($currentStep > 1)
                            <button type="button" class="btn btn-outline-secondary" wire:click="previousStep">Back</button>
                        @endif
                        <button type="button" class="btn btn-outline-primary" wire:click="saveDraft" wire:loading.attr="disabled">Save Draft</button>
                        @if($currentStep < 5)
                            <button type="button" class="btn btn-primary" wire:click="nextStep" wire:loading.attr="disabled">Continue <i class="fas fa-arrow-right ml-1"></i></button>
                        @else
                            <button type="button" class="btn btn-primary" wire:click="submitForVerification" wire:loading.attr="disabled"><i class="fas fa-paper-plane mr-1"></i>Submit for Verification</button>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>