@extends('layouts.app')
@section('title', 'Profil')

@section('content')
@php $p = $user->routePrefix(); @endphp
<div class="row g-3">
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">Informasi Akun</div>
            <div class="card-body">
                <dl class="mb-0">
                    <dt class="text-muted fw-normal small">Nama</dt><dd>{{ $user->name }}</dd>
                    <dt class="text-muted fw-normal small">Username/NIP</dt><dd>{{ $user->username }}</dd>
                    <dt class="text-muted fw-normal small">Role</dt><dd>{{ $user->role_label }}</dd>
                    <dt class="text-muted fw-normal small">Status</dt><dd class="mb-0"><x-status-badge :status="$user->status" /></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card mb-3">
            <div class="card-header">Edit Profil</div>
            <div class="card-body">
                <form method="POST" action="{{ route($p . '.profile.update') }}" novalidate>
                    @csrf @method('PUT')
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nama</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required maxlength="100">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username/NIP</label>
                            <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" class="form-control @error('username') is-invalid @enderror" required maxlength="50">
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <button class="btn btn-primary">Simpan Profil</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Ubah Password</div>
            <div class="card-body">
                <p class="text-muted small">Password akun ini berbeda dengan password/kunci yang Anda gunakan untuk mengenkripsi file.</p>
                <form method="POST" action="{{ route($p . '.profile.password') }}" novalidate>
                    @csrf @method('PUT')
                    <div class="mb-3" style="max-width: 420px;">
                        <label for="current_password" class="form-label">Password Saat Ini</label>
                        <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="new_password" class="form-label">Password Baru</label>
                            <input type="password" id="new_password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                        </div>
                    </div>
                    <button class="btn btn-primary">Ubah Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
