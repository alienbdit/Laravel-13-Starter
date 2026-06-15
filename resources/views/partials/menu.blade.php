<!-- Menu -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                @include('partials.brand-logo')
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">{{ config('app.name', 'Laravel') }}</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item {{ request()->is('/') ? 'active' : '' }}">
            <a href="{{ url('/') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Dashboard</div>
            </a>
        </li>

        {{--
            Add your menu items here. Examples:

            Single item:
            <li class="menu-item {{ request()->is('users*') ? 'active' : '' }}">
                <a href="{{ url('/users') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user"></i>
                    <div>Users</div>
                </a>
            </li>

            Item with submenu (add 'active open' to the parent when a child is active):
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-cog"></i>
                    <div>Settings</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="#" class="menu-link"><div>General</div></a>
                    </li>
                </ul>
            </li>

            Section header:
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Apps &amp; Pages</span>
            </li>
        --}}

        <!-- Pages -->
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Pages</span></li>
        <li class="menu-item {{ request()->is('settings*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-dock-top"></i>
                <div data-i18n="Account Settings">Account Settings</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->is('settings/account') ? 'active' : '' }}">
                    <a href="{{ route('settings.account') }}" class="menu-link">
                        <div data-i18n="Account">Account</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('settings/notifications') ? 'active' : '' }}">
                    <a href="{{ route('settings.notifications') }}" class="menu-link">
                        <div data-i18n="Notifications">Notifications</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('settings/connections') ? 'active' : '' }}">
                    <a href="{{ route('settings.connections') }}" class="menu-link">
                        <div data-i18n="Connections">Connections</div>
                    </a>
                </li>
            </ul>
        </li>

@canany(['users.view', 'roles.view', 'permissions.view'])
        <!-- Administration -->
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Administration</span></li>
        @can('users.view')
        <li class="menu-item {{ request()->is('admin/users*') ? 'active' : '' }}">
            <a href="{{ route('admin.users.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div>Users</div>
            </a>
        </li>
        @endcan
        @can('roles.view')
        <li class="menu-item {{ request()->is('admin/roles*') ? 'active' : '' }}">
            <a href="{{ route('admin.roles.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
                <div>Roles</div>
            </a>
        </li>
        @endcan
        @can('permissions.view')
        <li class="menu-item {{ request()->is('admin/permissions*') ? 'active' : '' }}">
            <a href="{{ route('admin.permissions.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-key"></i>
                <div>Permissions</div>
            </a>
        </li>
        @endcan
        @endcanany

        <!-- Misc -->
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Misc</span></li>
        <li class="menu-item">
            <a href="https://themeselection.com/demo/sneat-bootstrap-html-admin-template/documentation/" target="_blank" class="menu-link">
                <i class="menu-icon tf-icons bx bx-file"></i>
                <div data-i18n="Documentation">Documentation</div>
            </a>
        </li>
    </ul>
</aside>
<!-- / Menu -->

