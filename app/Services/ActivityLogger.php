<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\File;
use App\Models\User;

class ActivityLogger
{
    /** Daftar aktivitas penting (dipakai untuk filter). */
    public const ACTIVITIES = [
        'Login', 'Logout', 'Enkripsi File', 'Dekripsi File', 'Upload File', 'Download File',
        'Hapus File', 'Tambah User', 'Edit User', 'Hapus User', 'Aktifkan User',
        'Nonaktifkan User', 'Ubah Password',
    ];

    public function record(
        User|int $user,
        string $activity,
        string $status = 'success',
        ?File $file = null,
        ?string $fileName = null,
        ?int $fileSize = null,
        ?string $description = null,
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $user instanceof User ? $user->id : $user,
            'file_id' => $file?->id,
            'activity' => $activity,
            'file_name' => $fileName ?? $file?->original_name,
            'file_size' => $fileSize ?? $file?->original_size,
            'status' => $status,
            'description' => $description,
            'ip_address' => request()?->ip(),
        ]);
    }
}
