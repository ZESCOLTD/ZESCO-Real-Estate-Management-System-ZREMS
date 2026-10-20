<div>
    <x-slot name="title">Lease Agreement Register</x-slot>
    <x-slot name="pageHeader">Lease Agreement Register</x-slot>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-0 font-weight-bold">Lease Agreements</h5>
                <small class="text-muted">Manage lease agreements</small>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn text-white bg-success mr-2" disabled>
                    <i class="fas fa-file-excel mr-1"></i>
                    Excel Import
                </button>

                <button type="button" class="btn text-white bg-primary mr-2" disabled>
                    <i class="fas fa-sync-alt mr-1"></i>
                    Sync Lease Agreements
                </button>
            
        </div>

        </div>
            {{-- LEASE STATS --}}
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card elevation-2 border-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted text-uppercase">Total Leases</small>
                    <h3 class="mb-0 font-weight-bold">0</h3>
                </div>

                <div style="font-size: 2.2rem; color: {{ config('theme.color_primary') }};">
                    <i class="fas fa-file-contract text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card elevation-2 border-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted text-uppercase">Ongoing Leases</small>
                    <h3 class="mb-0 font-weight-bold">0</h3>
                </div>

                <div style="font-size: 2.2rem; color: {{ config('theme.color_primary') }};">
                    <i class="fas fa-calendar-check text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    
</div>
{{-- LEASE FILTERS --}}
<div class="card elevation-2 mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8 mb-2">
                <label for="lease-search">Search lease</label>
                <input
                    id="lease-search"
                    type="text"
                    class="form-control"
                    placeholder="Search leases..."
                    disabled>
            </div>

            <div class="col-md-4 mb-2">
                <label for="lease-status">Status</label>
                <select id="lease-status" class="form-control" disabled>
                    <option selected>All statuses</option>
                    <option>Ongoing</option>
                    <option>Expired</option>
                </select>
            </div>
        </div>
    </div>
</div>
{{-- LEASE REGISTER TABLE --}}
<div class="card elevation-2 border-0 mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0 font-weight-bold">Lease Agreements</h5>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>Lease No.</th>
                        <th>Property</th>
                        <th>Lessee</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No lease agreements available yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
    </div>
</div>