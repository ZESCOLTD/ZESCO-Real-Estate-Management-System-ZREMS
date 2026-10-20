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

                @can('spms_lookup.view')
                    <li class="nav-header text-uppercase" style="color: {{ config('theme.color_accent') }}">ACQUISITION</li>
                    <li class="nav-item">
                        <a href="{{ route('spms-document.show') }}" class="nav-link {{ request()->routeIs('spms-document.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-search"></i><p>SPMS Lookup</p>
                        </a>
                    </li>
                @endcan

                @can('land_properties.view')
                    <li class="nav-header text-uppercase" style="color: {{ config('theme.color_accent') }}">ASSETS</li>
                    <li class="nav-item">
                        <a href="{{ route('fms_assets_register.index') }}" class="nav-link {{ request()->routeIs('fms_assets_register.index') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-map-marked-alt"></i><p>Land &amp; Properties</p>
                        </a>
                    </li>
                @endcan

                @can('lease_agreements.view')
                    <li class="nav-item">
                        <a href="{{ route('lease_agreements.index') }}"
                        class="nav-link {{ request()->routeIs('lease_agreements.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-contract"></i>
                            <p>Lease Agreement Register</p>
                        </a>
                    </li>
                @endcan

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

                @canany(['configuration.view', 'configuration_workflow_definitions.view'])
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
