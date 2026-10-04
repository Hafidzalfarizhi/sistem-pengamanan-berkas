@props(['status'])
@php
    $map = [
        'success'   => ['success', 'Berhasil'],
        'failed'    => ['danger', 'Gagal'],
        'active'    => ['success', 'Aktif'],
        'inactive'  => ['secondary', 'Nonaktif'],
        'encrypted' => ['primary', 'Terenkripsi'],
        'decrypted' => ['info', 'Terdekripsi'],
    ];
    [$color, $label] = $map[$status] ?? ['secondary', $status];
@endphp
<span class="badge text-bg-{{ $color }}">{{ $label }}</span>
