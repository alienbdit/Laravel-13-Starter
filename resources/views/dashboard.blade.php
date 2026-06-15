@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Welcome --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h5 class="card-title mb-1">Welcome back, {{ auth()->user()->name }}!</h5>
                        <p class="text-muted mb-0">Here's what's happening in your application.</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        @can('users.view')
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-group me-1"></i> Manage Users
                            </a>
                        @endcan
                        @can('roles.view')
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bx bx-shield-quarter me-1"></i> Manage Roles
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat cards — replace with real data for your app --}}
    <div class="row mb-4">
        <div class="col-sm-6 col-xl-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <span class="fw-semibold d-block text-muted mb-1">Stat One</span>
                            <h3 class="card-title mb-0">—</h3>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-user"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <span class="fw-semibold d-block text-muted mb-1">Stat Two</span>
                            <h3 class="card-title mb-0">—</h3>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-check-circle"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <span class="fw-semibold d-block text-muted mb-1">Stat Three</span>
                            <h3 class="card-title mb-0">—</h3>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="bx bx-collection"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <span class="fw-semibold d-block text-muted mb-1">Stat Four</span>
                            <h3 class="card-title mb-0">—</h3>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="bx bx-trending-up"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Your content goes here --}}

</div>
@endsection
