<div>

    <x-slot name="title">Land & Properties</x-slot>
    <x-slot name="pageHeader">Land & Properties Register</x-slot>

    <div class="container-fluid">

        {{-- HEADER ACTIONS --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

            <div>
                <h5 class="mb-0 font-weight-bold">
                    Grouped Asset Register
                </h5>
                <small class="text-muted">
                    Browse assets grouped by Link ID
                </small>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="d-flex flex-wrap gap-2">

                {{-- EXCEL IMPORT --}}
                <a href="{{route('imports.fms_assets.index')}}"
                   class="btn text-white bg-success mr-2">
                    <i class="fas fa-file-excel mr-1"></i>
                    Excel Import
                </a>

                {{-- AUTOMATED SYNC --}}
{{--                <a href="#"--}}
{{--                   class="btn text-white bg-primary">--}}
{{--                    <i class="fas fa-sync-alt mr-1"></i>--}}
{{--                    Automated Sync--}}
{{--                </a>--}}
                <button class="btn text-white bg-primary mr-2"
                        wire:click="startFullAssetSync"
                        wire:loading.attr="disabled">

        <span wire:loading.remove wire:target="startFullAssetSync">
            <i class="fas fa-sync-alt mr-1"></i>
            Sync Fixed Assets
        </span>

                    <span wire:loading wire:target="startFullAssetSync">
            <span class="spinner-border spinner-border-sm"></span>
            Starting...
        </span>

                </button>
            </div>

        </div>

        {{-- HEADER STATS --}}
        <div class="row mb-4">

            {{-- TOTAL ASSETS --}}
            <div class="col-md-3 mb-3">
                <div class="card elevation-2 border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted text-uppercase">Total Assets</small>
                            <h3 class="mb-0 font-weight-bold">
                                {{ $this->stats['total'] }}
                            </h3>
                        </div>

                        <div style="font-size: 2.2rem; color: {{ config('theme.color_primary') }};">
                            <i class="fas fa-boxes text-warning" ></i>
                        </div>

                    </div>
                </div>
            </div>

            {{-- TOTAL GROUPS --}}
            <div class="col-md-3 mb-3">
                <div class="card elevation-2 border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted text-uppercase">Total Groups</small>
                            <h3 class="mb-0 font-weight-bold">
                                {{ $this->stats['grouped'] }}
                            </h3>
                        </div>

                        <div style="font-size: 2.2rem; color: {{ config('theme.color_primary') }};">
                            <i class="fas fa-layer-group text-warning" ></i>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ACTIVE FILTER STATUS --}}
            {{-- ACTIVE CATEGORY CARDS --}}
            @foreach($this->categories as $cat)

                @php
                    $icon = match(strtoupper($cat->major_category)) {
                        'LAND' => 'fas fa-map-marked-alt',
                        'BUILDINGS' => 'fas fa-building',
                        default => 'fas fa-tags',
                    };
                @endphp

                <div class="col-md-3 mb-3">
                    <div class="card elevation-2 border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">

                            <div>
                                <small class="text-muted">{{ $cat->major_category }}</small>
                                <h5 class="mb-0 font-weight-bold">
                                    {{ $cat->total }}
                                </h5>

