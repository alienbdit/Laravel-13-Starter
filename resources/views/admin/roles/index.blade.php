@extends('layouts.app')

@section('title', 'Roles')

@section('content')
@php $avatarColors = ['primary', 'success', 'warning', 'info', 'danger', 'secondary']; @endphp

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">Roles</h4>
        @can('roles.create')
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                <i class="bx bx-plus me-1"></i> New Role
            </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible py-2" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Role Cards --}}
    <div class="row g-3 mb-4">
        @foreach($roles as $role)
        <div class="col-xl-3 col-lg-4 col-sm-6">
            <div class="card h-100 mb-0">
                <div class="card-body p-3">

                    {{-- Name + menu --}}
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-shield-quarter fs-5"></i>
                                </span>
                            </span>
                            <div>
                                <h6 class="mb-0 lh-1">{{ $role->label }}</h6>
                                <small class="text-muted">{{ $role->name }}</small>
                            </div>
                        </div>
                        @canany(['roles.edit', 'roles.delete'])
                        <div class="dropdown ms-1">
                            <button class="btn btn-sm p-0 text-muted" type="button" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @can('roles.edit')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.roles.edit', $role) }}">
                                            <i class="bx bx-edit-alt me-1"></i> Edit
                                        </a>
                                    </li>
                                @endcan
                                @can('roles.delete')
                                    <li>
                                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                              onsubmit="return confirm('Delete \'{{ $role->label }}\'?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bx bx-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                        @endcanany
                    </div>

                    {{-- Avatars + user count --}}
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="avatar-group d-flex">
                            @forelse($role->users->take(4) as $user)
                                @php $c = $avatarColors[$user->id % count($avatarColors)]; @endphp
                                <div class="avatar avatar-xs pull-up" title="{{ $user->name }}">
                                    @if($user->profile_photo)
                                        <img src="{{ $user->photoUrl() }}" alt="{{ $user->name }}"
                                             class="rounded-circle" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        <span class="avatar-initial rounded-circle bg-label-{{ $c }}">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                    @endif
                                </div>
                            @empty
                                <span class="text-muted" style="font-size:.75rem;">No users</span>
                            @endforelse
                            @if($role->users_count > 4)
                                <div class="avatar avatar-xs">
                                    <span class="avatar-initial rounded-circle bg-secondary text-white"
                                          style="font-size:.65rem;">
                                        +{{ $role->users_count - 4 }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <small class="text-muted">{{ $role->users_count }} user{{ $role->users_count !== 1 ? 's' : '' }}</small>
                    </div>

                    {{-- Permissions + edit link --}}
                    <div class="border-top pt-2 d-flex align-items-center justify-content-between">
                        <small class="text-muted">
                            {{ $role->permissions->count() }} permission{{ $role->permissions->count() !== 1 ? 's' : '' }}
                        </small>
                        @can('roles.edit')
                            <a href="{{ route('admin.roles.edit', $role) }}"
                               class="text-primary text-decoration-none" style="font-size:.8rem;">
                                Edit <i class="bx bx-chevron-right"></i>
                            </a>
                        @endcan
                    </div>

                </div>
            </div>
        </div>
        @endforeach

        {{-- Add New Role --}}
        @can('roles.create')
        <div class="col-xl-3 col-lg-4 col-sm-6">
            <a href="{{ route('admin.roles.create') }}" class="text-decoration-none">
                <div class="card h-100 mb-0 border-dashed d-flex align-items-center
                            justify-content-center text-center"
                     style="border: 2px dashed #d9dee3; min-height: 130px;">
                    <div class="card-body p-3">
                        <div class="avatar avatar-sm mb-2 mx-auto">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="bx bx-plus fs-5"></i>
                            </span>
                        </div>
                        <p class="mb-0 fw-semibold text-body" style="font-size:.85rem;">Add New Role</p>
                    </div>
                </div>
            </a>
        </div>
        @endcan
    </div>

    {{-- Users table --}}
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between py-3 flex-wrap gap-2">
            <h6 class="mb-0">Users &amp; their roles</h6>
            <div class="d-flex gap-2">
                <input type="text" id="userSearch" class="form-control form-control-sm"
                       placeholder="Search..." style="width:180px;">
                <select id="roleFilter" class="form-select form-select-sm" style="width:150px;">
                    <option value="">All roles</option>
                    @foreach($roles as $role)
                        <option value="{{ strtolower($role->label) }}">{{ $role->label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" id="usersTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">User</th>
                        <th>Roles</th>
                        <th class="pe-3"></th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($users as $user)
                    @php $c = $avatarColors[$user->id % count($avatarColors)]; @endphp
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar avatar-xs flex-shrink-0">
                                    <span class="avatar-initial rounded-circle bg-label-{{ $c }}">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="fw-semibold d-block" style="font-size:.875rem;">{{ $user->name }}</span>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="align-middle">
                            @forelse($user->roles as $role)
                                <span class="badge bg-label-primary me-1">{{ $role->label }}</span>
                            @empty
                                <span class="badge bg-label-secondary">No role</span>
                            @endforelse
                        </td>
                        <td class="pe-3 text-end align-middle">
                            @can('users.edit')
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="text-muted" title="Edit user">
                                    <i class="bx bx-edit-alt"></i>
                                </a>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('page-js')
<script>
(function () {
    const search = document.getElementById('userSearch');
    const filter = document.getElementById('roleFilter');
    const rows   = [...document.querySelectorAll('#usersTable tbody tr')];

    function applyFilter() {
        const q = search.value.toLowerCase();
        const r = filter.value.toLowerCase();
        rows.forEach(row => {
            const t = row.textContent.toLowerCase();
            row.style.display = (!q || t.includes(q)) && (!r || t.includes(r)) ? '' : 'none';
        });
    }

    search.addEventListener('input', applyFilter);
    filter.addEventListener('change', applyFilter);
})();
</script>
@endpush
