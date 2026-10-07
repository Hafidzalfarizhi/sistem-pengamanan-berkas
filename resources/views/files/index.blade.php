@extends('layouts.app')
@section('title', 'File Saya')

@section('content')
@php $p = auth()->user()->routePrefix(); @endphp

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Cari nama file</label>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="mis. laporan">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="encrypted" @selected(request('status') === 'encrypted')>Terenkripsi</option>
                    <option value="decrypted" @selected(request('status') === 'decrypted')>Terdekripsi</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Tipe</label>
                <select name="type" class="form-select">
                    <option value="">Semua</option>
                    @foreach ($types as $type)
                        <option value="{{ $type }}" @selected(request('type') === $type)>{{ strtoupper($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Terapkan</button>
                <a href="{{ route($p . '.files.index') }}" class="btn btn-light border">Reset</a>
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
                    <th><x-sort-link column="original_name" label="Nama File" /></th>
                    <th><x-sort-link column="original_size" label="Ukuran" /></th>
                    <th><x-sort-link column="original_extension" label="Tipe File" /></th>
                    <th><x-sort-link column="status" label="Status" /></th>
                    <th><x-sort-link column="created_at" label="Tanggal" /></th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($files as $file)
                    <tr>
                        <td>{{ $files->firstItem() + $loop->index }}</td>
                        <td class="text-break">{{ $file->original_name }}</td>
                        <td class="text-nowrap">{{ $file->size_human }}</td>
                        <td>{{ $file->type_label }}</td>
                        <td><x-status-badge :status="$file->status" /></td>
                        <td class="text-nowrap">{{ $file->created_at_formatted }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route($p . '.files.show', $file) }}" class="btn btn-sm btn-light border" title="Detail"><i class="bi bi-eye"></i></a>
                            <a href="{{ route($p . '.files.download', $file) }}" class="btn btn-sm btn-light border" title="Download"><i class="bi bi-download"></i></a>
                            <form method="POST" action="{{ route($p . '.files.destroy', $file) }}" class="d-inline"
                                  data-confirm="Hapus file &quot;{{ $file->original_name }}&quot;? Tindakan ini tidak dapat dibatalkan." data-confirm-title="Hapus File" data-confirm-ok="Ya, hapus">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light border text-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5">Belum ada file.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($files->hasPages())
        <div class="card-footer bg-white">{{ $files->links() }}</div>
    @endif
</div>
@endsection
