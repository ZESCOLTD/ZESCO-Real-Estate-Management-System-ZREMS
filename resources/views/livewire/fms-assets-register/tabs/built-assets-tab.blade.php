<div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Built Assets</span>

            <button class="btn btn-sm btn-primary"
                    data-toggle="modal"
                    data-target="#addAssetModal">
                + Add Asset
            </button>
        </div>

        <div class="card-body table-responsive">

            <table class="table table-sm table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Condition</th>
                    <th width="120">Actions</th>
                </tr>
                </thead>

                <tbody>
                @forelse($assets as $asset)
                    <tr>
                        <td>{{ $asset->asset_code }}</td>
                        <td>{{ $asset->name }}</td>
                        <td>{{ $asset->asset_type }}</td>
                        <td>{{ $asset->condition_status }}</td>

                        <td>
                            <button class="btn btn-sm btn-outline-primary"
                                    data-toggle="modal"
                                    data-target="#viewAssetModal{{ $asset->id }}">
                                View
                            </button>
                        </td>
                    </tr>

                    {{-- VIEW MODAL --}}
                    <div class="modal fade" id="viewAssetModal{{ $asset->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title">Asset Details</h5>
                                    <button class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">

                                    <div class="row">

                                        <div class="col-md-6">
                                            <p><strong>Code:</strong> {{ $asset->asset_code }}</p>
                                            <p><strong>Name:</strong> {{ $asset->name }}</p>
                                            <p><strong>Type:</strong> {{ $asset->asset_type }}</p>
                                            <p><strong>Classification:</strong> {{ $asset->classification }}</p>
                                        </div>

                                        <div class="col-md-6">
                                            <p><strong>Condition:</strong> {{ $asset->condition_status }}</p>
                                            <p><strong>Commissioned:</strong> {{ $asset->commissioning_date }}</p>
                                            <p><strong>Replacement Date:</strong> {{ $asset->replacement_due_date }}</p>
                                            <p><strong>Cost:</strong> {{ number_format($asset->replacement_cost,2) }}</p>
                                        </div>

                                        <div class="col-12">
                                            <p><strong>Description:</strong></p>
                                            <p>{{ $asset->description }}</p>
                                        </div>

                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>

                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No assets found</td>
                    </tr>
                @endforelse
                </tbody>

            </table>

        </div>
    </div>

    {{-- ADD ASSET MODAL --}}
    <div wire:ignore.self class="modal fade" id="addAssetModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Add Asset</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label>Asset Code</label>
                            <input type="text" wire:model="asset_code" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Name</label>
                            <input type="text" wire:model="name" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Type</label>
                            <input type="text" wire:model="asset_type" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Classification</label>
                            <input type="text" wire:model="classification" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Condition</label>
                            <input type="text" wire:model="condition_status" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Commissioning Date</label>
                            <input type="date" wire:model="commissioning_date" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Replacement Date</label>
                            <input type="date" wire:model="replacement_due_date" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Replacement Cost</label>
                            <input type="number" wire:model="replacement_cost" class="form-control">
                        </div>

                        <div class="col-12">
                            <label>Description</label>
                            <textarea wire:model="description" class="form-control"></textarea>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button wire:click="saveAsset" class="btn btn-primary">Save</button>
                </div>

            </div>
        </div>
    </div>

</div>
