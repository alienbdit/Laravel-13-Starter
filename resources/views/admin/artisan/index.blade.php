@extends('layouts.app')

@section('title', 'Artisan Console')

@push('page-css')
<style>
    #terminal-output {
        background: #1a1a2e;
        color: #e2e8f0;
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.8rem;
        line-height: 1.6;
        min-height: 300px;
        max-height: 500px;
        overflow-y: auto;
        padding: 1rem;
        border-radius: 0.375rem;
        white-space: pre-wrap;
        word-break: break-all;
    }
    #terminal-output .term-prompt  { color: #63e6be; }
    #terminal-output .term-success { color: #69db7c; }
    #terminal-output .term-error   { color: #ff6b6b; }
    #terminal-output .term-info    { color: #74c0fc; }
    #terminal-output .term-muted   { color: #868e96; }
    .cmd-badge {
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.75rem;
    }
    .command-row:not(:last-child) {
        border-bottom: 1px solid rgba(0,0,0,.06);
    }
    .run-btn { min-width: 70px; }
    .group-icon { width: 2rem; height: 2rem; }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Admin /</span> Artisan Console
            </h4>
            <p class="text-muted mb-0 small">Run whitelisted artisan commands directly from the browser.</p>
        </div>
    </div>

    <div class="alert alert-warning d-flex align-items-start gap-2 mb-4" role="alert">
        <i class="bx bx-error-circle fs-5 mt-1 flex-shrink-0"></i>
        <div>
            <strong>Caution:</strong> Commands like <code>migrate:rollback</code> and <code>db:seed</code> modify the
            database. Make sure you have a backup before running destructive operations.
        </div>
    </div>

    <div class="row g-4">

        {{-- Left: command groups --}}
        <div class="col-lg-7">

            @php
                $groupIcons = [
                    'Cache'    => 'bx-tachometer',
                    'Database' => 'bx-data',
                    'System'   => 'bx-server',
                ];
                $groupColors = [
                    'Cache'    => 'primary',
                    'Database' => 'success',
                    'System'   => 'info',
                ];
            @endphp

            @foreach ($commandGroups as $group => $commands)
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center gap-2 pb-2">
                    <span class="badge bg-label-{{ $groupColors[$group] ?? 'secondary' }} rounded p-2 group-icon d-flex align-items-center justify-content-center">
                        <i class="bx {{ $groupIcons[$group] ?? 'bx-terminal' }} fs-5"></i>
                    </span>
                    <h6 class="mb-0">{{ $group }}</h6>
                </div>
                <div class="card-body p-0">
                    @foreach ($commands as $cmd => $description)
                    <div class="command-row d-flex align-items-center gap-3 px-4 py-3">
                        <code class="cmd-badge badge bg-label-dark text-nowrap">{{ $cmd }}</code>
                        <span class="text-muted small flex-grow-1">{{ $description }}</span>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary run-btn flex-shrink-0"
                            data-command="{{ $cmd }}"
                            onclick="runCommand(this)"
                        >
                            <i class="bx bx-play me-1"></i>Run
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

        </div>

        {{-- Right: terminal output --}}
        <div class="col-lg-5">
            <div class="card sticky-top" style="top: 1.5rem;">
                <div class="card-header d-flex align-items-center justify-content-between pb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-label-dark rounded p-2 group-icon d-flex align-items-center justify-content-center">
                            <i class="bx bx-terminal fs-5"></i>
                        </span>
                        <h6 class="mb-0">Output</h6>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearTerminal()">
                        <i class="bx bx-trash me-1"></i>Clear
                    </button>
                </div>
                <div class="card-body p-0">
                    <div id="terminal-output"><span class="term-muted">// No command run yet. Click Run on any command.</span></div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between py-2">
                    <span id="term-status" class="small text-muted">Ready</span>
                    <span id="term-time" class="small text-muted"></span>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Confirmation Modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="confirmModalHeader">
                <h5 class="modal-title d-flex align-items-center gap-2" id="confirmModalLabel">
                    <i class="bx fs-4" id="confirmModalIcon"></i>
                    <span id="confirmModalTitle"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">You are about to run:</p>
                <pre class="bg-dark text-light rounded p-3 mb-3" style="font-size:.85rem;">$ php artisan <span id="confirmModalCmd"></span></pre>
                <p class="mb-0" id="confirmModalDesc"></p>
                <div id="confirmModalWarning" class="alert alert-danger d-flex align-items-start gap-2 mt-3 mb-0 d-none">
                    <i class="bx bx-error fs-5 flex-shrink-0 mt-1"></i>
                    <div id="confirmModalWarningText"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="confirmModalRunBtn" onclick="confirmRun()">
                    <i class="bx bx-play me-1"></i>Run Command
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('page-js')
<script>
const terminal   = document.getElementById('terminal-output');
const termStatus = document.getElementById('term-status');
const termTime   = document.getElementById('term-time');
const csrfToken  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const modal      = new bootstrap.Modal(document.getElementById('confirmModal'));

