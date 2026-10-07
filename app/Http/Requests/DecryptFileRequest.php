<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DecryptFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Ukuran file terenkripsi = isi + header (< 1 KB)
            'file' => [
                'required', 'file',
                'max:' . (config('securefile.max_file_size_kb') + 4),
                'extensions:' . config('securefile.encrypted_extension'),
            ],
            'file_password' => ['required', 'string', 'max:128'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File terenkripsi wajib dipilih.',
            'file.file' => 'File tidak dapat didekripsi.',
            'file.uploaded' => 'Ukuran file melebihi batas yang diperbolehkan.',
            'file.max' => 'Ukuran file melebihi batas yang diperbolehkan.',
            'file.extensions' => 'Format file tidak didukung. Pilih file berekstensi .' . config('securefile.encrypted_extension') . '.',
            'file_password.required' => 'Password/kunci wajib diisi.',
        ];
    }
}
