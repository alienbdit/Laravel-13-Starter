@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        Administration /
        <a href="{{ route('admin.users.index') }}" class="text-muted fw-light">Users</a> /
        Edit
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit User &mdash; {{ $user->name }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="name">Username</label>
                    <div class="col-sm-9">
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $user->name) }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="email">Email</label>
                    <div class="col-sm-9">
                        <input type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email', $user->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="password">New Password</label>
                    <div class="col-sm-9">
                        <input type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password"
                               placeholder="Leave blank to keep current password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-sm-3 col-form-label">Roles</label>
                    <div class="col-sm-9">
                        @foreach($roles as $role)
                            @php
                                $checked = old('roles')
                                    ? in_array($role->id, old('roles', []))
                                    : $user->roles->contains($role);
                            @endphp
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox"
                                       name="roles[]" value="{{ $role->id }}"
                                       id="role_{{ $role->id }}"
                                       {{ $checked ? 'checked' : '' }}>
                                <label class="form-check-label" for="role_{{ $role->id }}">
                                    <span class="fw-semibold">{{ $role->label }}</span>
                                    <small class="text-muted ms-1">({{ $role->name }})</small>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
