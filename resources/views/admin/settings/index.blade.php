@extends('layouts.app')

@section('title', 'Site Settings')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">Administration / <span class="text-muted fw-light">Site Settings</span></h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible py-2" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Vertical nav --}}
        <div class="col-md-3 col-lg-2 mb-4">
            <div class="nav flex-column nav-pills" id="settingsTabs" role="tablist">
                <button class="nav-link text-start mb-1 {{ $activeTab === 'general'  ? 'active' : '' }}"
                        data-bs-toggle="pill" data-bs-target="#tab-general" type="button">
                    <i class="bx bx-cog me-2"></i> General
                </button>
                <button class="nav-link text-start mb-1 {{ $activeTab === 'email'    ? 'active' : '' }}"
                        data-bs-toggle="pill" data-bs-target="#tab-email" type="button">
                    <i class="bx bx-envelope me-2"></i> Email
                </button>
                <button class="nav-link text-start mb-1 {{ $activeTab === 'security'  ? 'active' : '' }}"
                        data-bs-toggle="pill" data-bs-target="#tab-security" type="button">
                    <i class="bx bx-shield-quarter me-2"></i> Security
                </button>
                <button class="nav-link text-start {{ $activeTab === 'appearance' ? 'active' : '' }}"
                        data-bs-toggle="pill" data-bs-target="#tab-appearance" type="button">
                    <i class="bx bx-palette me-2"></i> Appearance
                </button>
            </div>
        </div>

        {{-- Tab panes --}}
        <div class="col-md-9 col-lg-10">
            <div class="tab-content">

                {{-- ── General ──────────────────────────────────────────── --}}
                <div class="tab-pane fade {{ $activeTab === 'general' ? 'show active' : '' }}" id="tab-general">
                    <div class="card">
                        <div class="card-header py-3">
                            <h5 class="mb-0">General</h5>
                            <small class="text-muted">Basic identity and locale settings for the site.</small>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.settings.update-general') }}" method="POST">
                                @csrf @method('PUT')

                                <div class="row g-3 mb-4">
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold" for="app_name">App Name</label>
                                        <input type="text" id="app_name" name="app_name"
                                               class="form-control @error('app_name') is-invalid @enderror"
                                               value="{{ old('app_name', $s['app_name'] ?? config('app.name')) }}">
                                        @error('app_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <div class="form-text">Shown in the browser tab, emails, and sidebar.</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold" for="app_description">Tagline / Description</label>
                                        <input type="text" id="app_description" name="app_description"
                                               class="form-control @error('app_description') is-invalid @enderror"
                                               placeholder="Short description of your site"
                                               value="{{ old('app_description', $s['app_description'] ?? '') }}">
                                        @error('app_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold" for="app_timezone">Timezone</label>
                                        <select id="app_timezone" name="app_timezone"
                                                class="form-select @error('app_timezone') is-invalid @enderror">
                                            @php
                                                $current = old('app_timezone', $s['app_timezone'] ?? config('app.timezone', 'UTC'));
                                                $zones   = collect(DateTimeZone::listIdentifiers())
                                                    ->groupBy(fn($z) => str_contains($z, '/') ? explode('/', $z)[0] : 'Other');
                                            @endphp
                                            @foreach($zones as $region => $list)
                                                <optgroup label="{{ $region }}">
                                                    @foreach($list as $tz)
                                                        <option value="{{ $tz }}" {{ $current === $tz ? 'selected' : '' }}>
                                                            {{ $tz }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                        @error('app_timezone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold" for="date_format">Date Format</label>
                                        @php
                                            $dateFormats = [
                                                'Y-m-d'   => '2026-06-16 (ISO)',
                                                'd/m/Y'   => '16/06/2026',
                                                'm/d/Y'   => '06/16/2026',
                                                'd M Y'   => '16 Jun 2026',
                                                'D d M Y' => 'Mon 16 Jun 2026',
                                            ];
                                            $currentFmt = old('date_format', $s['date_format'] ?? 'Y-m-d');
                                        @endphp
                                        <select id="date_format" name="date_format"
                                                class="form-select @error('date_format') is-invalid @enderror">
                                            @foreach($dateFormats as $fmt => $label)
                                                <option value="{{ $fmt }}" {{ $currentFmt === $fmt ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('date_format')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Save General Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ── Email ────────────────────────────────────────────── --}}
                <div class="tab-pane fade {{ $activeTab === 'email' ? 'show active' : '' }}" id="tab-email">
                    <div class="card">
                        <div class="card-header py-3">
                            <h5 class="mb-0">Email</h5>
                            <small class="text-muted">Sender identity for outgoing emails (notifications, 2FA codes, password resets).</small>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.settings.update-email') }}" method="POST">
                                @csrf @method('PUT')

                                <div class="row g-3 mb-4">
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold" for="mail_from_name">From Name</label>
                                        <input type="text" id="mail_from_name" name="mail_from_name"
                                               class="form-control @error('mail_from_name') is-invalid @enderror"
                                               value="{{ old('mail_from_name', $s['mail_from_name'] ?? '') }}">
                                        @error('mail_from_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold" for="mail_from_address">From Address</label>
                                        <input type="email" id="mail_from_address" name="mail_from_address"
                                               class="form-control @error('mail_from_address') is-invalid @enderror"
                                               placeholder="no-reply@example.com"
                                               value="{{ old('mail_from_address', $s['mail_from_address'] ?? '') }}">
                                        @error('mail_from_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="alert alert-info py-2 mb-4">
                                    <i class="bx bx-info-circle me-1"></i>
                                    SMTP driver, host, port, and credentials are configured in <code>.env</code>
                                    (<code>MAIL_MAILER</code>, <code>MAIL_HOST</code>, etc.).
                                    During development, <code>MAIL_MAILER=log</code> writes emails to
                                    <code>storage/logs/laravel.log</code>.
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Save Email Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ── Security ─────────────────────────────────────────── --}}
                <div class="tab-pane fade {{ $activeTab === 'security' ? 'show active' : '' }}" id="tab-security">
                    <div class="card">
                        <div class="card-header py-3">
                            <h5 class="mb-0">Security &amp; Access</h5>
                            <small class="text-muted">Control who can access the site and how sessions behave.</small>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.settings.update-security') }}" method="POST">
                                @csrf @method('PUT')

                                {{-- Allow registration --}}
                                <div class="d-flex align-items-start justify-content-between border rounded p-3 mb-3">
                                    <div>
                                        <h6 class="mb-1">Allow Public Registration</h6>
                                        <p class="text-muted mb-0 small">
                                            When disabled, the registration page returns 403 and new accounts can
                                            only be created by an admin from the Users panel.
                                        </p>
                                    </div>
                                    <div class="form-check form-switch ms-3 flex-shrink-0">
                                        <input class="form-check-input" type="checkbox"
                                               id="allow_registration" name="allow_registration"
                                               {{ ($s['allow_registration'] ?? '1') === '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allow_registration"></label>
                                    </div>
                                </div>

                                {{-- Require 2FA --}}
                                <div class="d-flex align-items-start justify-content-between border rounded p-3 mb-3">
                                    <div>
                                        <h6 class="mb-1">Require 2FA for All Users</h6>
                                        <p class="text-muted mb-0 small">
                                            After login, users who have not yet set up 2FA will be redirected
                                            to the Security page before they can use the app.
                                        </p>
                                    </div>
                                    <div class="form-check form-switch ms-3 flex-shrink-0">
                                        <input class="form-check-input" type="checkbox"
                                               id="require_2fa" name="require_2fa"
                                               {{ ($s['require_2fa'] ?? '0') === '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="require_2fa"></label>
                                    </div>
                                </div>

                                {{-- Session lifetime --}}
                                <div class="mb-4">
                                    <label class="form-label fw-semibold" for="session_lifetime">Session Lifetime</label>
                                    @php
                                        $lifetimes = [
                                            '30'   => '30 minutes',
                                            '60'   => '1 hour',
                                            '120'  => '2 hours (default)',
                                            '240'  => '4 hours',
                                            '480'  => '8 hours',
                                            '1440' => '24 hours',
                                        ];
                                        $currentLt = old('session_lifetime', $s['session_lifetime'] ?? '120');
                                    @endphp
                                    <select id="session_lifetime" name="session_lifetime"
                                            class="form-select @error('session_lifetime') is-invalid @enderror"
                                            style="max-width: 220px;">
                                        @foreach($lifetimes as $val => $label)
                                            <option value="{{ $val }}" {{ $currentLt === $val ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('session_lifetime')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="form-text">Applies to new sessions. Existing sessions are unaffected until they expire.</div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Save Security Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ── Appearance ───────────────────────────────────────── --}}
                <div class="tab-pane fade {{ $activeTab === 'appearance' ? 'show active' : '' }}" id="tab-appearance">
                    <form action="{{ route('admin.settings.update-appearance') }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        {{-- Site Logo --}}
                        <div class="card mb-4">
                            <div class="card-header py-3">
                                <h5 class="mb-0">Site Logo</h5>
                                <small class="text-muted">Replaces the default SVG icon in the sidebar brand area.</small>
                            </div>
                            <div class="card-body">
                                @if(!empty($s['site_logo']))
                                <div class="d-flex align-items-center gap-4 mb-4 p-3 bg-lighter rounded">
                                    <img src="{{ asset($s['site_logo']) }}" alt="Current logo"
                                         style="height:48px;width:auto;object-fit:contain;">
                                    <div>
                                        <p class="mb-1 fw-semibold">Current logo</p>
                                        <small class="text-muted">{{ basename($s['site_logo']) }}</small>
                                    </div>
                                    <div class="ms-auto form-check">
                                        <input class="form-check-input" type="checkbox"
                                               id="remove_site_logo" name="remove_site_logo" value="1">
                                        <label class="form-check-label text-danger" for="remove_site_logo">
                                            Remove logo
                                        </label>
                                    </div>
                                </div>
                                @endif

                                <div class="mb-0">
                                    <label class="form-label" for="site_logo">
                                        {{ !empty($s['site_logo']) ? 'Replace logo' : 'Upload logo' }}
                                    </label>
                                    <input type="file" id="site_logo" name="site_logo"
                                           class="form-control @error('site_logo') is-invalid @enderror"
                                           accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml">
                                    @error('site_logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">JPG, PNG, GIF, WebP or SVG · max 2 MB. Recommended height: 28–48 px.</div>
                                </div>

                                <div id="logoPreview" class="mt-3 d-none">
                                    <p class="mb-1 text-muted small">Preview:</p>
                                    <img id="logoPreviewImg" src="" alt="Preview"
                                         style="height:48px;width:auto;object-fit:contain;border:1px solid #eee;border-radius:4px;padding:4px;">
                                </div>
                            </div>
                        </div>

                        {{-- Login Background --}}
                        <div class="card mb-4">
                            <div class="card-header py-3">
                                <h5 class="mb-0">Login Page Background</h5>
                                <small class="text-muted">Full-cover background image shown on the login, register, and password reset pages.</small>
                            </div>
                            <div class="card-body">
                                @if(!empty($s['login_bg']))
                                <div class="mb-4 position-relative rounded overflow-hidden border"
                                     style="height:160px;background:url('{{ asset($s['login_bg']) }}') center/cover no-repeat;">
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <div class="form-check bg-white rounded px-2 py-1 shadow-sm">
                                            <input class="form-check-input" type="checkbox"
                                                   id="remove_login_bg" name="remove_login_bg" value="1">
                                            <label class="form-check-label text-danger small" for="remove_login_bg">
                                                Remove image
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="mb-0">
                                    <label class="form-label" for="login_bg">
                                        {{ !empty($s['login_bg']) ? 'Replace background' : 'Upload background image' }}
                                    </label>
                                    <input type="file" id="login_bg" name="login_bg"
                                           class="form-control @error('login_bg') is-invalid @enderror"
                                           accept="image/jpeg,image/png,image/gif,image/webp">
                                    @error('login_bg')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">JPG, PNG, GIF or WebP · max 5 MB. Recommended: 1920 × 1080 px.</div>
                                </div>

                                <div id="bgPreview" class="mt-3 d-none rounded overflow-hidden border"
                                     style="height:140px;">
                                    <img id="bgPreviewImg" src="" alt="Preview"
                                         style="width:100%;height:100%;object-fit:cover;">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Save Appearance
                        </button>
                    </form>
                </div>

            </div>{{-- /tab-content --}}
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
// Restore active tab from URL ?tab= param and update it on tab switch
(function () {
    const params  = new URLSearchParams(location.search);
    const tabMap  = { general: '#tab-general', email: '#tab-email', security: '#tab-security', appearance: '#tab-appearance' };
    const target  = tabMap[params.get('tab')] ?? null;

    if (target) {
        const btn = document.querySelector(`[data-bs-target="${target}"]`);
        if (btn) {
            // Bootstrap 5: deactivate current, activate requested
            document.querySelectorAll('#settingsTabs .nav-link.active').forEach(el => {
                el.classList.remove('active');
            });
            document.querySelectorAll('.tab-pane.show.active').forEach(el => {
                el.classList.remove('show', 'active');
            });
            btn.classList.add('active');
            document.querySelector(target)?.classList.add('show', 'active');
        }
    }
})();

// Image file preview
(function () {
    function previewFile(inputId, previewWrap, previewImg) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('change', function () {
            const file = this.files?.[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById(previewImg).src = e.target.result;
                document.getElementById(previewWrap).classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    }

    previewFile('site_logo', 'logoPreview', 'logoPreviewImg');
    previewFile('login_bg',  'bgPreview',   'bgPreviewImg');
})();
</script>
@endpush
