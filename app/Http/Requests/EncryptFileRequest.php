<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class EncryptFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:' . config('securefile.max_file_size_kb'),
                'extensions:' . implode(',', config('securefile.allowed_extensions')),
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! $value instanceof UploadedFile) {
                        return;
                    }
                    $extension = strtolower($value->getClientOriginalExtension());
                    $allowed = config('securefile.allowed_mimes.' . $extension, []);
                    if (! in_array($value->getMimeType(), $allowed, true)) {
                        $fail('Format file tidak didukung.');
                    }
                },
            ],
            'file_password' => [
                'required', 'string',
                'min:' . config('securefile.min_file_password_length'),
                'max:128', 'confirmed',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File wajib dipilih.',
            'file.file' => 'Format file tidak didukung.',
            'file.uploaded' => 'Ukuran file melebihi batas yang diperbolehkan.',
            'file.max' => 'Ukuran file melebihi batas yang diperbolehkan.',
            'file.extensions' => 'Format file tidak didukung.',
            'file_password.required' => 'Password/kunci wajib diisi.',
            'file_password.min' => 'Password/kunci minimal :min karakter.',
            'file_password.confirmed' => 'Konfirmasi password/kunci tidak sama.',
        ];
    }
}
