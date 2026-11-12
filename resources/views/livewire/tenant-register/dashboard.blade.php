<div>
    <x-slot name="title">Tenant Dashboard</x-slot>
    <x-slot name="pageHeader">Tenant Dashboard</x-slot>

    <style>
        .tenant-dashboard {
            --td-ink: #253745;
            --td-muted: #71808a;
            --td-line: #e3e8e7;
            --td-amber: #e69732;
            --td-green: #33806b;
            --td-blue: #437f98;
            --td-coral: #bf6458;
            --td-violet: #73619a;
            color: var(--td-ink);
        }
        .tenant-dashboard .td-heading {
            align-items: flex-end;
            border-bottom: 1px solid var(--td-line);
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding: .2rem 0 1rem;
        }
        .tenant-dashboard .td-kicker {
            color: var(--td-muted);
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .tenant-dashboard h1 { color: var(--td-ink); font-size: 1.55rem; font-weight: 700; margin: .25rem 0; }
        .tenant-dashboard .td-heading p { color: var(--td-muted); font-size: .83rem; margin: 0; }
        .tenant-dashboard .td-date { color: var(--td-muted); font-size: .8rem; white-space: nowrap; }
        .tenant-dashboard .td-actions { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1rem; }
        .tenant-dashboard .td-actions .btn { border-radius: 5px; font-size: .8rem; font-weight: 600; }
        .tenant-dashboard .td-metrics {
            display: grid;
            gap: .75rem;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            margin-bottom: 1rem;
        }
        .tenant-dashboard .td-metric {
            background: var(--metric-tint, #fff);
            border: 1px solid var(--metric-border, var(--td-line));
            border-left: 4px solid var(--metric-color, var(--td-blue));
            border-radius: 5px;
            color: inherit;
            display: block;
            min-height: 120px;
            padding: .9rem 1rem;
            transition: box-shadow .16s ease, transform .16s ease;
        }
        .tenant-dashboard .td-metric:hover { box-shadow: 0 6px 16px rgba(37,55,69,.08); color: inherit; text-decoration: none; transform: translateY(-2px); }
        .tenant-dashboard .td-metric-top { align-items: center; display: flex; justify-content: space-between; }
        .tenant-dashboard .td-metric-label { color: var(--td-muted); font-size: .76rem; font-weight: 700; }
        .tenant-dashboard .td-icon {
            align-items: center;
            background: var(--metric-tint, #edf2f3);
            border-radius: 5px;
            color: var(--metric-color, var(--td-blue));
            display: inline-flex;
            height: 32px;
            justify-content: center;
            width: 34px;
        }
        .tenant-dashboard .td-value { color: var(--metric-color, var(--td-ink)); font-size: 1.65rem; font-weight: 700; line-height: 1.1; margin-top: .55rem; }
        .tenant-dashboard .td-note { color: var(--td-muted); font-size: .73rem; margin-top: .25rem; }
        .tenant-dashboard .td-amber { --metric-color: #985b13; --metric-tint: #fff4df; --metric-border: #f0d5a5; }
        .tenant-dashboard .td-green { --metric-color: #246951; --metric-tint: #eaf5ee; --metric-border: #c9e2d2; }
        .tenant-dashboard .td-blue { --metric-color: #28647c; --metric-tint: #eaf4f8; --metric-border: #c7dfe9; }
        .tenant-dashboard .td-coral { --metric-color: #a4493e; --metric-tint: #fff0ed; --metric-border: #f0d0ca; }
        .tenant-dashboard .td-violet { --metric-color: #5b4b83; --metric-tint: #f1eef8; --metric-border: #dad3ec; }
        .tenant-dashboard .td-columns { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1.15fr) minmax(300px, .85fr); }
        .tenant-dashboard .td-panel { background: #fff; border: 1px solid var(--td-line); border-radius: 5px; min-width: 0; }
        .tenant-dashboard .td-panel--blue .td-panel-head { background: #edf5f8; border-top: 3px solid var(--td-blue); }
        .tenant-dashboard .td-panel--amber .td-panel-head { background: #fff6e8; border-top: 3px solid var(--td-amber); }
        .tenant-dashboard .td-panel--green .td-panel-head { background: #edf5f0; border-top: 3px solid var(--td-green); }
        .tenant-dashboard .td-panel-head {
            align-items: center;
            border-bottom: 1px solid var(--td-line);
            display: flex;
            justify-content: space-between;
            padding: .8rem .95rem;
        }
        .tenant-dashboard .td-panel-head h2 { color: var(--td-ink); font-size: .92rem; font-weight: 700; margin: 0; }
        .tenant-dashboard .td-panel-head small { color: var(--td-muted); font-size: .72rem; }
        .tenant-dashboard .td-panel-body { padding: .25rem .95rem; }
        .tenant-dashboard .td-row { align-items: center; border-bottom: 1px solid #edf0ef; display: flex; gap: .75rem; justify-content: space-between; min-height: 57px; padding: .55rem 0; }
        .tenant-dashboard .td-row:last-child { border-bottom: 0; }
        .tenant-dashboard .td-row-main { min-width: 0; }
        .tenant-dashboard .td-row-main a { color: var(--td-ink); display: block; font-size: .82rem; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .tenant-dashboard .td-row-meta { color: var(--td-muted); font-size: .73rem; margin-top: .16rem; }
        .tenant-dashboard .td-status { border-radius: 3px; flex: 0 0 auto; font-size: .67rem; font-weight: 700; padding: .23rem .4rem; white-space: nowrap; }
        .tenant-dashboard .td-status-pending { background: #fbf0df; color: #966018; }
        .tenant-dashboard .td-status-returned { background: #f8ece9; color: #a55047; }
        .tenant-dashboard .td-status-verified { background: #e6f2ed; color: #2f7864; }
        .tenant-dashboard .td-status-draft { background: #edf0f1; color: #64747e; }
        .tenant-dashboard .td-empty { color: var(--td-muted); font-size: .8rem; padding: 1.1rem 0; }
        .tenant-dashboard .td-activity { border-left: 2px solid #e8ecea; margin: .35rem 0 .35rem .35rem; padding: .2rem 0 .2rem .9rem; }
        .tenant-dashboard .td-activity-row { padding: .45rem 0; position: relative; }
        .tenant-dashboard .td-activity-row::before { background: var(--td-amber); border: 2px solid #fff; border-radius: 50%; content: ''; height: 9px; left: -1.27rem; position: absolute; top: .76rem; width: 9px; }
        .tenant-dashboard .td-activity-row a { color: var(--td-ink); font-size: .79rem; font-weight: 700; }
        .tenant-dashboard .td-activity-meta { color: var(--td-muted); display: block; font-size: .71rem; margin-top: .1rem; }
        .tenant-dashboard .td-panel-link { color: #9a5d1f; font-size: .73rem; font-weight: 700; }
        .tenant-dashboard .td-table { margin-bottom: 0; min-width: 650px; }
        .tenant-dashboard .td-table thead th { background: var(--td-ink); border-bottom: 3px solid var(--td-amber); border-top: 0; color: #fff; font-size: .68rem; padding: .7rem .75rem; text-transform: uppercase; white-space: nowrap; }
        .tenant-dashboard .td-table td { color: #4b5a62; font-size: .79rem; padding: .7rem .75rem; vertical-align: middle; }
        .tenant-dashboard .td-table tbody tr:nth-child(even) { background: #fbfcfb; }
        .tenant-dashboard .td-table a { color: var(--td-ink); font-weight: 700; }
        .tenant-dashboard .td-table small { color: var(--td-muted); display: block; font-size: .7rem; }
        @media (max-width: 767.98px) {
            .tenant-dashboard .td-heading { align-items: flex-start; flex-direction: column; gap: .5rem; }
            .tenant-dashboard .td-columns { grid-template-columns: 1fr; }
            .tenant-dashboard .td-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 420px) { .tenant-dashboard .td-metrics { grid-template-columns: 1fr; } }
    </style>

    <div class="container-fluid tenant-dashboard">
        <header class="td-heading">
            <div>
                <div class="td-kicker">Tenant Management</div>
                <h1>Tenant Dashboard</h1>
                <p>Current housing register, applications and document follow-up.</p>
            </div>
            <div class="td-date"><i class="far fa-calendar-alt mr-1"></i>{{ $todayLabel }}</div>
        </header>

        <nav class="td-actions" aria-label="Tenant management quick actions">
            @can('tenant_register.create')
                <a class="btn btn-primary" href="{{ route('tenant-register.onboarding') }}"><i class="fas fa-user-plus mr-1"></i>New Tenant Application</a>
            @endcan
            @can('tenant_register.verify')
                <a class="btn btn-outline-secondary" href="{{ route('tenant-register.verification') }}"><i class="fas fa-clipboard-check mr-1"></i>Verification Queue</a>
            @endcan
            @can('tenant_register.view')
                <a class="btn btn-outline-secondary" href="{{ route('tenant-register.index') }}"><i class="fas fa-list mr-1"></i>Tenant Register</a>
            @endcan
            @can('tenant_register.documents.view')
                <a class="btn btn-outline-secondary" href="{{ route('tenant-register.documents') }}"><i class="fas fa-folder-open mr-1"></i>Documents</a>
            @endcan
        </nav>

        @if($metrics !== [])
            <section class="td-metrics" aria-label="Tenant summary indicators">
                @foreach($metrics as $metric)
                    <a class="td-metric td-{{ $metric['tone'] }}" href="{{ $metric['href'] }}">
                        <div class="td-metric-top"><span class="td-metric-label">{{ $metric['label'] }}</span><span class="td-icon"><i class="{{ $metric['icon'] }}"></i></span></div>
                        <div class="td-value">{{ number_format($metric['value']) }}</div>
                        <div class="td-note">{{ $metric['detail'] }}</div>
                    </a>
                @endforeach
            </section>
        @endif

        <div class="td-columns">
            @if($canViewRegister)
                <section class="td-panel td-panel--blue">
                    <div class="td-panel-head">
                        <div><h2>Recent tenant applications</h2><small>Latest submitted or updated applications</small></div>
                        <a class="td-panel-link" href="{{ route('tenant-register.index') }}">Register <i class="fas fa-arrow-right ml-1"></i></a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover td-table">
                            <thead><tr><th>Application</th><th>Applicant</th><th>Requested housing</th><th>Status</th></tr></thead>
                            <tbody>
                        @forelse($recentApplications as $application)
                            @php
                                $statusClass = match ($application->status) {
                                    'Pending Verification' => 'td-status-pending',
                                    'Returned for Correction' => 'td-status-returned',
                                    'Verified' => 'td-status-verified',
                                    default => 'td-status-draft',
                                };
                                $applicationUrl = in_array($application->status, ['Draft', 'Returned for Correction'], true)
                                    && ((int) $application->created_by === (int) auth()->id() || auth()->user()->can('tenant_register.update'))
                                    ? route('tenant-register.onboarding.edit', $application)
                                    : ($canVerify ? route('tenant-register.verification') : route('tenant-register.index'));
                            @endphp
                            <tr>
                                <td><a href="{{ $applicationUrl }}">{{ $application->reference_no }}</a></td>
                                <td>{{ $application->applicant_name ?: 'Unnamed applicant' }}<small>{{ ucwords(str_replace('_', ' ', $application->tenant_type)) }}</small></td>
                                <td>{{ $application->station_location }}<small>{{ $application->category }}</small></td>
                                <td><span class="td-status {{ $statusClass }}">{{ $application->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No submitted applications yet.</td></tr>
                        @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            <section class="td-panel td-panel--amber">
                <div class="td-panel-head">
                    <div><h2>Needs attention</h2><small>Items requiring follow-up</small></div>
                </div>
                <div class="td-panel-body">
                    @if($canVerify)
                        <div class="td-row">
                            <div class="td-row-main"><a href="{{ route('tenant-register.verification') }}">My verification queue</a><div class="td-row-meta">Applications assigned to you</div></div>
                            <span class="td-status td-status-pending">{{ number_format($pendingReviews) }}</span>
                        </div>
                    @endif
                    @if($canViewDocuments)
                        <div class="td-row">
                            <div class="td-row-main"><a href="{{ route('tenant-register.documents') }}">Documents expiring in 30 days</a><div class="td-row-meta">Check renewal or replacement</div></div>
                            <span class="td-status {{ $documentsExpiringSoon > 0 ? 'td-status-returned' : 'td-status-verified' }}">{{ number_format($documentsExpiringSoon) }}</span>
                        </div>
                    @endif
                    @if(! $canVerify && ! $canViewDocuments)
                        <div class="td-empty">No follow-up queue is available for this account.</div>
                    @elseif($pendingReviews === 0 && $documentsExpiringSoon === 0)
                        <div class="td-empty">Nothing currently needs follow-up.</div>
                    @endif
                </div>
            </section>

            @if($canViewHistory)
                <section class="td-panel td-panel--green">
                    <div class="td-panel-head">
                        <div><h2>Recent activity</h2><small>Latest tenant-related changes</small></div>
                        <a class="td-panel-link" href="{{ route('tenant-register.history') }}">Full history <i class="fas fa-arrow-right ml-1"></i></a>
                    </div>
                    <div class="td-panel-body">
                        @forelse($recentEvents as $event)
                            @php($owner = $event->tenantable)
                            <div class="td-activity">
                                <div class="td-activity-row">
                                    <a href="{{ route('tenant-register.history', $owner instanceof \App\Models\TenantOnboardingApplication ? ['application_id' => $owner->id] : ($owner instanceof \App\Models\TenantRegister ? ['tenant_register_id' => $owner->id] : [])) }}">{{ $event->summary }}</a>
                                    <span class="td-activity-meta">{{ $event->actor?->name ?: 'System' }} · {{ $event->occurred_at?->diffForHumans() }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="td-empty">No tenant activity recorded yet.</div>
                        @endforelse
                    </div>
                </section>
            @endif
        </div>
    </div>
</div>