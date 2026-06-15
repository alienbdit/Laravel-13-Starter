@extends('layouts.app')

@section('title', 'SMS Gateway')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Administration / <span class="text-muted fw-light">SMS Gateway</span></h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible py-2" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible py-2" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            {{-- Settings form --}}
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h5 class="mb-0">Gateway Configuration</h5>
                    <small class="text-muted">Used for sending SMS-based 2FA codes and notifications.</small>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sms-gateway.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Enable toggle --}}
                        <div class="d-flex align-items-center justify-content-between mb-4 p-3 bg-lighter rounded">
                            <div>
                                <h6 class="mb-0">Enable SMS Gateway</h6>
                                <small class="text-muted">When disabled, SMS codes are only logged.</small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="is_enabled"
                                       name="is_enabled" value="1"
                                       {{ $settings->is_enabled ? 'checked' : '' }}>
                            </div>
                        </div>

                        {{-- Provider --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold" for="provider">Provider</label>
                            <select id="provider" name="provider" class="form-select" onchange="toggleProviderFields()">
                                @foreach([
                                    'twilio'  => 'Twilio',
                                    'vonage'  => 'Vonage (Nexmo)',
                                    'aws_sns' => 'AWS SNS',
                                    'custom'  => 'Custom HTTP Endpoint',
                                ] as $value => $label)
                                    <option value="{{ $value }}" {{ $settings->provider === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-3 mb-4">
                            {{-- API Key --}}
                            <div class="col-md-6">
                                <label class="form-label" for="api_key" id="apiKeyLabel">
                                    API Key / Account SID
                                </label>
                                <input type="text" id="api_key" name="api_key"
                                       class="form-control @error('api_key') is-invalid @enderror"
                                       value="{{ old('api_key', $settings->api_key) }}"
                                       placeholder="Enter API key">
                                @error('api_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- API Secret --}}
                            <div class="col-md-6">
                                <label class="form-label" for="api_secret" id="apiSecretLabel">
                                    API Secret / Auth Token
                                </label>
                                <div class="input-group">
                                    <input type="password" id="api_secret" name="api_secret"
                                           class="form-control @error('api_secret') is-invalid @enderror"
                                           value="{{ old('api_secret', $settings->api_secret) }}"
                                           placeholder="Enter secret">
                                    <button class="btn btn-outline-secondary" type="button"
                                            onclick="toggleSecret(this)">
                                        <i class="bx bx-show"></i>
                                    </button>
                                </div>
                                @error('api_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- From Number --}}
                            <div class="col-md-6">
                                <label class="form-label" for="from_number">From Number / Sender ID</label>
                                <input type="text" id="from_number" name="from_number"
                                       class="form-control @error('from_number') is-invalid @enderror"
                                       value="{{ old('from_number', $settings->from_number) }}"
                                       placeholder="+1 555 000 0000">
                                @error('from_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Endpoint URL (custom only) --}}
                            <div class="col-md-6" id="endpointGroup">
                                <label class="form-label" for="endpoint_url">Endpoint URL</label>
                                <input type="url" id="endpoint_url" name="endpoint_url"
                                       class="form-control @error('endpoint_url') is-invalid @enderror"
                                       value="{{ old('endpoint_url', $settings->endpoint_url) }}"
                                       placeholder="https://api.example.com/sms/send">
                                @error('endpoint_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Provider hints --}}
            <div class="card mb-4" id="providerHint">
                <div class="card-body p-3">
                    <div id="hint-twilio" class="d-none">
                        <h6 class="mb-1"><i class="bx bx-info-circle text-info me-1"></i> Twilio</h6>
                        <p class="text-muted mb-0 small">
                            <strong>API Key</strong> = Account SID &nbsp;·&nbsp;
                            <strong>Secret</strong> = Auth Token &nbsp;·&nbsp;
                            <strong>From</strong> = your Twilio phone number.
                            Endpoint URL is not used.
                        </p>
                    </div>
                    <div id="hint-vonage" class="d-none">
                        <h6 class="mb-1"><i class="bx bx-info-circle text-info me-1"></i> Vonage (Nexmo)</h6>
                        <p class="text-muted mb-0 small">
                            <strong>API Key</strong> = Vonage API key &nbsp;·&nbsp;
                            <strong>Secret</strong> = Vonage API secret &nbsp;·&nbsp;
                            <strong>From</strong> = sender name or number.
                            Endpoint URL is not used.
                        </p>
                    </div>
                    <div id="hint-aws_sns" class="d-none">
                        <h6 class="mb-1"><i class="bx bx-info-circle text-warning me-1"></i> AWS SNS</h6>
                        <p class="text-muted mb-0 small">
                            AWS SNS requires the <code>aws/aws-sdk-php</code> package.
                            Install it with <code>composer require aws/aws-sdk-php</code> and implement
                            <code>SmsService::sendAwsSns()</code>.
                        </p>
                    </div>
                    <div id="hint-custom" class="d-none">
                        <h6 class="mb-1"><i class="bx bx-info-circle text-success me-1"></i> Custom HTTP Endpoint</h6>
                        <p class="text-muted mb-0 small">
                            A POST request is sent to your <strong>Endpoint URL</strong> with
                            <code>{ to, message, from }</code> in the body, plus any extra params.
                            The <strong>API Key</strong> is sent as a <code>Bearer</code> token in the
                            <code>Authorization</code> header.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Test SMS --}}
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0">Send Test SMS</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Sends a test message using the saved settings. The gateway must be enabled.
                    </p>
                    <form action="{{ route('admin.sms-gateway.test') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="test_phone">Phone Number</label>
                            <input type="tel" id="test_phone" name="test_phone"
                                   class="form-control"
                                   placeholder="+1 555 000 0000">
                        </div>
                        <button type="submit" class="btn btn-outline-primary d-grid w-100">
                            <i class="bx bx-send me-1"></i> Send Test
                        </button>
                    </form>
                </div>
            </div>

            {{-- Status card --}}
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-{{ $settings->is_enabled ? 'success' : 'secondary' }}">
                                <i class="bx bx-{{ $settings->is_enabled ? 'wifi' : 'wifi-off' }} fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $settings->is_enabled ? 'Gateway Active' : 'Gateway Inactive' }}</h6>
                            <small class="text-muted">
                                Provider: <strong>{{ strtoupper(str_replace('_', ' ', $settings->provider)) }}</strong>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
const providerLabels = {
    twilio  : { key: 'Account SID', secret: 'Auth Token' },
    vonage  : { key: 'API Key', secret: 'API Secret' },
    aws_sns : { key: 'Access Key ID', secret: 'Secret Access Key' },
    custom  : { key: 'API Key (Bearer)', secret: 'API Secret (Basic Auth)' },
};

function toggleProviderFields() {
    const p = document.getElementById('provider').value;
    const endpointGroup = document.getElementById('endpointGroup');
    endpointGroup.style.display = p === 'custom' ? '' : 'none';

    const labels = providerLabels[p] ?? providerLabels.custom;
    document.getElementById('apiKeyLabel').textContent    = labels.key;
    document.getElementById('apiSecretLabel').textContent = labels.secret;

    ['twilio','vonage','aws_sns','custom'].forEach(id => {
        document.getElementById('hint-' + id)?.classList.add('d-none');
    });
    document.getElementById('hint-' + p)?.classList.remove('d-none');
}

function toggleSecret(btn) {
    const input = btn.previousElementSibling;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    btn.innerHTML = isPassword ? '<i class="bx bx-hide"></i>' : '<i class="bx bx-show"></i>';
}

// Init on load
toggleProviderFields();
</script>
@endpush
