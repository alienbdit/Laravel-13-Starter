@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        Administration /
        <a href="{{ route('admin.roles.index') }}" class="text-muted fw-light">Roles</a> /
        Edit
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Role &mdash; {{ $role->label }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Role Slug</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" value="{{ $role->name }}" disabled>
                        <small class="text-muted">The slug cannot be changed after creation.</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="label">Display Name</label>
                    <div class="col-sm-9">
                        <input type="text"
                               class="form-control @error('label') is-invalid @enderror"
                               id="label" name="label" value="{{ old('label', $role->label) }}">
                        @error('label')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-sm-3 col-form-label">Permissions</label>
                    <div class="col-sm-9">
                        @foreach($permissions as $group => $groupPermissions)
                            <p class="text-uppercase text-muted small fw-semibold mb-2 mt-3">{{ $group }}</p>
                            @foreach($groupPermissions as $permission)
                                @php
                                    $checked = old('permissions')
                                        ? in_array($permission->id, old('permissions', []))
                                        : $role->permissions->contains($permission);
                                @endphp
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox"
                                           name="permissions[]" value="{{ $permission->id }}"
                                           id="perm_{{ $permission->id }}"
                                           {{ $checked ? 'checked' : '' }}>
                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                        {{ $permission->label }}
                                        <small class="text-muted">({{ $permission->name }})</small>
                                    </label>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
