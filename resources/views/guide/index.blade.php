@extends('layouts.app')
@section('title', 'Panduan Penggunaan')

@section('content')
@php
    $isAdmin = auth()->user()->isAdministrator();
    $maxLabel = \App\Support\Format::bytes(config('securefile.max_file_size_kb') * 1024);

    $sections = [
        ['Login', 'bi-box-arrow-in-right', [
            'Buka halaman login, lalu isi Username/NIP dan Password akun Anda.',
            'Klik tombol Login. Anda akan diarahkan ke dashboard sesuai peran akun.',
            'Akun dibuat oleh Administrator. Hubungi Administrator bila Anda belum memiliki akun atau lupa password.',
        ]],
        ['Enkripsi File', 'bi-file-earmark-lock2', [
            'Buka menu Enkripsi File.',
            'Pilih file (klik area unggah atau tarik file ke dalamnya). Format yang didukung: TXT, PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG dengan ukuran maksimum ' . $maxLabel . '.',
            'Isi Password/Kunci dan konfirmasinya. Ingat baik-baik password ini karena tidak dapat dipulihkan.',
            'Klik Enkripsi dan tunggu hingga proses selesai.',
            'Klik Download untuk menyimpan file terenkripsi (berekstensi .' . config('securefile.encrypted_extension') . ').',
        ]],
        ['Dekripsi File', 'bi-unlock', [
            'Buka menu Dekripsi File.',
            'Pilih file terenkripsi (.' . config('securefile.encrypted_extension') . ') dan isi Password/Kunci yang dipakai saat enkripsi.',
            'Klik Dekripsi dan tunggu hingga proses selesai.',
            'Klik Download untuk menyimpan file hasil dekripsi.',
            'Bila muncul pesan "Password atau kunci tidak valid.", periksa kembali password Anda.',
        ]],
        ['File Saya', 'bi-folder2-open', [
            'Menu ini menampilkan file yang pernah Anda enkripsi atau dekripsi. Anda hanya melihat file milik Anda sendiri.',
            'Gunakan kolom pencarian, filter status/tipe, dan klik judul kolom untuk mengurutkan.',
            'Tombol Detail menampilkan informasi file, Download mengunduh file, dan Hapus menghapus file setelah konfirmasi.',
        ]],
        [$isAdmin ? 'Riwayat Aktivitas' : 'Riwayat', 'bi-clock-history', $isAdmin ? [
            'Menu ini menampilkan aktivitas penting seluruh pengguna beserta tanggal dan waktu lengkap.',
            'Gunakan filter pengguna, aktivitas, status, nama file, serta rentang tanggal untuk mempersempit data.',
            'Klik Detail untuk melihat keterangan lengkap suatu aktivitas.',
        ] : [
            'Menu ini menampilkan aktivitas Anda sendiri beserta ukuran file, status, serta tanggal dan waktu.',
            'Gunakan filter dan pencarian untuk menemukan aktivitas tertentu, lalu klik Detail untuk keterangan lengkap.',
        ]],
    ];

    if ($isAdmin) {
        $sections[] = ['Manajemen User', 'bi-people', [
            'Buka menu Manajemen User untuk melihat, mencari, dan memfilter daftar pengguna.',
            'Klik Tambah User, isi nama, username/NIP, password, role, dan status, lalu simpan. Tidak ada registrasi mandiri.',
            'Gunakan tombol Edit untuk mengubah data, tombol Aktifkan/Nonaktifkan untuk mengatur akses login, dan tombol Hapus (dengan konfirmasi) untuk menghapus user beserta file dan riwayatnya.',
            'Anda tidak dapat menghapus atau menonaktifkan akun Anda sendiri.',
        ]];
    }

    $sections[] = ['Notifikasi', 'bi-bell', [
        'Notifikasi memberi tahu hasil proses seperti enkripsi, dekripsi, dan perubahan data.',
        'Klik tanda centang untuk menandai satu notifikasi dibaca, atau gunakan "Tandai semua dibaca".',
    ]];
    $sections[] = ['Profil', 'bi-person-circle', [
        'Menu Profil menampilkan nama, username/NIP, role, dan status akun.',
        'Anda dapat mengubah nama dan username/NIP, serta mengganti password akun (isi password saat ini lalu password baru).',
        'Password akun berbeda dengan password/kunci untuk file.',
    ]];
    $sections[] = ['Logout', 'bi-box-arrow-left', [
        'Klik Logout di bagian bawah sidebar setelah selesai agar sesi Anda berakhir dengan aman.',
    ]];
@endphp

<div class="row">
    <div class="col-xl-9">
        <div class="accordion" id="guideAccordion">
            @foreach ($sections as $i => [$title, $icon, $steps])
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#guide{{ $i }}">
                            <i class="bi {{ $icon }} me-2 text-primary"></i> {{ $title }}
                        </button>
                    </h2>
                    <div id="guide{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#guideAccordion">
                        <div class="accordion-body">
                            <ol class="mb-0">
                                @foreach ($steps as $step)<li class="mb-1">{{ $step }}</li>@endforeach
                            </ol>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
