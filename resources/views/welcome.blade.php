<div class="estate-dashboard">
	<style>
		.estate-dashboard {
			--dash-ink: #253745;
			--dash-muted: #71808a;
			--dash-line: #e5e9e8;
			--dash-paper: #fff;
			--dash-ground: #f4f6f5;
			--dash-amber: #e69732;
			--dash-green: #34806d;
			--dash-blue: #4d8292;
			--dash-coral: #b75e51;
			--dash-slate: #64747e;
			color: var(--dash-ink);
			padding-bottom: 1.5rem;
		}

		.estate-dashboard .dashboard-hero {
			align-items: center;
			background: #f1f4f2;
			border-left: 4px solid var(--dash-amber);
			display: flex;
			justify-content: space-between;
			margin-bottom: 1.25rem;
			min-height: 144px;
			padding: 1.5rem 1.75rem;
		}

		.estate-dashboard .dashboard-eyebrow {
			color: var(--dash-muted);
			font-size: .72rem;
			font-weight: 700;
			letter-spacing: 1px;
			text-transform: uppercase;
		}

		.estate-dashboard .eyebrow-dot {
			background: var(--dash-amber);
			border-radius: 50%;
			display: inline-block;
			height: 7px;
			margin-right: .4rem;
			vertical-align: 1px;
			width: 7px;
		}

		.estate-dashboard .dashboard-hero h1 {
			color: var(--dash-ink);
			font-size: 1.75rem;
			font-weight: 700;
			margin: .45rem 0 .3rem;
		}

		.estate-dashboard .dashboard-hero h1 span { color: #a86320; }
		.estate-dashboard .dashboard-hero p { color: #64737a; margin: 0; }

		.estate-dashboard .hero-side {
			align-items: flex-end;
			display: flex;
			flex-direction: column;
			gap: .8rem;
			margin-left: 1rem;
		}

		.estate-dashboard .today-label {
			color: var(--dash-muted);
			font-size: .84rem;
			white-space: nowrap;
		}

		.estate-dashboard .quick-actions {
			display: flex;
			flex-wrap: wrap;
			gap: .5rem;
			margin-bottom: 1.25rem;
		}

		.estate-dashboard .quick-actions .btn { border-radius: 5px; font-weight: 600; }

		.estate-dashboard .metric-grid {
			display: grid;
			gap: .85rem;
			grid-template-columns: repeat(auto-fit, minmax(185px, 1fr));
			margin-bottom: 1.25rem;
		}

		.estate-dashboard .metric-tile {
			animation: dashboard-rise .35s ease-out both;
			background: var(--dash-paper);
			border: 1px solid var(--dash-line);
			border-radius: 6px;
			color: inherit;
			display: block;
			min-height: 132px;
			padding: 1rem 1.05rem;
			transition: border-color .18s ease, transform .18s ease, box-shadow .18s ease;
		}

		.estate-dashboard .metric-tile:hover {
			border-color: #cbd4d2;
			box-shadow: 0 8px 18px rgba(37, 55, 69, .07);
			color: inherit;
			text-decoration: none;
			transform: translateY(-2px);
		}

		.estate-dashboard .metric-top {
			align-items: center;
			display: flex;
			justify-content: space-between;
		}

		.estate-dashboard .metric-label {
			color: var(--dash-muted);
			font-size: .79rem;
			font-weight: 600;
		}

		.estate-dashboard .metric-icon {
			align-items: center;
			border-radius: 5px;
			display: inline-flex;
			font-size: .95rem;
			height: 34px;
			justify-content: center;
			width: 34px;
		}

		.estate-dashboard .tone-amber { background: #fbf2e5; color: #a86320; }
		.estate-dashboard .tone-green { background: #e8f2ee; color: var(--dash-green); }
		.estate-dashboard .tone-blue { background: #e9f1f3; color: var(--dash-blue); }
		.estate-dashboard .tone-coral { background: #f8ece9; color: var(--dash-coral); }
		.estate-dashboard .tone-slate { background: #edf0f1; color: var(--dash-slate); }

		.estate-dashboard .metric-value {
			color: var(--dash-ink);
			font-size: 1.8rem;
			font-weight: 700;
			line-height: 1.1;
			margin-top: .65rem;
		}

		.estate-dashboard .metric-note { color: var(--dash-muted); font-size: .76rem; margin-top: .3rem; }

		.estate-dashboard .dashboard-columns {
			display: grid;
			gap: 1rem;
			grid-template-columns: minmax(0, 1.35fr) minmax(280px, .85fr);
			margin-bottom: 1rem;
		}

		.estate-dashboard .dashboard-panel {
			background: var(--dash-paper);
			border: 1px solid var(--dash-line);
			border-radius: 6px;
			min-width: 0;
		}

		.estate-dashboard .panel-heading {
			align-items: center;
			border-bottom: 1px solid var(--dash-line);
			display: flex;
			justify-content: space-between;
			padding: .9rem 1rem;
		}

		.estate-dashboard .panel-heading h2 {
			color: var(--dash-ink);
			font-size: .96rem;
			font-weight: 700;
			margin: 0;
		}

		.estate-dashboard .panel-heading small { color: var(--dash-muted); font-size: .76rem; }
		.estate-dashboard .panel-body { padding: .35rem 1rem; }

		.estate-dashboard .queue-row,
		.estate-dashboard .lease-row {
			align-items: center;
			border-bottom: 1px solid #edf0ef;
			display: flex;
			gap: .75rem;
			justify-content: space-between;
			min-height: 58px;
			padding: .55rem 0;
		}

		.estate-dashboard .queue-row:last-child,
		.estate-dashboard .lease-row:last-child { border-bottom: 0; }

		.estate-dashboard .queue-main,
		.estate-dashboard .lease-main { min-width: 0; }

		.estate-dashboard .queue-main a,
		.estate-dashboard .lease-main a {
			color: var(--dash-ink);
			display: block;
			font-size: .84rem;
			font-weight: 600;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
		}

		.estate-dashboard .queue-meta,
		.estate-dashboard .lease-meta { color: var(--dash-muted); font-size: .74rem; margin-top: .15rem; }

		.estate-dashboard .queue-tag {
			background: #fbf2e5;
			border-radius: 3px;
			color: #92591f;
			flex: 0 0 auto;
			font-size: .68rem;
			font-weight: 700;
			padding: .22rem .4rem;
			text-transform: uppercase;
		}

		.estate-dashboard .status-row { margin: .8rem 0 1rem; }
		.estate-dashboard .status-label { display: flex; font-size: .8rem; justify-content: space-between; margin-bottom: .32rem; }
		.estate-dashboard .status-label span:first-child { color: #4f5e66; }
		.estate-dashboard .status-label strong { color: var(--dash-ink); }
		.estate-dashboard .status-track { background: #edf0ef; border-radius: 2px; height: 5px; overflow: hidden; }
		.estate-dashboard .status-fill { background: var(--dash-blue); border-radius: 2px; height: 100%; }
		.estate-dashboard .empty-note { color: var(--dash-muted); font-size: .82rem; padding: 1rem 0; }

		.estate-dashboard .recent-table { margin-bottom: 0; }
		.estate-dashboard .recent-table thead th {
			background: #f7f8f7;
			border-top: 0;
			color: var(--dash-muted);
			font-size: .69rem;
			font-weight: 700;
			padding: .65rem .75rem;
			text-transform: uppercase;
			white-space: nowrap;
		}
		.estate-dashboard .recent-table td { color: #4e5d64; font-size: .8rem; padding: .7rem .75rem; vertical-align: middle; }
		.estate-dashboard .recent-table td a { color: var(--dash-ink); font-weight: 700; }
		.estate-dashboard .status-pill { background: #edf2f0; border-radius: 3px; color: #47645a; font-size: .7rem; padding: .22rem .42rem; white-space: nowrap; }
		.estate-dashboard .panel-link { color: #9a5d1f; font-size: .76rem; font-weight: 700; }
		.estate-dashboard .summary-line { color: var(--dash-muted); font-size: .78rem; padding: .25rem 0 .6rem; }

		@keyframes dashboard-rise {
			from { opacity: 0; transform: translateY(7px); }
			to { opacity: 1; transform: translateY(0); }
		}

		@media (max-width: 767.98px) {
			.estate-dashboard .dashboard-hero { align-items: flex-start; flex-direction: column; padding: 1.15rem; }
			.estate-dashboard .dashboard-hero h1 { font-size: 1.45rem; }
			.estate-dashboard .hero-side { align-items: flex-start; margin: .85rem 0 0; }
			.estate-dashboard .dashboard-columns { grid-template-columns: 1fr; }
			.estate-dashboard .metric-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
			.estate-dashboard .metric-tile { min-height: 124px; padding: .85rem; }
			.estate-dashboard .metric-value { font-size: 1.55rem; }
		}

		@media (max-width: 420px) {
			.estate-dashboard .metric-grid { grid-template-columns: 1fr; }
		}
	</style>

	<div class="container-fluid px-0">
		<header class="dashboard-hero">
			<div>
				<div class="dashboard-eyebrow"><span class="eyebrow-dot"></span> Real Estate Portfolio</div>
				<h1>{{ $greeting }}, <span>{{ $firstName }}.</span></h1>
				<p>A clear view of your properties, projects, leases and next actions.</p>
			</div>
			<div class="hero-side">
				<div class="today-label"><i class="far fa-calendar-alt mr-1"></i>{{ $todayLabel }}</div>
			</div>
		</header>

		<nav class="quick-actions" aria-label="Quick actions">
			@can('real_estate_projects.create')
				<a class="btn btn-primary btn-sm" href="{{ route('real-estate-projects.proposals.create') }}"><i class="fas fa-plus mr-1"></i>New project proposal</a>
			@endcan
			@can('lease_agreements.manage')
				<a class="btn btn-outline-secondary btn-sm" href="{{ route('lease_agreements.import') }}"><i class="fas fa-file-import mr-1"></i>Import leases</a>
			@endcan
			@can('tenant_register.create')
				<a class="btn btn-outline-secondary btn-sm" href="{{ route('tenant-register.import') }}"><i class="fas fa-home mr-1"></i>Import tenants</a>
			@endcan
			@can('asset_register.create')
				<a class="btn btn-outline-secondary btn-sm" href="{{ route('asset-register.import') }}"><i class="fas fa-boxes mr-1"></i>Import assets</a>
			@endcan
		</nav>

		@if($metrics !== [])
			<section class="metric-grid" aria-label="Portfolio summary">
				@foreach($metrics as $index => $metric)
					<a class="metric-tile" href="{{ $metric['href'] }}" style="animation-delay: {{ min($index, 5) * 45 }}ms">
						<div class="metric-top">
							<span class="metric-label">{{ $metric['label'] }}</span>
							<span class="metric-icon tone-{{ $metric['tone'] }}"><i class="{{ $metric['icon'] }}"></i></span>
						</div>
						<div class="metric-value">{{ number_format($metric['value']) }}</div>
						<div class="metric-note">{{ $metric['note'] }}</div>
					</a>
				@endforeach
			</section>
		@endif

		<div class="dashboard-columns">
			<section class="dashboard-panel">
				<div class="panel-heading">
					<div><h2>Action needed</h2><small>Your current work queue</small></div>
					@if($canApprove)<a class="panel-link" href="{{ route('real-estate-projects.approvals') }}">Open approvals <i class="fas fa-arrow-right ml-1"></i></a>@endif
				</div>
				<div class="panel-body">
					@if($canApprove && $reviewWorkflows->isNotEmpty())
						@foreach($reviewWorkflows as $workflow)
							@if($workflow->workflowable)
								<div class="queue-row">
									<div class="queue-main">
										<a href="{{ route('real-estate-projects.show', $workflow->workflowable) }}">{{ $workflow->workflowable->project_title ?: $workflow->workflowable->reference_no }}</a>
										<div class="queue-meta">{{ $workflow->workflowable->reference_no }} <span class="mx-1">&middot;</span> Round {{ $workflow->approval_round }}</div>
									</div>
									<span class="queue-tag">Review</span>
								</div>
							@endif
						@endforeach
					@elseif($canApprove)
						<div class="empty-note"><i class="far fa-check-circle mr-1"></i>No proposals are waiting for your review.</div>
					@elseif($canProjects)
						<div class="empty-note">Your assigned actions will appear here.</div>
					@else
						<div class="empty-note">No work queue is available for your account.</div>
					@endif
				</div>
			</section>

			@if($canProjects)
				<section class="dashboard-panel">
					<div class="panel-heading">
						<div><h2>Project portfolio</h2><small>By lifecycle status</small></div>
						<a class="panel-link" href="{{ route('real-estate-projects.register') }}">View register <i class="fas fa-arrow-right ml-1"></i></a>
					</div>
					<div class="panel-body">
						@php($projectTotal = (int) $projectStatuses->sum('total'))
						@forelse($projectStatuses as $status)
							<div class="status-row">
								<div class="status-label"><span>{{ $status->lifecycle_status ?: 'Unspecified' }}</span><strong>{{ number_format($status->total) }}</strong></div>
								<div class="status-track"><div class="status-fill" style="width: {{ $projectTotal > 0 ? max(4, ($status->total / $projectTotal) * 100) : 0 }}%"></div></div>
							</div>
						@empty
							<div class="empty-note">Project status summaries will appear as projects are registered.</div>
						@endforelse
					</div>
				</section>
			@endif
		</div>

		@if($canLeases)
			<section class="dashboard-panel mb-3">
				<div class="panel-heading">
					<div><h2>Lease dates to watch</h2><small>{{ number_format($expiringLeaseCount) }} ending in the next 90 days</small></div>
					<a class="panel-link" href="{{ route('lease_agreements.index') }}">Lease register <i class="fas fa-arrow-right ml-1"></i></a>
				</div>
				<div class="panel-body">
					@forelse($upcomingLeases as $lease)
						<div class="lease-row">
							<div class="lease-main">
								<a href="{{ route('lease_agreements.index') }}">{{ $lease->landlord_name }}</a>
								<div class="lease-meta">{{ \Illuminate\Support\Str::limit($lease->contract_description, 90) }}</div>
							</div>
							<span class="queue-tag">{{ $lease->end_date?->format('j M Y') }}</span>
						</div>
					@empty
						<div class="empty-note">No lease expiries in the next 90 days.</div>
					@endforelse
				</div>
			</section>
		@endif

		@if($canProjects)
			<section class="dashboard-panel">
				<div class="panel-heading">
					<div><h2>Recently updated projects</h2><small>Latest portfolio activity</small></div>
					<a class="panel-link" href="{{ route('real-estate-projects.register') }}">All projects <i class="fas fa-arrow-right ml-1"></i></a>
				</div>
				<div class="table-responsive">
					<table class="table recent-table">
						<thead><tr><th>Reference</th><th>Project</th><th>Lifecycle</th><th>Proposal</th><th>Updated</th></tr></thead>
						<tbody>
							@forelse($recentProjects as $project)
								<tr>
									<td><a href="{{ route('real-estate-projects.show', $project) }}">{{ $project->reference_no }}</a></td>
									<td>{{ $project->project_title ?: 'Untitled project' }}</td>
									<td><span class="status-pill">{{ $project->lifecycle_status }}</span></td>
									<td>{{ $project->proposal_status ?: '—' }}</td>
									<td>{{ $project->updated_at?->format('j M Y') }}</td>
								</tr>
							@empty
								<tr><td colspan="5" class="text-center text-muted py-4">No project activity yet.</td></tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</section>
		@endif
	</div>
</div>
