<!-- Navbar -->
<nav
    class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar"
>
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
        <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center">
                <i class="bx bx-search fs-4 lh-0"></i>
                <input
                    type="text"
                    class="form-control border-0 shadow-none"
                    placeholder="Search..."
                    aria-label="Search..."
                >
            </div>
        </div>
        <!-- /Search -->

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- User -->
            @php
                $navUser      = auth()->user();
                $navColors    = ['primary','success','warning','info','danger','secondary'];
                $navColor     = $navColors[$navUser?->id % count($navColors)] ?? 'primary';
                $navInitial   = strtoupper(substr($navUser?->name ?? 'U', 0, 1));
            @endphp
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        @if($navUser->profile_photo)
                            <img src="{{ $navUser->photoUrl() }}" alt="{{ $navUser->name }}"
                                 class="rounded-circle" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <span class="avatar-initial rounded-circle bg-label-{{ $navColor }}">{{ $navInitial }}</span>
                        @endif
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile') }}">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar">
                                        @if($navUser->profile_photo)
                                            <img src="{{ $navUser->photoUrl() }}" alt="{{ $navUser->name }}"
                                                 class="rounded-circle" style="width:100%;height:100%;object-fit:cover;">
                                        @else
                                            <span class="avatar-initial rounded-circle bg-label-{{ $navColor }}">{{ $navInitial }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block lh-1 mt-1">{{ $navUser->name }}</span>
                                    <small class="text-muted">{{ $navUser->email }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li><div class="dropdown-divider"></div></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile') }}">
                            <i class="bx bx-user me-2"></i>
                            <span class="align-middle">My Profile</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('settings.security') }}">
                            <i class="bx bx-shield-quarter me-2"></i>
                            <span class="align-middle">Security / 2FA</span>
                        </a>
                    </li>
                    <li><div class="dropdown-divider"></div></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="bx bx-power-off me-2"></i>
                                <span class="align-middle">Log Out</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>
</nav>
<!-- / Navbar -->
