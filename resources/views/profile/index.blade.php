@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
@php
    $avatarColors = ['primary', 'success', 'warning', 'info', 'danger', 'secondary'];
    $color = $avatarColors[$user->id % count($avatarColors)];
@endphp
<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Page header --}}
    <div class="card mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-4 flex-wrap">

                {{-- Avatar with upload overlay --}}
                <div class="flex-shrink-0">
                    <form id="photoForm" action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" id="photoInput" name="photo" class="d-none"
                               accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                               onchange="this.form.submit()">
                        <label for="photoInput" class="d-block position-relative mb-0"
                               style="width:80px;height:80px;cursor:pointer;" title="Click to change photo">
                            @if($user->profile_photo)
                                <img src="{{ $user->photoUrl() }}" alt="{{ $user->name }}"
                                     class="rounded-circle"
                                     style="width:80px;height:80px;object-fit:cover;">
                            @else
                                <span class="avatar-initial rounded-circle bg-label-{{ $color }} d-flex align-items-center justify-content-center"
                                      style="width:80px;height:80px;font-size:2rem;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                            @endif
                            <span class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle
                                         d-flex align-items-center justify-content-center"
                                  style="width:26px;height:26px;box-shadow:0 2px 6px rgba(0,0,0,.25);">
                                <i class="bx bx-camera" style="font-size:13px;line-height:1;"></i>
                            </span>
                        </label>
                    </form>
                </div>

                {{-- Name / email / roles --}}
                <div class="flex-grow-1">
                    <h4 class="mb-0 fw-bold">{{ $user->name }}</h4>
                    <p class="text-muted mb-1">{{ $user->email }}</p>
                    <div class="d-flex gap-1 flex-wrap">
                        @forelse($user->roles as $role)
                            <span class="badge bg-label-primary">{{ $role->label }}</span>
                        @empty
                            <span class="badge bg-label-secondary">No role</span>
                        @endforelse
                    </div>
                </div>

                {{-- Remove photo --}}
                @if($user->profile_photo)
                <div class="flex-shrink-0">
                    <form action="{{ route('profile.photo.remove') }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bx bx-trash me-1"></i> Remove Photo
                        </button>
                    </form>
                </div>
                @endif
            </div>

            @if(session('photo_success'))
                <div class="alert alert-success alert-dismissible py-2 mt-3 mb-0" role="alert">
                    {{ session('photo_success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @error('photo')
                <div class="alert alert-danger py-2 mt-3 mb-0" role="alert">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row g-4">

        {{-- Profile Information --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h5 class="mb-0">Profile Information</h5>
                    <small class="text-muted">Update your name and email address.</small>
                </div>
                <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible py-2" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="name">Username</label>
                            <input type="text" id="name" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}"
                                   autocomplete="username">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Used to log in. Must be unique.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold" for="email">Email Address</label>
                            <input type="email" id="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}"
                                   autocomplete="email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h5 class="mb-0">Change Password</h5>
                    <small class="text-muted">Use a strong password you don't use elsewhere.</small>
                </div>
                <div class="card-body">

                    @if(session('password_success'))
                        <div class="alert alert-success alert-dismissible py-2" role="alert">
                            {{ session('password_success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="current_password">Current Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="current_password" name="current_password"
                                       class="form-control @error('current_password') is-invalid @enderror"
                                       autocomplete="current-password">
                                <span class="input-group-text cursor-pointer" onclick="togglePwd('current_password', this)">
                                    <i class="bx bx-hide"></i>
                                </span>
                            </div>
                            @error('current_password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="password">New Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       autocomplete="new-password">
                                <span class="input-group-text cursor-pointer" onclick="togglePwd('password', this)">
                                    <i class="bx bx-hide"></i>
                                </span>
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Minimum 8 characters.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold" for="password_confirmation">Confirm New Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       class="form-control"
                                       autocomplete="new-password">
                                <span class="input-group-text cursor-pointer" onclick="togglePwd('password_confirmation', this)">
                                    <i class="bx bx-hide"></i>
                                </span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-lock-alt me-1"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('page-js')
<script>
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bx-hide', 'bx-show');
    } else {
        input.type = 'password';
        icon.classList.replace('bx-show', 'bx-hide');
    }
}
</script>
@endpush
