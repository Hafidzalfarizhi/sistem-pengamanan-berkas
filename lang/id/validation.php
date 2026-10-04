<?php

return [
    'required' => ':attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'confirmed' => 'Konfirmasi :attribute tidak sama.',
    'unique' => ':attribute sudah digunakan.',
    'in' => ':attribute tidak valid.',
    'regex' => 'Format :attribute tidak valid.',
    'different' => ':attribute dan :other harus berbeda.',
    'file' => ':attribute harus berupa file.',
    'extensions' => 'Format file tidak didukung.',
    'max' => [
        'string' => ':attribute maksimal :max karakter.',
        'file' => 'Ukuran file melebihi batas yang diperbolehkan.',
    ],
    'min' => [
        'string' => ':attribute minimal :min karakter.',
        'file' => ':attribute minimal :min KB.',
    ],
    'custom' => [],
    'attributes' => [
        'name' => 'Nama',
        'username' => 'Username/NIP',
        'password' => 'Password',
        'current_password' => 'Password saat ini',
        'role' => 'Role',
        'status' => 'Status',
        'file' => 'File',
        'file_password' => 'Password/kunci',
    ],
];
