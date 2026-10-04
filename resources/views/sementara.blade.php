@extends('layouts.app')
@section('title', $judul)

@section('content')
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-hammer text-primary" style="font-size: 2.5rem;"></i>
        <h2 class="h5 mt-3">{{ $judul }}</h2>
        <p class="text-muted mb-0">Halaman ini dibuat pada Tahap {{ $tahap }}.</p>
    </div>
</div>
@endsection
