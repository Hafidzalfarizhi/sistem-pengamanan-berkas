{{-- Variabel: $result (File), $title --}}
<div class="card border-success mb-4">
    <div class="card-body">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-check-circle-fill text-success fs-2"></i>
            <div class="flex-grow-1">
                <h2 class="h6 fw-semibold mb-1">{{ $title }}</h2>
                <dl class="row small mb-3">
                    <dt class="col-sm-3 text-muted fw-normal">Nama file</dt><dd class="col-sm-9">{{ $result->original_name }}</dd>
                    <dt class="col-sm-3 text-muted fw-normal">Ukuran</dt><dd class="col-sm-9">{{ $result->size_human }}</dd>
                    <dt class="col-sm-3 text-muted fw-normal">Status</dt><dd class="col-sm-9"><x-status-badge :status="$result->status" /></dd>
                </dl>
                <a href="{{ route(auth()->user()->routeName('files.download'), $result) }}" class="btn btn-primary"><i class="bi bi-download me-1"></i> Download</a>
                <a href="{{ route(auth()->user()->routeName('files.index')) }}" class="btn btn-light border">Lihat di File Saya</a>
            </div>
        </div>
    </div>
</div>
