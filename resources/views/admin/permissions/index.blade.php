@extends('layouts.app')

@section('title', 'Permissions')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Administration / <span class="text-muted fw-light">Permissions</span></h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Permissions</h5>
            @can('permissions.create')
                <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-plus me-1"></i> New Permission
                </a>
            @endcan
        </div>
        <div class="card-body p-0">
            @foreach($permissions as $group => $groupPermissions)
                <div class="px-4 pt-4 pb-2">
                    <p class="text-uppercase text-muted small fw-semibold mb-3">
                        <i class="bx bx-folder me-1"></i> {{ $group }}
                    </p>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Slug</th>
                                <th>Label</th>
                                <th>Assigned to Roles</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupPermissions as $permission)
                            <tr>
                                <td><code>{{ $permission->name }}</code></td>
                                <td>{{ $permission->label }}</td>
                                <td>
                                    @forelse($permission->roles as $role)
                                        <span class="badge bg-label-secondary me-1">{{ $role->label }}</span>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                </td>
                                <td class="text-nowrap">
                                    @can('permissions.edit')
                                        <a href="{{ route('admin.permissions.edit', $permission) }}"
                                           class="btn btn-sm btn-outline-primary me-1">Edit</a>
                                    @endcan
                                    @can('permissions.delete')
                                        <form action="{{ route('admin.permissions.destroy', $permission) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete permission \'{{ $permission->name }}\'? This will remove it from all roles.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(! $loop->last)
                    <hr class="my-0">
                @endif
            @endforeach
        </div>
    </div>
</div>
@endsection
