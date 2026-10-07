<?php

namespace App\Http\Controllers;

use App\Http\Requests\DecryptFileRequest;
use App\Models\File;
use App\Services\ActivityLogger;
use App\Services\Cryptography\Exceptions\CorruptedDataException;
use App\Services\Cryptography\Exceptions\InvalidKeyException;
use App\Services\Cryptography\FileEncryptionService;
use App\Services\NotificationService;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class DecryptionController extends Controller
{
    public function __construct(
        private FileEncryptionService $crypto,
        private ActivityLogger $logger,
        private NotificationService $notifier,
    ) {
    }

    public function index(Request $request): View
    {
        $result = null;
        if ($id = session('result.file_id')) {
            $result = File::where('user_id', $request->user()->id)->find($id);
        }

        return view('decryption.index', ['result' => $result]);
    }

    public function store(DecryptFileRequest $request): RedirectResponse
    {
        @set_time_limit(0);

        $user = $request->user();
        $upload = $request->file('file');
        $uploadName = Format::safeFileName($upload->getClientOriginalName());
        $uploadSize = (int) $upload->getSize();

        $disk = Storage::disk(config('securefile.disk'));
        $directory = config('securefile.directories.decrypted');
        $uuid = (string) Str::uuid();
        $disk->makeDirectory($directory);

        $tempPath = $disk->path($directory . '/' . $uuid . '.tmp');
        $finalPath = null;

        try {
            $result = $this->crypto->decryptFile($upload->getRealPath(), $tempPath, (string) $request->input('file_password'));

            $name = Format::safeFileName($result['original_name']);
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            if (! in_array($extension, config('securefile.allowed_extensions'), true)) {
                throw new CorruptedDataException('Ekstensi file hasil tidak didukung.');
            }

            $storedName = $uuid . '.' . $extension;
            $relativePath = $directory . '/' . $storedName;
            $finalPath = $disk->path($relativePath);

            if (! @rename($tempPath, $finalPath)) {
                throw new \RuntimeException('Gagal menyimpan file hasil dekripsi.');
            }

            $file = DB::transaction(function () use ($user, $name, $storedName, $extension, $result, $relativePath, $finalPath) {
                $file = File::create([
                    'user_id' => $user->id,
                    'original_name' => $name,
                    'stored_name' => $storedName,
                    'original_extension' => $extension,
                    'mime_type' => config('securefile.allowed_mimes.' . $extension . '.0'),
                    'original_size' => $result['size'],
                    'processed_size' => $result['size'],
                    'storage_path' => $relativePath,
                    'status' => File::STATUS_DECRYPTED,
                ]);

                $this->logger->record($user, 'Dekripsi File', 'success', $file, description: 'File berhasil didekripsi.');
                $this->notifier->send($user, 'Dekripsi berhasil', 'File berhasil didekripsi.', 'success');

                return $file;
            });
        } catch (Throwable $e) {
            foreach ([$tempPath, $finalPath] as $path) {
                if ($path && is_file($path)) {
                    @unlink($path);
                }
            }

            if ($e instanceof InvalidKeyException) {
                $message = 'Password atau kunci tidak valid.';
                $reason = 'Password atau kunci tidak valid.';
            } elseif ($e instanceof CorruptedDataException) {
                $message = 'File tidak dapat didekripsi.';
                $reason = 'File rusak atau bukan file terenkripsi.';
            } else {
                report($e);
                $message = 'File tidak dapat didekripsi. Periksa file dan password/kunci.';
                $reason = 'Kesalahan saat memproses file.';
            }

            $this->logger->record($user, 'Dekripsi File', 'failed', null, $uploadName, $uploadSize, $reason);
            $this->notifier->send($user, 'Dekripsi gagal', 'File gagal diproses.', 'error');

            return back()->withErrors(['process' => $message]);
        }

        return redirect()->route($user->routeName('decrypt.index'))->with('result', ['file_id' => $file->id]);
    }
}
