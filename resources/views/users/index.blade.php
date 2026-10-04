@extends('layouts.app')
@section('title', 'Manajemen User')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Cari nama / username</label>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Role</label>
                <select name="role" class="form-select">
                    <option value="">Semua</option>
                    <option value="administrator" @selected(request('role') === 'administrator')>Administrator</option>
                    <option value="user" @selected(request('role') === 'user')>User</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="active" @selected(request('status') === 'active')>Aktif</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Terapkan</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-light border">Reset</a>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary ms-auto"><i class="bi bi-person-plus me-1"></i> Tambah User</a>
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
                    <th><x-sort-link column="name" label="Nama" /></th>
                    <th><x-sort-link column="username" label="Username/NIP" /></th>
                    <th><x-sort-link column="role" label="Role" /></th>
                    <th><x-sort-link column="status" label="Status" /></th>
                    <th><x-sort-link column="created_at" label="Dibuat" /></th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                    <tr>
                        <td>{{ $users->firstItem() + $loop->index }}</td>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->username }}</td>
                        <td>{{ $u->role_label }}</td>
                        <td><x-status-badge :status="$u->status" /></td>
                        <td class="text-nowrap">{{ \App\Support\Format::dateTime($u->created_at) }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-light border" title="Edit"><i class="bi bi-pencil"></i></a>
                            @if ($u->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.toggle', $u) }}" class="d-inline"
                                      data-confirm="{{ $u->isActive() ? 'Nonaktifkan' : 'Aktifkan' }} user &quot;{{ $u->name }}&quot;?"
                                      data-confirm-title="{{ $u->isActive() ? 'Nonaktifkan User' : 'Aktifkan User' }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-light border" title="{{ $u->isActive() ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="bi {{ $u->isActive() ? 'bi-person-slash' : 'bi-person-check' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="d-inline"
                                      data-confirm="Hapus user &quot;{{ $u->name }}&quot;? Seluruh file dan riwayat milik user ini juga akan dihapus."
                                      data-confirm-title="Hapus User" data-confirm-ok="Ya, hapus">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light border text-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5">Tidak ada user.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())
        <div class="card-footer bg-white">{{ $users->links() }}</div>
    @endif
</div>
@endsection
