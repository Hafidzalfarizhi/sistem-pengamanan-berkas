@extends('layouts.app')
@section('title', 'Uji Tampilan')

@section('content')
<div class="row g-3 mb-4">
    @foreach ([['Total File', 12, 'bi-files'], ['File Terenkripsi', 8, 'bi-file-earmark-lock2'], ['File Terdekripsi', 4, 'bi-file-earmark-check']] as [$label, $value, $icon])
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon"><i class="bi {{ $icon }}"></i></div>
                    <div>
                        <div class="value">{{ $value }}</div>
                        <div class="label">{{ $label }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card mb-4">
    <div class="card-header">Badge status</div>
    <div class="card-body d-flex flex-wrap gap-2">
        @foreach (['success', 'failed', 'active', 'inactive', 'encrypted', 'decrypted'] as $status)
            <x-status-badge :status="$status" />
        @endforeach
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Tabel dengan kolom yang bisa diurutkan</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th><x-sort-link column="name" label="Nama File" /></th>
                    <th><x-sort-link column="size" label="Ukuran" /></th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>1</td><td>laporan.pdf</td><td>{{ \App\Support\Format::bytes(2578432) }}</td><td><x-status-badge status="encrypted" /></td></tr>
                <tr><td>2</td><td>data.xlsx</td><td>{{ \App\Support\Format::bytes(48200) }}</td><td><x-status-badge status="decrypted" /></td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Uji komponen interaktif</div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="/uji-toast" class="btn btn-outline-primary">Uji Toast</a>

            <form method="POST" action="/uji-konfirmasi" class="d-inline"
                  data-confirm="Yakin ingin melanjutkan?" data-confirm-title="Uji Konfirmasi" data-confirm-ok="Ya, kirim">
                @csrf
                <button class="btn btn-danger">Uji Dialog Konfirmasi</button>
            </form>

            <form method="POST" action="/uji-loading" class="d-inline" data-loading>
                @csrf
                <button type="submit" class="btn btn-primary">Uji Loading (3 detik)</button>
            </form>
        </div>

        <label class="form-label" for="uji_password">Uji tombol lihat password</label>
        <div class="input-group" style="max-width: 360px;">
            <input type="password" id="uji_password" class="form-control" value="rahasia123">
            <button class="btn btn-outline-secondary" type="button" data-toggle-password="#uji_password" aria-label="Tampilkan"><i class="bi bi-eye"></i></button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Pintasan uji</div>
    <div class="card-body">
        <p class="mb-2">Tampilan sebagai:
            <a href="/uji-tampilan?role=administrator">Administrator</a> |
            <a href="/uji-tampilan?role=user">User</a>
        </p>
        <p class="mb-0">Halaman error:
            @foreach ([403, 404, 419, 429, 500, 503] as $code)
                <a href="/uji-error/{{ $code }}">{{ $code }}</a>@if (! $loop->last) | @endif
            @endforeach
        </p>
    </div>
</div>
@endsection
