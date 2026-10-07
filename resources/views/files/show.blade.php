@extends('layouts.app')
@section('title', 'Detail File')

@section('content')
@php $p = auth()->user()->routePrefix(); @endphp
<div class="row">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header">Detail File</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted fw-normal">Nama File</dt><dd class="col-sm-8 text-break">{{ $file->original_name }}</dd>
                    <dt class="col-sm-4 text-muted fw-normal">Ukuran File</dt><dd class="col-sm-8">{{ $file->size_human }}</dd>
                    <dt class="col-sm-4 text-muted fw-normal">Tipe File</dt><dd class="col-sm-8">{{ $file->type_label }}</dd>
                    <dt class="col-sm-4 text-muted fw-normal">Status</dt><dd class="col-sm-8"><x-status-badge :status="$file->status" /></dd>
                    <dt class="col-sm-4 text-muted fw-normal">Tanggal</dt><dd class="col-sm-8">{{ $file->created_at_formatted }}</dd>
                    <dt class="col-sm-4 text-muted fw-normal">Pemilik</dt><dd class="col-sm-8">{{ $file->user->name }} ({{ $file->user->username }})</dd>
                </dl>
            </div>
            <div class="card-footer bg-white d-flex gap-2">
                <a href="{{ route($p . '.files.download', $file) }}" class="btn btn-primary"><i class="bi bi-download me-1"></i> Download</a>
                <a href="{{ route($p . '.files.index') }}" class="btn btn-light border">Kembali</a>
            </div>
        </div>
    </div>
</div>
@endsection