// Commands that deserve a destructive warning
const DESTRUCTIVE = {
    'migrate:rollback': 'This will roll back the last batch of database migrations. Data in affected tables may be lost.',
    'db:seed':          'This will re-seed the database. Duplicate or unexpected data may be created if records already exist.',
    'migrate':          'This will modify the database schema. Ensure you have a backup before proceeding.',
};

let pendingBtn = null;

function clearTerminal() {
    terminal.innerHTML = '<span class="term-muted">// Terminal cleared.</span>';
    termStatus.textContent = 'Ready';
    termTime.textContent   = '';
}

function appendLine(html) {
    terminal.innerHTML += '\n' + html;
    terminal.scrollTop = terminal.scrollHeight;
}

function runCommand(btn) {
    const command     = btn.dataset.command;
    const description = btn.closest('.command-row').querySelector('.text-muted').textContent.trim();
    const isDestructive = command in DESTRUCTIVE;

    pendingBtn = btn;

    // Populate modal
    document.getElementById('confirmModalCmd').textContent  = command;
    document.getElementById('confirmModalDesc').textContent = description;
    document.getElementById('confirmModalTitle').textContent = isDestructive ? 'Destructive Command' : 'Confirm Command';

    const icon   = document.getElementById('confirmModalIcon');
    const header = document.getElementById('confirmModalHeader');
    const runBtn = document.getElementById('confirmModalRunBtn');
    const warning = document.getElementById('confirmModalWarning');

    if (isDestructive) {
        icon.className   = 'bx bx-error fs-4 text-danger';
        header.className = 'modal-header border-danger';
        runBtn.className = 'btn btn-danger';
        warning.classList.remove('d-none');
        document.getElementById('confirmModalWarningText').textContent = DESTRUCTIVE[command];
    } else {
        icon.className   = 'bx bx-terminal fs-4 text-primary';
        header.className = 'modal-header';
        runBtn.className = 'btn btn-primary';
        warning.classList.add('d-none');
    }

    modal.show();
}

function confirmRun() {
    modal.hide();

    const btn     = pendingBtn;
    const command = btn.dataset.command;
    pendingBtn    = null;

    document.querySelectorAll('.run-btn').forEach(b => b.disabled = true);
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Running';

    terminal.innerHTML = `<span class="term-prompt">$ php artisan ${escapeHtml(command)}</span>`;
    termStatus.textContent = 'Running…';
    termTime.textContent   = '';

    const start = Date.now();

    fetch('{{ route('admin.artisan.run') }}', {
        method:  'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept':       'application/json',
        },
        body: JSON.stringify({ command }),
    })
    .then(res => res.json().then(data => ({ ok: res.ok, data })))
    .then(({ ok, data }) => {
        const elapsed = ((Date.now() - start) / 1000).toFixed(2);

        if (!ok) {
            appendLine(`<span class="term-error">Error: ${escapeHtml(data.error ?? 'Unknown error')}</span>`);
            termStatus.innerHTML = '<span class="text-danger">Failed</span>';
        } else if (data.success) {
            appendLine(`<span class="term-success">${escapeHtml(data.output)}</span>`);
            appendLine(`<span class="term-info">Exit code: ${data.exit_code}</span>`);
            termStatus.innerHTML = '<span class="text-success">&#10003; Success</span>';
        } else {
            appendLine(`<span class="term-error">${escapeHtml(data.output)}</span>`);
            appendLine(`<span class="term-info">Exit code: ${data.exit_code}</span>`);
            termStatus.innerHTML = '<span class="text-danger">&#10007; Failed</span>';
        }

        termTime.textContent = `${elapsed}s`;
    })
    .catch(err => {
        appendLine(`<span class="term-error">Network error: ${escapeHtml(err.message)}</span>`);
        termStatus.innerHTML = '<span class="text-danger">Error</span>';
    })
    .finally(() => {
        document.querySelectorAll('.run-btn').forEach(b => b.disabled = false);
        btn.innerHTML = '<i class="bx bx-play me-1"></i>Run';
    });
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}
</script>
@endpush
