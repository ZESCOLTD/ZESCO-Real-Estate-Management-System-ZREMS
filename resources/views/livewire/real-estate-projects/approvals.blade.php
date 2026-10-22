<div>
    <x-slot name="title">Project Approvals</x-slot>
    <x-slot name="pageHeader">Project Approvals</x-slot>

    <div class="container-fluid px-0">
        <div class="mb-3"><h3 class="mb-1">Project Approvals</h3><p class="text-muted mb-0">Proposal reviews assigned to you</p></div>
        @if(session()->has('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <section class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background-color: {{ config('theme.color_primary') }}; color: {{ config('theme.color_primary_text_light') }}"><tr><th>Project reference</th><th>Title / submitted by</th><th>Approval stage</th><th>Review round</th><th>Current approver(s)</th><th>Decision</th></tr></thead>
                    <tbody>
                        @forelse($workflows as $workflow)
                            @php($project = $workflow->workflowable)
                            <tr>
                                <td>{{ $project->reference_no }}</td>
                                <td>{{ $project->project_title ?: 'Untitled proposal' }}<div class="small text-muted">{{ $workflow->initiator?->name ?: '—' }}</div></td>
                                <td>{{ $workflow->currentStep?->step_name ?: '—' }}</td>
                                <td>{{ $workflow->approval_round }}</td>
                                <td>{{ collect($workflow->current_approver_ids ?? [])->map(fn ($id) => $approverNames[$id] ?? null)->filter()->join(', ') ?: 'You' }}</td>
                                <td>
                                    @can('real_estate_projects.approve')
                                        <div class="mb-2">
                                            <input class="form-control form-control-sm" placeholder="Comment (required for return or rejection)" wire:model="comments.{{ $workflow->id }}">
                                            @error("comments.{$workflow->id}") <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="d-flex flex-wrap">
                                            <button type="button" class="btn btn-sm btn-success mr-1 mb-1" wire:click="decide({{ $workflow->id }}, 'approve')" wire:loading.attr="disabled">Approve</button>
                                            <button type="button" class="btn btn-sm btn-warning mr-1 mb-1" wire:click="decide({{ $workflow->id }}, 'return')" wire:loading.attr="disabled">Return for Revision</button>
                                            <button type="button" class="btn btn-sm btn-danger mr-1 mb-1" wire:click="decide({{ $workflow->id }}, 'reject')" wire:loading.attr="disabled">Reject</button>
                                            <a class="btn btn-sm btn-outline-secondary mb-1" href="{{ route('real-estate-projects.show', $project) }}">Open</a>
                                        </div>
                                    @else
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('real-estate-projects.show', $project) }}">Open</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">No proposals are currently assigned to you.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-body">{{ $projects->links() }}</div>
        </section>
    </div>
</div>
