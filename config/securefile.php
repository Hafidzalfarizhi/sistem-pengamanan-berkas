<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ukuran maksimum file (dalam KB)
    |--------------------------------------------------------------------------
    | Diambil dari .env agar mudah disesuaikan tanpa mengubah kode.
    */
    'max_file_size_kb' => (int) env('SECUREFILE_MAX_SIZE_KB', 2048),

    /*
    |--------------------------------------------------------------------------
    | Ekstensi file yang didukung untuk proses enkripsi
    |--------------------------------------------------------------------------
    */
    'allowed_extensions' => [
        'txt', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png',
    ],

    /*
    |--------------------------------------------------------------------------
    | MIME type yang diperbolehkan untuk setiap ekstensi
    |--------------------------------------------------------------------------
    | Dokumen Office modern (docx/xlsx) pada praktiknya dapat terdeteksi
    | sebagai application/zip, sedangkan dokumen lama (doc/xls) kadang
    | terdeteksi sebagai application/octet-stream atau CDF.
    */
    'allowed_mimes' => [
        'txt'  => ['text/plain'],
        'pdf'  => ['application/pdf'],
        'doc'  => ['application/msword', 'application/vnd.ms-office', 'application/x-cfb', 'application/octet-stream'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'xls'  => ['application/vnd.ms-excel', 'application/vnd.ms-office', 'application/x-cfb', 'application/octet-stream'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ekstensi untuk file hasil enkripsi
    |--------------------------------------------------------------------------
    */
    'encrypted_extension' => 'sfl',

    /*
    |--------------------------------------------------------------------------
    | Direktori penyimpanan (relatif terhadap disk "local" = storage/app/private)
    |--------------------------------------------------------------------------
    */
    'disk' => 'local',

    'directories' => [
        'encrypted' => 'enkripsi',
        'decrypted' => 'dekripsi',
        'temporary' => 'sementara',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Ukuran blok baca file (byte) untuk pemrosesan bertahap
    |--------------------------------------------------------------------------
    */
    'chunk_size' => 8192,

    'min_file_password_length' => 6,

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'per_page' => 10,
];
