<div>
    <x-slot name="title">Asset Register</x-slot>
    <x-slot name="pageHeader">Asset Register</x-slot>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-0 font-weight-bold">Asset Register</h5>
                <small class="text-muted">Browse and manage the official asset records</small>
            </div>
            @can('asset_register.create')
                <a href="{{ route('asset-register.import') }}" class="btn text-white bg-success mr-2"><i class="fas fa-file-excel mr-1"></i>Excel Import</a>
            @endcan
        </div>

        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card elevation-2 border-0"><div class="card-body d-flex justify-content-between align-items-center">
                    <div><small class="text-muted text-uppercase">Total Assets</small><h3 class="mb-0 font-weight-bold">{{ number_format($stats['total']) }}</h3></div>
                    <div style="font-size: 2.2rem; color: {{ config('theme.color_primary') }}"><i class="fas fa-boxes text-warning"></i></div>
                </div></div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card elevation-2 border-0"><div class="card-body d-flex justify-content-between align-items-center">
                    <div><small class="text-muted text-uppercase">Locations</small><h3 class="mb-0 font-weight-bold">{{ number_format($stats['locations']) }}</h3></div>
                    <div style="font-size: 2.2rem; color: {{ config('theme.color_primary') }}"><i class="fas fa-map-marker-alt text-warning"></i></div>
                </div></div>
            </div>
            @foreach($categoryCounts as $category)
                <div class="col-md-3 mb-3">
                    <div class="card elevation-2 border-0"><div class="card-body d-flex justify-content-between align-items-center">
                        <div><small class="text-muted">{{ $category->major_category }}</small><h5 class="mb-0 font-weight-bold">{{ number_format($category->total) }}</h5></div>
                        <div style="font-size: 2.2rem; color: {{ config('theme.color_primary') }}"><i class="fas fa-tags text-warning"></i></div>
                    </div></div>
                </div>
            @endforeach
        </div>

        <section class="card elevation-2 mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-6 mb-2">
                        <label class="sr-only" for="asset-search">Search Asset Number or Description</label>
                        <input id="asset-search" type="search" class="form-control" placeholder="Search Asset Number or Description" wire:model.live.debounce.350ms="search">
                    </div>
                    @foreach([
                        ['businessUnit', 'BU', $businessUnits],
                        ['costCentre', 'CC', $costCentres],
                        ['area', 'Area', $areas],
                        ['location', 'Location', $locations],
                        ['subLocation', 'Sub Location', $subLocations],
                        ['majorCategory', 'Major Category', $majorCategories],
                        ['status', 'Status', $statuses],
                        ['reportline', 'Reportline', $reportlines],
                    ] as [$property, $label, $options])
                        <div class="form-group col-md-3 col-xl-2 mb-2">
                            <label class="sr-only" for="asset-filter-{{ $property }}">Filter by {{ $label }}</label>
                            <select id="asset-filter-{{ $property }}" class="form-control" wire:model.live="{{ $property }}">
                                <option value="">All {{ $label }}</option>
                                @foreach($options as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
                <div wire:loading.delay wire:target="search,businessUnit,costCentre,area,location,subLocation,majorCategory,status,reportline" class="mt-2 text-muted"><i class="fas fa-spinner fa-spin mr-1"></i>Searching assets...</div>
            </div>
        </section>

        <section class="card elevation-2">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0" style="min-width: 1800px;">
                    <thead style="background-color: {{ config('theme.color_primary') }}; color: {{ config('theme.color_primary_text_light') }}">
                        <tr>
                            <th>BU</th>
                            <th>CC</th>
                            <th>Asset Number</th>
                            <th>Asset Description</th>
                            <th>Area</th>
                            <th>Location</th>
                            <th>Sub Location</th>
                            <th>Major Category</th>
                            <th>Sub Major Category</th>
                            <th>Minor Category</th>
                            <th>Date Placed in Service</th>
                            <th>Life in Months</th>
                            <th class="text-right">Cost</th>
                            <th>Status</th>
                            <th>Reportline</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                            <tr>
                                <td>{{ $asset->business_unit }}</td>
                                <td>{{ $asset->cost_centre }}</td>
                                <td><a href="{{ route('asset-register.edit', $asset) }}">{{ $asset->asset_number }}</a></td>
                                <td>{{ $asset->description }}</td>
                                <td>{{ $asset->area }}</td>
                                <td>{{ $asset->location }}</td>
                                <td>{{ $asset->sub_location }}</td>
                                <td>{{ $asset->major_category }}</td>
                                <td>{{ $asset->sub_major_category }}</td>
                                <td>{{ $asset->minor_category }}</td>
                                <td>{{ $asset->date_placed_in_service?->format('d-M-y') }}</td>
                                <td>{{ $asset->life_in_months }}</td>
                                <td class="text-right">{{ $asset->asset_cost !== null ? number_format((float) $asset->asset_cost, 2) : '' }}</td>
                                <td>{{ $asset->status }}</td>
                                <td>{{ $asset->reportline }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="15" class="text-center text-muted py-5">No assets match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-body">{{ $assets->links() }}</div>
        </section>
    </div>
</div>
