{{-- Variabel: $accept (string), $hint (string) --}}
<div class="dropzone @error('file') border-danger @enderror" data-dropzone>
    <input type="file" name="file" id="file" accept="{{ $accept }}" required>
    <div data-file-empty>
        <i class="bi bi-cloud-arrow-up big-icon"></i>
        <div class="fw-semibold mt-2">Tarik file ke sini atau klik untuk memilih</div>
        <div class="text-muted small">{{ $hint }}</div>
    </div>
    <div data-file-info class="d-none">
        <i class="bi bi-file-earmark-check big-icon"></i>
        <div class="fw-semibold mt-2" data-file-name></div>
        <div class="text-muted small">Ukuran: <span data-file-size></span> &middot; Tipe: <span data-file-type></span></div>
        <div class="text-muted small mt-1">Klik atau tarik file lain untuk mengganti.</div>
    </div>
</div>
@error('file')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
