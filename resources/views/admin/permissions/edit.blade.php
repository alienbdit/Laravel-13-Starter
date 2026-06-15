@extends('layouts.app')

@section('title', 'Edit Permission')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        Administration /
        <a href="{{ route('admin.permissions.index') }}" class="text-muted fw-light">Permissions</a> /
        Edit
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Permission &mdash; <code>{{ $permission->name }}</code></h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.permissions.update', $permission) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Slug</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" value="{{ $permission->name }}" disabled>
                        <small class="text-muted">The slug cannot be changed after creation.</small>
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-sm-3 col-form-label" for="label">Label</label>
                    <div class="col-sm-9">
                        <input type="text"
                               class="form-control @error('label') is-invalid @enderror"
                               id="label" name="label" value="{{ old('label', $permission->label) }}">
                        @error('label')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
