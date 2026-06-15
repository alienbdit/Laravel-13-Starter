@extends('layouts.auth')

@section('title', '404 Not Found')

@section('content')
<div class="text-center py-3">
    <h1 class="fw-bold mb-2" style="font-size: 5rem; line-height: 1; color: var(--bs-primary);">404</h1>
    <h4 class="mb-2">Page Not Found</h4>
    <p class="text-muted mb-4">The page you're looking for doesn't exist or has been moved.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">
        <i class="bx bx-home me-1"></i> Back to Dashboard
    </a>
</div>
@endsection
