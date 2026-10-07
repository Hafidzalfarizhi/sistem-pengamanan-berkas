@extends('layouts.app')
@section('title', auth()->user()->isAdministrator() ? 'Riwayat Aktivitas' : 'Riwayat')

@section('content')
@php $me = auth()->user(); $p = $me->routePrefix(); $isAdmin = $me->isAdministrator(); @endphp

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Cari</label>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="aktivitas, file, keterangan{{ $isAdmin ? ', pengguna' : '' }}">
            </div>
            @if ($isAdmin)
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Pengguna</label>
                    <select name="user_id" class="form-select">
                        <option value="">Semua</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" @selected((string) request('user_id') === (string) $u->id)>{{ $u->name }} ({{ $u->username }})</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Aktivitas</label>
                <select name="activity" class="form-select">
                    <option value="">Semua</option>
                    @foreach ($activities as $a)
                        <option value="{{ $a }}" @selected(request('activity') === $a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="success" @selected(request('status') === 'success')>Berhasil</option>
                    <option value="failed" @selected(request('status') === 'failed')>Gagal</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-1">Nama file</label>
                <input type="text" name="file_name" value="{{ request('file_name') }}" class="form-control">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Tanggal mulai</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Tanggal akhir</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Terapkan</button>
                <a href="{{ route($p . '.history.index') }}" class="btn btn-light border">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:60px">No</th>
                    @if ($isAdmin)
                        <th>Pengguna</th>
                        <th><x-sort-link column="activity" label="Aktivitas" /></th>
                        <th><x-sort-link column="file_name" label="Nama File" /></th>
                    @else
                        <th><x-sort-link column="file_name" label="Nama File" /></th>
                        <th><x-sort-link column="activity" label="Aktivitas" /></th>
                    @endif
                    <th><x-sort-link column="file_size" label="Ukuran File" /></th>
                    <th><x-sort-link column="status" label="Status" /></th>
                    <th><x-sort-link column="created_at" label="Tanggal & Waktu" /></th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td>{{ $logs->firstItem() + $loop->index }}</td>
                        @if ($isAdmin)
                            <td>{{ $log->user->name ?? '-' }}</td>
                            <td>{{ $log->activity }}</td>
                            <td class="text-break">{{ $log->file_name ?? '-' }}</td>
                        @else
                            <td class="text-break">{{ $log->file_name ?? '-' }}</td>
                            <td>{{ $log->activity }}</td>
                        @endif
                        <td class="text-nowrap">{{ $log->size_human }}</td>
                        <td><x-status-badge :status="$log->status" /></td>
                        <td class="text-nowrap">{{ $log->created_at_formatted }}</td>
                        <td class="text-end"><a href="{{ route($p . '.history.show', $log) }}" class="btn btn-sm btn-light border" title="Detail"><i class="bi bi-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $isAdmin ? 8 : 7 }}" class="text-center text-muted py-5">Tidak ada aktivitas yang sesuai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($logs->hasPages())
        <div class="card-footer bg-white">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
