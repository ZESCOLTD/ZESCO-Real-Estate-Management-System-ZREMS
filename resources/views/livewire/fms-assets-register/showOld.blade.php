<div>
    <x-slot name="title">Land Parcel Details</x-slot>
    <x-slot name="pageHeader">Land Details</x-slot>

    {{-- HEADER --}}
    <div class="card mb-3 shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center">
            {{-- BACK BUTTON --}}
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                ← Back
            </a>
{{--            <div>--}}
{{--                <h4 class="mb-1">--}}
{{--                    Parcel: {{ $parcel->parcel_no ?? 'N/A' }}--}}
{{--                </h4>--}}

{{--                <small class="text-muted">--}}
{{--                    {{ $parcel->province }} / {{ $parcel->district }}--}}
{{--                </small>--}}

{{--                <div class="mt-2">--}}
{{--                    <span class="badge bg-primary">{{ $parcel->tenure_type }}</span>--}}
{{--                    <span class="badge bg-success">{{ $parcel->status }}</span>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <div class="text-end">--}}
{{--                <div><strong>{{ number_format($parcel->area_hectares, 2) }} Ha</strong></div>--}}

{{--                @if($parcel->lease_expiry_date)--}}
{{--                    <small class="text-danger">--}}
{{--                        Lease expires: {{ $parcel->lease_expiry_date }}--}}
{{--                    </small>--}}
{{--                @endif--}}
{{--            </div>--}}

        </div>
    </div>

    {{-- TABS --}}
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#overview">Overview</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#assets">Properties</a></li>
{{--        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#legal">Legal</a></li>--}}
{{--        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#planning">Planning</a></li>--}}
{{--        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#survey">Survey & Titles</a></li>--}}
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#financials">Payments</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#documents">Documents</a></li>
{{--        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#compliance">Compliance</a></li>--}}
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#disputes">Disputes</a></li>
    </ul>

    <div class="tab-content">

        <div class="tab-content">

            <div class="tab-pane fade show active" id="overview">
                <livewire:fms-assets-register.tabs.overview-tab :parcelId="$parcelId" />
            </div>

            <div class="tab-pane fade" id="assets">
                <livewire:fms-assets-register.tabs.built-assets-tab :parcelId="$parcelId" />
            </div>

{{--            <div class="tab-pane fade" id="legal">--}}
{{--                <livewire:fms-assets-register.tabs.legal-tab :parcelId="$parcelId" />--}}
{{--            </div>--}}

            <div class="tab-pane fade" id="financials">
                <livewire:fms-assets-register.tabs.payments-tab :parcelId="$parcelId" />
            </div>

            <div class="tab-pane fade" id="documents">
                <livewire:fms-assets-register.tabs.documents-tab :parcelId="$parcelId" />
            </div>

{{--            <div class="tab-pane fade" id="compliance">--}}
{{--                <livewire:fms-assets-register.tabs.compliance-tab :parcelId="$parcelId" />--}}
{{--            </div>--}}

            <div class="tab-pane fade" id="disputes">
                <livewire:fms-assets-register.tabs.disputes-tab :parcelId="$parcelId" />
            </div>

        </div>


    </div>

</div>
