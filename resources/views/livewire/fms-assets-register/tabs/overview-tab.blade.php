<div>

    <div class="row g-3">

        {{-- =======================
            BASIC INFO
        ========================--}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-light fw-semibold">
                    Basic Information
                </div>

                <div class="card-body">

                    <div class="mb-2">
                        <small class="text-muted">Asset Number</small>
                        <div class="fw-bold">{{ $asset->asset_number ?? '-' }}</div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Tag Number</small>
                        <div>{{ $asset->tag_number ?? '-' }}</div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Serial Number</small>
                        <div>{{ $asset->serial_number ?? '-' }}</div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Description</small>
                        <div>{{ $asset->description ?? '-' }}</div>
                    </div>

                </div>
            </div>
        </div>

        {{-- =======================
            CLASSIFICATION
        ========================--}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-light fw-semibold">
                    Classification
                </div>

                <div class="card-body">

                    <div class="mb-2">
                        <small class="text-muted">Major Category</small>
                        <div class="fw-bold">
                            {{ $asset->major_category ?? '-' }}
                        </div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Sub Major Category</small>
                        <div>{{ $asset->sub_major_category ?? '-' }}</div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Minor Category</small>
                        <div>{{ $asset->minor_category ?? '-' }}</div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Sub Minor Category</small>
                        <div>{{ $asset->sub_minor_category ?? '-' }}</div>
                    </div>

                </div>
            </div>
        </div>

        {{-- =======================
            ORGANIZATION / GROUPING
        ========================--}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-light fw-semibold">
                    Organization
                </div>

                <div class="card-body">

                    <div class="mb-2">
                        <small class="text-muted">Business Unit</small>
                        <div>{{ $asset->business_unit ?? '-' }}</div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Cost Centre</small>
                        <div>{{ $asset->cost_centre ?? '-' }}</div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Link ID</small>
                        <div>
                            <span class="badge bg-primary">
                                {{ $asset->link_id ?? '-' }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- =======================
            ASSET DETAILS
        ========================--}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-light fw-semibold">
                    Asset Details
                </div>

                <div class="card-body">

                    <div class="mb-2">
                        <small class="text-muted">Units</small>
                        <div>{{ $asset->units ?? '-' }}</div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Life (Months)</small>
                        <div>{{ $asset->life_in_months ?? '-' }}</div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Date Placed In Service</small>
                        <div>{{ $asset->date_placed_in_service ?? '-' }}</div>
                    </div>

                </div>
            </div>
        </div>

    </div>

</div>
