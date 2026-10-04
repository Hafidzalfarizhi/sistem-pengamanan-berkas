<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    @vite(['resources/js/app.js'])
</head>
<body>
    @include('partials.sidebar')

    <div class="main">
        <header class="topbar">
            <button class="btn btn-light border d-lg-none" type="button" data-sidebar-toggle aria-label="Menu"><i class="bi bi-list"></i></button>
            <h1>@yield('title', 'Dashboard')</h1>
            <div class="ms-auto d-flex align-items-center gap-3">
                <a href="{{ route(auth()->user()->routeName('notifications.index')) }}" class="btn btn-light border position-relative" aria-label="Notifikasi">
                    <i class="bi bi-bell"></i>
                    @if (($unreadCount ?? 0) > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-warning">{{ $unreadCount }}</span>
                    @endif
                </a>
                <div class="text-end d-none d-sm-block lh-sm">
                    <div class="fw-semibold small">{{ auth()->user()->name }}</div>
                    <span class="badge text-bg-{{ auth()->user()->isAdministrator() ? 'primary' : 'secondary' }}">{{ auth()->user()->role_label }}</span>
                </div>
            </div>
        </header>

        <main class="content">
            @yield('content')
        </main>
    </div>

    {{-- Toast --}}
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2100">
        @foreach (['success' => ['success', 'bi-check-circle-fill'], 'error' => ['danger', 'bi-x-circle-fill'], 'info' => ['info', 'bi-info-circle-fill']] as $key => [$color, $icon])
            @if (session($key))
                <div class="toast align-items-center text-bg-{{ $color }} border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body"><i class="bi {{ $icon }} me-2"></i>{{ session($key) }}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    {{-- Dialog konfirmasi --}}
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" data-confirm-title>Konfirmasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body" data-confirm-message></div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" data-confirm-ok>Ya, lanjutkan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading --}}
    <div class="loading-overlay" id="loadingOverlay">
        <div>
            <div class="spinner-border mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
            <h5 class="mb-1">Memproses file...</h5>
            <p class="mb-0 opacity-75">Mohon tunggu dan jangan menutup halaman ini.<br>Waktu proses bergantung pada ukuran file.</p>
        </div>
    </div>
</body>
</html>
