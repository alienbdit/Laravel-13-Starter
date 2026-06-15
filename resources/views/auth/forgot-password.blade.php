@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<h4 class="mb-2">Forgot Password? 🔒</h4>
<p class="mb-4">Enter your email and we'll send you instructions to reset your password</p>

@if (session('status'))
    <div class="alert alert-success" role="alert">{{ session('status') }}</div>
@endif

<form id="formAuthentication" class="mb-3" action="{{ route('password.email') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input
            type="text"
            class="form-control @error('email') is-invalid @enderror"
            id="email"
            name="email"
            value="{{ old('email') }}"
            placeholder="Enter your email"
            autofocus
        >
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <button class="btn btn-primary d-grid w-100">Send Reset Link</button>
</form>

<div class="text-center">
    <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center">
        <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm"></i>
        Back to login
    </a>
</div>
@endsection
