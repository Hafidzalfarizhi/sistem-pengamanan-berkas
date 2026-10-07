@extends('layouts.app')
@section('title', 'Dekripsi File')

@section('content')
@php $p = auth()->user()->routePrefix(); @endphp

@if ($result)
    @include('partials.file-result', ['result' => $result, 'title' => 'File berhasil didekripsi'])
@endif

@if ($errors->has('process'))
    <div class="alert alert-danger"><i class="bi bi-x-circle-fill me-2"></i>{{ $errors->first('process') }}</div>
@endif

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">Dekripsi File</div>
            <div class="card-body">
                <form method="POST" action="{{ route($p . '.decrypt.store') }}" enctype="multipart/form-data" data-loading>
                    @csrf

                    <label class="form-label">File Terenkripsi</label>
                    @include('partials.dropzone', [
                        'accept' => '.' . config('securefile.encrypted_extension'),
                        'hint' => 'Pilih file berekstensi .' . config('securefile.encrypted_extension') . ' hasil enkripsi aplikasi ini.',
                    ])

                    <div class="mt-3 mb-4" style="max-width: 420px;">
                        <label for="file_password" class="form-label">Password/Kunci</label>
                        <div class="input-group has-validation">
                            <input type="password" id="file_password" name="file_password" class="form-control @error('file_password') is-invalid @enderror" autocomplete="off" required>
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="#file_password" aria-label="Tampilkan"><i class="bi bi-eye"></i></button>
                            @error('file_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="bi bi-unlock me-1"></i> Dekripsi</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
