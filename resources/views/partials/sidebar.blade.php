@php
    $me = auth()->user();
    $p = $me->routePrefix();
    $isAdmin = $me->isAdministrator();
    $items = [
        ['Dashboard', $p . '.dashboard', 'bi-grid-1x2', $p . '.dashboard'],
        ['Enkripsi File', $p . '.encrypt.index', 'bi-file-earmark-lock2', $p . '.encrypt.*'],
        ['Dekripsi File', $p . '.decrypt.index', 'bi-unlock', $p . '.decrypt.*'],
        ['File Saya', $p . '.files.index', 'bi-folder2-open', $p . '.files.*'],
        [$isAdmin ? 'Riwayat Aktivitas' : 'Riwayat', $p . '.history.index', 'bi-clock-history', $p . '.history.*'],
    ];
@endphp
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand d-flex align-items-center gap-3">
        <span class="mark"><i class="bi bi-lock-fill"></i></span>
        <div>
            <div class="name">{{ config('app.name') }}</div>
            <small>{{ $isAdmin ? 'Administrator' : 'User' }}</small>
        </div>
    </div>

    <nav>
        <div class="sidebar-label">Menu Utama</div>
        @foreach ($items as [$label, $routeName, $icon, $pattern])
            <a class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($routeName) }}">
                <i class="bi {{ $icon }}"></i> {{ $label }}
            </a>
        @endforeach

        @if ($isAdmin)
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                <i class="bi bi-people"></i> Manajemen User
            </a>
        @endif

        <a class="nav-link {{ request()->routeIs($p . '.notifications.*') ? 'active' : '' }}" href="{{ route($p . '.notifications.index') }}">
            <i class="bi bi-bell"></i> Notifikasi
            @if (($unreadCount ?? 0) > 0)
                <span class="badge rounded-pill text-bg-warning ms-auto">{{ $unreadCount }}</span>
            @endif
        </a>
        <a class="nav-link {{ request()->routeIs($p . '.guide') ? 'active' : '' }}" href="{{ route($p . '.guide') }}">
            <i class="bi bi-journal-text"></i> Panduan Penggunaan
        </a>
        <a class="nav-link {{ request()->routeIs($p . '.profile.*') ? 'active' : '' }}" href="{{ route($p . '.profile.edit') }}">
            <i class="bi bi-person-circle"></i> Profil
        </a>

        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</button>
        </form>
    </nav>

    <div class="sidebar-foot">{{ $me->name }}<br><span class="opacity-75">{{ $me->username }}</span></div>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
