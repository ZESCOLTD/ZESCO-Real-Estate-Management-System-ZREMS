<div>
    <x-slot name="title">Lease Statuses</x-slot>
    <x-slot name="pageHeader">Lease Statuses</x-slot>

    <div class="container-fluid">
        <div class="mb-4">
            <h5 class="mb-0 font-weight-bold">Lease Status Configuration</h5>
            <small class="text-muted">Manage the statuses available to lease agreements.</small>
        </div>

        @if (session()->has('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="row">
            {{-- ADD / EDIT FORM --}}
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <strong>{{ $editingId ? 'Edit Status' : 'Add Status' }}</strong>
                    </div>

                    <div class="card-body">
                        <form wire:submit="save">
                            <div class="form-group">
                                <label for="status-name">Status name</label>
                                <input
                                    id="status-name"
                                    type="text"
                                    class="form-control"
                                    wire:model="name"
                                    placeholder="Enter a status name">

                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-check mb-3">
                                <input
                                    id="status-active"
                                    type="checkbox"
                                    class="form-check-input"
                                    wire:model="isActive">
                                <label for="status-active" class="form-check-label">
                                    Available for new leases
                                </label>
                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary"
                                wire:loading.attr="disabled"
                                wire:target="save">
                                {{ $editingId ? 'Update Status' : 'Add Status' }}
                            </button>

                            @if ($editingId)
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    wire:click="resetForm">
                                    Cancel
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            {{-- STATUS LIST --}}
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <strong>Statuses</strong>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Availability</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($statuses as $status)
                                    <tr wire:key="lease-status-{{ $status->id }}">
                                        <td>{{ $status->name }}</td>
                                        <td><code>{{ $status->code }}</code></td>
                                        <td>
                                            @if ($status->is_active)
                                                <span class="badge bg-success">Available</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-warning"
                                                wire:click="edit({{ $status->id }})">
                                                Edit
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No lease statuses found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>