{{--                                <small class="text-muted">--}}
{{--                                    {{ $cat->major_category }}--}}
{{--                                </small>--}}
                            </div>

                            <div style="font-size: 2.2rem; color: {{ config('theme.color_primary') }};">
                                <i class="{{ $icon }} text-warning"></i>
                            </div>

                        </div>
                    </div>
                </div>

            @endforeach


        </div>

        @if($syncRunning)

            <div wire:poll.2s="refreshAssetSyncProgress"
                 class="card border-0 elevation-1 mb-3">

                <div class="card-body">

                    <div class="d-flex justify-content-between mb-2">

                        <strong>
                            <i class="fas fa-sync-alt fa-spin text-info"></i>
                            Synchronizing Assets From Fixed Assets Register
                        </strong>

                        <span>
                    {{ $syncedAssets }} / {{ $totalSyncAssets }}
                </span>

                    </div>

                    <div class="progress" style="height: 20px;">

                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                             role="progressbar"
                             style="width: {{ $syncPercentage }}%">

                            {{ $syncPercentage }}%

                        </div>

                    </div>

                </div>

            </div>

        @endif
        {{-- FILTERS --}}
        <div class="card elevation-2 mb-4">

            <div class="card-body">

                <div class="row">

                    <div class="col-md-6 mb-2">
                        <input type="text"
                               class="form-control"
                               placeholder="Search assets (min 3 characters)..."
                               wire:model.live.debounce.400ms="search">
                    </div>

                    <div class="col-md-4 mb-2">
                        <select class="form-control"
                                wire:model="filterCategory">
                            <option value="">-- All Categories --</option>
                            @foreach($this->categories as $cat)
                                <option value="{{ $cat->major_category }}">{{ $cat->major_category }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                {{-- GLOBAL LOADER --}}
                <div wire:loading.delay wire:target="search, filterCategory"
                     class="mt-2 text-muted">
                    <i class="fas fa-spinner fa-spin mr-1"></i>
                    Searching assets...
                </div>

            </div>

        </div>

        {{-- GROUP LIST --}}
        <div>

            @forelse($groups as $group)

                @php
                    $open = in_array($group->link_id, $expandedGroups);
                @endphp

                <div class="card mb-2 elevation-1">

                    {{-- HEADER --}}
                    <div class="card-header p-0 border-0 bg-white">

                        <div
                            wire:click="toggleGroup('{{ $group->link_id }}')"
                            class="d-flex justify-content-between align-items-center px-3 py-3
                                   group-header"
                            style="cursor:pointer; transition: all 0.2s ease;">

                            {{-- LEFT --}}
                            <div class="d-flex align-items-center">

                                <i class="fas fa-chevron-{{ $open ? 'down' : 'right' }} mr-3 text-muted"></i>

                                <div>
                                    <div class="font-weight-bold">
                                        {{ $group->name }}
                                    </div>

                                    <small class="text-muted">
                                        Click to {{ $open ? 'collapse' : 'expand' }} assets in this group
                                    </small>
                                </div>

                            </div>

                            {{-- RIGHT COUNT (ALIGNED FAR RIGHT) --}}
                            <div class="text-right">
                                <span class="badge bg-primary px-3 py-2" >
                                    {{ $group->total }} assets
                                </span>
                            </div>

                        </div>

                    </div>

                    {{-- BODY --}}
                    @if($open)
                        <div class="card-body bg-light border-top">

                            {{-- LOADING (FIXED: shows on OPEN + CLOSE + SWITCH) --}}
                            <div wire:loading.delay wire:target="toggleGroup"
                                 class="text-center text-muted py-3">

                                <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                                <div>Loading assets...</div>

                            </div>

                            <div wire:loading.remove wire:target="toggleGroup">

                                <div class="table-responsive">

                                    <table class="table table-sm table-hover mb-0">

                                        <thead class="bg-primary text-white">
                                        <tr>
                                            <th>Asset #</th>
                                            <th>LAMS #</th>
                                            <th>Description</th>
                                            <th>Tag</th>
                                            <th>Category</th>
                                            <th>Town</th>
                                            <th></th>
                                        </tr>
                                        </thead>

                                        <tbody>

                                        @foreach($this->getGroupItems($group->link_id) as $asset)
                                            <tr>
                                                <td>{{ $asset->asset_number }}</td>
                                                <td>{{ $asset->lams_asset_number }}</td>
                                                <td>{{ $asset->description }}</td>
                                                <td>{{ $asset->tag_number }}</td>
                                                <td>{{ $asset->major_category }}</td>
                                                <td>{{ $asset->town }}</td>
                                                {{-- ACTIONS --}}
                                                <td class="text-center">
                                                    <div class="input-group-prepend">
                                                        <button type="button"
                                                                class="btn btn-outline-warning dropdown-toggle"
                                                                data-toggle="dropdown">
                                                            Action <span class="caret"></span>
                                                        </button>

                                                        <div class="dropdown-menu dropdown-menu-right">

                                                            <a class="dropdown-item"
                                                               href="{{ route('fms_assets_register.show', $asset->id) }}">
                                                                View
                                                            </a>
                                                            <a class="dropdown-item"
                                                               href="{{ route('fms_assets_register.edit', $asset->id) }}">
                                                                Edit
                                                            </a>

                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach

                                        </tbody>

                                    </table>

                                </div>

                            </div>

                        </div>
                    @endif

                </div>

            @empty

                <div class="card">
                    <div class="card-body text-center text-muted">
                        No asset groups found
                    </div>
                </div>

            @endforelse

        </div>

        {{-- PAGINATION --}}
        <div class="mt-3">
            {{ $groups->links() }}
        </div>

    </div>

</div>
