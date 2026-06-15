<!-- Menu -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link">
            @php $siteLogo = setting('site_logo'); @endphp
            @if($siteLogo)
                <span class="app-brand-logo demo">
                    <img src="{{ asset($siteLogo) }}" alt="{{ config('app.name') }}"
                         style="height:28px;width:auto;object-fit:contain;">
                </span>
            @else
                <span class="app-brand-logo demo">
                    @include('partials.brand-logo')
                </span>
            @endif
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
            <li class="menu-item {{ request()->is('your-route*') ? 'active' : '' }}">
                <a href="{{ url('/your-route') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file"></i>
                    <div>Page Name</div>
                </a>
            </li>

            Item with submenu (add 'active open' to the parent when a child is active):
            <li class="menu-item {{ request()->is('parent*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-cog"></i>
                    <div>Parent</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->is('parent/child') ? 'active' : '' }}">
                        <a href="{{ url('/parent/child') }}" class="menu-link">
                            <div>Child</div>
                        </a>
                    </li>
                </ul>
            </li>

            Section header:
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Section</span>
            </li>
        --}}

@canany(['users.view', 'roles.view', 'permissions.view'])
        <!-- Administration -->
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Administration</span></li>
        <li class="menu-item {{ request()->is('admin/*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
                <div>Administration</div>
            </a>
            <ul class="menu-sub">
                @can('users.view')
                <li class="menu-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}" class="menu-link">
                        <div>Users</div>
                    </a>
                </li>
                @endcan
                @can('roles.view')
                <li class="menu-item {{ request()->is('admin/roles*') ? 'active' : '' }}">
                    <a href="{{ route('admin.roles.index') }}" class="menu-link">
                        <div>Roles</div>
                    </a>
                </li>
                @endcan
                @can('permissions.view')
                <li class="menu-item {{ request()->is('admin/permissions*') ? 'active' : '' }}">
                    <a href="{{ route('admin.permissions.index') }}" class="menu-link">
                        <div>Permissions</div>
                    </a>
                </li>
                @endcan
                @if(auth()->user()?->hasRole('super_admin'))
                <li class="menu-item {{ request()->is('admin/settings*') ? 'active' : '' }}">
                    <a href="{{ route('admin.settings.index') }}" class="menu-link">
                        <div>Site Settings</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('admin/sms-gateway*') ? 'active' : '' }}">
                    <a href="{{ route('admin.sms-gateway.index') }}" class="menu-link">
                        <div>SMS Gateway</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('admin/artisan*') ? 'active' : '' }}">
                    <a href="{{ route('admin.artisan.index') }}" class="menu-link">
                        <div>Artisan Console</div>
                    </a>
                </li>
                @endif
            </ul>
        </li>
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
