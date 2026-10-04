@extends('layouts.guest')
@section('title', 'Login')

@section('content')
<div class="auth-wrap">
    <section class="auth-side">
        <div class="d-flex align-items-center gap-3">
            <span class="d-inline-grid" style="width:42px;height:42px;border-radius:11px;display:inline-grid;place-items:center;background:var(--sfl-brass);color:#fff;font-size:1.25rem"><i class="bi bi-lock-fill"></i></span>
            <span class="text-white fw-semibold fs-5">{{ config('app.name') }}</span>
        </div>
        <div>
            <h2>Kunci file Anda sebelum dibagikan.</h2>
            <p class="mt-3 mb-0" style="max-width: 38ch;">Enkripsi dan dekripsi dokumen serta gambar dengan password milik Anda sendiri.</p>
        </div>
    </section>

    <section class="auth-form">
        <div class="box">
            <h1 class="h4 fw-semibold mb-1">Masuk</h1>
            <p class="text-muted mb-4">Gunakan akun yang diberikan Administrator.</p>

            <form method="POST" action="{{ route('login.attempt') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="username" class="form-label">Username/NIP</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}"
                           class="form-control form-control-lg @error('username') is-invalid @enderror"
                           autocomplete="username" autofocus required>
                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group input-group-lg has-validation">
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               autocomplete="current-password" required>
                        <button class="btn btn-outline-secondary" type="button" data-toggle-password="#password" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">Login</button>
            </form>
        </div>
    </section>
</div>
@endsection
