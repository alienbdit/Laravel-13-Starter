@extends('layouts.app')

@section('title', 'Set Up Authenticator App')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Security / <span class="text-muted fw-light">Authenticator App Setup</span></h4>

    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-body p-4">
                    <h5 class="mb-1">Scan QR Code</h5>
                    <p class="text-muted mb-4">
                        Open your authenticator app (Google Authenticator, Authy, etc.) and scan the QR code below.
                    </p>

                    <div class="text-center mb-4">
                        <div class="d-inline-block p-3 bg-white border rounded">
                            {!! $qrSvg !!}
                        </div>
                    </div>

                    <p class="text-muted small mb-1">Can't scan? Enter this key manually:</p>
                    <div class="input-group mb-4">
                        <input type="text" class="form-control form-control-sm font-monospace"
                               value="{{ $secret }}" id="secretKey" readonly>
                        <button class="btn btn-outline-secondary btn-sm" type="button" id="copyBtn"
                                onclick="navigator.clipboard.writeText(document.getElementById('secretKey').value)
                                         .then(() => this.innerHTML = '<i class=\'bx bx-check\'></i> Copied')">
                            <i class="bx bx-copy"></i> Copy
                        </button>
                    </div>

                    <hr>

                    <h6 class="mb-3">Enter the 6-digit code to confirm</h6>

                    @if($errors->any())
                        <div class="alert alert-danger py-2 mb-3">{{ $errors->first() }}</div>
                    @endif

                    <form action="{{ route('settings.two-factor.confirm-totp') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <input type="text" name="code"
                                   class="form-control form-control-lg text-center fw-bold @error('code') is-invalid @enderror"
                                   placeholder="••••••"
                                   maxlength="6"
                                   inputmode="numeric"
                                   autocomplete="one-time-code"
                                   autofocus>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary d-grid w-100">
                            Confirm &amp; Activate
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('settings.security') }}" class="text-muted small">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
