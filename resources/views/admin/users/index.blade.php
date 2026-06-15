@extends('layouts.app')

@section('title', 'Users')

@section('content')
@php $avatarColors = ['primary', 'success', 'warning', 'info', 'danger', 'secondary']; @endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">Administration / <span class="text-muted fw-light">Users</span></h4>
        @can('users.create')
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                <i class="bx bx-plus me-1"></i> New User
            </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible py-2" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible py-2" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between py-3 flex-wrap gap-2">
            <h6 class="mb-0">All Users</h6>
            <div class="d-flex gap-2">
                <input type="text" id="userSearch" class="form-control form-control-sm"
                       placeholder="Search name or email…" style="width:200px;">
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
                        <th class="pe-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($users as $user)
                    @php $c = $avatarColors[$user->id % count($avatarColors)]; @endphp
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar avatar-xs flex-shrink-0">
                                    @if($user->profile_photo)
                                        <img src="{{ $user->photoUrl() }}" alt="{{ $user->name }}"
                                             class="rounded-circle" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        <span class="avatar-initial rounded-circle bg-label-{{ $c }}">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <span class="fw-semibold d-block" style="font-size:.875rem;">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                            <span class="badge bg-label-secondary ms-1" style="font-size:.7rem;">You</span>
                                        @endif
                                    </span>
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
                        <td class="pe-3 text-end align-middle text-nowrap">
                            @can('users.edit')
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="btn btn-sm btn-icon btn-outline-primary me-1" title="Edit user">
                                    <i class="bx bx-edit-alt"></i>
                                </a>
                            @endcan
                            @can('users.delete')
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Delete user \'{{ $user->name }}\'?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Delete user">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                @endif
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
        @if($users->isNotEmpty())
        <div id="noResults" class="text-center text-muted py-4 d-none">No users match your search.</div>
        @endif
    </div>
</div>
@endsection

@push('page-js')
<script>
(function () {
    const search = document.getElementById('userSearch');
    const filter = document.getElementById('roleFilter');
    const rows   = [...document.querySelectorAll('#usersTable tbody tr')];
    const empty  = document.getElementById('noResults');

    function applyFilter() {
        const q = search.value.toLowerCase().trim();
        const r = filter.value.toLowerCase();
        let visible = 0;

        rows.forEach(row => {
            if (row.cells.length < 2) return;
            const text = row.textContent.toLowerCase();
            const show = (!q || text.includes(q)) && (!r || text.includes(r));
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (empty) empty.classList.toggle('d-none', visible > 0 || rows.length === 0);
    }

    search.addEventListener('input', applyFilter);
    filter.addEventListener('change', applyFilter);
})();
</script>
@endpush
