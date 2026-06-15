@extends('layouts.app')

@section('title', 'New Permission')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        Administration /
        <a href="{{ route('admin.permissions.index') }}" class="text-muted fw-light">Permissions</a> /
        New Permission
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Create Permission</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="name">Slug</label>
                    <div class="col-sm-9">
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}"
                               placeholder="e.g. posts.view" autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Use <code>resource.action</code> format. Lowercase letters, numbers, dots, hyphens, and underscores only.
                        </small>
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-sm-3 col-form-label" for="label">Label</label>
                    <div class="col-sm-9">
                        <input type="text"
                               class="form-control @error('label') is-invalid @enderror"
                               id="label" name="label" value="{{ old('label') }}"
                               placeholder="e.g. View Posts">
                        @error('label')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary me-2">Create Permission</button>
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
