<div>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <span>Disputes</span>

            <button class="btn btn-sm btn-danger"
                    data-toggle="modal"
                    data-target="#addDisputeModal">
                + Report Dispute
            </button>
        </div>

        <div class="card-body">

            @forelse($disputes as $d)
                <div class="card mb-2 border">
                    <div class="card-body">

                        <div class="d-flex justify-content-between">
                            <strong>{{ $d->dispute_type ?? 'General' }}</strong>

                            <span class="badge
                                {{ $d->status === 'resolved' ? 'bg-success' : 'bg-danger' }}">
                                {{ ucfirst($d->status) }}
                            </span>
                        </div>

                        <p class="mt-2 mb-1">{{ $d->description }}</p>

                        <small class="text-muted">
                            Reported: {{ $d->reported_at }}
                        </small>

                    </div>
                </div>

            @empty
                <p class="text-muted text-center">No disputes recorded</p>
            @endforelse

        </div>
    </div>

    {{-- MODAL --}}
    <div wire:ignore.self class="modal fade" id="addDisputeModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5>Report Dispute</h5>
                    <button class="btn-close" data-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label>Type</label>
                        <input type="text" wire:model="dispute_type" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea wire:model="description" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Status</label>
                        <select wire:model="status" class="form-control">
                            <option value="open">Open</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Reported Date</label>
                        <input type="date" wire:model="reported_at" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button wire:click="saveDispute" class="btn btn-danger">Save</button>
                </div>

            </div>
        </div>
    </div>

</div>
