@extends('layouts.app')
@section('title', 'Enkripsi File')

@section('content')
@php $p = auth()->user()->routePrefix(); $maxLabel = \App\Support\Format::bytes(config('securefile.max_file_size_kb') * 1024); @endphp

@if ($result)
    @include('partials.file-result', ['result' => $result, 'title' => 'File berhasil dienkripsi'])
@endif

@if ($errors->has('process'))
    <div class="alert alert-danger"><i class="bi bi-x-circle-fill me-2"></i>{{ $errors->first('process') }}</div>
@endif

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">Enkripsi File</div>
            <div class="card-body">
                <form method="POST" action="{{ route($p . '.encrypt.store') }}" enctype="multipart/form-data" data-loading>
                    @csrf

                    <label class="form-label">File</label>
                    @include('partials.dropzone', [
                        'accept' => '.txt,.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png',
                        'hint' => 'TXT, PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG. Maksimum ' . $maxLabel . '.',
                    ])

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label for="file_password" class="form-label">Password/Kunci</label>
                            <div class="input-group has-validation">
                                <input type="password" id="file_password" name="file_password" class="form-control @error('file_password') is-invalid @enderror" autocomplete="new-password" required minlength="{{ config('securefile.min_file_password_length') }}">
                                <button class="btn btn-outline-secondary" type="button" data-toggle-password="#file_password" aria-label="Tampilkan"><i class="bi bi-eye"></i></button>
                                @error('file_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="file_password_confirmation" class="form-label">Konfirmasi Password/Kunci</label>
                            <div class="input-group">
                                <input type="password" id="file_password_confirmation" name="file_password_confirmation" class="form-control" autocomplete="new-password" required>
                                <button class="btn btn-outline-secondary" type="button" data-toggle-password="#file_password_confirmation" aria-label="Tampilkan"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning small mt-4 mb-4">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Ingat password/kunci ini. Password tidak disimpan dan file <strong>tidak dapat dipulihkan</strong> bila password hilang.
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="bi bi-lock me-1"></i> Enkripsi</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
