@props(['column', 'label'])
@php
    $active = request('sort') === $column;
    $dir = $active && request('dir') === 'asc' ? 'desc' : 'asc';
@endphp
<a href="{{ request()->fullUrlWithQuery(['sort' => $column, 'dir' => $dir, 'page' => null]) }}" class="text-decoration-none text-reset">
    {{ $label }}
    @if ($active)
        <i class="bi bi-caret-{{ request('dir') === 'asc' ? 'up' : 'down' }}-fill small"></i>
    @endif
</a>
