@extends('layouts.app')
@section('title', 'Notifikasi')

@section('content')
@php $p = auth()->user()->routePrefix(); $icons = ['info' => ['info', 'bi-info-circle-fill'], 'success' => ['success', 'bi-check-circle-fill'], 'warning' => ['warning', 'bi-exclamation-triangle-fill'], 'error' => ['danger', 'bi-x-circle-fill']]; @endphp

<div class="d-flex flex-wrap gap-2 mb-3">
    <div class="btn-group">
        <a href="{{ route($p . '.notifications.index') }}" class="btn btn-sm {{ request('filter') !== 'unread' ? 'btn-primary' : 'btn-light border' }}">Semua</a>
        <a href="{{ route($p . '.notifications.index', ['filter' => 'unread']) }}" class="btn btn-sm {{ request('filter') === 'unread' ? 'btn-primary' : 'btn-light border' }}">Belum dibaca</a>
    </div>
    <form method="POST" action="{{ route($p . '.notifications.read-all') }}" class="ms-auto">
        @csrf
        <button class="btn btn-sm btn-light border"><i class="bi bi-check2-all me-1"></i> Tandai semua dibaca</button>
    </form>
</div>

<div class="card">
    <div class="list-group list-group-flush">
        @forelse ($notifications as $n)
            @php [$color, $icon] = $icons[$n->type] ?? $icons['info']; @endphp
            <div class="list-group-item d-flex gap-3 align-items-start py-3 {{ $n->is_read ? '' : 'bg-light' }}">
                <i class="bi {{ $icon }} text-{{ $color }} fs-5"></i>
                <div class="flex-grow-1">
                    <div class="fw-semibold">{{ $n->title }} @unless ($n->is_read)<span class="badge text-bg-warning ms-1">Baru</span>@endunless</div>
                    <div>{{ $n->message }}</div>
                    <small class="text-muted">{{ $n->created_at_formatted }}</small>
                </div>
                @unless ($n->is_read)
                    <form method="POST" action="{{ route($p . '.notifications.read', $n) }}">
                        @csrf
                        <button class="btn btn-sm btn-light border" title="Tandai dibaca"><i class="bi bi-check2"></i></button>
                    </form>
                @endunless
            </div>
        @empty
            <div class="list-group-item text-center text-muted py-5">Tidak ada notifikasi.</div>
        @endforelse
    </div>
    @if ($notifications->hasPages())
        <div class="card-footer bg-white">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
