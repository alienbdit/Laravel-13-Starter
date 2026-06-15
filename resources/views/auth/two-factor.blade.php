@extends('layouts.auth')

@section('title', 'Two-Factor Verification')

@section('content')

@if($type === 'totp')
    <h4 class="mb-1">Authenticator Code 🔐</h4>
    <p class="mb-4 text-muted">Enter the 6-digit code from your authenticator app.</p>
@elseif($type === 'sms')
    <h4 class="mb-1">Check Your Phone 📱</h4>
    <p class="mb-4 text-muted">We sent a 6-digit verification code to your phone number.</p>
@else
    <h4 class="mb-1">Check Your Email 📧</h4>
    <p class="mb-4 text-muted">We sent a 6-digit verification code to your email address.</p>
@endif

@if(session('resent'))
    <div class="alert alert-success py-2 mb-3">A new code has been sent.</div>
@endif

@if($errors->any())
    <div class="alert alert-danger py-2 mb-3">{{ $errors->first() }}</div>
@endif

<form action="{{ route('two-factor.post') }}" method="POST" class="mb-3">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="code">Verification Code</label>
        <input type="text" id="code" name="code"
               class="form-control text-center fw-bold fs-5 letter-spacing-6"
               style="letter-spacing: .5rem;"
               placeholder="• • • • • •"
               maxlength="6"
               inputmode="numeric"
               autocomplete="one-time-code"
               autofocus>
    </div>

    <div class="mb-3">
        <button class="btn btn-primary d-grid w-100" type="submit">Verify</button>
    </div>
</form>

@if($type !== 'totp')
<p class="text-center mb-2">
    <span class="text-muted">Didn't receive a code?</span>
    <form action="{{ route('two-factor.resend') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-link p-0 align-baseline">Resend</button>
    </form>
</p>
@endif

<p class="text-center">
    <a href="{{ route('login') }}">
        <i class="bx bx-chevron-left"></i> Back to login
    </a>
</p>

@endsection
