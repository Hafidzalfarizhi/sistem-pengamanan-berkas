@extends('layouts.app')
@section('title', 'Detail Aktivitas')

@section('content')
@php $me = auth()->user(); $p = $me->routePrefix(); @endphp
<div class="row">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header">Detail Aktivitas</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted fw-normal">Pengguna</dt><dd class="col-sm-8">{{ $log->user->name ?? '-' }}</dd>
                    <dt class="col-sm-4 text-muted fw-normal">Aktivitas</dt><dd class="col-sm-8">{{ $log->activity }}</dd>
                    <dt class="col-sm-4 text-muted fw-normal">Nama File</dt><dd class="col-sm-8 text-break">{{ $log->file_name ?? '-' }}</dd>
                    <dt class="col-sm-4 text-muted fw-normal">Ukuran File</dt><dd class="col-sm-8">{{ $log->size_human }}</dd>
                    <dt class="col-sm-4 text-muted fw-normal">Status</dt><dd class="col-sm-8"><x-status-badge :status="$log->status" /></dd>
                    <dt class="col-sm-4 text-muted fw-normal">Deskripsi</dt><dd class="col-sm-8">{{ $log->description ?? '-' }}</dd>
                    <dt class="col-sm-4 text-muted fw-normal">Tanggal</dt><dd class="col-sm-8">{{ \App\Support\Format::dateLong($log->created_at) }}</dd>
                    <dt class="col-sm-4 text-muted fw-normal">Waktu</dt><dd class="col-sm-8">{{ \App\Support\Format::timeWib($log->created_at) }}</dd>
                    @if ($me->isAdministrator())
                        <dt class="col-sm-4 text-muted fw-normal">IP Address</dt><dd class="col-sm-8">{{ $log->ip_address ?? '-' }}</dd>
                    @endif
                </dl>
            </div>
            <div class="card-footer bg-white">
                <a href="{{ route($p . '.history.index') }}" class="btn btn-light border">Kembali</a>
            </div>
        </div>
    </div>
</div>
@endsection
