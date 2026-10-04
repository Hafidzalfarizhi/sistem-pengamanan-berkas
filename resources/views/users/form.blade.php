@extends('layouts.app')
@section('title', $managed->exists ? 'Edit User' : 'Tambah User')

@section('content')
@php $editing = $managed->exists; $self = $editing && $managed->id === auth()->id(); @endphp
<div class="row">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header">{{ $editing ? 'Edit User' : 'Tambah User' }}</div>
            <div class="card-body">
                <form method="POST" action="{{ $editing ? route('admin.users.update', $managed) : route('admin.users.store') }}" novalidate>
                    @csrf
                    @if ($editing) @method('PUT') @endif

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $managed->name) }}" class="form-control @error('name') is-invalid @enderror" required maxlength="100">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username/NIP</label>
                        <input type="text" id="username" name="username" value="{{ old('username', $managed->username) }}" class="form-control @error('username') is-invalid @enderror" required maxlength="50" autocomplete="off">
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password @if ($editing)<small class="text-muted">(kosongkan bila tidak diubah)</small>@endif</label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" @if(!$editing) required @endif>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" @if(!$editing) required @endif>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="role" class="form-label">Role</label>
                            <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" @disabled($self)>
                                <option value="user" @selected(old('role', $managed->role) === 'user')>User</option>
                                <option value="administrator" @selected(old('role', $managed->role) === 'administrator')>Administrator</option>
                            </select>
                            @if ($self)<input type="hidden" name="role" value="{{ $managed->role }}">@endif
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" @disabled($self)>
                                <option value="active" @selected(old('status', $managed->status) === 'active')>Aktif</option>
                                <option value="inactive" @selected(old('status', $managed->status) === 'inactive')>Nonaktif</option>
                            </select>
                            @if ($self)<input type="hidden" name="status" value="{{ $managed->status }}">@endif
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light border">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
