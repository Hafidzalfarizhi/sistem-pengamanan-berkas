@extends('layouts.app')
@section('title', auth()->user()->isAdministrator() ? 'Dashboard Administrator' : 'Dashboard')

@section('content')
@php $me = auth()->user(); $p = $me->routePrefix(); @endphp

<div class="row g-3 mb-4">
    @foreach ($stats as $label => $stat)
        <div class="col-6 col-xl-4">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon"><i class="bi {{ $stat['icon'] }}"></i></div>
                    <div>
                        <div class="value">{{ number_format($stat['value'], 0, ',', '.') }}</div>
                        <div class="label">{{ $label }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-md-{{ $me->isAdministrator() ? '4' : '6' }}">
        <a class="quick-action" href="{{ route($p . '.encrypt.index') }}"><i class="bi bi-file-earmark-lock2"></i><div><div class="fw-semibold">Enkripsi File</div><small class="text-muted">Kunci file dengan password</small></div></a>
    </div>
    <div class="col-md-{{ $me->isAdministrator() ? '4' : '6' }}">
        <a class="quick-action" href="{{ route($p . '.decrypt.index') }}"><i class="bi bi-unlock"></i><div><div class="fw-semibold">Dekripsi File</div><small class="text-muted">Buka kembali file terenkripsi</small></div></a>
    </div>
    @if ($me->isAdministrator())
        <div class="col-md-4">
            <a class="quick-action" href="{{ route('admin.users.create') }}"><i class="bi bi-person-plus"></i><div><div class="fw-semibold">Tambah User</div><small class="text-muted">Buat akun pengguna baru</small></div></a>
        </div>
    @endif
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Aktivitas Terbaru</span>
        <a href="{{ route($p . '.history.index') }}" class="btn btn-sm btn-outline-primary">Lihat semua</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    @if ($me->isAdministrator())<th>Pengguna</th>@endif
                    <th>Aktivitas</th><th>Nama File</th><th>Ukuran</th><th>Status</th><th>Tanggal &amp; Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recent as $log)
                    <tr>
                        @if ($me->isAdministrator())<td>{{ $log->user->name ?? '-' }}</td>@endif
                        <td>{{ $log->activity }}</td>
                        <td>{{ $log->file_name ?? '-' }}</td>
                        <td>{{ $log->size_human }}</td>
                        <td><x-status-badge :status="$log->status" /></td>
                        <td class="text-nowrap">{{ $log->created_at_formatted }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada aktivitas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
