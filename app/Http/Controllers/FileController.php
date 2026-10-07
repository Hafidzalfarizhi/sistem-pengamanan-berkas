<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    private const SORTABLE = ['created_at', 'original_name', 'original_size', 'original_extension', 'status'];

    public function __construct(private ActivityLogger $logger, private NotificationService $notifier)
    {
    }

    public function index(Request $request): View
    {
        // user_id SELALU dari pengguna yang sedang login, bukan dari request.
        $query = File::query()->where('user_id', $request->user()->id);

        if ($term = trim((string) $request->query('q'))) {
            $query->where('original_name', 'like', '%' . addcslashes($term, '%_\\') . '%');
        }
        if (in_array($request->query('status'), [File::STATUS_ENCRYPTED, File::STATUS_DECRYPTED], true)) {
            $query->where('status', $request->query('status'));
        }
        if (in_array($request->query('type'), config('securefile.allowed_extensions'), true)) {
            $query->where('original_extension', $request->query('type'));
        }

        $sort = in_array($request->query('sort'), self::SORTABLE, true) ? $request->query('sort') : 'created_at';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $files = $query->orderBy($sort, $dir)->orderBy('id', 'desc')
            ->paginate(config('securefile.per_page'))->withQueryString();

        return view('files.index', ['files' => $files, 'types' => config('securefile.allowed_extensions')]);
    }

    public function show(File $file): View
    {
        Gate::authorize('view', $file);

        return view('files.show', ['file' => $file->load('user:id,name,username')]);
    }

    public function download(Request $request, File $file): StreamedResponse
    {
        Gate::authorize('download', $file);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk(config('securefile.disk'));
        $path = $file->storage_path;

        // Perlindungan path traversal: path harus berada di dalam direktori yang dikenal.
        $allowedDirs = array_values(config('securefile.directories'));
        abort_if(
            str_contains($path, '..') || ! in_array(explode('/', $path)[0] ?? '', $allowedDirs, true),
            404
        );
        abort_unless($disk->exists($path), 404, 'File tidak ditemukan.');

        $this->logger->record($request->user(), 'Download File', 'success', $file, description: 'File diunduh.');

        return $disk->download($path, $file->download_name, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Request $request, File $file): RedirectResponse
    {
        Gate::authorize('delete', $file);

        $user = $request->user();
        $name = $file->original_name;
        $size = $file->original_size;

        Storage::disk(config('securefile.disk'))->delete($file->storage_path);

        // Catat sebelum dihapus; file_name/file_size menjadi snapshot riwayat.
        $this->logger->record($user, 'Hapus File', 'success', $file, $name, $size, 'File dihapus.');
        $file->delete();

        $this->notifier->send($user, 'File dihapus', 'File "' . $name . '" berhasil dihapus.', 'info');

        return redirect()->route($user->routeName('files.index'))->with('success', 'File berhasil dihapus.');
    }
}
