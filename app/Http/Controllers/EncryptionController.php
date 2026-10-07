<?php

namespace App\Http\Controllers;

use App\Http\Requests\EncryptFileRequest;
use App\Models\File;
use App\Services\ActivityLogger;
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

class EncryptionController extends Controller
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

        return view('encryption.index', ['result' => $result]);
    }

    public function store(EncryptFileRequest $request): RedirectResponse
    {
        @set_time_limit(0);

        $user = $request->user();
        $upload = $request->file('file');

        $originalName = Format::safeFileName($upload->getClientOriginalName());
        $extension = strtolower($upload->getClientOriginalExtension());
        $mime = $upload->getMimeType();
        $originalSize = (int) $upload->getSize();

        $disk = Storage::disk(config('securefile.disk'));
        $directory = config('securefile.directories.encrypted');
        $storedName = Str::uuid() . '.' . config('securefile.encrypted_extension');
        $relativePath = $directory . '/' . $storedName;

        $disk->makeDirectory($directory);
        $absolutePath = $disk->path($relativePath);

        try {
            $result = $this->crypto->encryptFile(
                $upload->getRealPath(),
                $absolutePath,
                $originalName,
                (string) $request->input('file_password'),
            );

            $file = DB::transaction(function () use ($user, $originalName, $storedName, $extension, $mime, $result, $relativePath) {
                $file = File::create([
                    'user_id' => $user->id,
                    'original_name' => $originalName,
                    'stored_name' => $storedName,
                    'original_extension' => $extension,
                    'mime_type' => $mime,
                    'original_size' => $result['original_size'],
                    'processed_size' => $result['processed_size'],
                    'storage_path' => $relativePath,
                    'status' => File::STATUS_ENCRYPTED,
                ]);

                $this->logger->record($user, 'Enkripsi File', 'success', $file, description: 'File berhasil dienkripsi.');
                $this->notifier->send($user, 'Enkripsi berhasil', 'File berhasil dienkripsi.', 'success');

                return $file;
            });
        } catch (Throwable $e) {
            report($e);

            if (is_file($absolutePath)) {
                @unlink($absolutePath);
            }

            $this->logger->record($user, 'Enkripsi File', 'failed', null, $originalName, $originalSize, 'Proses enkripsi gagal.');
            $this->notifier->send($user, 'Enkripsi gagal', 'File gagal diproses.', 'error');

            return back()
                ->withErrors(['process' => 'Proses enkripsi gagal. Silakan coba kembali.'])
                ->withInput($request->except(['file', 'file_password', 'file_password_confirmation']));
        }

        return redirect()->route($user->routeName('encrypt.index'))->with('result', ['file_id' => $file->id]);
    }
}
