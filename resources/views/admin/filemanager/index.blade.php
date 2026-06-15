@extends('layouts.app')

@section('title', 'File Manager')

@push('page-css')
<style>
    .lfm-wrapper {
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        overflow: hidden;
        background: #fff;
    }
    .lfm-wrapper iframe {
        display: block;
        width: 100%;
        height: calc(100vh - 220px);
        min-height: 500px;
        border: none;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Admin /</span> File Manager
            </h4>
            <p class="text-muted mb-0 small">Browse, upload, and manage files and images.</p>
        </div>
    </div>

    <div class="lfm-wrapper">
        <iframe src="{{ route('unisharp.lfm.show') }}"></iframe>
    </div>

</div>
@endsection
