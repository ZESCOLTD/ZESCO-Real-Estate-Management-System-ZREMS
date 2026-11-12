<div>
    <x-slot name="title">Tenant History &amp; Status</x-slot>
    <x-slot name="pageHeader">Tenant History &amp; Status</x-slot>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="font-weight-bold mb-0">Tenant History &amp; Status</h5>
                <small class="text-muted">Chronological, read-only record of tenant-related changes</small>
            </div>
            <a href="{{ route('tenant-register.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i>Tenant Register</a>
        </div>

        <section class="card elevation-2 mb-3">
            <div class="card-body"><div class="row">
                <div class="form-group col-md-8 mb-2">
                    <label class="sr-only" for="tenant-history-search">Search history</label>
                    <input id="tenant-history-search" type="search" class="form-control" placeholder="Search event or summary" wire:model.live.debounce.300ms="search">
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label class="sr-only" for="tenant-history-type">Record type</label>
                    <select id="tenant-history-type" class="form-control" wire:model.live="recordType">
                        <option value="">All record types</option>
                        <option value="application">Onboarding applications</option>
                        <option value="register">Imported register records</option>
                    </select>
                </div>
            </div></div>
        </section>

        <section class="card elevation-2">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background-color: {{ config('theme.color_primary') }}; color: {{ config('theme.color_primary_text_light') }}">
                        <tr><th>Date</th><th>Tenant / record</th><th>Record type</th><th>Event</th><th>Changed by</th><th>Details</th></tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            @php($record = $event->tenantable)
                            <tr>
                                <td class="text-nowrap">{{ $event->occurred_at?->format('d M Y H:i') }}</td>
                                <td>
                                    @if($record instanceof \App\Models\TenantOnboardingApplication)
                                        {{ $record->applicant_name ?: 'Applicant' }}<div class="small text-muted">{{ $record->reference_no }}</div>
                                    @elseif($record)
                                        {{ $record->current_tenant ?: 'Vacant unit' }}<div class="small text-muted">{{ $record->station_location }} · {{ $record->physical_address }}</div>
                                    @else
                                        Record unavailable
                                    @endif
                                </td>
                                <td>{{ $record instanceof \App\Models\TenantOnboardingApplication ? 'Onboarding' : 'Tenant register' }}</td>
                                <td>{{ str($event->event_type)->replace('_', ' ')->title() }}</td>
                                <td>{{ $event->actor?->name ?: 'System' }}</td>
                                <td>{{ $event->summary }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">No tenant history events match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-body">{{ $events->links() }}</div>
        </section>
    </div>
</div>