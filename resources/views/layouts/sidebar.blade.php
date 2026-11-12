<aside class="main-sidebar sidebar-dark-primary elevation-4"
       style="background: {{ config('theme.color_primary') }}">
    <a href="{{ route('dashboard') }}" class="brand-link" style="background: {{ config('theme.color_accent') }}">
        <img src="{{ asset('assets-dashboard/dist/img/logo/zesco_logo.png') }}"
             alt="Logo"
             class="brand-image img-circle elevation-3"
             style="opacity: .8">
        <span class="brand-text font-weight-light">{{ config('app.name') }}</span>
    </a>

    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar nav-child-indent flex-column"
                data-widget="treeview"
                role="menu"
                data-accordion="false">
                @can('dashboard.view')
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                        </a>
                    </li>
                @endcan

                @can('asset_register.view')
                    <li class="nav-header text-uppercase" style="color: {{ config('theme.color_accent') }}">ASSETS</li>
                    <li class="nav-item">
                        <a href="{{ route('asset-register.index') }}" class="nav-link {{ request()->routeIs('asset-register.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-list-alt"></i><p>Asset Register</p>
                        </a>
                    </li>
                @endcan

                @can('tenant_register.view')
                    @php $tenantsOpen = request()->routeIs('tenant-register.*'); @endphp
                    <li class="nav-item {{ $tenantsOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $tenantsOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Tenant Management <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('tenant-register.dashboard') }}" class="nav-link {{ request()->routeIs('tenant-register.dashboard') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i><p>Tenant Dashboard</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('tenant-register.index') }}" class="nav-link {{ request()->routeIs('tenant-register.index') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i><p>Tenant Register</p>
                                </a>
                            </li>
                            @can('tenant_register.view')
                                <li class="nav-item">
                                    <a href="{{ route('tenant-register.onboarding') }}" class="nav-link {{ request()->routeIs('tenant-register.onboarding*') ? 'active' : '' }}">
                                        <i class="far fa-dot-circle nav-icon"></i><p>Tenant Onboarding</p>
                                    </a>
                                </li>
                            @endcan
                            @can('tenant_register.verify')
                                <li class="nav-item">
                                    <a href="{{ route('tenant-register.verification') }}" class="nav-link {{ request()->routeIs('tenant-register.verification') ? 'active' : '' }}">
                                        <i class="far fa-dot-circle nav-icon"></i><p>Tenant Verification</p>
                                    </a>
                                </li>
                            @endcan
                            @can('tenant_register.documents.view')
                                <li class="nav-item">
                                    <a href="{{ route('tenant-register.documents') }}" class="nav-link {{ request()->routeIs('tenant-register.documents*') ? 'active' : '' }}">
                                        <i class="far fa-dot-circle nav-icon"></i><p>Tenant Documents</p>
                                    </a>
                                </li>
                            @endcan
                            @can('tenant_register.history.view')
                                <li class="nav-item">
                                    <a href="{{ route('tenant-register.history') }}" class="nav-link {{ request()->routeIs('tenant-register.history*') ? 'active' : '' }}">
                                        <i class="far fa-dot-circle nav-icon"></i><p>Tenant History &amp; Status</p>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                @can('real_estate_projects.view')
                    @php $projectsOpen = request()->routeIs('real-estate-projects.*'); @endphp
                    <li class="nav-item {{ $projectsOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $projectsOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-building"></i>
                            <p>Real Estate Projects <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('real-estate-projects.dashboard') }}" class="nav-link {{ request()->routeIs('real-estate-projects.dashboard') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i><p>Project Dashboard</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('real-estate-projects.register') }}" class="nav-link {{ request()->routeIs('real-estate-projects.register') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i><p>Project Register</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('real-estate-projects.proposals') }}" class="nav-link {{ request()->routeIs('real-estate-projects.proposals*') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i><p>Project Proposals</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('real-estate-projects.approvals') }}" class="nav-link {{ request()->routeIs('real-estate-projects.approvals') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i><p>Project Approvals</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('real-estate-projects.reports') }}" class="nav-link {{ request()->routeIs('real-estate-projects.reports') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i><p>Project Reports</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan

                @php $leasesOpen = request()->routeIs('lease_agreements.*'); @endphp
                <li class="nav-item {{ $leasesOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $leasesOpen ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-contract"></i>
                        <p>Lease Management <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('lease_tenant_workflow.view')
                            <li class="nav-item">
                                <a href="{{ route('lease_agreements.dashboard') }}" class="nav-link {{ request()->routeIs('lease_agreements.dashboard') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i><p>Lease Dashboard</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('lease_agreements.index') }}" class="nav-link {{ request()->routeIs('lease_agreements.index') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i><p>ZESCO as Tenant Lease Register</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('lease_agreements.landlord_register') }}" class="nav-link {{ request()->routeIs('lease_agreements.landlord_register') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i><p>ZESCO as Landlord Lease Register</p>
                                </a>
                            </li>
                            @php $newLeaseOpen = request()->routeIs('lease_agreements.new*'); @endphp
                            <li class="nav-item {{ $newLeaseOpen ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link {{ $newLeaseOpen ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i><p>New Lease <i class="right fas fa-angle-left"></i></p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('lease_agreements.new') }}" class="nav-link {{ request()->routeIs('lease_agreements.new') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i><p>ZESCO as Tenant</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('lease_agreements.new.landlord') }}" class="nav-link {{ request()->routeIs('lease_agreements.new.landlord') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i><p>ZESCO as Landlord</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('lease_agreements.renewals') }}" class="nav-link {{ request()->routeIs('lease_agreements.renewals') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i><p>Lease Renewals</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('lease_agreements.terminations') }}" class="nav-link {{ request()->routeIs('lease_agreements.terminations') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i><p>Lease Terminations</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('lease_agreements.handovers') }}" class="nav-link {{ request()->routeIs('lease_agreements.handovers') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i><p>Property Handover</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('lease_agreements.documents_history') }}" class="nav-link {{ request()->routeIs('lease_agreements.documents_history') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i><p>Lease Documents &amp; History</p>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>

                @canany(['users.view', 'workflow.view', 'configuration.view'])
                    <li class="nav-header text-uppercase" style="color: {{ config('theme.color_accent') }}">SETTINGS</li>
                @endcanany

                @can('users.view')
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users-cog"></i><p>User Management</p>
                        </a>
                    </li>
                @endcan

                @can('workflow.view')
                    <li class="nav-item">
                        <a href="{{ route('workflow.approvers') }}" class="nav-link {{ request()->routeIs('workflow.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-project-diagram"></i><p>Workflow Setup</p>
                        </a>
                    </li>
                @endcan

                @auth
                    @php
                        $notifOpen = request()->routeIs('notifications.*')
                            || request()->routeIs('settings.notifications')
                            || request()->routeIs('admin.email_log');
                        $canSeeEmailLog = \App\Models\ServiceDepartmentPerson::where('hod_user_id', auth()->id())->exists();
                    @endphp
                    <li class="nav-item {{ $notifOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $notifOpen ? 'active' : '' }}">
                            <i class="nav-icon far fa-bell"></i>
                            <p>Email Notifications <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.index') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i><p>My Notifications</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('settings.notifications') }}" class="nav-link {{ request()->routeIs('settings.notifications') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i><p>My Preferences</p>
                                </a>
                            </li>
                            @if($canSeeEmailLog)
                                <li class="nav-item">
                                    <a href="{{ route('admin.email_log') }}" class="nav-link {{ request()->routeIs('admin.email_log') ? 'active' : '' }}">
                                        <i class="far fa-dot-circle nav-icon text-warning"></i><p>Email Audit Log</p>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endauth

                @canany(['configuration.view', 'configuration.manage', 'configuration_workflow_definitions.view'])
                    <li class="nav-item {{ request()->routeIs('configurations.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('configurations.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>Configurations <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('configuration.manage')
                                <li class="nav-item">
                                    <a href="{{ route('configurations.roles.index') }}" class="nav-link {{ request()->routeIs('configurations.roles.*') ? 'active' : '' }}">
                                        <i class="far fa-dot-circle nav-icon"></i><p>Roles and Permissions</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('configurations.asset-register') }}" class="nav-link {{ request()->routeIs('configurations.asset-register') ? 'active' : '' }}">
                                        <i class="far fa-dot-circle nav-icon"></i><p>Asset Register Configuration</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('configurations.lease_statuses.index') }}"
                                    class="nav-link {{ request()->routeIs('configurations.lease_statuses.*') ? 'active' : '' }}">
                                        <i class="far fa-dot-circle nav-icon"></i>
                                        <p>Lease Statuses</p>
                                    </a>
                                </li>
                            @endcan
                            <li class="nav-item">
                                <a href="{{ route('configurations.workflows.workflow_definitions.index') }}" class="nav-link {{ request()->routeIs('configurations.workflows.workflow_definitions.*') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i><p>Workflow Definitions</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan
            </ul>
        </nav>
    </div>
</aside>
