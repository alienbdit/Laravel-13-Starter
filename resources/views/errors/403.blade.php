@extends('layouts.auth')

@section('title', '403 Forbidden')

@section('content')
<div class="text-center py-3">
    <h1 class="fw-bold mb-2" style="font-size: 5rem; line-height: 1; color: var(--bs-primary);">403</h1>
    <h4 class="mb-2">Access Denied</h4>
    <p class="text-muted mb-4">You don't have permission to view this page.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">
        <i class="bx bx-home me-1"></i> Back to Dashboard
    </a>
</div>
@endsection
