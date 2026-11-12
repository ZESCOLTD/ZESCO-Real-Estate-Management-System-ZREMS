<div class="tenant-application">
    <style>
        .tenant-application { --ta-ink: #253745; --ta-muted: #71808a; --ta-line: #e3e8e7; --ta-accent: #f59d33; color: var(--ta-ink); }
        .tenant-application .ta-toolbar { align-items: center; display: flex; justify-content: space-between; margin-bottom: .85rem; }
        .tenant-application .ta-back { color: var(--ta-muted); font-size: .82rem; font-weight: 600; }
        .tenant-application .ta-back:hover { color: var(--ta-ink); text-decoration: none; }
        .tenant-application .ta-overview { background: #fff; border: 1px solid var(--ta-line); border-left: 4px solid var(--ta-accent); border-radius: 5px; margin-bottom: 1rem; padding: 1.15rem 1.25rem 0; }
        .tenant-application .ta-overview-top { align-items: flex-start; display: flex; gap: 1rem; justify-content: space-between; }
        .tenant-application .ta-kicker, .tenant-application .ta-fact-label { color: var(--ta-muted); font-size: .68rem; font-weight: 700; text-transform: uppercase; }
        .tenant-application .ta-overview h1 { color: var(--ta-ink); font-size: 1.45rem; font-weight: 700; margin: .25rem 0 .35rem; }
        .tenant-application .ta-meta { align-items: center; color: var(--ta-muted); display: flex; flex-wrap: wrap; font-size: .8rem; gap: .45rem; }
        .tenant-application .ta-reference { color: var(--ta-ink); font-weight: 700; }
        .tenant-application .ta-status { border-radius: 4px; flex: 0 0 auto; font-size: .75rem; font-weight: 700; padding: .42rem .65rem; }
        .tenant-application .ta-status--warning { background: #fff4df; color: #88530e; }
        .tenant-application .ta-status--success { background: #eaf5ee; color: #246951; }
        .tenant-application .ta-status--danger { background: #fff0ed; color: #a4493e; }
        .tenant-application .ta-status--secondary { background: #edf1f2; color: #55636c; }
        .tenant-application .ta-facts { border-top: 1px solid var(--ta-line); display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); margin-top: 1rem; }
        .tenant-application .ta-fact { min-width: 0; padding: .75rem .85rem .85rem 0; }
        .tenant-application .ta-fact + .ta-fact { border-left: 1px solid var(--ta-line); padding-left: .85rem; }
        .tenant-application .ta-fact-value { color: var(--ta-ink); display: block; font-size: .84rem; font-weight: 600; margin-top: .2rem; overflow-wrap: anywhere; }
        .tenant-application .ta-content { align-items: start; display: grid; gap: 1rem; grid-template-columns: minmax(0, 1.55fr) minmax(275px, .85fr); }
        .tenant-application .ta-main, .tenant-application .ta-sidebar { display: grid; gap: 1rem; min-width: 0; }
        .tenant-application .ta-panel { background: #fff; border: 1px solid var(--ta-line); border-radius: 5px; min-width: 0; }
        .tenant-application .ta-panel-head { align-items: center; border-bottom: 1px solid var(--ta-line); display: flex; gap: .6rem; padding: .8rem .95rem; }
        .tenant-application .ta-panel-head i { color: #437f98; width: 1rem; }
        .tenant-application .ta-panel-head h2 { color: var(--ta-ink); font-size: .9rem; font-weight: 700; margin: 0; }
        .tenant-application .ta-fields { display: grid; gap: .9rem 1.25rem; grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 1rem; }
        .tenant-application .ta-field { min-width: 0; }
        .tenant-application .ta-field-label { color: var(--ta-muted); display: block; font-size: .72rem; margin-bottom: .2rem; }
        .tenant-application .ta-field-value { color: var(--ta-ink); display: block; font-size: .84rem; font-weight: 600; overflow-wrap: anywhere; }
        .tenant-application .ta-round { border-bottom: 1px solid var(--ta-line); padding: .9rem .95rem .6rem; }
        .tenant-application .ta-round:last-child { border-bottom: 0; }
        .tenant-application .ta-round-head { align-items: center; display: flex; justify-content: space-between; }
        .tenant-application .ta-round-head strong { font-size: .82rem; }
        .tenant-application .ta-round-status { background: #edf1f2; border-radius: 3px; color: #55636c; font-size: .68rem; font-weight: 700; padding: .2rem .4rem; }
        .tenant-application .ta-round-step { color: var(--ta-muted); display: block; font-size: .74rem; margin-top: .25rem; }
        .tenant-application .ta-event { border-left: 2px solid #d5e4e9; margin: .8rem 0 .8rem .2rem; padding: 0 0 .1rem .75rem; }
        .tenant-application .ta-event-top { align-items: baseline; display: flex; flex-wrap: wrap; gap: .25rem .5rem; justify-content: space-between; }
        .tenant-application .ta-event-top strong { font-size: .78rem; }
        .tenant-application .ta-event-top small, .tenant-application .ta-event-meta, .tenant-application .ta-event-comment { color: var(--ta-muted); font-size: .71rem; }
        .tenant-application .ta-event-comment { background: #f6f8f8; border-radius: 3px; margin-top: .35rem; padding: .4rem .5rem; }
        .tenant-application .ta-history { align-items: center; background: #fff; border: 1px solid var(--ta-line); border-radius: 5px; color: var(--ta-ink); display: flex; gap: .7rem; padding: .75rem .9rem; }
        .tenant-application .ta-history:hover { border-color: #b7c8ce; color: var(--ta-ink); text-decoration: none; }
        .tenant-application .ta-history-icon { align-items: center; background: #edf4f6; border-radius: 4px; color: #437f98; display: inline-flex; height: 34px; justify-content: center; width: 34px; }
        .tenant-application .ta-history strong, .tenant-application .ta-history small { display: block; }
        .tenant-application .ta-history strong { font-size: .8rem; }
        .tenant-application .ta-history small { color: var(--ta-muted); font-size: .7rem; margin-top: .1rem; }
        .tenant-application .ta-history > .fa-chevron-right { color: var(--ta-muted); font-size: .7rem; margin-left: auto; }
        .tenant-application .ta-documents { font-size: .78rem; }
        .tenant-application .ta-documents th { background: #f7f9f9; border-top: 0; color: var(--ta-muted); font-size: .68rem; text-transform: uppercase; }
        .tenant-application .ta-documents td, .tenant-application .ta-documents th { vertical-align: middle; }
        @media (max-width: 900px) { .tenant-application .ta-content { grid-template-columns: minmax(0, 1fr); } }
        @media (max-width: 575.98px) {
            .tenant-application .ta-overview { padding: .95rem .9rem 0; }
            .tenant-application .ta-overview-top { align-items: flex-start; flex-direction: column; gap: .65rem; }
            .tenant-application .ta-overview h1 { font-size: 1.2rem; }
            .tenant-application .ta-facts { grid-template-columns: minmax(0, 1fr); }
            .tenant-application .ta-fact, .tenant-application .ta-fact + .ta-fact { border-left: 0; border-top: 1px solid var(--ta-line); padding: .6rem 0; }
            .tenant-application .ta-fact:first-child { border-top: 0; }
            .tenant-application .ta-fields { grid-template-columns: minmax(0, 1fr); }
        }
    </style>

    <x-slot name="title">Tenant Application</x-slot>
    <x-slot name="pageHeader">Tenant Application</x-slot>

    @php
        $application = $this->application;
        $statusTone = match ($application->status) {
            'Pending Verification' => 'warning',
            'Verified' => 'success',
            'Returned for Correction' => 'danger',
            default => 'secondary',
        };
        $canResume = in_array($application->status, ['Draft', 'Returned for Correction'], true)
            && ((int) $application->created_by === (int) auth()->id() || auth()->user()->can('tenant_register.update'));
        $detailGroups = [
            [
                'title' => 'Applicant details',
                'icon' => 'fa-id-card',
                'fields' => [
                    ['label' => 'Applicant type', 'value' => ucwords(str_replace('_', ' ', $application->tenant_type))],
                    ['label' => 'Man No.', 'value' => $application->man_no],
                    ['label' => 'Identity reference', 'value' => $application->identity_reference],
                    ['label' => 'Employer', 'value' => $application->employer],
                    ['label' => 'Organization registration number', 'value' => $application->organization_registration_no],
                    ['label' => 'Contact person', 'value' => $application->contact_person],
                ],
            ],
            [
                'title' => 'Contact and address',
                'icon' => 'fa-address-book',
                'fields' => [
                    ['label' => 'Phone', 'value' => $application->phone],
                    ['label' => 'Email', 'value' => $application->email],
                    ['label' => 'Physical address', 'value' => $application->physical_address],
                    ['label' => 'Postal address', 'value' => $application->postal_address],
                ],
            ],
            [
                'title' => 'Housing request',
                'icon' => 'fa-house-user',
                'fields' => [
                    ['label' => 'Station / location', 'value' => $application->station_location],
                    ['label' => 'Housing category', 'value' => $application->category],
                    ['label' => 'Location code', 'value' => $application->location_code],
                    ['label' => 'Requested unit', 'value' => $application->requested_unit_reference],
                    ['label' => 'Occupants', 'value' => $application->occupants_count],
                    ['label' => 'Requested move-in date', 'value' => $application->requested_move_in_date],
                ],
            ],
        ];
    @endphp

    <div class="container-fluid">
        <div class="ta-toolbar">
            <a class="ta-back" href="{{ route('tenant-register.onboarding') }}"><i class="fas fa-arrow-left mr-1"></i>My Applications</a>
            @if($canResume)
                <a class="btn btn-primary btn-sm" href="{{ route('tenant-register.onboarding.edit', $application) }}"><i class="fas fa-edit mr-1"></i>{{ $application->status === 'Draft' ? 'Continue Draft' : 'Revise Application' }}</a>
            @endif
        </div>

        <section class="ta-overview">
            <div class="ta-overview-top">
                <div>
                    <span class="ta-kicker">Tenant onboarding application</span>
                    <h1>{{ $application->applicant_name ?: 'Unnamed applicant' }}</h1>
                    <div class="ta-meta"><span class="ta-reference">{{ $application->reference_no }}</span><span aria-hidden="true">/</span><span>{{ ucwords(str_replace('_', ' ', $application->tenant_type)) }}</span></div>
                </div>
                <span class="ta-status ta-status--{{ $statusTone }}">{{ $application->status }}</span>
            </div>
            <div class="ta-facts">
                <div class="ta-fact"><span class="ta-fact-label">Submitted</span><span class="ta-fact-value">{{ $application->submitted_at?->format('d M Y') ?: 'Not submitted' }}</span></div>
                <div class="ta-fact"><span class="ta-fact-label">Last updated</span><span class="ta-fact-value">{{ $application->updated_at?->format('d M Y, H:i') ?: '—' }}</span></div>
                <div class="ta-fact"><span class="ta-fact-label">Requested station</span><span class="ta-fact-value">{{ $application->station_location ?: '—' }}</span></div>
            </div>
        </section>

        <div class="ta-content">
            <main class="ta-main">
                @foreach($detailGroups as $group)
                    <section class="ta-panel">
                        <header class="ta-panel-head"><i class="fas {{ $group['icon'] }}"></i><h2>{{ $group['title'] }}</h2></header>
                        <div class="ta-fields">
                            @foreach($group['fields'] as $field)
                                <div class="ta-field">
                                    <span class="ta-field-label">{{ $field['label'] }}</span>
                                    <span class="ta-field-value">{{ $field['value'] !== null && $field['value'] !== '' ? $field['value'] : '—' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                @can('tenant_register.documents.view')
                    <section class="ta-panel">
                        <header class="ta-panel-head"><i class="fas fa-paperclip"></i><h2>Supporting documents</h2><span class="text-muted small ml-auto">{{ $application->documents->count() }}</span></header>
                        <div class="table-responsive">
                            <table class="table table-hover ta-documents mb-0">
                                <thead><tr><th>Document</th><th>Type</th><th>Uploaded</th><th>Review status</th><th></th></tr></thead>
                                <tbody>
                                    @forelse($application->documents as $document)
                                        <tr>
                                            <td class="font-weight-bold">{{ $document->original_name }}</td>
                                            <td>{{ $document->document_type }}</td>
                                            <td>{{ $document->uploaded_at?->format('d M Y') ?: '—' }}</td>
                                            <td>{{ $document->review_status }}</td>
                                            <td><a href="{{ route('tenant-register.documents.download', $document) }}" class="btn btn-sm btn-outline-primary" aria-label="Download {{ $document->original_name }}" title="Download document"><i class="fas fa-download"></i></a></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4">No supporting documents uploaded.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endcan
            </main>

            <aside class="ta-sidebar">
                <section class="ta-panel">
                    <header class="ta-panel-head"><i class="fas fa-clipboard-check"></i><h2>Verification activity</h2></header>
                    @forelse($application->workflowInstances as $workflow)
                        <div class="ta-round">
                            <div class="ta-round-head"><strong>Round {{ $workflow->approval_round }}</strong><span class="ta-round-status">{{ $workflow->status }}</span></div>
                            <span class="ta-round-step">{{ $workflow->currentStep?->step_name ?: 'Review complete' }}</span>
                            @foreach($workflow->actions->sortBy('acted_at') as $action)
                                <div class="ta-event">
                                    <div class="ta-event-top"><strong>{{ ucfirst($action->action) }}</strong><small>{{ $action->acted_at?->format('d M Y, H:i') ?: '' }}</small></div>
                                    <div class="ta-event-meta">{{ $action->actor?->name ?: 'System' }}</div>
                                    @if($action->comment)<div class="ta-event-comment">{{ $action->comment }}</div>@endif
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <div class="p-3 text-muted small">No verification round yet. Activity will appear here after submission.</div>
                    @endforelse
                </section>

                @can('tenant_register.history.view')
                    <a class="ta-history" href="{{ route('tenant-register.history', ['application_id' => $application->id]) }}">
                        <span class="ta-history-icon"><i class="fas fa-history"></i></span>
                        <span><strong>Full application history</strong><small>View all recorded changes</small></span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                @endcan
            </aside>
        </div>
    </div>
</div>